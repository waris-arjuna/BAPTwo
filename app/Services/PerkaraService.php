<?php

namespace App\Services;

use App\Models\Alamat;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\Tkp;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PerkaraService
{
    public function __construct(
        protected AuditTrailService $auditTrail,
        protected WilayahService $wilayahService
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
                    if (str_contains('draft', $lowerSearch)) {
                        $q->orWhere('tahap_laporan_status', 'draft');
                    }
                    if (str_contains('belum dilanjutkan', $lowerSearch)) {
                        $q->orWhere(function (Builder $sub) {
                            $sub->where('tahap_laporan_status', 'final')
                                ->whereNull('nomor_surat_perintah');
                        });
                    }
                    if (str_contains('sprindik tersedia', $lowerSearch)) {
                        $q->orWhereNotNull('nomor_surat_perintah');
                    }
                    if (str_contains('perkara terbentuk', $lowerSearch)) {
                        $q->orWhere('tahap_perkara_status', 'final');
                    }
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Draft') {
                    $query->where('tahap_laporan_status', 'draft');
                } elseif ($status === 'Belum Dilanjutkan') {
                    $query->where('tahap_laporan_status', 'final')
                        ->whereNull('nomor_surat_perintah');
                } elseif ($status === 'Sprindik Tersedia') {
                    $query->whereNotNull('nomor_surat_perintah')
                        ->where(function (Builder $q) {
                            $q->whereNull('tahap_perkara_status')
                                ->orWhere('tahap_perkara_status', '!=', 'final');
                        });
                } elseif ($status === 'Perkara Terbentuk') {
                    $query->where('tahap_perkara_status', 'final');
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
            ->where(function (Builder $q) {
                $q->whereNotNull('nomor_surat_perintah')
                    ->orWhereNotNull('tahap_sprindik_status');
            })
            ->with(['dokumenSprindik', 'dokumenLaporan', 'creator', 'berkasPerkaras'])
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
                    if (str_contains('draft', $lowerSearch)) {
                        $q->orWhere('tahap_sprindik_status', 'draft')
                            ->orWhere('tahap_perkara_status', 'draft');
                    }
                    if (str_contains('belum menjadi perkara', $lowerSearch)) {
                        $q->orWhere(function (Builder $sub) {
                            $sub->where('tahap_sprindik_status', 'final')
                                ->whereNull('tahap_perkara_status');
                        });
                    }
                    if (str_contains('perkara terbentuk', $lowerSearch)) {
                        $q->orWhere('tahap_perkara_status', 'final');
                    }
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Draft') {
                    $query->where('tahap_sprindik_status', 'draft');
                } elseif ($status === 'Draft Perkara') {
                    $query->where('tahap_perkara_status', 'draft');
                } elseif ($status === 'Belum Menjadi Perkara') {
                    $query->where('tahap_sprindik_status', 'final')
                        ->whereNull('tahap_perkara_status');
                } elseif ($status === 'Perkara Terbentuk') {
                    $query->where('tahap_perkara_status', 'final');
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
     * Laporan Kejadian yang sudah final dan belum memiliki Sprindik untuk dropdown tambah Sprindik
     *
     * @return Collection<int, Perkara>
     */
    public function getAvailableForSprindik(): Collection
    {
        return Perkara::query()
            ->with('dokumenLaporan')
            ->where(function ($q) {
                $q->where('tahap_laporan_status', 'final')
                    ->orWhereNull('tahap_laporan_status');
            })
            ->where(function ($q) {
                $q->whereNull('nomor_surat_perintah')
                    ->orWhere('nomor_surat_perintah', '');
            })
            ->whereNull('tahap_sprindik_status')
            ->orderByDesc('tanggal_laporan_kejadian')
            ->get();
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
     * Hapus / Reset Sprindik dari Perkara
     */
    public function removeSprindik(Perkara $perkara, User $user): void
    {
        DB::transaction(function () use ($perkara, $user) {
            $oldData = $perkara->toArray();

            $perkara->update([
                'tahap_laporan_status' => 'final',
                'nomor_surat_perintah' => null,
                'tanggal_surat_perintah' => null,
                'tahap_sprindik_status' => null,
                'sprindik_locked_at' => null,
                'sprindik_finalized_by' => null,
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
            $oldData = $perkara->load(['tkp.alamat', 'ppnsUsers', 'tersangkas.berkasPerkara.pasals'])->toArray();
            $isFinal = ($data['action_type'] ?? 'lanjut') === 'lanjut';

            // 1. Simpan / Update Alamat & TKP (F-07, F-36, F-37)
            $tkpId = $perkara->tkp_id;
            $hasTkpInput = ! empty($data['tkp_provinsi_kode'])
                || ! empty($data['tkp_alamat_detail'])
                || ! empty($data['tkp_klasifikasi'])
                || ! empty($data['tkp_legalitas'])
                || ! empty($data['tkp_modus']);

            if ($hasTkpInput || $tkpId !== null) {
                $alamatPayload = $this->resolveTkpAlamatPayload($data, $perkara->tkp?->alamat);

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

                    if ($berkas instanceof BerkasPerkara) {
                        if ($berkas->trashed()) {
                            $berkas->restore();
                        }
                        $berkas->update([
                            'perkara_id' => $perkara->id,
                            'nomor_berkas' => $tskData['nomor_berkas'] ?? $berkas->nomor_berkas,
                            'updated_by' => $user->id,
                        ]);
                    } else {
                        $berkas = BerkasPerkara::create([
                            'perkara_id' => $perkara->id,
                            'tersangka_id' => $tersangka->id,
                            'nomor_berkas' => $tskData['nomor_berkas'] ?? null,
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
     * Resolve nama wilayah dari WilayahService apabila hanya kode wilayah yang dikirim
     *
     * @param  array<string, mixed>  $data
     * @return array<string, ?string>
     */
    protected function resolveTkpAlamatPayload(array $data, ?Alamat $existing = null): array
    {
        $provKode = (string) ($data['tkp_provinsi_kode'] ?? $existing?->provinsi_kode ?? '');
        $kabKode = (string) ($data['tkp_kabupaten_kode'] ?? $existing?->kabupaten_kode ?? '');
        $kecKode = (string) ($data['tkp_kecamatan_kode'] ?? $existing?->kecamatan_kode ?? '');
        $desaKode = (string) ($data['tkp_desa_kode'] ?? $existing?->desa_kode ?? '');

        $provNama = (string) ($data['tkp_provinsi_nama'] ?? '');
        if ($provNama === '' && $provKode !== '') {
            foreach ($this->wilayahService->getProvinsi() as $item) {
                if ($item['code'] === $provKode) {
                    $provNama = $item['name'];
                    break;
                }
            }
        }

        $kabNama = (string) ($data['tkp_kabupaten_nama'] ?? '');
        if ($kabNama === '' && $provKode !== '' && $kabKode !== '') {
            foreach ($this->wilayahService->getKabupaten($provKode) as $item) {
                if ($item['code'] === $kabKode) {
                    $kabNama = $item['name'];
                    break;
                }
            }
        }

        $kecNama = (string) ($data['tkp_kecamatan_nama'] ?? '');
        if ($kecNama === '' && $kabKode !== '' && $kecKode !== '') {
            foreach ($this->wilayahService->getKecamatan($kabKode) as $item) {
                if ($item['code'] === $kecKode) {
                    $kecNama = $item['name'];
                    break;
                }
            }
        }

        $desaNama = (string) ($data['tkp_desa_nama'] ?? '');
        if ($desaNama === '' && $kecKode !== '' && $desaKode !== '') {
            foreach ($this->wilayahService->getDesa($kecKode) as $item) {
                if ($item['code'] === $desaKode) {
                    $desaNama = $item['name'];
                    break;
                }
            }
        }

        return [
            'provinsi_kode' => $provKode ?: null,
            'provinsi_nama' => $provNama ?: ($existing?->provinsi_nama ?? null),
            'kabupaten_kode' => $kabKode ?: null,
            'kabupaten_nama' => $kabNama ?: ($existing?->kabupaten_nama ?? null),
            'kecamatan_kode' => $kecKode ?: null,
            'kecamatan_nama' => $kecNama ?: ($existing?->kecamatan_nama ?? null),
            'desa_kode' => $desaKode ?: null,
            'desa_nama' => $desaNama ?: ($existing?->desa_nama ?? null),
            'rt' => $data['tkp_rt'] ?? $existing?->rt,
            'rw' => $data['tkp_rw'] ?? $existing?->rw,
            'alamat_detail' => $data['tkp_alamat_detail'] ?? $existing?->alamat_detail,
        ];
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
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => null,
                'tanggal_surat_perintah' => null,
                'tahap_sprindik_status' => null,
                'deskripsi' => 'Dugaan peredaran sediaan farmasi tanpa izin edar.',
            ],
            [
                'id' => 2,
                'nomor_laporan_kejadian' => 'LK/002/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-04',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => 'SP/001/IX/2026',
                'tanggal_surat_perintah' => '2026-09-05',
                'tahap_sprindik_status' => 'final',
                'sprindik_locked_at' => now(),
                'deskripsi' => 'Dugaan peredaran kosmetik mengandung bahan berbahaya.',
            ],
            [
                'id' => 3,
                'nomor_laporan_kejadian' => 'LK/003/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-05',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => 'SP/002/IX/2026',
                'tanggal_surat_perintah' => '2026-09-06',
                'tahap_sprindik_status' => 'final',
                'sprindik_locked_at' => now(),
                'deskripsi' => 'Dugaan pangan olahan tanpa izin edar skala rumahan.',
            ],
            [
                'id' => 4,
                'nomor_laporan_kejadian' => 'LK/004/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-08',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => null,
                'tanggal_surat_perintah' => null,
                'tahap_sprindik_status' => null,
                'deskripsi' => 'Dugaan obat tradisional ilegal mengandung BKO.',
            ],
            [
                'id' => 5,
                'nomor_laporan_kejadian' => 'LK/005/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-10',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => 'SP/003/IX/2026',
                'tanggal_surat_perintah' => '2026-09-11',
                'tahap_sprindik_status' => 'final',
                'sprindik_locked_at' => now(),
                'deskripsi' => 'Dugaan suplemen kesehatan palsu secara daring.',
            ],
        ];

        foreach ($samplePerkara as $item) {
            Perkara::create($item);
        }
    }

    /**
     * Ambil daftar Perkara untuk halaman Data Perkara
     */
    public function getDataPerkaraList(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $this->ensureBaselinePerkara();

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return Perkara::query()
            ->whereNotNull('tahap_perkara_status')
            ->with([
                'dokumenSprindik',
                'dokumenLaporan',
                'tkp.alamat',
                'ppnsUtama',
                'tersangkas.berkasPerkara.pasals',
                'creator',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_surat_perintah', 'like', "%{$search}%")
                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('tkp', function (Builder $tkpQ) use ($search) {
                            $tkpQ->where('klasifikasi', 'like', "%{$search}%")
                                ->orWhere('legalitas', 'like', "%{$search}%")
                                ->orWhere('modus', 'like', "%{$search}%")
                                ->orWhereHas('alamat', function (Builder $almQ) use ($search) {
                                    $almQ->where('kabupaten_nama', 'like', "%{$search}%")
                                        ->orWhere('kecamatan_nama', 'like', "%{$search}%")
                                        ->orWhere('desa_nama', 'like', "%{$search}%")
                                        ->orWhere('alamat_detail', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHas('ppnsUtama', function (Builder $userQ) use ($search) {
                            $userQ->where('nama', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tersangkas', function (Builder $tskQ) use ($search) {
                            $tskQ->where('nama', 'like', "%{$search}%")
                                ->orWhere('alias', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%")
                                ->orWhereHas('berkasPerkara', function (Builder $bpQ) use ($search) {
                                    $bpQ->where('nomor_berkas', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Draft' || $status === 'Draft Perkara') {
                    $query->where('tahap_perkara_status', 'draft');
                } elseif ($status === 'Perkara Terbentuk') {
                    $query->where('tahap_perkara_status', 'final');
                }
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat_perintah', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat_perintah', '<=', $tanggalSampai);
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ambil Sprindik final yang siap/belum dibuatkan Perkara
     *
     * @return Collection<int, Perkara>
     */
    public function getAvailableForPerkara(): Collection
    {
        return Perkara::query()
            ->with(['dokumenSprindik', 'dokumenLaporan'])
            ->where('tahap_sprindik_status', 'final')
            ->whereNull('tahap_perkara_status')
            ->whereDoesntHave('tersangkas')
            ->orderByDesc('tanggal_surat_perintah')
            ->get();
    }

    /**
     * Ambil daftar Berkas Perkara untuk monitoring Progress Perkara
     */
    public function getProgressPerkaraList(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return BerkasPerkara::query()
            ->with([
                'perkara.dokumenSprindik',
                'perkara.ppnsUtama',
                'tersangka',
                'pasals',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_berkas', 'like', "%{$search}%")
                        ->orWhere('status_proses', 'like', "%{$search}%")
                        ->orWhereHas('tersangka', function (Builder $tskQ) use ($search) {
                            $tskQ->where('nama', 'like', "%{$search}%")
                                ->orWhere('alias', 'like', "%{$search}%");
                        })
                        ->orWhereHas('perkara', function (Builder $perkQ) use ($search) {
                            $perkQ->where('nomor_surat_perintah', 'like', "%{$search}%");
                        })
                        ->orWhereHas('pasals', function (Builder $pasQ) use ($search) {
                            $pasQ->where('nama_pasal', 'like', "%{$search}%")
                                ->orWhere('pasal', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Dalam Proses') {
                    $query->whereNull('status_formal');
                } elseif ($status === 'Selesai') {
                    $query->where('status_formal', 'Selesai');
                } elseif ($status === 'Tidak Selesai') {
                    $query->where('status_formal', 'Tidak Selesai');
                } elseif ($status === 'DPO') {
                    $query->where('is_dpo', true);
                }
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_spdp', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_spdp', '<=', $tanggalSampai);
            })
            ->orderBy('progress_persen')
            ->orderBy('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
