<?php

namespace App\Services;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TahapanService
{
    /**
     * Mendapatkan daftar jenis tahapan berikutnya yang valid untuk dicatat pada Berkas Perkara.
     * Alur utama linier: SPDP -> Tahap I -> P-21 -> Tahap II -> Keputusan Pengadilan.
     * Sub-tahap (P-16, P-17P, P-18P, P-19) dikelola mandiri dan tidak menghalangi alur utama.
     *
     * @return list<JenisTahap>
     */
    public function getNextAvailableStages(BerkasPerkara $berkas, bool $ignoreDpo = false): array
    {
        if ($berkas->status_formal === 'tidak_selesai' || $berkas->hasSp3()) {
            return [];
        }

        if (! $ignoreDpo && $berkas->is_dpo) {
            return [];
        }

        if ($berkas->hasFinalTahap(JenisTahap::KEPUTUSAN_PENGADILAN) || $berkas->hasTahap(JenisTahap::KEPUTUSAN_PENGADILAN)) {
            return [];
        }

        if ($berkas->hasFinalTahap(JenisTahap::TAHAP_2)) {
            return $berkas->hasTahap(JenisTahap::KEPUTUSAN_PENGADILAN) ? [] : [JenisTahap::KEPUTUSAN_PENGADILAN];
        }

        if ($berkas->hasFinalTahap(JenisTahap::P21)) {
            return $berkas->hasTahap(JenisTahap::TAHAP_2) ? [] : [JenisTahap::TAHAP_2];
        }

        if ($berkas->hasFinalTahap(JenisTahap::TAHAP_1)) {
            return $berkas->hasTahap(JenisTahap::P21) ? [] : [JenisTahap::P21];
        }

        if ($berkas->hasFinalTahap(JenisTahap::SPDP)) {
            return $berkas->hasTahap(JenisTahap::TAHAP_1) ? [] : [JenisTahap::TAHAP_1];
        }

        return $berkas->hasTahap(JenisTahap::SPDP) ? [] : [JenisTahap::SPDP];
    }

    /**
     * Memeriksa apakah transisi ke tahapan target valid.
     */
    public function canProceedTo(BerkasPerkara $berkas, JenisTahap $targetTahap): bool
    {
        if ($targetTahap->isOptional()) {
            if ($targetTahap === JenisTahap::P16 || $targetTahap === JenisTahap::P17P) {
                return ($berkas->hasFinalTahap(JenisTahap::SPDP) || $berkas->hasTahap(JenisTahap::SPDP))
                    && ! $berkas->hasTahap($targetTahap);
            }
            if ($targetTahap === JenisTahap::P18P || $targetTahap === JenisTahap::P19) {
                return $berkas->hasFinalTahap(JenisTahap::TAHAP_1)
                    && ! $berkas->hasTahap($targetTahap);
            }
        }

        return in_array($targetTahap, $this->getNextAvailableStages($berkas), true);
    }

