<?php

namespace App\Services;

use App\Models\Bidang;
use App\Models\InstansiHukum;
use App\Models\Jabatan;
use App\Models\JaksaPenuntut;
use App\Models\Panggol;
use App\Models\Pasal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MasterDataService
{
    public const TYPES = [
        'jabatan' => [
            'title' => 'Master Jabatan',
            'singular' => 'Jabatan',
            'description' => 'Kelola daftar jabatan pegawai dan penyidik (PPNS).',
        ],
        'bidang' => [
            'title' => 'Master Bidang / Unit',
            'singular' => 'Bidang',
            'description' => 'Kelola daftar bidang atau bagian organisasi.',
        ],
        'panggol' => [
            'title' => 'Master Pangkat & Golongan',
            'singular' => 'Pangkat & Golongan',
            'description' => 'Kelola referensi pangkat dan golongan ruang pegawai.',
        ],
        'pasal' => [
            'title' => 'Master Pasal & Undang-Undang',
            'singular' => 'Pasal / UU',
            'description' => 'Kelola daftar pasal dan undang-undang untuk sangkaan berkas perkara.',
        ],
        'instansi-hukum' => [
            'title' => 'Master Instansi Hukum',
            'singular' => 'Instansi Hukum',
            'description' => 'Kelola daftar Kejaksaan dan Pengadilan mitra penanganan perkara.',
        ],
        'jaksa-penuntut' => [
            'title' => 'Master Jaksa Penuntut Umum (JPU)',
            'singular' => 'Jaksa Penuntut',
            'description' => 'Kelola daftar Jaksa Penuntut Umum beserta asal Kejaksaan.',
        ],
    ];

    /**
     * @return array{title: string, singular: string, description: string}
     */
    public function getMeta(string $type): array
    {
        if (! isset(self::TYPES[$type])) {
            throw new NotFoundHttpException;
        }

        return self::TYPES[$type];
    }

    public function list(string $type, string $search = '', string $status = '', int $perPage = 10): LengthAwarePaginator
    {
        $this->getMeta($type);

        $applyStatus = function ($query) use ($type, $status) {
            if ($status === 'aktif') {
                $query->whereNull('deleted_at');
                if ($type === 'pasal') {
                    $query->where('status', 'aktif');
                }
            } elseif ($status === 'nonaktif') {
                if ($type === 'pasal') {
                    $query->where(fn ($q) => $q->whereNotNull('deleted_at')->orWhere('status', 'nonaktif'));
                } else {
                    $query->onlyTrashed();
                }
            }
        };

        $searchLower = strtolower($search);
        $matchesActive = $search !== '' && str_contains($searchLower, 'aktif') && ! str_contains($searchLower, 'non');
        $matchesInactive = $search !== '' && (str_contains($searchLower, 'nonaktif') || str_contains($searchLower, 'terhapus') || str_contains($searchLower, 'hapus'));

        $applySearch = function ($query, array $columns) use ($search, $matchesActive, $matchesInactive, $type) {
            if ($search === '') {
                return;
            }
            $query->where(function ($sub) use ($columns, $search, $matchesActive, $matchesInactive, $type) {
                foreach ($columns as $idx => $col) {
                    if ($idx === 0) {
                        $sub->where($col, 'like', "%{$search}%");
                    } else {
                        $sub->orWhere($col, 'like', "%{$search}%");
                    }
                }
                if ($matchesActive) {
                    if ($type === 'pasal') {
                        $sub->orWhere(fn ($p) => $p->whereNull('deleted_at')->where('status', 'aktif'));
                    } else {
                        $sub->orWhereNull('deleted_at');
                    }
                }
                if ($matchesInactive) {
                    if ($type === 'pasal') {
                        $sub->orWhere(fn ($p) => $p->whereNotNull('deleted_at')->orWhere('status', 'nonaktif'));
                    } else {
                        $sub->orWhereNotNull('deleted_at');
                    }
                }
            });
        };

        $paginate = fn ($builder) => $builder->paginate($perPage)->withQueryString();

        return match ($type) {
            'jabatan' => $paginate(Jabatan::withTrashed()->tap(fn ($q) => $applySearch($q, ['nama_jabatan']))->tap($applyStatus)->orderBy('nama_jabatan')),
            'bidang' => $paginate(Bidang::withTrashed()->tap(fn ($q) => $applySearch($q, ['nama_bidang']))->tap($applyStatus)->orderBy('nama_bidang')),
            'panggol' => $paginate(Panggol::withTrashed()->tap(fn ($q) => $applySearch($q, ['pangkat', 'golongan']))->tap($applyStatus)->orderBy('golongan')),
            'pasal' => $paginate(Pasal::withTrashed()->tap(fn ($q) => $applySearch($q, ['nama_pasal', 'pasal', 'isi', 'status']))->tap($applyStatus)->orderBy('nama_pasal')->orderBy('pasal')),
            'instansi-hukum' => $paginate(InstansiHukum::withTrashed()->tap(fn ($q) => $applySearch($q, ['nama', 'wilayah', 'jenis']))->tap($applyStatus)->orderBy('jenis')->orderBy('nama')),
            'jaksa-penuntut' => $paginate(JaksaPenuntut::withTrashed()->with('instansiHukum')->tap(fn ($q) => $applySearch($q, ['nama_jaksa', 'nip']))->tap($applyStatus)->orderBy('nama_jaksa')),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(string $type, array $data, ?int $actorId = null): Model
    {
        $modelClass = $this->resolveModelClass($type);

        if (in_array($type, ['pasal', 'instansi-hukum', 'jaksa-penuntut'], true)) {
            $data['created_by'] = $actorId;
            $data['updated_by'] = $actorId;
        }

        return $modelClass::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $type, int $id, array $data, ?int $actorId = null): Model
    {
        $modelClass = $this->resolveModelClass($type);
        /** @var Model $record */
        $record = $modelClass::withTrashed()->findOrFail($id);

        if (in_array($type, ['pasal', 'instansi-hukum', 'jaksa-penuntut'], true)) {
            $data['updated_by'] = $actorId;
        }

        $record->update($data);

        return $record;
    }

    public function softDelete(string $type, int $id): void
    {
        $modelClass = $this->resolveModelClass($type);
        /** @var Model $record */
        $record = $modelClass::findOrFail($id);
        $record->delete();
    }

    public function restore(string $type, int $id): void
    {
        $modelClass = $this->resolveModelClass($type);
        /** @var Model $record */
        $record = $modelClass::onlyTrashed()->findOrFail($id);
        $record->restore();
    }

    /**
     * @return class-string<Model>
     */
    private function resolveModelClass(string $type): string
    {
        $this->getMeta($type);

        return match ($type) {
            'jabatan' => Jabatan::class,
            'bidang' => Bidang::class,
            'panggol' => Panggol::class,
            'pasal' => Pasal::class,
            'instansi-hukum' => InstansiHukum::class,
            'jaksa-penuntut' => JaksaPenuntut::class,
        };
    }
}
