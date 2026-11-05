<?php

namespace App\Services;

use App\Models\Alamat;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\Tkp;
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
        protected ?PerkaraListQuery $listQuery = null
    ) {
        $this->dokumenService = $dokumenService ?? app(DokumenService::class);
        $this->listQuery = $listQuery ?? app(PerkaraListQuery::class);
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
        if ($perkara->isPerkaraLocked()) {
            abort(403, 'Data pembentukan perkara dan berkas pra-SPDP sudah difinalkan dan terkunci secara permanen.');
        }

        return DB::transaction(function () use ($perkara, $data, $user) {
            $oldData = $perkara->load(['tkp.alamat', 'ppnsUsers', 'tersangkas.alamat', 'tersangkas.berkasPerkara.pasals'])->toArray();
            $isFinal = ($data['action_type'] ?? 'lanjut') === 'lanjut';

            // 1. Simpan / Update Alamat & TKP (F-07, F-36, F-37)
            $tkpId = $perkara->tkp_id;
            $hasTkpInput = ! empty($data['tkp_nama'])
                || ! empty($data['tkp_provinsi_kode'])
                || ! empty($data['tkp_alamat_detail'])
                || ! empty($data['tkp_klasifikasi'])
                || ! empty($data['tkp_legalitas'])
                || ! empty($data['tkp_modus']);

            if ($hasTkpInput || $tkpId !== null) {
                $alamatPayload = $this->resolveAlamatPayload($data, $perkara->tkp?->alamat, 'tkp_');

                if ($perkara->tkp && $perkara->tkp->alamat) {
                    $perkara->tkp->alamat->update($alamatPayload);
                    $alamatId = $perkara->tkp->alamat_id;
                } else {
                    $alamat = Alamat::create($alamatPayload);
                    $alamatId = $alamat->id;
                }

                $tkpPayload = [
                    'alamat_id' => $alamatId,
                    'klasifikasi' => $data['tkp_klasifikasi'] ?? $perkara->tkp?->klasifikasi,
                    'legalitas' => $data['tkp_legalitas'] ?? $perkara->tkp?->legalitas,
                    'modus' => $data['tkp_modus'] ?? $perkara->tkp?->modus,
                    'updated_by' => $user->id,
                ];

                if ($perkara->tkp) {
                    $perkara->tkp->update($tkpPayload);
                    $tkpId = $perkara->tkp->id;
                } else {
                    $tkpPayload['created_by'] = $user->id;
                    $tkp = Tkp::create($tkpPayload);
                    $tkpId = $tkp->id;
                }
            }

            // 2. Sinkronisasi Tim PPNS Utama & Anggota (F-08)
            $ppnsSync = [];
            $utamaId = ! empty($data['ppns_utama_id']) ? (int) $data['ppns_utama_id'] : null;
            if ($utamaId !== null) {
                $ppnsSync[$utamaId] = ['peran' => 'utama'];
            }

            $anggotaIds = is_array($data['ppns_anggota_ids'] ?? null) ? $data['ppns_anggota_ids'] : [];
            foreach ($anggotaIds as $anggotaId) {
                $id = (int) $anggotaId;
                if ($id > 0 && $id !== $utamaId) {
                    $ppnsSync[$id] = ['peran' => 'anggota'];
                }
            }

            if ($ppnsSync !== [] || array_key_exists('ppns_utama_id', $data)) {
                $perkara->ppnsUsers()->sync($ppnsSync);
            }

            // 3. Simpan Tersangka -> 1 Berkas Perkara per Tersangka -> Pasal Berlapis (F-02, F-03, F-04, F-33)
            if (is_array($data['tersangka'] ?? null)) {
                $keptTersangkaIds = [];

                foreach ($data['tersangka'] as $tskData) {
                    $existingId = ! empty($tskData['id']) ? (int) $tskData['id'] : null;
                    $tersangka = null;

                    if ($existingId !== null) {
                        $tersangka = Tersangka::where('perkara_id', $perkara->id)->find($existingId);
                    }

                    // Simpan / Update Alamat Tersangka (PRD F-03)
                    $hasTskAlamat = ! empty($tskData['provinsi_kode'])
                        || ! empty($tskData['alamat_detail'])
                        || ! empty($tskData['kabupaten_kode'])
                        || ! empty($tskData['kecamatan_kode'])
                        || ! empty($tskData['desa_kode']);

                    $tskAlamatId = $tersangka?->alamat_id;

                    if ($hasTskAlamat || $tskAlamatId !== null) {
                        $existingTskAlamat = $tskAlamatId ? Alamat::find($tskAlamatId) : null;
                        $alamatPayload = $this->resolveAlamatPayload($tskData, $existingTskAlamat);

                        if ($existingTskAlamat instanceof Alamat) {
                            $existingTskAlamat->update($alamatPayload);
                        } else {
                            $newAlamat = Alamat::create($alamatPayload);
                            $tskAlamatId = $newAlamat->id;
                        }
                    }

                    $tskPayload = [
                        'perkara_id' => $perkara->id,
                        'nama' => $tskData['nama'] ?? null,
                        'alias' => $tskData['alias'] ?? null,
                        'bin_binti' => $tskData['bin_binti'] ?? null,
                        'nik' => $tskData['nik'] ?? null,
                        'tempat_lahir' => $tskData['tempat_lahir'] ?? null,
                        'tanggal_lahir' => ($tskData['tanggal_lahir'] ?? null) ?: null,
                        'jenis_kelamin' => ($tskData['jenis_kelamin'] ?? null) ?: null,
                        'agama' => ($tskData['agama'] ?? null) ?: null,
                        'pekerjaan' => $tskData['pekerjaan'] ?? null,
                        'kewarganegaraan' => $tskData['kewarganegaraan'] ?? 'WNI',
                        'alamat_id' => $tskAlamatId,
                        'updated_by' => $user->id,
                    ];

                    if ($tersangka instanceof Tersangka) {
                        $tersangka->update($tskPayload);
                    } else {
                        $tskPayload['created_by'] = $user->id;
                        $tersangka = Tersangka::create($tskPayload);
                    }

                    $keptTersangkaIds[] = $tersangka->id;

                    // 1 Tersangka = 1 Berkas Perkara (UNIQUE tersangka_id, Nomor Berkas manual tanpa pola - F-02, F-33)
                    $berkas = BerkasPerkara::withTrashed()
                        ->where('tersangka_id', $tersangka->id)
                        ->first();

                    $nomorBerkas = ! empty($tskData['nomor_berkas'])
                        ? $tskData['nomor_berkas']
                        : ($perkara->nomor_laporan_kejadian ?: 'BP-'.$perkara->id);

                    if ($berkas instanceof BerkasPerkara) {
                        if ($berkas->trashed()) {
                            $berkas->restore();
                        }
                        $berkas->update([
                            'perkara_id' => $perkara->id,
                            'nomor_berkas' => $nomorBerkas,
                            'updated_by' => $user->id,
                        ]);
                    } else {
                        $berkas = BerkasPerkara::create([
                            'perkara_id' => $perkara->id,
                            'tersangka_id' => $tersangka->id,
                            'nomor_berkas' => $nomorBerkas,
                            'status_formal' => null,
                            'status_proses' => 'Baru',
                            'progress_persen' => 0,
                            'is_dpo' => false,
                            'created_by' => $user->id,
                            'updated_by' => $user->id,
                        ]);
                    }

                    // Sinkronisasi Pasal Berlapis per Berkas Perkara (F-04)
                    $pasalIds = array_values(array_filter(
                        array_map('intval', is_array($tskData['pasal_ids'] ?? null) ? $tskData['pasal_ids'] : []),
                        fn (int $id): bool => $id > 0
                    ));
                    $berkas->pasals()->sync($pasalIds);
                }

                // Soft delete tersangka & berkas yang dihapus dari daftar saat masih draft
                $removedTersangkas = Tersangka::where('perkara_id', $perkara->id)
                    ->whereNotIn('id', $keptTersangkaIds)
                    ->get();

                foreach ($removedTersangkas as $removed) {
                    $removed->berkasPerkara?->delete();
                    $removed->delete();
                }
            }

            // 4. Update status tahapan perkara
            $perkara->update([
                'tkp_id' => $tkpId,
                'penuntut_umum_instansi_id' => ! empty($data['penuntut_umum_instansi_id']) ? (int) $data['penuntut_umum_instansi_id'] : null,
                'penuntut_umum_nama' => ! empty($data['penuntut_umum_nama']) ? trim($data['penuntut_umum_nama']) : null,
                'penuntut_umum_nip' => ! empty($data['penuntut_umum_nip']) ? trim($data['penuntut_umum_nip']) : null,
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => $perkara->laporan_locked_at ?? now(),
                'laporan_finalized_by' => $perkara->laporan_finalized_by ?? $user->id,
                'tahap_sprindik_status' => 'final',
                'sprindik_locked_at' => $perkara->sprindik_locked_at ?? now(),
                'sprindik_finalized_by' => $perkara->sprindik_finalized_by ?? $user->id,
                'tahap_perkara_status' => $isFinal ? 'final' : 'draft',
                'perkara_locked_at' => $isFinal ? now() : null,
                'perkara_finalized_by' => $isFinal ? $user->id : null,
                'updated_by' => $user->id,
            ]);

            $freshPerkara = $perkara->fresh(['tkp.alamat', 'ppnsUsers', 'tersangkas.berkasPerkara.pasals']);

            $this->auditTrail->log(
                aksi: $isFinal ? 'finalize_perkara_berkas' : 'save_draft_perkara_berkas',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: $oldData,
                dataBaru: $freshPerkara?->toArray(),
                perkaraId: $perkara->id,
                userId: $user->id
            );

            return $freshPerkara ?? $perkara;
        });
    }

    /**
     * Resolusi payload alamat (TKP / Tersangka) dengan auto-fill nama wilayah via WilayahService
     *
     * @param  array<string, mixed>  $data
     * @return array<string, ?string>
     */
    protected function resolveAlamatPayload(array $data, ?Alamat $existing = null, string $prefix = ''): array
    {
        $provKode = (string) ($data[$prefix.'provinsi_kode'] ?? $existing?->provinsi_kode ?? '');
        $kabKode = (string) ($data[$prefix.'kabupaten_kode'] ?? $existing?->kabupaten_kode ?? '');
        $kecKode = (string) ($data[$prefix.'kecamatan_kode'] ?? $existing?->kecamatan_kode ?? '');
        $desaKode = (string) ($data[$prefix.'desa_kode'] ?? $existing?->desa_kode ?? '');

        $provNama = (string) ($data[$prefix.'provinsi_nama'] ?? '');
        if ($provNama === '' && $provKode !== '') {
            foreach ($this->wilayahService->getProvinsi() as $item) {
                if ($item['code'] === $provKode) {
                    $provNama = $item['name'];
                    break;
                }
            }
        }

        $kabNama = (string) ($data[$prefix.'kabupaten_nama'] ?? '');
        if ($kabNama === '' && $provKode !== '' && $kabKode !== '') {
            foreach ($this->wilayahService->getKabupaten($provKode) as $item) {
                if ($item['code'] === $kabKode) {
                    $kabNama = $item['name'];
                    break;
                }
            }
        }

        $kecNama = (string) ($data[$prefix.'kecamatan_nama'] ?? '');
        if ($kecNama === '' && $kabKode !== '' && $kecKode !== '') {
            foreach ($this->wilayahService->getKecamatan($kabKode) as $item) {
                if ($item['code'] === $kecKode) {
                    $kecNama = $item['name'];
                    break;
                }
            }
        }

        $desaNama = (string) ($data[$prefix.'desa_nama'] ?? '');
        if ($desaNama === '' && $kecKode !== '' && $desaKode !== '') {
            foreach ($this->wilayahService->getDesa($kecKode) as $item) {
                if ($item['code'] === $desaKode) {
                    $desaNama = $item['name'];
                    break;
                }
            }
        }

        return [
            'nama' => $data[$prefix.'nama'] ?? $existing?->nama,
            'provinsi_kode' => $provKode ?: null,
            'provinsi_nama' => $provNama ?: ($existing?->provinsi_nama ?? null),
            'kabupaten_kode' => $kabKode ?: null,
            'kabupaten_nama' => $kabNama ?: ($existing?->kabupaten_nama ?? null),
            'kecamatan_kode' => $kecKode ?: null,
            'kecamatan_nama' => $kecNama ?: ($existing?->kecamatan_nama ?? null),
            'desa_kode' => $desaKode ?: null,
            'desa_nama' => $desaNama ?: ($existing?->desa_nama ?? null),
            'rt' => $data[$prefix.'rt'] ?? $existing?->rt,
            'rw' => $data[$prefix.'rw'] ?? $existing?->rw,
            'alamat_detail' => $data[$prefix.'alamat_detail'] ?? $existing?->alamat_detail,
        ];
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
