<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\Perkara;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerkaraService
{
    public function __construct(
        protected AuditTrailService $auditTrail
    ) {}

    /**
     * Daftar Laporan Kejadian dengan filter dan paginasi
     */
    public function getLaporanKejadianList(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $this->ensureBaselinePerkara();

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return Perkara::query()
            ->with(['dokumenLaporan', 'creator'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_laporan_kejadian', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('dokumenLaporan', function (Builder $docQ) use ($search) {
                            $docQ->where('nama_file_asli', 'like', "%{$search}%")
                                ->orWhere('nama_dokumen', 'like', "%{$search}%");
                        });

                    $lowerSearch = strtolower($search);
                    if (str_contains('belum dilanjutkan', $lowerSearch)) {
                        $q->orWhereNull('nomor_surat_perintah');
                    }
                    if (str_contains('sprindik tersedia', $lowerSearch)) {
                        $q->orWhereNotNull('nomor_surat_perintah');
                    }
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Belum Dilanjutkan') {
                    $query->whereNull('nomor_surat_perintah');
                } elseif ($status === 'Sprindik Tersedia') {
                    $query->whereNotNull('nomor_surat_perintah');
                }
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_laporan_kejadian', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_laporan_kejadian', '<=', $tanggalSampai);
            })
            ->orderByDesc('tanggal_laporan_kejadian')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Daftar Sprindik dengan filter dan paginasi
     */
    public function getSprindikList(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $this->ensureBaselinePerkara();

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return Perkara::query()
            ->whereNotNull('nomor_surat_perintah')
            ->with(['dokumenSprindik', 'dokumenLaporan', 'creator'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_surat_perintah', 'like', "%{$search}%")
                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('dokumenSprindik', function (Builder $docQ) use ($search) {
                            $docQ->where('nama_file_asli', 'like', "%{$search}%")
                                ->orWhere('nama_dokumen', 'like', "%{$search}%");
                        });

                    $lowerSearch = strtolower($search);
                    if (str_contains('belum menjadi perkara', $lowerSearch)) {
                        $q->orWhereNotNull('nomor_surat_perintah');
                    }
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Belum Menjadi Perkara') {
                    $query->whereNotNull('nomor_surat_perintah');
                }
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat_perintah', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat_perintah', '<=', $tanggalSampai);
            })
            ->orderByDesc('tanggal_surat_perintah')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Laporan Kejadian yang belum memiliki Sprindik untuk dropdown tambah Sprindik
     *
     * @return Collection<int, Perkara>
     */
    public function getAvailableForSprindik(): Collection
    {
        return Perkara::query()
            ->whereNull('nomor_surat_perintah')
            ->orderByDesc('tanggal_laporan_kejadian')
            ->get();
    }

    /**
     * Buat Laporan Kejadian baru
     *
     * @param  array{nomor_laporan: string, tanggal_laporan: string, deskripsi?: ?string}  $data
     */
    public function createLaporanKejadian(array $data, ?UploadedFile $file, User $user): Perkara
    {
        return DB::transaction(function () use ($data, $file, $user) {
            $perkara = Perkara::create([
                'nomor_laporan_kejadian' => $data['nomor_laporan'],
                'tanggal_laporan_kejadian' => $data['tanggal_laporan'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            if ($file instanceof UploadedFile) {
                $this->storeDokumen($perkara, $file, 'lapkej', $user);
            }

            $this->auditTrail->log(
                aksi: 'create_laporan_kejadian',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: null,
                dataBaru: $perkara->toArray(),
                perkaraId: $perkara->id,
                userId: $user->id
            );

            return $perkara;
        });
    }

    /**
     * Update Laporan Kejadian
     *
     * @param  array{nomor_laporan: string, tanggal_laporan: string, deskripsi?: ?string}  $data
     */
    public function updateLaporanKejadian(Perkara $perkara, array $data, ?UploadedFile $file, User $user): Perkara
    {
        return DB::transaction(function () use ($perkara, $data, $file, $user) {
            $oldData = $perkara->toArray();

            $perkara->update([
                'nomor_laporan_kejadian' => $data['nomor_laporan'],
                'tanggal_laporan_kejadian' => $data['tanggal_laporan'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'updated_by' => $user->id,
            ]);

            if ($file instanceof UploadedFile) {
                $this->storeDokumen($perkara, $file, 'lapkej', $user);
            }

            $this->auditTrail->log(
                aksi: 'update_laporan_kejadian',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: $oldData,
                dataBaru: $perkara->fresh()->toArray(),
                perkaraId: $perkara->id,
                userId: $user->id
            );

            return $perkara;
        });
    }

    /**
     * Kaitkan / Buat Sprindik pada Perkara
     *
     * @param  array{nomor_sprindik: string, tanggal_sprindik: string, deskripsi?: ?string}  $data
     */
    public function attachSprindik(Perkara $perkara, array $data, ?UploadedFile $file, User $user): Perkara
    {
        return DB::transaction(function () use ($perkara, $data, $file, $user) {
            $oldData = $perkara->toArray();

            $perkara->update([
                'nomor_surat_perintah' => $data['nomor_sprindik'],
                'tanggal_surat_perintah' => $data['tanggal_sprindik'],
                'deskripsi' => $data['deskripsi'] ?? $perkara->deskripsi,
                'updated_by' => $user->id,
            ]);

            if ($file instanceof UploadedFile) {
                $this->storeDokumen($perkara, $file, 'sprindik', $user);
            }

            $this->auditTrail->log(
                aksi: 'attach_sprindik',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: $oldData,
                dataBaru: $perkara->fresh()->toArray(),
                perkaraId: $perkara->id,
                userId: $user->id
            );

            return $perkara;
        });
    }

    /**
     * Update Sprindik pada Perkara
     *
     * @param  array{nomor_sprindik: string, tanggal_sprindik: string, deskripsi?: ?string}  $data
     */
    public function updateSprindik(Perkara $perkara, array $data, ?UploadedFile $file, User $user): Perkara
    {
        return DB::transaction(function () use ($perkara, $data, $file, $user) {
            $oldData = $perkara->toArray();

            $perkara->update([
                'nomor_surat_perintah' => $data['nomor_sprindik'],
                'tanggal_surat_perintah' => $data['tanggal_sprindik'],
                'deskripsi' => $data['deskripsi'] ?? $perkara->deskripsi,
                'updated_by' => $user->id,
            ]);

            if ($file instanceof UploadedFile) {
                $this->storeDokumen($perkara, $file, 'sprindik', $user);
            }

            $this->auditTrail->log(
                aksi: 'update_sprindik',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: $oldData,
                dataBaru: $perkara->fresh()->toArray(),
                perkaraId: $perkara->id,
                userId: $user->id
            );

            return $perkara;
        });
    }

    /**
     * Soft delete Laporan Kejadian (dan Perkara)
     */
    public function deleteLaporanKejadian(Perkara $perkara, User $user): void
    {
        DB::transaction(function () use ($perkara, $user) {
            $oldData = $perkara->toArray();

            $this->auditTrail->log(
                aksi: 'delete_laporan_kejadian',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: $oldData,
                dataBaru: null,
                perkaraId: $perkara->id,
                userId: $user->id
            );

            $perkara->dokumen()->delete();
            $perkara->delete();
        });
    }

    /**
     * Hapus / Reset Sprindik dari Perkara
     */
    public function removeSprindik(Perkara $perkara, User $user): void
    {
        DB::transaction(function () use ($perkara, $user) {
            $oldData = $perkara->toArray();

            $perkara->update([
                'nomor_surat_perintah' => null,
                'tanggal_surat_perintah' => null,
                'updated_by' => $user->id,
            ]);

            // Soft delete dokumen sprindik
            $perkara->dokumen()->where('kategori', 'sprindik')->delete();

            $this->auditTrail->log(
                aksi: 'remove_sprindik',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: $oldData,
                dataBaru: $perkara->fresh()->toArray(),
                perkaraId: $perkara->id,
                userId: $user->id
            );
        });
    }

    /**
     * Simpan file dokumen PDF ke storage privat
     */
    protected function storeDokumen(Perkara $perkara, UploadedFile $file, string $kategori, User $user): Dokumen
    {
        $path = $file->store("dokumen/{$kategori}", 'local');

        return Dokumen::create([
            'perkara_id' => $perkara->id,
            'kategori' => $kategori,
            'nama_dokumen' => $file->getClientOriginalName(),
            'nama_file_asli' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize() ?: 0,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * Pastikan baseline perkara terisi jika database kosong (misal saat testing dengan RefreshDatabase)
     */
    public function ensureBaselinePerkara(): void
    {
        if (Perkara::count() > 0) {
            return;
        }

        $samplePerkara = [
            [
                'id' => 1,
                'nomor_laporan_kejadian' => 'LK/001/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-02',
                'nomor_surat_perintah' => null,
                'tanggal_surat_perintah' => null,
                'deskripsi' => 'Dugaan peredaran sediaan farmasi tanpa izin edar.',
            ],
            [
                'id' => 2,
                'nomor_laporan_kejadian' => 'LK/002/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-04',
                'nomor_surat_perintah' => 'SP/001/IX/2026',
                'tanggal_surat_perintah' => '2026-09-05',
                'deskripsi' => 'Dugaan peredaran kosmetik mengandung bahan berbahaya.',
            ],
            [
                'id' => 3,
                'nomor_laporan_kejadian' => 'LK/003/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-05',
                'nomor_surat_perintah' => 'SP/002/IX/2026',
                'tanggal_surat_perintah' => '2026-09-06',
                'deskripsi' => 'Dugaan pangan olahan tanpa izin edar skala rumahan.',
            ],
            [
                'id' => 4,
                'nomor_laporan_kejadian' => 'LK/004/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-08',
                'nomor_surat_perintah' => null,
                'tanggal_surat_perintah' => null,
                'deskripsi' => 'Dugaan obat tradisional ilegal mengandung BKO.',
            ],
            [
                'id' => 5,
                'nomor_laporan_kejadian' => 'LK/005/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-10',
                'nomor_surat_perintah' => 'SP/003/IX/2026',
                'tanggal_surat_perintah' => '2026-09-11',
                'deskripsi' => 'Dugaan suplemen kesehatan palsu secara daring.',
            ],
        ];

        foreach ($samplePerkara as $item) {
            Perkara::create($item);
        }
    }
}