    /**
     * Memvalidasi aturan urutan dan maksimal 1x untuk tahapan berikutnya.
     * Melempar DomainException jika melanggar aturan PRD.
     *
     * @throws DomainException
     */
    public function validateStageOrder(BerkasPerkara $berkas, JenisTahap $targetTahap, bool $isDraft = false): void
    {
        if ($berkas->hasSp3()) {
            throw new DomainException('Perkara yang telah diterbitkan SP3 tidak dapat dilanjutkan ke tahap apa pun.');
        }

        if ($berkas->status_formal === 'tidak_selesai') {
            throw new DomainException('Berkas perkara dengan status Tidak Selesai tidak dapat dilanjutkan ke tahapan berikutnya.');
        }

        if ($berkas->hasTahap(JenisTahap::KEPUTUSAN_PENGADILAN)) {
            throw new DomainException('Perkara telah selesai hingga Keputusan Pengadilan dan seluruh tahapan telah terkunci permanen.');
        }

        if (! $isDraft && $berkas->is_dpo) {
            throw new DomainException('Tidak dapat melanjutkan tahapan perkara karena Tersangka berstatus DPO Aktif.');
        }

        if ($berkas->hasTahap($targetTahap)) {
            throw new DomainException("Tahapan {$targetTahap->label()} sudah pernah dicatat pada Berkas Perkara ini. Satu jenis tahapan maksimal 1 kali.");
        }

        if ($targetTahap->isOptional()) {
            if (in_array($targetTahap, [JenisTahap::P16, JenisTahap::P17P], true) && ! $berkas->hasFinalTahap(JenisTahap::SPDP) && ! $berkas->hasTahap(JenisTahap::SPDP)) {
                throw new DomainException("Sub-tahapan {$targetTahap->label()} hanya dapat dicatat setelah SPDP ada.");
            }
            if (in_array($targetTahap, [JenisTahap::P18P, JenisTahap::P19], true) && ! $berkas->hasFinalTahap(JenisTahap::TAHAP_1)) {
                throw new DomainException("Sub-tahapan {$targetTahap->label()} hanya dapat dicatat setelah Tahap I difinalkan.");
            }

            return;
        }

        $available = $this->getNextAvailableStages($berkas, ignoreDpo: true);

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
            throw new DomainException("Tahapan {$tahap->jenis_tahap->label()} telah difinalkan dan terkunci permanen. Administrator pun tidak dapat mengedit atau memundurkan tahap yang telah dikunci.");
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
        UploadedFile|array|null $file,
        User $user,
        ?TahapanPerkara $existingTahap = null
    ): TahapanPerkara {
        return DB::transaction(function () use ($berkas, $data, $file, $user, $existingTahap): TahapanPerkara {
            $isDraft = ($data['action_type'] ?? 'lanjut') === 'draft';
            $jenisTahap = $existingTahap !== null
                ? $existingTahap->jenis_tahap
                : ($data['jenis_tahap'] instanceof JenisTahap ? $data['jenis_tahap'] : JenisTahap::from((string) $data['jenis_tahap']));

            if ($berkas->hasSp3()) {
                throw new DomainException('Perkara yang telah diterbitkan SP3 tidak dapat diedit atau dilanjutkan ke tahap apa pun.');
            }

            if ($berkas->status_formal === 'tidak_selesai') {
                throw new DomainException('Berkas perkara dengan status Tidak Selesai tidak dapat diedit atau dilanjutkan ke tahap apa pun.');
            }

            if ($existingTahap !== null) {
                if ($existingTahap->isFinal()) {
                    // Hanya SPDP dan Tahap 1 yang diizinkan diakses saat final untuk melengkapi sub-tahap
                    if (! in_array($existingTahap->jenis_tahap, [JenisTahap::SPDP, JenisTahap::TAHAP_1], true)) {
                        $this->assertStageNotLocked($existingTahap);
                    }
                }
            } else {
                $this->validateStageOrder($berkas, $jenisTahap, $isDraft);
            }

            // DPO Gate Menyeluruh: PRD F-24 s.d. F-27, D-053
            if (! $isDraft && $berkas->is_dpo) {
                throw new DomainException("Tidak dapat melanjutkan ke tahapan {$jenisTahap->label()} karena Tersangka berstatus DPO Aktif.");
            }

            // Keputusan Pengadilan Terminal Gate: PRD F-18, F-22, D-053
            if ($berkas->hasTahap(JenisTahap::KEPUTUSAN_PENGADILAN)) {
                $finalPutusan = $berkas->tahapan()->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN)->where('status', 'final')->first();
                if ($finalPutusan) {
                    throw new DomainException('Seluruh tahapan perkara telah terkunci permanen setelah Keputusan Pengadilan dicatat.');
                }
            }

            // Jika tahap induk sudah berstatus final (hanya boleh SPDP atau Tahap 1 untuk melengkapi sub-tahap),
            // lindungi data induk dari modifikasi, unggah file induk, atau pencatatan ulang finalisasi.
            if ($existingTahap !== null && $existingTahap->isFinal()) {
                $tahap = $existingTahap;

                // Simpan sub-tahap opsional tersemat jika ada input
                if ($jenisTahap === JenisTahap::SPDP) {
                    $this->saveSubStage($berkas, JenisTahap::P16, 'p16', $data, request()->file('p16_dokumen'), $user);
                    $this->saveSubStage($berkas, JenisTahap::P17P, 'p17p', $data, request()->file('p17p_dokumen'), $user);
                } elseif ($jenisTahap === JenisTahap::TAHAP_1) {
                    $this->saveSubStage($berkas, JenisTahap::P18P, 'p18p', $data, request()->file('p18p_dokumen'), $user);
                    $this->saveSubStage($berkas, JenisTahap::P19, 'p19', $data, request()->file('p19_dokumen'), $user);
                }

                return $tahap;
            }

            $tahapData = [
                'berkas_perkara_id' => $berkas->id,
                'jenis_tahap' => $jenisTahap,
                'catatan' => $data['catatan'] ?? null,
                'updated_by' => $user->id,
            ];

            if ($jenisTahap === JenisTahap::KEPUTUSAN_PENGADILAN) {
                $tahapData['pengadilan_instansi_id'] = ! empty($data['pengadilan_instansi_id']) ? (int) $data['pengadilan_instansi_id'] : null;
                $tahapData['amar_putusan'] = ! empty($data['amar_putusan']) ? trim($data['amar_putusan']) : null;
            }

            // Jika belum final, update nomor dan tanggal
            if ($existingTahap === null || ! $existingTahap->isFinal()) {
                $tahapData['nomor_surat'] = $data['nomor_surat'] ?? null;
                $tahapData['tanggal_surat'] = ! empty($data['tanggal_surat'])
                    ? $data['tanggal_surat']
                    : ($existingTahap?->tanggal_surat?->format('Y-m-d') ?? null);
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

            // Hapus dokumen lama jika ditandai untuk dihapus pada mode draft
            if (! empty($data['deleted_dokumen_ids']) && $existingTahap !== null && ! $existingTahap->isFinal()) {
                $docsToDelete = $existingTahap->dokumen()
                    ->whereIn('id', (array) $data['deleted_dokumen_ids'])
                    ->get();
                foreach ($docsToDelete as $doc) {
                    $doc->delete();
                }
            }

            // Simpan dokumen jika diunggah (mendukung berkas tunggal maupun berkas jamak/array)
            if ($file !== null) {
                $filesToProcess = is_array($file) ? $file : [$file];
                foreach ($filesToProcess as $singleFile) {
                    if ($singleFile instanceof UploadedFile) {
                        $path = $singleFile->store('dokumen/tahapan', 'local');
                        Dokumen::create([
                            'perkara_id' => $berkas->perkara_id,
                            'berkas_perkara_id' => $berkas->id,
                            'tahapan_perkara_id' => $tahap->id,
                            'kategori' => 'tahapan',
                            'nama_dokumen' => "Dokumen {$jenisTahap->label()} - {$tahap->nomor_surat}",
                            'nama_file_asli' => $singleFile->getClientOriginalName(),
                            'file_path' => $path,
                            'file_size' => $singleFile->getSize() ?: 0,
                            'created_by' => $user->id,
                            'updated_by' => $user->id,
                        ]);
                    }
                }
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
                    $tanggalSpdp = Carbon::parse($tahap->tanggal_surat);
                    $updateBerkas['tanggal_spdp'] = $tanggalSpdp->toDateString();
                }

                // SOP SLA 1 Bulan Kalender (30 hari) direset per tahapan aktif (SPDP, TAHAP_1, P21) - Poin 5
                if (in_array($jenisTahap, [JenisTahap::SPDP, JenisTahap::TAHAP_1, JenisTahap::P21], true) && ! empty($tahap->tanggal_surat)) {
                    $tglTahap = Carbon::parse($tahap->tanggal_surat);
                    $sla1Bulan = $tglTahap->copy()->addMonthNoOverflow();
                    $akhirTahun = $tglTahap->copy()->endOfYear();
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
     * @return array<int, array{nama: string, nomor: string, tanggal: string, status: string, dokumen: string|null, url: string|null}>
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
                    'url' => route('laporan-kejadian.show', $perkara),
                ];
            }

            if ($perkara->tahap_sprindik_status === 'final') {
                $references[] = [
                    'nama' => 'Surat Perintah Penyidikan (Sprindik)',
                    'nomor' => (string) ($perkara->nomor_surat_perintah ?? '-'),
                    'tanggal' => $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-',
                    'status' => 'Final / Terkunci',
                    'dokumen' => $perkara->dokumenSprindik?->nama_file_asli,
                    'url' => route('sprindik.show', $perkara),
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
                'url' => route('berkas-perkara.tahapan.show', [$berkas, $t]),
            ];
        }

        return $references;
    }

    /**
     * Helper query terpadu untuk daftar tahapan perkara dengan filter & paginasi.
     *
     * @param  array<string>|string  $jenisTahap
     */
    protected function paginateTahapanQuery(
        array|string $jenisTahap,
        Request $request,
        int $perPage = 10,
        ?string $subStage = null
    ): LengthAwarePaginator {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        $query = TahapanPerkara::query();

        if (is_array($jenisTahap)) {
            $query->whereIn('jenis_tahap', $jenisTahap);
            if ($subStage !== null && $subStage !== 'all' && $subStage !== '') {
                $query->where('jenis_tahap', $subStage);
            }
        } else {
            $query->where('jenis_tahap', $jenisTahap);
        }

        return $query
            ->when($status !== '', fn ($q) => $q->where('status', strtolower($status)))
            ->when($tanggalDari !== '', fn ($q) => $q->whereDate('tanggal_surat', '>=', $tanggalDari))
            ->when($tanggalSampai !== '', fn ($q) => $q->whereDate('tanggal_surat', '<=', $tanggalSampai))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('berkasPerkara', function (Builder $bQ) use ($search) {
                            $bQ->where('nomor_berkas', 'like', "%{$search}%")
                                ->orWhereHas('tersangka', function (Builder $tQ) use ($search) {
                                    $tQ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nik', 'like', "%{$search}%");
                                })
                                ->orWhereHas('perkara', function (Builder $pQ) use ($search) {
                                    $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->with([
                'berkasPerkara.perkara.penuntutUmumInstansi',
                'berkasPerkara.tersangka',
                'dokumen',
                'pengadilanInstansi',
                'createdBy',
                'finalizedBy',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Helper query berkas perkara yang siap lanjut ke tahapan berikutnya.
     */
    protected function queryAvailableBerkas(
        JenisTahap $requiredPreviousStage,
        JenisTahap $targetStage,
        bool $dpoCheck = true
    ): Collection {
        return BerkasPerkara::query()
            ->whereHas('tahapan', function (Builder $q) use ($requiredPreviousStage) {
                $q->where('jenis_tahap', $requiredPreviousStage->value)->where('status', 'final');
            })
            ->whereDoesntHave('tahapan', function (Builder $q) use ($targetStage) {
                $q->where('jenis_tahap', $targetStage->value);
            })
            ->whereDoesntHave('tahapan', function (Builder $q) {
                $q->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN->value)->where('status', 'final');
            })
            ->whereDoesntHave('sp3')
            ->where(fn (Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->when($dpoCheck, fn ($q) => $q->where('is_dpo', false))
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Mengambil daftar tahapan SPDP (termasuk sub-tahap opsional P-16 dan P-17P) dengan filter dan paginasi.
     */
    public function getSpdpList(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginateTahapanQuery(
            [JenisTahap::SPDP->value, JenisTahap::P16->value, JenisTahap::P17P->value],
            $request,
            $perPage,
            $request->string('sub_stage', 'all')->trim()->toString()
        );
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai SPDP (pra-SPDP final & belum ada SPDP).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForSpdp(): Collection
    {
        return BerkasPerkara::query()
            ->whereHas('perkara', function (Builder $q) {
                $q->where('tahap_perkara_status', 'final');
            })
            ->whereDoesntHave('tahapan', function (Builder $q) {
                $q->where('jenis_tahap', JenisTahap::SPDP->value);
            })
            ->whereDoesntHave('sp3')
            ->where('is_dpo', false)
            ->where(fn (Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('created_at')
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
        ?UploadedFile $file,
        User $user
    ): ?TahapanPerkara {
        $existing = TahapanPerkara::where('berkas_perkara_id', $berkas->id)
            ->where('jenis_tahap', $jenisTahap->value)
            ->first();

        $nomor = $data["{$prefix}_nomor_surat"] ?? ($existing?->nomor_surat ?? null);
        $tanggal = ! empty($data["{$prefix}_tanggal_surat"])
            ? $data["{$prefix}_tanggal_surat"]
            : ($existing?->tanggal_surat?->format('Y-m-d') ?? null);
        $catatan = $data["{$prefix}_catatan"] ?? ($existing?->catatan ?? null);

        if (empty($nomor) && empty($tanggal) && empty($catatan) && $file === null) {
            return null;
        }

        $isParentDraft = ($data['action_type'] ?? 'lanjut') === 'draft';
        $isSubDraft = $isParentDraft || empty($nomor) || empty($tanggal);

        if ($existing !== null && $existing->isFinal()) {
            $status = 'final';
            $finalizedAt = $existing->finalized_at ?? now();
            $finalizedBy = $existing->finalized_by ?? $user->id;
        } else {
            $status = $isSubDraft ? 'draft' : 'final';
            $finalizedAt = $status === 'final' ? now() : null;
            $finalizedBy = $status === 'final' ? $user->id : null;
        }

        $tahapData = [
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => $jenisTahap,
            'nomor_surat' => $nomor,
            'tanggal_surat' => $tanggal,
            'catatan' => $catatan,
            'status' => $status,
            'finalized_at' => $finalizedAt,
            'finalized_by' => $finalizedBy,
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
            Dokumen::create([
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
    public function getTahap1List(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginateTahapanQuery(
            [JenisTahap::TAHAP_1->value, JenisTahap::P18P->value, JenisTahap::P19->value],
            $request,
            $perPage,
            $request->string('sub_stage', 'all')->trim()->toString()
        );
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
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForTahap1(): Collection
    {
        return $this->queryAvailableBerkas(JenisTahap::SPDP, JenisTahap::TAHAP_1);
    }

    /**
     * Mengambil daftar tahapan P-21 dengan filter dan paginasi.
     */
    public function getP21List(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginateTahapanQuery(JenisTahap::P21->value, $request, $perPage);
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai P-21 (Tahap 1 final & belum ada P-21 final).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForP21(): Collection
    {
        return $this->queryAvailableBerkas(JenisTahap::TAHAP_1, JenisTahap::P21);
    }

    /**
     * Mengambil daftar tahapan Tahap 2 dengan filter dan paginasi.
     */
    public function getTahap2List(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginateTahapanQuery(JenisTahap::TAHAP_2->value, $request, $perPage);
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai Tahap 2 (P-21 final & belum ada Tahap 2).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForTahap2(): Collection
    {
        return $this->queryAvailableBerkas(JenisTahap::P21, JenisTahap::TAHAP_2, true);
    }

    /**
     * Mengambil daftar Putusan Perkara (Keputusan Pengadilan) dengan filter dan paginasi.
     */
    public function getPutusanPerkaraList(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginateTahapanQuery(JenisTahap::KEPUTUSAN_PENGADILAN->value, $request, $perPage);
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap mencatat Putusan Pengadilan (Tahap 2 final & belum ada Putusan).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForPutusan(): Collection
    {
        return $this->queryAvailableBerkas(JenisTahap::TAHAP_2, JenisTahap::KEPUTUSAN_PENGADILAN);
    }
}
