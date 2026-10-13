<?php

namespace App\Services;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
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

            if ($existingTahap !== null) {
                $this->assertStageNotLocked($existingTahap);
            } else {
                $this->validateStageOrder($berkas, $jenisTahap);
            }

            $tahapData = [
                'berkas_perkara_id' => $berkas->id,
                'jenis_tahap' => $jenisTahap,
                'nomor_surat' => $data['nomor_surat'] ?? null,
                'tanggal_surat' => ! empty($data['tanggal_surat']) ? $data['tanggal_surat'] : null,
                'catatan' => $data['catatan'] ?? null,
                'status' => $isDraft ? 'draft' : 'final',
                'finalized_at' => $isDraft ? null : now(),
                'finalized_by' => $isDraft ? null : $user->id,
                'updated_by' => $user->id,
            ];

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
                    $updateBerkas['sla_deadline'] = $tanggalSpdp->copy()->addMonth()->toDateString();
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
        $this->ensureBaselineTahapan();

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
        $this->ensureBaselineTahapan();

        $base = TahapanPerkara::query()->whereIn('jenis_tahap', [JenisTahap::SPDP->value, JenisTahap::P16->value, JenisTahap::P17P->value]);

        return [
            'all' => (clone $base)->count(),
            'spdp' => (clone $base)->where('jenis_tahap', JenisTahap::SPDP->value)->count(),
            'p16' => (clone $base)->where('jenis_tahap', JenisTahap::P16->value)->count(),
            'p17p' => (clone $base)->where('jenis_tahap', JenisTahap::P17P->value)->count(),
        ];
    }

    /**
     * Memastikan baseline data tahapan terisi jika database kosong (misal saat testing dengan RefreshDatabase).
     */
    public function ensureBaselineTahapan(): void
    {
        if (TahapanPerkara::count() > 0) {
            return;
        }

        app(PerkaraService::class)->ensureBaselinePerkara();

        $perkara = Perkara::whereNotNull('tahap_sprindik_status')->first();
        if (! $perkara) {
            return;
        }

        $tersangka = Tersangka::firstOrCreate(
            ['perkara_id' => $perkara->id, 'nama' => 'Budi Santoso'],
            ['nik' => '6371012345670001']
        );

        $berkas = BerkasPerkara::firstOrCreate(
            ['perkara_id' => $perkara->id, 'tersangka_id' => $tersangka->id],
            ['nomor_berkas' => 'BP/001/IX/2026/BPOM']
        );

        TahapanPerkara::create([
            'id' => 1,
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => JenisTahap::SPDP->value,
            'nomor_surat' => 'SPDP/001/IX/2026',
            'tanggal_surat' => '2026-09-04',
            'status' => 'final',
            'locked_at' => now(),
        ]);
    }
}
