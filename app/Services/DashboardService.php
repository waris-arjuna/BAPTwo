<?php

namespace App\Services;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Zona waktu operasional BPOM Kalimantan Selatan (WITA / UTC+8).
     */
    public const ZONA_WAKTU = 'Asia/Makassar';

    /**
     * Ambang batas hari sisa SLA untuk kategori Mendekati Batas SLA (<= 7 hari).
     */
    public const AMBANG_MENDEKATI_SLA_HARI = 7;

    /**
     * Ambang batas persentase progress perkara tahap awal (<= 15%, yaitu bobot tahapan SPDP).
     * Berkas pada tahap awal ini memerlukan tindakan atau verifikasi penyidik untuk melangkah ke tahapan berikutnya.
     */
    public const AMBANG_PROGRESS_AWAL_PERSEN = 15;

    public function __construct(
        protected TahapanService $tahapanService
    ) {}

    /**
     * Mengambil 5 metrik statistik utama untuk Dashboard BAPTwo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStatistics(?int $tahun = null): array
    {
        $tahun = $tahun ?: (int) Carbon::now(self::ZONA_WAKTU)->year;

        return Cache::remember("dashboard_stats_{$tahun}", 120, function () use ($tahun) {
            // Total Perkara tahun berjalan (berdasarkan tanggal Laporan Kejadian)
            $totalPerkara = Perkara::query()
                ->whereYear('tanggal_laporan_kejadian', $tahun)
                ->count();

            // Berkas Berjalan (belum berstatus formal selesai maupun tidak selesai)
            $berkasBerjalan = BerkasPerkara::query()
                ->tahunLk($tahun)
                ->dalamProses()
                ->count();

            // Menunggu Verifikasi / Tindakan Penyidik (memiliki draft aktif atau baru pada tahap awal <= 15%)
            // Dibatasi hanya pada berkas dalam proses (tidak menghitung berkas selesai atau SP3)
            $menungguTindakan = BerkasPerkara::query()
                ->tahunLk($tahun)
                ->dalamProses()
                ->where(function ($q) {
                    $q->whereHas('tahapan', function ($tQ) {
                        $tQ->where('status', 'draft');
                    })
                        ->orWhere(function ($subQ) {
                            $subQ->whereNull('progress_persen')
                                ->orWhere('progress_persen', '<=', self::AMBANG_PROGRESS_AWAL_PERSEN);
                        });
                })
                ->count();

            // Selesai (hanya dari status formal 'selesai')
            $berkasSelesai = BerkasPerkara::query()
                ->tahunLk($tahun)
                ->selesai()
                ->count();

            // Rata-rata waktu penyelesaian dari SPDP s.d. Tahap II
            $avgDays = $this->calculateAverageCompletionDays($tahun);
            $avgDaysText = $avgDays !== null ? round($avgDays).' Hari' : '-';

            return [
                [
                    'title' => 'Total Perkara',
                    'value' => (string) $totalPerkara,
                    'description' => "Perkara pada tahun {$tahun}",
                    'icon' => 'document',
                    'iconClass' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
                ],
                [
                    'title' => 'Perkara Berjalan',
                    'value' => (string) $berkasBerjalan,
                    'description' => 'Sedang dalam proses penyidikan',
                    'icon' => 'play-circle',
                    'iconClass' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                ],
                [
                    'title' => 'Menunggu Verifikasi Penyidik',
                    'value' => (string) $menungguTindakan,
                    'description' => 'Menunggu tindakan penyidik',
                    'icon' => 'verification',
                    'iconClass' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                ],
                [
                    'title' => 'Selesai',
                    'value' => (string) $berkasSelesai,
                    'description' => 'Perkara selesai',
                    'icon' => 'check',
                    'iconClass' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
                ],
                [
                    'title' => 'Rata-rata Waktu Penyelesaian',
                    'value' => $avgDaysText,
                    'description' => 'Dari SPDP s.d. Tahap II',
                    'icon' => 'time',
                    'iconClass' => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400',
                ],
            ];
        });
    }

    /**
     * Bersihkan cache statistik dashboard saat terjadi mutasi data.
     */
    public static function clearCache(?int $tahun = null): void
    {
        if ($tahun !== null) {
            Cache::forget("dashboard_stats_{$tahun}");
            Cache::forget("dashboard_stage_averages_{$tahun}");
        } else {
            $currentYear = (int) Carbon::now(self::ZONA_WAKTU)->year;
            for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
                Cache::forget("dashboard_stats_{$y}");
                Cache::forget("dashboard_stage_averages_{$y}");
            }
        }
    }

    /**
     * Mengambil daftar tahun yang memiliki data Laporan Kejadian untuk filter dropdown.
     *
     * @return array<int, int>
     */
    public function getAvailableYears(): array
    {
        $currentYear = (int) Carbon::now(self::ZONA_WAKTU)->year;

        $yearExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "DISTINCT strftime('%Y', tanggal_laporan_kejadian) as tahun"
            : 'DISTINCT YEAR(tanggal_laporan_kejadian) as tahun';

        $years = Perkara::query()
            ->whereNotNull('tanggal_laporan_kejadian')
            ->selectRaw($yearExpr)
            ->pluck('tahun')
            ->map(fn ($y) => (int) $y);

        return $years->push($currentYear)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /**
     * Hitung persentase bilangan bulat menggunakan Largest Remainder Method (Hare-Niemeyer).
     * Menjamin total akumulasi persentase selalu tepat 100% jika ada data (> 0).
     *
     * @param  array<string, int>  $values
     * @return array<string, int>
     */
    public function calculatePercentages(array $values): array
    {
        $total = array_sum($values);
        if ($total <= 0) {
            return array_fill_keys(array_keys($values), 0);
        }

        $floored = [];
        $remainders = [];
        $sumFloored = 0;

        foreach ($values as $key => $val) {
            $float = ($val / $total) * 100;
            $int = (int) floor($float);
            $floored[$key] = $int;
            $remainders[$key] = $float - $int;
            $sumFloored += $int;
        }

        $remainingPoints = 100 - $sumFloored;
        arsort($remainders);

        foreach (array_keys($remainders) as $key) {
            if ($remainingPoints <= 0) {
                break;
            }
            if ($values[$key] > 0) {
                $floored[$key] += 1;
                $remainingPoints--;
            }
        }

        return $floored;
    }

    /**
     * Mengambil data status perkara untuk Donut Chart pada Dashboard.
     * Menggunakan acuan tanggal Laporan Kejadian (LK) dan agregasi tunggal efisien.
     *
     * @return array<string, mixed>
     */
    public function getCaseStatusData(?int $tahun = null, ?int $bulan = null): array
    {
        $tahun = $tahun ?: (int) Carbon::now(self::ZONA_WAKTU)->year;
        $bulan = ($bulan && $bulan >= 1 && $bulan <= 12) ? $bulan : null;

        $today = Carbon::today(self::ZONA_WAKTU)->toDateString();
        $approachingDate = Carbon::today(self::ZONA_WAKTU)->addDays(self::AMBANG_MENDEKATI_SLA_HARI)->toDateString();

        $baseQuery = BerkasPerkara::query()->tahunLk($tahun, $bulan);

        $selesaiVal = BerkasPerkara::STATUS_FORMAL_SELESAI;
        $tidakSelesaiVal = BerkasPerkara::STATUS_FORMAL_TIDAK_SELESAI;
        $dalamProsesVal = BerkasPerkara::STATUS_FORMAL_DALAM_PROSES;

        $result = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status_formal = ? THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status_formal = ? THEN 1 ELSE 0 END) as tidak_selesai,
                SUM(CASE WHEN status_formal IS NULL OR status_formal = ? THEN 1 ELSE 0 END) as dalam_proses,
                SUM(CASE WHEN (status_formal IS NULL OR status_formal = ?) AND sla_deadline IS NULL THEN 1 ELSE 0 END) as belum_spdp,
                SUM(CASE WHEN (status_formal IS NULL OR status_formal = ?) AND sla_deadline IS NOT NULL AND DATE(sla_deadline) > ? THEN 1 ELSE 0 END) as dalam_sla,
                SUM(CASE WHEN (status_formal IS NULL OR status_formal = ?) AND sla_deadline IS NOT NULL AND DATE(sla_deadline) >= ? AND DATE(sla_deadline) <= ? THEN 1 ELSE 0 END) as mendekati_sla,
                SUM(CASE WHEN (status_formal IS NULL OR status_formal = ?) AND sla_deadline IS NOT NULL AND DATE(sla_deadline) < ? THEN 1 ELSE 0 END) as lewat_sla
            ', [
                $selesaiVal,
                $tidakSelesaiVal,
                $dalamProsesVal,
                $dalamProsesVal,
                $dalamProsesVal,
                $approachingDate,
                $dalamProsesVal,
                $today,
                $approachingDate,
                $dalamProsesVal,
                $today,
            ])
            ->first();

        $total = (int) ($result->total ?? 0);
        $selesai = (int) ($result->selesai ?? 0);
        $tidakSelesai = (int) ($result->tidak_selesai ?? 0);
        $dalamProses = (int) ($result->dalam_proses ?? 0);

        $belumSpdp = (int) ($result->belum_spdp ?? 0);
        $dalamSla = (int) ($result->dalam_sla ?? 0);
        $mendekatiSla = (int) ($result->mendekati_sla ?? 0);
        $lewatSla = (int) ($result->lewat_sla ?? 0);

        $percentages = $this->calculatePercentages([
            'selesai' => $selesai,
            'dalamProses' => $dalamProses,
            'tidakSelesai' => $tidakSelesai,
        ]);

        // Perhitungan perbandingan dengan periode tahun lalu (tahun - 1)
        $prevTahun = $tahun - 1;
        $totalPrev = BerkasPerkara::query()
            ->tahunLk($prevTahun, $bulan)
            ->count();

        $diff = $total - $totalPrev;
        if ($totalPrev === 0 && $total === 0) {
            $comparison = [
                'direction' => 'none',
                'text' => 'Belum ada perkara di '.$prevTahun,
                'diff' => 0,
            ];
        } elseif ($diff > 0) {
            $comparison = [
                'direction' => 'up',
                'text' => '▲ '.$diff.' perkara dibanding '.$prevTahun,
                'diff' => $diff,
            ];
        } elseif ($diff < 0) {
            $comparison = [
                'direction' => 'down',
                'text' => '▼ '.abs($diff).' perkara dibanding '.$prevTahun,
                'diff' => $diff,
            ];
        } else {
            $comparison = [
                'direction' => 'same',
                'text' => 'Sama dengan '.$prevTahun,
                'diff' => 0,
            ];
        }

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $periodeText = $bulan && isset($bulanNames[$bulan])
            ? "{$bulanNames[$bulan]} {$tahun}"
            : "Tahun {$tahun}";

        $updatedAt = Carbon::now(self::ZONA_WAKTU)->translatedFormat('d M Y, H:i').' WITA';

        return [
            'totalPerkara' => $total,
            'selesai' => $selesai,
            'dalamProses' => $dalamProses,
            'tidakSelesai' => $tidakSelesai,
            'selesaiPercent' => $percentages['selesai'],
            'dalamProsesPercent' => $percentages['dalamProses'],
            'tidakSelesaiPercent' => $percentages['tidakSelesai'],
            'sla' => [
                'belumSpdp' => $belumSpdp,
                'dalamSla' => $dalamSla,
                'mendekatiSla' => $mendekatiSla,
                'lewatSla' => $lewatSla,
            ],
            'comparison' => $comparison,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'periodeText' => $periodeText,
            'availableYears' => $this->getAvailableYears(),
            'updatedAt' => $updatedAt,
        ];
    }

    /**
     * Mengambil data rata-rata waktu penyelesaian per tahapan (dalam Hari Kerja).
     * Mengukur selisih hari kerja antar-tahap berurutan:
     * - SPDP: Sprindik -> SPDP
     * - TAHAP I: SPDP -> TAHAP I
     * - P-21: TAHAP I -> P-21
     * - TAHAP II: P-21 -> TAHAP II
     *
     * @return array<int, array{name: string, value: string, sample: int}>
     */
    public function getStageAverageDays(?int $tahun = null): array
    {
        $tahun = $tahun ?: (int) Carbon::now(self::ZONA_WAKTU)->year;

        return Cache::remember("dashboard_stage_averages_{$tahun}", 120, function () use ($tahun) {
            $berkasList = BerkasPerkara::query()
                ->tahunLk($tahun)
                ->with([
                    'perkara:id,tanggal_surat_perintah',
                    'tahapan' => function ($q) {
                        $q->where('status', 'final')
                            ->whereIn('jenis_tahap', [
                                JenisTahap::SPDP->value,
                                JenisTahap::TAHAP_1->value,
                                JenisTahap::P21->value,
                                JenisTahap::TAHAP_2->value,
                            ])
                            ->select('id', 'berkas_perkara_id', 'jenis_tahap', 'tanggal_surat');
                    },
                ])
                ->get(['id', 'perkara_id', 'tanggal_spdp']);

            $spdpDays = [];
            $tahap1Days = [];
            $p21Days = [];
            $tahap2Days = [];

            foreach ($berkasList as $berkas) {
                $tahapanMap = $berkas->tahapan->keyBy(fn ($t) => $t->jenis_tahap->value);

                $tglSprindik = $berkas->perkara?->tanggal_surat_perintah ? Carbon::parse($berkas->perkara->tanggal_surat_perintah) : null;
                $spdpTahap = $tahapanMap->get(JenisTahap::SPDP->value);
                $tglSpdp = $spdpTahap?->tanggal_surat ? Carbon::parse($spdpTahap->tanggal_surat) : ($berkas->tanggal_spdp ? Carbon::parse($berkas->tanggal_spdp) : null);

                $tahap1Tahap = $tahapanMap->get(JenisTahap::TAHAP_1->value);
                $tglTahap1 = $tahap1Tahap?->tanggal_surat ? Carbon::parse($tahap1Tahap->tanggal_surat) : null;

                $p21Tahap = $tahapanMap->get(JenisTahap::P21->value);
                $tglP21 = $p21Tahap?->tanggal_surat ? Carbon::parse($p21Tahap->tanggal_surat) : null;

                $tahap2Tahap = $tahapanMap->get(JenisTahap::TAHAP_2->value);
                $tglTahap2 = $tahap2Tahap?->tanggal_surat ? Carbon::parse($tahap2Tahap->tanggal_surat) : null;

                // 1. SPDP: Sprindik -> SPDP
                if ($tglSprindik && $tglSpdp && $tglSpdp->greaterThanOrEqualTo($tglSprindik)) {
                    $spdpDays[] = $tglSprindik->diffInWeekdays($tglSpdp);
                }

                // 2. TAHAP I: SPDP -> TAHAP I
                if ($tglSpdp && $tglTahap1 && $tglTahap1->greaterThanOrEqualTo($tglSpdp)) {
                    $tahap1Days[] = $tglSpdp->diffInWeekdays($tglTahap1);
                }

                // 3. P-21: TAHAP I -> P-21
                if ($tglTahap1 && $tglP21 && $tglP21->greaterThanOrEqualTo($tglTahap1)) {
                    $p21Days[] = $tglTahap1->diffInWeekdays($tglP21);
                }

                // 4. TAHAP II: P-21 -> TAHAP II
                if ($tglP21 && $tglTahap2 && $tglTahap2->greaterThanOrEqualTo($tglP21)) {
                    $tahap2Days[] = $tglP21->diffInWeekdays($tglTahap2);
                }
            }

            $formatAverage = function (array $days): string {
                if (count($days) === 0) {
                    return '- Hari';
                }

                return (string) round(array_sum($days) / count($days)).' Hari';
            };

            return [
                [
                    'name' => 'SPDP',
                    'value' => $formatAverage($spdpDays),
                    'sample' => count($spdpDays),
                ],
                [
                    'name' => 'TAHAP I',
                    'value' => $formatAverage($tahap1Days),
                    'sample' => count($tahap1Days),
                ],
                [
                    'name' => 'P-21',
                    'value' => $formatAverage($p21Days),
                    'sample' => count($p21Days),
                ],
                [
                    'name' => 'TAHAP II',
                    'value' => $formatAverage($tahap2Days),
                    'sample' => count($tahap2Days),
                ],
            ];
        });
    }

    /**
     * Mengambil daftar Berkas Perkara prioritas (auto-filter progress terendah & tanggal tertua, maks 5 baris).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPriorityProgressPerkara(Request $request, int $limit = 5): array
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        $query = BerkasPerkara::query()
            ->with([
                'perkara.dokumenSprindik',
                'perkara.ppnsUtama',
                'perkara.ppnsAnggota',
                'tersangka',
                'pasals',
                'tahapan',
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nomor_berkas', 'like', "%{$search}%")
                        ->orWhere('status_proses', 'like', "%{$search}%")
                        ->orWhereHas('tersangka', function ($tQ) use ($search) {
                            $tQ->where('nama', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%");
                        })
                        ->orWhereHas('perkara', function ($pQ) use ($search) {
                            $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                        })
                        ->orWhereHas('pasals', function ($psQ) use ($search) {
                            $psQ->where('nama_pasal', 'like', "%{$search}%")
                                ->orWhere('pasal', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function ($q) use ($status) {
                if (strtolower($status) === 'dalam proses') {
                    $q->dalamProses();
                } elseif (strtolower($status) === 'selesai') {
                    $q->selesai();
                } elseif (strtolower($status) === 'tidak selesai') {
                    $q->tidakSelesai();
                }
            }, function ($q) {
                // default (tidak ada filter status dipilih): hanya tampilkan berkas dalam proses (sembunyikan Selesai DAN Tidak Selesai)
                $q->dalamProses();
            })
            ->when($tanggalDari !== '', function ($q) use ($tanggalDari) {
                $q->whereDate('tanggal_spdp', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function ($q) use ($tanggalSampai) {
                $q->whereDate('tanggal_spdp', '<=', $tanggalSampai);
            })
            ->orderByRaw('sla_deadline IS NULL')
            ->orderBy('sla_deadline', 'asc')
            ->orderBy('progress_persen', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit($limit);

        $berkasCollection = $query->get();

        return $berkasCollection->map(function (BerkasPerkara $b) {
            $perkara = $b->perkara;
            $tersangka = $b->tersangka;

            // Kumpulkan tahapan yang sudah ada
            $tahapanMap = $b->tahapan->keyBy(fn ($t) => $t->jenis_tahap->value);

            $spdpTahap = $tahapanMap->get(JenisTahap::SPDP->value);
            $tahap1Tahap = $tahapanMap->get(JenisTahap::TAHAP_1->value);
            $p21Tahap = $tahapanMap->get(JenisTahap::P21->value);
            $tahap2Tahap = $tahapanMap->get(JenisTahap::TAHAP_2->value);

            $statusFormal = $b->status_formal ?: 'Dalam Proses';
            if ($statusFormal === 'selesai') {
                $statusFormal = 'Selesai';
            } elseif ($statusFormal === 'tidak_selesai') {
                $statusFormal = 'Tidak Selesai';
            }

            $pasalList = $b->pasals->pluck('nama_pasal')->join(', ');
            if (empty($pasalList)) {
                $pasalList = $b->pasals->pluck('pasal')->join(', ') ?: '-';
            }

            // Estimasi SLA (1 bulan kalender dari SPDP)
            $estimasi = '-';
            if ($b->sla_deadline) {
                $estimasi = Carbon::parse($b->sla_deadline)->format('d-m-Y');
            } elseif ($spdpTahap && $spdpTahap->tanggal_surat) {
                $estimasi = Carbon::parse($spdpTahap->tanggal_surat)->addMonth()->format('d-m-Y');
            }

            return [
                'id' => $b->id,
                'perkara_id' => $b->perkara_id,
                'nomor' => $b->nomor_berkas ?: '(Draft Belum Bernomor)',
                'tersangka' => $tersangka?->nama ?: '-',
                'nik' => $tersangka?->nik ?: '-',
                'alias' => $tersangka?->alias ?: null,
                'is_dpo' => (bool) $b->is_dpo,
                'pasal' => $pasalList,
                'tahap' => $b->status_proses ?: 'Baru',
                'progress' => (int) ($b->progress_persen ?? 0),
                'status' => $statusFormal,
                'spdp' => $spdpTahap?->tanggal_surat?->format('d-m-Y') ?? null,
                'tahap1' => $tahap1Tahap?->tanggal_surat?->format('d-m-Y') ?? null,
                'p21' => $p21Tahap?->tanggal_surat?->format('d-m-Y') ?? null,
                'tahap2' => $tahap2Tahap?->tanggal_surat?->format('d-m-Y') ?? null,
                'estimasi' => $estimasi,
                'sprindik' => $perkara?->nomor_surat_perintah ?: '-',
                'tanggal_sprindik' => $perkara?->tanggal_surat_perintah?->format('d-m-Y') ?? '-',
                'ppns_utama' => $perkara?->ppnsUtama?->first()?->nama ?: '-',
                'ppns_anggota' => $perkara?->ppnsAnggota?->pluck('nama')->join(', ') ?: '-',
                'detail_url' => $perkara ? route('perkara.show', $perkara->id) : '#',
                'tahapan_list' => $b->tahapan->map(fn ($t) => [
                    'jenis' => $t->jenis_tahap->label(),
                    'nomor' => $t->nomor_surat ?: '-',
                    'tanggal' => $t->tanggal_surat?->format('d M Y') ?? '-',
                    'status' => $t->isFinal() ? 'Final' : 'Draft',
                ])->toArray(),
            ];
        })->toArray();
    }

    /**
     * Menghitung rata-rata hari penyelesaian dari tanggal SPDP ke Tahap 2.
     */
    protected function calculateAverageCompletionDays(?int $tahun = null): ?float
    {
        $selesaiList = BerkasPerkara::query()
            ->when($tahun !== null, fn ($q) => $q->tahunLk($tahun))
            ->selesai()
            ->with([
                'tahapan' => function ($q) {
                    $q->where('status', 'final')
                        ->whereIn('jenis_tahap', [
                            JenisTahap::SPDP->value,
                            JenisTahap::TAHAP_2->value,
                        ])
                        ->select('id', 'berkas_perkara_id', 'jenis_tahap', 'tanggal_surat', 'status');
                },
            ])
            ->get(['id', 'perkara_id']);

        if ($selesaiList->isEmpty()) {
            return null;
        }

        $durations = [];
        foreach ($selesaiList as $berkas) {
            $tahapanMap = $berkas->tahapan->keyBy(fn ($t) => $t->jenis_tahap instanceof JenisTahap ? $t->jenis_tahap->value : $t->jenis_tahap);
            $spdp = $tahapanMap->get(JenisTahap::SPDP->value);
            $tahap2 = $tahapanMap->get(JenisTahap::TAHAP_2->value);

            if ($spdp && $tahap2 && $spdp->tanggal_surat && $tahap2->tanggal_surat) {
                $tglSpdp = Carbon::parse($spdp->tanggal_surat);
                $tglTahap2 = Carbon::parse($tahap2->tanggal_surat);

                // Lewati berkas jika tanggal Tahap II lebih awal dari tanggal SPDP (data anomali)
                if ($tglTahap2->lessThan($tglSpdp)) {
                    continue;
                }

                $durations[] = $tglSpdp->diffInDays($tglTahap2);
            }
        }

        return ! empty($durations) ? (array_sum($durations) / count($durations)) : null;
    }
}
