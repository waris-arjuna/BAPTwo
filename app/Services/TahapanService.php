<?php

namespace App\Services;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use DomainException;

class TahapanService
{
    /**
     * Mendapatkan daftar jenis tahapan berikutnya yang valid untuk dicatat pada Berkas Perkara.
     * Mendukung tahapan wajib dan skipping tahapan opsional (PRD F-10 s.d. F-13).
     *
     * @return list<JenisTahap>
     */
    public function getNextAvailableStages(BerkasPerkara $berkas): array
    {
        if ($berkas->status_formal === 'tidak_selesai' || $berkas->hasSp3()) {
            return [];
        }

        $latest = $berkas->latestFinalTahap();

        if ($latest === null) {
            // Belum ada tahapan progress, tahap pembuka wajib SPDP
            return $berkas->hasTahap(JenisTahap::SPDP) ? [] : [JenisTahap::SPDP];
        }

        $candidates = match ($latest->jenis_tahap) {
            JenisTahap::SPDP => [
                JenisTahap::P16,
                JenisTahap::P17P,
                JenisTahap::TAHAP_1,
            ],
            JenisTahap::P16 => [
                JenisTahap::P17P,
                JenisTahap::TAHAP_1,
            ],
            JenisTahap::P17P => [
                JenisTahap::TAHAP_1,
            ],
            JenisTahap::TAHAP_1 => [
                JenisTahap::P18P,
                JenisTahap::P19,
                JenisTahap::P21,
            ],
            JenisTahap::P18P => [
                JenisTahap::P19,
                JenisTahap::P21,
            ],
            JenisTahap::P19 => [
                JenisTahap::P21,
            ],
            JenisTahap::P21 => [
                JenisTahap::TAHAP_2,
            ],
            JenisTahap::TAHAP_2 => [
                JenisTahap::KEPUTUSAN_PENGADILAN,
            ],
            JenisTahap::KEPUTUSAN_PENGADILAN => [],
        };

        // Filter: tahapan yang sudah pernah dicatat tidak boleh dipilih lagi (maksimal 1x per berkas perkara - PRD F-11)
        return array_values(array_filter(
            $candidates,
            fn (JenisTahap $tahap): bool => ! $berkas->hasTahap($tahap)
        ));
    }

    /**
     * Memeriksa apakah transisi ke tahapan target valid.
     */
    public function canProceedTo(BerkasPerkara $berkas, JenisTahap $targetTahap): bool
    {
        return in_array($targetTahap, $this->getNextAvailableStages($berkas), true);
    }

    /**
     * Memvalidasi aturan urutan dan maksimal 1x untuk tahapan berikutnya.
     * Melempar DomainException jika melanggar aturan PRD.
     *
     * @throws DomainException
     */
    public function validateStageOrder(BerkasPerkara $berkas, JenisTahap $targetTahap): void
    {
        if ($berkas->hasSp3()) {
            throw new DomainException('Perkara yang telah diterbitkan SP3 tidak dapat dilanjutkan ke tahap apa pun.');
        }

        if ($berkas->status_formal === 'tidak_selesai') {
            throw new DomainException('Berkas perkara dengan status Tidak Selesai tidak dapat dilanjutkan ke tahapan berikutnya.');
        }

        if ($berkas->hasTahap($targetTahap)) {
            throw new DomainException("Tahapan {$targetTahap->label()} sudah pernah dicatat pada Berkas Perkara ini. Satu jenis tahapan maksimal 1 kali.");
        }

        $available = $this->getNextAvailableStages($berkas);

        if (! in_array($targetTahap, $available, true)) {
            $latest = $berkas->latestFinalTahap();
            $posisi = $latest !== null ? $latest->jenis_tahap->label() : 'Pra-SPDP';

            throw new DomainException("Tahapan {$targetTahap->label()} tidak valid untuk urutan progress saat ini (posisi terakhir: {$posisi}).");
        }
    }

