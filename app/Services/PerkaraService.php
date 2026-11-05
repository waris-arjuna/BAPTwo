<?php

namespace App\Services;

use App\Models\Alamat;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\Queries\PerkaraListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PerkaraService
{
    public function __construct(
        protected AuditTrailService $auditTrail,
        protected WilayahService $wilayahService,
        protected ?DokumenService $dokumenService = null,
        protected ?PerkaraListQuery $listQuery = null,
        protected ?AlamatService $alamatService = null,
        protected ?BerkasPerkaraService $berkasPerkaraService = null
    ) {
        $this->dokumenService = $dokumenService ?? app(DokumenService::class);
        $this->listQuery = $listQuery ?? app(PerkaraListQuery::class);
        $this->alamatService = $alamatService ?? app(AlamatService::class);
        $this->berkasPerkaraService = $berkasPerkaraService ?? app(BerkasPerkaraService::class);
    }

    /**
     * Normalisasi filter dari Request atau array
     *
     * @return array<string, mixed>
     */
    protected function normalizeFilters(mixed $requestOrFilters): array
    {
        if (is_object($requestOrFilters) && method_exists($requestOrFilters, 'all')) {
            return $requestOrFilters->all();
        }

        return (array) $requestOrFilters;
    }

    /**
     * Daftar Laporan Kejadian dengan filter dan paginasi
     */
    public function getLaporanKejadianList(mixed $requestOrFilters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->listQuery->getLaporanKejadianList($this->normalizeFilters($requestOrFilters), $perPage);
    }

    /**
     * Hitung ringkasan status Laporan Kejadian untuk cards
     *
     * @return array{total: int, draft: int, final: int, selesai: int}
     */
    public function getLaporanKejadianSummary(): array
    {
        return $this->listQuery->getLaporanKejadianSummary();
    }

    /**
     * Daftar Sprindik dengan filter dan paginasi
     */
    public function getSprindikList(mixed $requestOrFilters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->listQuery->getSprindikList($this->normalizeFilters($requestOrFilters), $perPage);
    }

    /**
     * Hitung ringkasan status Sprindik untuk cards
     *
     * @return array{total: int, draft: int, final: int, selesai: int}
     */
    public function getSprindikSummary(): array
    {
        return $this->listQuery->getSprindikSummary();
    }

    /**
     * Laporan Kejadian yang sudah final dan belum memiliki Sprindik untuk dropdown tambah Sprindik
     *
     * @return Collection<int, Perkara>
     */
    public function getAvailableForSprindik(): Collection
    {
        return $this->listQuery->getAvailableForSprindik();
    }

    /**
     * Buat Laporan Kejadian baru (mendukung Draft maupun Simpan & Lanjut)
     *
     * @param  array{action_type?: string, nomor_laporan?: ?string, tanggal_laporan?: ?string, deskripsi?: ?string}  $data
     */
    public function createLaporanKejadian(array $data, ?UploadedFile $file, User $user): Perkara
    {
        return DB::transaction(function () use ($data, $file, $user) {
            $isFinal = ($data['action_type'] ?? 'lanjut') === 'lanjut';

            $perkara = Perkara::create([
                'nomor_laporan_kejadian' => $data['nomor_laporan'] ?? null,
                'tanggal_laporan_kejadian' => $data['tanggal_laporan'] ?? null,
                'tahap_laporan_status' => $isFinal ? 'final' : 'draft',
                'laporan_locked_at' => $isFinal ? now() : null,
                'laporan_finalized_by' => $isFinal ? $user->id : null,
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
     * Update Laporan Kejadian (hanya saat masih Draft; terkunci mutlak jika sudah Final)
     *
     * @param  array{action_type?: string, nomor_laporan?: ?string, tanggal_laporan?: ?string, deskripsi?: ?string}  $data
     */
    public function updateLaporanKejadian(Perkara $perkara, array $data, ?UploadedFile $file, User $user): Perkara
    {
        if ($perkara->isLaporanLocked()) {
            abort(403, 'Tahap Laporan Kejadian sudah difinalkan dan terkunci secara permanen.');
        }

        return DB::transaction(function () use ($perkara, $data, $file, $user) {
            $oldData = $perkara->toArray();
            $isFinal = ($data['action_type'] ?? 'draft') === 'lanjut';

            $updatePayload = [
                'nomor_laporan_kejadian' => $data['nomor_laporan'] ?? $perkara->nomor_laporan_kejadian,
                'tanggal_laporan_kejadian' => $data['tanggal_laporan'] ?? $perkara->tanggal_laporan_kejadian,
                'deskripsi' => $data['deskripsi'] ?? null,
                'updated_by' => $user->id,
            ];

            if ($isFinal) {
                $updatePayload['tahap_laporan_status'] = 'final';
                $updatePayload['laporan_locked_at'] = now();
                $updatePayload['laporan_finalized_by'] = $user->id;
            }

            $perkara->update($updatePayload);

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
     * Kaitkan / Buat Sprindik pada Perkara (mendukung Draft maupun Simpan & Lanjut)
     *
     * @param  array{action_type?: string, nomor_sprindik?: ?string, tanggal_sprindik?: ?string, deskripsi?: ?string}  $data
     */
    public function attachSprindik(Perkara $perkara, array $data, ?UploadedFile $file, User $user): Perkara
    {
        return DB::transaction(function () use ($perkara, $data, $file, $user) {
            if (! empty($perkara->nomor_surat_perintah) || ! empty($perkara->tahap_sprindik_status)) {
                throw new \DomainException('Laporan kejadian ini sudah terhubung ke Sprindik lain. Satu Laporan kejadian hanya boleh digunakan untuk satu Sprindik.');
            }

            $oldData = $perkara->toArray();
            $isFinal = ($data['action_type'] ?? 'lanjut') === 'lanjut';

            // Jika laporan kejadian belum terkunci saat sprindik dibuat, otomatis kunci laporan kejadian
            $laporanLockedAt = $perkara->laporan_locked_at ?? now();
            $laporanFinalizedBy = $perkara->laporan_finalized_by ?? $user->id;

            $perkara->update([
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => $laporanLockedAt,
                'laporan_finalized_by' => $laporanFinalizedBy,
                'nomor_surat_perintah' => $data['nomor_sprindik'] ?? null,
                'tanggal_surat_perintah' => $data['tanggal_sprindik'] ?? null,
                'tahap_sprindik_status' => $isFinal ? 'final' : 'draft',
                'sprindik_locked_at' => $isFinal ? now() : null,
                'sprindik_finalized_by' => $isFinal ? $user->id : null,
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
     * Update Sprindik pada Perkara (hanya saat masih Draft; terkunci mutlak jika sudah Final)
     *
     * @param  array{action_type?: string, nomor_sprindik?: ?string, tanggal_sprindik?: ?string, deskripsi?: ?string}  $data
     */
    public function updateSprindik(Perkara $perkara, array $data, ?UploadedFile $file, User $user): Perkara
    {
        if ($perkara->isSprindikLocked()) {
            abort(403, 'Tahap Sprindik sudah difinalkan dan terkunci secara permanen.');
        }

        return DB::transaction(function () use ($perkara, $data, $file, $user) {
            $oldData = $perkara->toArray();
            $isFinal = ($data['action_type'] ?? 'draft') === 'lanjut';

            $updatePayload = [
                'nomor_surat_perintah' => $data['nomor_sprindik'] ?? $perkara->nomor_surat_perintah,
                'tanggal_surat_perintah' => $data['tanggal_sprindik'] ?? $perkara->tanggal_surat_perintah,
                'deskripsi' => $data['deskripsi'] ?? $perkara->deskripsi,
                'updated_by' => $user->id,
            ];

            if ($isFinal) {
                $updatePayload['tahap_sprindik_status'] = 'final';
                $updatePayload['sprindik_locked_at'] = now();
                $updatePayload['sprindik_finalized_by'] = $user->id;
            }

            $perkara->update($updatePayload);

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
     * Pulihkan (restore) Laporan Kejadian & Perkara yang di-soft-delete
     */
    public function restorePerkara(int $id, User $user): Perkara
    {
        return DB::transaction(function () use ($id, $user) {
            $perkara = Perkara::onlyTrashed()->findOrFail($id);
            $perkara->restore();
            $perkara->dokumen()->restore();
            $perkara->tersangkas()->restore();
            $perkara->berkasPerkaras()->restore();

            $this->auditTrail->log(
                aksi: 'restore_perkara',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: null,
                dataBaru: $perkara->fresh()?->toArray(),
                perkaraId: $perkara->id,
                userId: $user->id
            );

            return $perkara;
        });
    }

    /**
     * Pulihkan (restore) Berkas Perkara yang di-soft-delete
     */
    public function restoreBerkas(int $id, User $user): BerkasPerkara
    {
        return DB::transaction(function () use ($id, $user) {
            $berkas = BerkasPerkara::onlyTrashed()->findOrFail($id);
            $berkas->restore();

            if ($berkas->tersangka_id) {
                Tersangka::withTrashed()->find($berkas->tersangka_id)?->restore();
            }

            $this->auditTrail->log(
                aksi: 'restore_berkas_perkara',
                tabel: 'berkas_perkara',
                recordId: $berkas->id,
                dataLama: null,
                dataBaru: $berkas->fresh()?->toArray(),
                perkaraId: $berkas->perkara_id,
                berkasPerkaraId: $berkas->id,
                userId: $user->id
            );

            return $berkas;
        });
    }

    /**
     * Simpan / Finalkan Data Pembentukan Perkara & Berkas Perkara Pra-SPDP:
     * TKP (F-07), Tim PPNS Utama & Anggota (F-08), Tersangka (F-03),
     * 1 Tersangka = 1 Berkas Perkara (F-02), Nomor Berkas Manual (F-33), dan Pasal Berlapis (F-04).
     *
     * @param  array<string, mixed>  $data
     */
    public function savePerkaraBerkas(Perkara $perkara, array $data, User $user): Perkara
    {
        return $this->berkasPerkaraService->savePerkaraBerkas($perkara, $data, $user);
    }

    /**
     * Resolusi payload alamat (TKP / Tersangka) dengan auto-fill nama wilayah via WilayahService
     *
     * @param  array<string, mixed>  $data
     * @return array<string, ?string>
     */
    public function resolveAlamatPayload(array $data, ?Alamat $existing = null, string $prefix = ''): array
    {
        return $this->alamatService->resolveAlamatPayload($data, $existing, $prefix);
    }

    /**
     * Simpan file dokumen PDF ke storage privat
     */
    protected function storeDokumen(Perkara $perkara, UploadedFile $file, string $kategori, User $user): Dokumen
    {
        return $this->dokumenService->simpan(
            $file,
            $kategori,
            [
                'perkara_id' => $perkara->id,
                'nama_dokumen' => $file->getClientOriginalName(),
            ],
            $user
        );
    }

    /**
     * Hitung ringkasan 5 card informasi di halaman Data Perkara
     * Menghasilkan jumlah yang konsisten: Dalam Proses + DPO Aktif + Selesai + Tidak Selesai == Total
     *
     * @return array{total: int, dalam_proses: int, dpo_aktif: int, selesai: int, tidak_selesai: int}
     */
    public function getDataPerkaraSummaryCards(): array
    {
        return $this->listQuery->getDataPerkaraSummaryCards();
    }

    /**
     * Hitung 4 kartu metrik kesiapan pelimpahan (Poin 10 & 11)
     */
    public function getPotensiTerlimpahCards(): array
    {
        return $this->listQuery->getPotensiTerlimpahCards();
    }

    /**
     * Ambil daftar PPNS Utama yang memiliki perkara untuk filter dropdown (urut abjad)
     *
     * @return Collection<int, User>
     */
    public function getAvailablePpnsForFilter(): Collection
    {
        return $this->listQuery->getAvailablePpnsForFilter();
    }

    /**
     * Ambil daftar Perkara untuk halaman Data Perkara
     */
    public function getDataPerkaraList(mixed $requestOrFilters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->listQuery->getDataPerkaraList($this->normalizeFilters($requestOrFilters), $perPage);
    }

    /**
     * Ambil Sprindik final yang siap/belum dibuatkan Perkara
     *
     * @return Collection<int, Perkara>
     */
    public function getAvailableForPerkara(): Collection
    {
        return $this->listQuery->getAvailableForPerkara();
    }

    /**
     * Ambil daftar Berkas Perkara untuk monitoring Progress Perkara
     */
    public function getProgressPerkaraList(mixed $requestOrFilters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->listQuery->getProgressPerkaraList($this->normalizeFilters($requestOrFilters), $perPage);
    }
}
