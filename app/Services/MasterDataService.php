<?php

namespace App\Services;

use App\Models\Bidang;
use App\Models\InstansiHukum;
use App\Models\Jabatan;
use App\Models\JaksaPenuntut;
use App\Models\Panggol;
use App\Models\Pasal;
use Illuminate\Database\Eloquent\Collection;
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
            'description' => 'Kelola daftar pasal dan undang-undang untuk sangkaan berlapis berkas perkara.',
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

    public function list(string $type, string $search = '', string $status = ''): Collection
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

        return match ($type) {
            'jabatan' => Jabatan::withTrashed()
                ->when($search !== '', function ($q) use ($search, $matchesActive, $matchesInactive) {
                    $q->where(function ($sub) use ($search, $matchesActive, $matchesInactive) {
                        $sub->where('nama_jabatan', 'like', "%{$search}%");
                        if ($matchesActive) {
                            $sub->orWhereNull('deleted_at');
                        }
                        if ($matchesInactive) {
                            $sub->orWhereNotNull('deleted_at');
                        }
                    });
                })
                ->tap($applyStatus)
                ->orderBy('nama_jabatan')
                ->get(),
            'bidang' => Bidang::withTrashed()
                ->when($search !== '', function ($q) use ($search, $matchesActive, $matchesInactive) {
                    $q->where(function ($sub) use ($search, $matchesActive, $matchesInactive) {
                        $sub->where('nama_bidang', 'like', "%{$search}%");
                        if ($matchesActive) {
                            $sub->orWhereNull('deleted_at');
                        }
                        if ($matchesInactive) {
                            $sub->orWhereNotNull('deleted_at');
                        }
                    });
                })
                ->tap($applyStatus)
                ->orderBy('nama_bidang')
                ->get(),
            'panggol' => Panggol::withTrashed()
                ->when($search !== '', function ($q) use ($search, $matchesActive, $matchesInactive) {
                    $q->where(function ($sub) use ($search, $matchesActive, $matchesInactive) {
                        $sub->where('pangkat', 'like', "%{$search}%")
                            ->orWhere('golongan', 'like', "%{$search}%");
                        if ($matchesActive) {
                            $sub->orWhereNull('deleted_at');
                        }
                        if ($matchesInactive) {
                            $sub->orWhereNotNull('deleted_at');
                        }
                    });
                })
                ->tap($applyStatus)
                ->orderBy('golongan')
                ->get(),
            'pasal' => Pasal::withTrashed()
                ->when($search !== '', function ($q) use ($search, $matchesActive, $matchesInactive) {
                    $q->where(function ($sub) use ($search, $matchesActive, $matchesInactive) {
                        $sub->where('nama_pasal', 'like', "%{$search}%")
                            ->orWhere('pasal', 'like', "%{$search}%")
                            ->orWhere('isi', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                        if ($matchesActive) {
                            $sub->orWhere(fn ($p) => $p->whereNull('deleted_at')->where('status', 'aktif'));
                        }
                        if ($matchesInactive) {
                            $sub->orWhere(fn ($p) => $p->whereNotNull('deleted_at')->orWhere('status', 'nonaktif'));
                        }
                    });
                })
                ->tap($applyStatus)
                ->orderBy('nama_pasal')
                ->orderBy('pasal')
                ->get(),
            'instansi-hukum' => InstansiHukum::withTrashed()
                ->when($search !== '', function ($q) use ($search, $matchesActive, $matchesInactive) {
                    $q->where(function ($sub) use ($search, $matchesActive, $matchesInactive) {
                        $sub->where('nama', 'like', "%{$search}%")
                            ->orWhere('wilayah', 'like', "%{$search}%")
                            ->orWhere('jenis', 'like', "%{$search}%");
                        if ($matchesActive) {
                            $sub->orWhereNull('deleted_at');
                        }
                        if ($matchesInactive) {
                            $sub->orWhereNotNull('deleted_at');
                        }
                    });
                })
                ->tap($applyStatus)
                ->orderBy('jenis')
                ->orderBy('nama')
                ->get(),
            'jaksa-penuntut' => JaksaPenuntut::withTrashed()
                ->with('instansiHukum')
                ->when($search !== '', function ($q) use ($search, $matchesActive, $matchesInactive) {
                    $q->where(function ($sub) use ($search, $matchesActive, $matchesInactive) {
                        $sub->where('nama_jaksa', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
                        if ($matchesActive) {
                            $sub->orWhereNull('deleted_at');
                        }
                        if ($matchesInactive) {
                            $sub->orWhereNotNull('deleted_at');
                        }
                    });
                })
                ->tap($applyStatus)
                ->orderBy('nama_jaksa')
                ->get(),
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