    /**
     * Menghitung persentase progress berbobot (PRD F-30):
     * - Pra-SPDP: 0%
     * - SPDP: 15%
     * - TAHAP I: 55%
     * - P-21/P21a: 85%
     * - TAHAP II & Keputusan Pengadilan: 100%
     */
    public function calculateProgress(BerkasPerkara $berkas): int
    {
        $finalStages = $berkas->tahapan()
            ->where('status', 'final')
            ->pluck('jenis_tahap')
            ->all();

        $maxProgress = 0;
        foreach ($finalStages as $stage) {
            $enum = $stage instanceof JenisTahap ? $stage : JenisTahap::tryFrom((string) $stage);
            if ($enum !== null && $enum->bobot() > $maxProgress) {
                $maxProgress = $enum->bobot();
            }
        }

        return $maxProgress;
    }

    /**
     * Menentukan label status proses berdasarkan tahapan progress terakhir yang difinalkan (PRD F-29).
     */
    public function determineStatusProses(BerkasPerkara $berkas): string
    {
        $latest = $berkas->latestFinalTahap();

        return $latest !== null ? $latest->jenis_tahap->label() : 'Baru';
    }

    /**
     * Memvalidasi penguncian permanen: tahap yang sudah final tidak boleh diedit atau diubah statusnya (PRD F-14 s.d. F-19).
     *
     * @throws DomainException
     */
    public function assertStageNotLocked(TahapanPerkara $tahap): void
    {
        if ($tahap->isFinal()) {
            throw new \DomainException("Tahapan {$tahap->jenis_tahap->label()} telah difinalkan dan terkunci permanen. Administrator pun tidak dapat mengedit atau memundurkan tahap yang telah dikunci.");
        }
    }

    /**
     * Menyimpan tahapan perkara (mendukung aksi Draft dan Simpan & Lanjut - PRD F-14 s.d. F-19).
     *
     * @param  array<string, mixed>  $data
     */
    public function saveStage(
        BerkasPerkara $berkas,
        array $data,
        ?\Illuminate\Http\UploadedFile $file,
        \App\Models\User $user,
        ?TahapanPerkara $existingTahap = null
    ): TahapanPerkara {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($berkas, $data, $file, $user, $existingTahap): TahapanPerkara {
            $isDraft = ($data['action_type'] ?? 'lanjut') === 'draft';
            $jenisTahap = $existingTahap !== null
                ? $existingTahap->jenis_tahap
                : ($data['jenis_tahap'] instanceof JenisTahap ? $data['jenis_tahap'] : JenisTahap::from((string) $data['jenis_tahap']));

            if ($berkas->hasSp3()) {
                throw new \DomainException('Perkara yang telah diterbitkan SP3 tidak dapat diedit atau dilanjutkan ke tahap apa pun.');
            }

            if ($berkas->status_formal === 'tidak_selesai') {
                throw new \DomainException('Berkas perkara dengan status Tidak Selesai tidak dapat diedit atau dilanjutkan ke tahap apa pun.');
            }

            if ($existingTahap !== null) {
                if ($existingTahap->isFinal()) {
                    // Hanya SPDP dan Tahap 1 yang diizinkan diakses saat final untuk melengkapi sub-tahap
                    if (! in_array($existingTahap->jenis_tahap, [JenisTahap::SPDP, JenisTahap::TAHAP_1], true)) {
                        $this->assertStageNotLocked($existingTahap);
                    }
                }
            } else {
                $this->validateStageOrder($berkas, $jenisTahap);
            }

            // DPO Gate: PRD F-24 s.d. F-27
            if ($jenisTahap === JenisTahap::TAHAP_2 && ! $isDraft && $berkas->is_dpo) {
                throw new \DomainException('Tidak dapat melanjutkan ke Tahap II (Pelimpahan Tersangka & Barang Bukti) karena Tersangka berstatus DPO.');
            }

            $tahapData = [
                'berkas_perkara_id' => $berkas->id,
                'jenis_tahap' => $jenisTahap,
                'catatan' => $data['catatan'] ?? null,
                'updated_by' => $user->id,
            ];

            // Jika belum final, update nomor dan tanggal
            if ($existingTahap === null || ! $existingTahap->isFinal()) {
                $tahapData['nomor_surat'] = $data['nomor_surat'] ?? null;
                $tahapData['tanggal_surat'] = ! empty($data['tanggal_surat']) ? $data['tanggal_surat'] : null;
                $tahapData['status'] = $isDraft ? 'draft' : 'final';
                $tahapData['finalized_at'] = $isDraft ? null : now();
                $tahapData['finalized_by'] = $isDraft ? null : $user->id;
            }

            if ($existingTahap !== null) {
                $existingTahap->update($tahapData);
                $tahap = $existingTahap;
            } else {
                $tahapData['created_by'] = $user->id;
                $tahap = TahapanPerkara::create($tahapData);
            }

            // Simpan dokumen jika diunggah
            if ($file !== null) {
                $path = $file->store('dokumen/tahapan', 'local');
                \App\Models\Dokumen::create([
                    'perkara_id' => $berkas->perkara_id,
                    'berkas_perkara_id' => $berkas->id,
                    'tahapan_perkara_id' => $tahap->id,
                    'kategori' => 'tahapan',
                    'nama_dokumen' => "Dokumen {$jenisTahap->label()} - {$tahap->nomor_surat}",
                    'nama_file_asli' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize() ?: 0,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }

            // Simpan sub-tahap opsional tersemat jika ada input
            if ($jenisTahap === JenisTahap::SPDP) {
                $this->saveSubStage($berkas, JenisTahap::P16, 'p16', $data, request()->file('p16_dokumen'), $user);
                $this->saveSubStage($berkas, JenisTahap::P17P, 'p17p', $data, request()->file('p17p_dokumen'), $user);
            } elseif ($jenisTahap === JenisTahap::TAHAP_1) {
                $this->saveSubStage($berkas, JenisTahap::P18P, 'p18p', $data, request()->file('p18p_dokumen'), $user);
                $this->saveSubStage($berkas, JenisTahap::P19, 'p19', $data, request()->file('p19_dokumen'), $user);
            }

            // Jika Simpan & Lanjut: update progres berkas perkara & tanggal SPDP jika SPDP
            if (! $isDraft) {
                $berkas->refresh();
                $updateBerkas = [
                    'progress_persen' => $this->calculateProgress($berkas),
                    'status_proses' => $this->determineStatusProses($berkas),
                ];

                if ($jenisTahap === JenisTahap::SPDP && ! empty($tahap->tanggal_surat)) {
                    $tanggalSpdp = \Carbon\Carbon::parse($tahap->tanggal_surat);
                    $updateBerkas['tanggal_spdp'] = $tanggalSpdp->toDateString();

                    // SLA 1 bulan kalender & Batas Tahun mutlak (PRD bagian Batas Waktu)
                    $sla1Bulan = $tanggalSpdp->copy()->addMonth();
                    $akhirTahun = $tanggalSpdp->copy()->endOfYear();
                    $effectiveDeadline = $sla1Bulan->gt($akhirTahun) ? $akhirTahun : $sla1Bulan;

                    $updateBerkas['sla_deadline'] = $effectiveDeadline->toDateString();
                }

                // PRD F-22: Simpan & Lanjut dari TAHAP II otomatis memicu status formal Selesai
                if ($jenisTahap === JenisTahap::TAHAP_2) {
                    $updateBerkas['status_formal'] = 'selesai';
                }

                $berkas->update($updateBerkas);

                // Audit log finalisasi
                app(AuditTrailService::class)->log(
                    aksi: 'finalisasi_tahap',
                    tabel: 'tahapan_perkara',
                    recordId: $tahap->id,
                    dataLama: null,
                    dataBaru: $tahap->toArray(),
                    perkaraId: $berkas->perkara_id,
                    berkasPerkaraId: $berkas->id,
                    userId: $user->id
                );
            } else {
                // Audit log draft
                app(AuditTrailService::class)->log(
                    aksi: 'simpan_draft_tahap',
                    tabel: 'tahapan_perkara',
                    recordId: $tahap->id,
                    dataLama: null,
                    dataBaru: $tahap->toArray(),
                    perkaraId: $berkas->perkara_id,
                    berkasPerkaraId: $berkas->id,
                    userId: $user->id
                );
            }

            return $tahap;
        });
    }

    /**
     * Mendapatkan daftar ringkasan referensi tahapan sebelumnya yang telah final untuk ditampilkan pada Card Referensi.
     *
     * @return array<int, array{nama: string, nomor: string, tanggal: string, status: string, dokumen: string|null}>
     */
    public function getPreviousStagesReference(BerkasPerkara $berkas): array
    {
        $references = [];
        $perkara = $berkas->perkara;

        if ($perkara !== null) {
            if ($perkara->tahap_laporan_status === 'final') {
                $references[] = [
                    'nama' => 'Laporan Kejadian',
                    'nomor' => (string) ($perkara->nomor_laporan_kejadian ?? '-'),
                    'tanggal' => $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-',
                    'status' => 'Final / Terkunci',
                    'dokumen' => $perkara->dokumenLaporan?->nama_file_asli,
                ];
            }

            if ($perkara->tahap_sprindik_status === 'final') {
                $references[] = [
                    'nama' => 'Surat Perintah Penyidikan (Sprindik)',
                    'nomor' => (string) ($perkara->nomor_surat_perintah ?? '-'),
                    'tanggal' => $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-',
                    'status' => 'Final / Terkunci',
                    'dokumen' => $perkara->dokumenSprindik?->nama_file_asli,
                ];
            }
        }

        // Tahapan progress pada berkas perkara yang sudah final
        $finalTahapan = $berkas->tahapan()
            ->where('status', 'final')
            ->with('dokumen')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($finalTahapan as $t) {
            $references[] = [
                'nama' => $t->jenis_tahap->label(),
                'nomor' => (string) ($t->nomor_surat ?? '-'),
                'tanggal' => $t->tanggal_surat?->format('d-m-Y') ?? '-',
                'status' => 'Final / Terkunci',
                'dokumen' => $t->dokumen->first()?->nama_file_asli,
            ];
        }

        return $references;
    }

    /**
     * Mengambil daftar tahapan SPDP (termasuk sub-tahap opsional P-16 dan P-17P) dengan filter dan paginasi.
     */
    public function getSpdpList(\Illuminate\Http\Request $request, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        $subStage = $request->string('sub_stage', 'all')->trim()->toString();
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return TahapanPerkara::query()
            ->whereIn('jenis_tahap', [JenisTahap::SPDP->value, JenisTahap::P16->value, JenisTahap::P17P->value])
            ->when($subStage !== 'all' && $subStage !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($subStage) {
                $query->where('jenis_tahap', $subStage);
            })
            ->when($status !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($status) {
                $query->where('status', strtolower($status));
            })
            ->when($tanggalDari !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat', '<=', $tanggalSampai);
            })
            ->when($search !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($search) {
                $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('berkasPerkara', function (\Illuminate\Database\Eloquent\Builder $bQ) use ($search) {
                            $bQ->where('nomor_berkas', 'like', "%{$search}%")
                                ->orWhereHas('tersangka', function (\Illuminate\Database\Eloquent\Builder $tQ) use ($search) {
                                    $tQ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nik', 'like', "%{$search}%");
                                })
                                ->orWhereHas('perkara', function (\Illuminate\Database\Eloquent\Builder $pQ) use ($search) {
                                    $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->with([
                'berkasPerkara.perkara',
                'berkasPerkara.tersangka',
                'dokumen',
                'createdBy',
                'finalizedBy',
            ])
            ->orderByDesc('tanggal_surat')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai SPDP (pra-SPDP final & belum ada SPDP).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForSpdp(): \Illuminate\Database\Eloquent\Collection
    {
        return BerkasPerkara::query()
            ->whereHas('perkara', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('tahap_perkara_status', 'final');
            })
            ->whereDoesntHave('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::SPDP->value);
            })
            ->whereDoesntHave('sp3')
            ->where(fn (\Illuminate\Database\Eloquent\Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Menghitung jumlah tahapan per sub-tahap untuk tab filter (Semua, SPDP, P-16, P-17P).
     *
     * @return array{all: int, spdp: int, p16: int, p17p: int}
     */
    public function getSpdpTabCounts(): array
    {
        $base = TahapanPerkara::query()->whereIn('jenis_tahap', [JenisTahap::SPDP->value, JenisTahap::P16->value, JenisTahap::P17P->value]);

        return [
            'all' => (clone $base)->count(),
            'spdp' => (clone $base)->where('jenis_tahap', JenisTahap::SPDP->value)->count(),
            'p16' => (clone $base)->where('jenis_tahap', JenisTahap::P16->value)->count(),
            'p17p' => (clone $base)->where('jenis_tahap', JenisTahap::P17P->value)->count(),
        ];
    }

    /**
     * Menyimpan sub-tahap opsional (P-16, P-17P, P-18P, P-19) yang tersemat pada form utama.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveSubStage(
        BerkasPerkara $berkas,
        JenisTahap $jenisTahap,
        string $prefix,
        array $data,
        ?\Illuminate\Http\UploadedFile $file,
        \App\Models\User $user
    ): ?TahapanPerkara {
        $nomor = $data["{$prefix}_nomor_surat"] ?? null;
        if (empty($nomor)) {
            return null;
        }

        $tanggal = ! empty($data["{$prefix}_tanggal_surat"]) ? $data["{$prefix}_tanggal_surat"] : null;
        $catatan = $data["{$prefix}_catatan"] ?? null;

        $existing = TahapanPerkara::where('berkas_perkara_id', $berkas->id)
            ->where('jenis_tahap', $jenisTahap->value)
            ->first();

        $tahapData = [
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => $jenisTahap,
            'nomor_surat' => $nomor,
            'tanggal_surat' => $tanggal,
            'catatan' => $catatan,
            'status' => 'final',
            'finalized_at' => now(),
            'finalized_by' => $user->id,
            'updated_by' => $user->id,
        ];

        if ($existing !== null) {
            $existing->update($tahapData);
            $subTahap = $existing;
        } else {
            $tahapData['created_by'] = $user->id;
            $subTahap = TahapanPerkara::create($tahapData);
        }

        if ($file !== null) {
            $path = $file->store('dokumen/tahapan', 'local');
            \App\Models\Dokumen::create([
                'perkara_id' => $berkas->perkara_id,
                'berkas_perkara_id' => $berkas->id,
                'tahapan_perkara_id' => $subTahap->id,
                'kategori' => 'tahapan',
                'nama_dokumen' => "Dokumen {$jenisTahap->label()} - {$subTahap->nomor_surat}",
                'nama_file_asli' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize() ?: 0,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }

        // Audit log sub-tahap
        app(AuditTrailService::class)->log(
            aksi: 'simpan_sub_tahap',
            tabel: 'tahapan_perkara',
            recordId: $subTahap->id,
            dataLama: null,
            dataBaru: $subTahap->toArray(),
            perkaraId: $berkas->perkara_id,
            berkasPerkaraId: $berkas->id,
            userId: $user->id
        );

        return $subTahap;
    }

    /**
     * Mengambil daftar tahapan Tahap 1 (termasuk P-18P dan P-19) dengan filter dan paginasi.
     */
    public function getTahap1List(\Illuminate\Http\Request $request, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        $subStage = $request->string('sub_stage', 'all')->trim()->toString();
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return TahapanPerkara::query()
            ->whereIn('jenis_tahap', [JenisTahap::TAHAP_1->value, JenisTahap::P18P->value, JenisTahap::P19->value])
            ->when($subStage !== 'all' && $subStage !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($subStage) {
                $query->where('jenis_tahap', $subStage);
            })
            ->when($status !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($status) {
                $query->where('status', strtolower($status));
            })
            ->when($tanggalDari !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat', '<=', $tanggalSampai);
            })
            ->when($search !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($search) {
                $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('berkasPerkara', function (\Illuminate\Database\Eloquent\Builder $bQ) use ($search) {
                            $bQ->where('nomor_berkas', 'like', "%{$search}%")
                                ->orWhereHas('tersangka', function (\Illuminate\Database\Eloquent\Builder $tQ) use ($search) {
                                    $tQ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nik', 'like', "%{$search}%");
                                })
                                ->orWhereHas('perkara', function (\Illuminate\Database\Eloquent\Builder $pQ) use ($search) {
                                    $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->with([
                'berkasPerkara.perkara',
                'berkasPerkara.tersangka',
                'dokumen',
                'createdBy',
                'finalizedBy',
            ])
            ->orderByDesc('tanggal_surat')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Menghitung tab counts untuk halaman Tahap 1 (Semua, Tahap 1, P-18P, P-19).
     *
     * @return array{all: int, tahap_1: int, p18p: int, p19: int}
     */
    public function getTahap1TabCounts(): array
    {
        $base = TahapanPerkara::query()->whereIn('jenis_tahap', [JenisTahap::TAHAP_1->value, JenisTahap::P18P->value, JenisTahap::P19->value]);

        return [
            'all' => (clone $base)->count(),
            'tahap_1' => (clone $base)->where('jenis_tahap', JenisTahap::TAHAP_1->value)->count(),
            'p18p' => (clone $base)->where('jenis_tahap', JenisTahap::P18P->value)->count(),
            'p19' => (clone $base)->where('jenis_tahap', JenisTahap::P19->value)->count(),
        ];
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai Tahap 1 (SPDP final & belum ada Tahap 1 final).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForTahap1(): \Illuminate\Database\Eloquent\Collection
    {
        return BerkasPerkara::query()
            ->whereHas('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::SPDP->value)->where('status', 'final');
            })
            ->whereDoesntHave('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::TAHAP_1->value);
            })
            ->whereDoesntHave('sp3')
            ->where(fn (\Illuminate\Database\Eloquent\Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Mengambil daftar tahapan P-21 dengan filter dan paginasi.
     */
    public function getP21List(\Illuminate\Http\Request $request, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return TahapanPerkara::query()
            ->where('jenis_tahap', JenisTahap::P21->value)
            ->when($status !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($status) {
                $query->where('status', strtolower($status));
            })
            ->when($tanggalDari !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat', '<=', $tanggalSampai);
            })
            ->when($search !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($search) {
                $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('berkasPerkara', function (\Illuminate\Database\Eloquent\Builder $bQ) use ($search) {
                            $bQ->where('nomor_berkas', 'like', "%{$search}%")
                                ->orWhereHas('tersangka', function (\Illuminate\Database\Eloquent\Builder $tQ) use ($search) {
                                    $tQ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nik', 'like', "%{$search}%");
                                })
                                ->orWhereHas('perkara', function (\Illuminate\Database\Eloquent\Builder $pQ) use ($search) {
                                    $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->with([
                'berkasPerkara.perkara',
                'berkasPerkara.tersangka',
                'dokumen',
                'createdBy',
                'finalizedBy',
            ])
            ->orderByDesc('tanggal_surat')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai P-21 (Tahap 1 final & belum ada P-21 final).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForP21(): \Illuminate\Database\Eloquent\Collection
    {
        return BerkasPerkara::query()
            ->whereHas('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::TAHAP_1->value)->where('status', 'final');
            })
            ->whereDoesntHave('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::P21->value);
            })
            ->whereDoesntHave('sp3')
            ->where(fn (\Illuminate\Database\Eloquent\Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Mengambil daftar tahapan Tahap 2 dengan filter dan paginasi.
     */
    public function getTahap2List(\Illuminate\Http\Request $request, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return TahapanPerkara::query()
            ->where('jenis_tahap', JenisTahap::TAHAP_2->value)
            ->when($status !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($status) {
                $query->where('status', strtolower($status));
            })
            ->when($tanggalDari !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat', '<=', $tanggalSampai);
            })
            ->when($search !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($search) {
                $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('berkasPerkara', function (\Illuminate\Database\Eloquent\Builder $bQ) use ($search) {
                            $bQ->where('nomor_berkas', 'like', "%{$search}%")
                                ->orWhereHas('tersangka', function (\Illuminate\Database\Eloquent\Builder $tQ) use ($search) {
                                    $tQ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nik', 'like', "%{$search}%");
                                })
                                ->orWhereHas('perkara', function (\Illuminate\Database\Eloquent\Builder $pQ) use ($search) {
                                    $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->with([
                'berkasPerkara.perkara',
                'berkasPerkara.tersangka',
                'dokumen',
                'createdBy',
                'finalizedBy',
            ])
            ->orderByDesc('tanggal_surat')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai Tahap 2 (P-21 final & belum ada Tahap 2).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForTahap2(): \Illuminate\Database\Eloquent\Collection
    {
        return BerkasPerkara::query()
            ->whereHas('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::P21->value)->where('status', 'final');
            })
            ->whereDoesntHave('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::TAHAP_2->value);
            })
            ->whereDoesntHave('sp3')
            ->where(fn (\Illuminate\Database\Eloquent\Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->where('is_dpo', false)
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Mengambil daftar Putusan Perkara (Keputusan Pengadilan) dengan filter dan paginasi.
     */
    public function getPutusanPerkaraList(\Illuminate\Http\Request $request, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        return TahapanPerkara::query()
            ->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN->value)
            ->when($status !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($status) {
                $query->where('status', strtolower($status));
            })
            ->when($tanggalDari !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat', '<=', $tanggalSampai);
            })
            ->when($search !== '', function (\Illuminate\Database\Eloquent\Builder $query) use ($search) {
                $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('berkasPerkara', function (\Illuminate\Database\Eloquent\Builder $bQ) use ($search) {
                            $bQ->where('nomor_berkas', 'like', "%{$search}%")
                                ->orWhereHas('tersangka', function (\Illuminate\Database\Eloquent\Builder $tQ) use ($search) {
                                    $tQ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nik', 'like', "%{$search}%");
                                })
                                ->orWhereHas('perkara', function (\Illuminate\Database\Eloquent\Builder $pQ) use ($search) {
                                    $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->with([
                'berkasPerkara.perkara',
                'berkasPerkara.tersangka',
                'dokumen',
                'createdBy',
                'finalizedBy',
            ])
            ->orderByDesc('tanggal_surat')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap mencatat Putusan Pengadilan (Tahap 2 final & belum ada Putusan).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForPutusan(): \Illuminate\Database\Eloquent\Collection
    {
        return BerkasPerkara::query()
            ->whereHas('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::TAHAP_2->value)->where('status', 'final');
            })
            ->whereDoesntHave('tahapan', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN->value);
            })
            ->whereDoesntHave('sp3')
            ->where(fn (\Illuminate\Database\Eloquent\Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('id')
            ->get();
    }
}
