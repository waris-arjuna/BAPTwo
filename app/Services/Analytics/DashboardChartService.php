<?php

namespace App\Services\Analytics;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardChartService
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
     * Terapkan filter periode pada query Perkara (atau query dengan kolom tanggal LK/surat).
     */
    public function applyPerkaraDateFilter(
        $query,
        string $mode,
        ?int $tahun,
        ?int $bulan,
        ?string $tanggalMulai,
        ?string $tanggalSelesai,
        string $column = 'tanggal_laporan_kejadian'
    ): void {
        if ($mode === 'semua') {
            $query->whereNotNull($column);
        } elseif ($mode === 'bulan') {
            $query->whereYear($column, $tahun)
                ->whereMonth($column, $bulan);
        } elseif ($mode === 'rentang' && $tanggalMulai && $tanggalSelesai) {
            $query->whereBetween($column, [$tanggalMulai, $tanggalSelesai]);
        } else {
            $query->whereYear($column, $tahun);
        }
    }

    /**
     * Terapkan filter periode berdasarkan tanggal Laporan Kejadian pada query BerkasPerkara.
     */
    public function applyBerkasPeriodFilter(
        $query,
        string $mode,
        ?int $tahun,
        ?int $bulan,
        ?string $tanggalMulai,
        ?string $tanggalSelesai
    ): void {
        if ($mode === 'semua') {
            $query->whereHas('perkara');
        } elseif ($mode === 'bulan') {
            $query->tahunLk((int) $tahun, $bulan);
        } elseif ($mode === 'rentang' && $tanggalMulai && $tanggalSelesai) {
            $query->whereHas('perkara', function ($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereBetween('tanggal_laporan_kejadian', [$tanggalMulai, $tanggalSelesai]);
            });
        } else {
            $query->tahunLk((int) $tahun);
        }
    }

    /**
     * Mengambil data tren bulanan komparasi Perkara Masuk vs Selesai (Jan-Des) untuk ApexCharts.
     *
     * @return array{
     *     labels: array<int, string>,
     *     incoming: array<int, int>,
     *     completed: array<int, int>,
     *     totalIncoming: int,
     *     totalCompleted: int,
     *     clearanceRate: float
     * }
     */
    public function getMonthlyCasesTrends(
        ?int $tahun = null,
        ?string $mode = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null
    ): array {
        $tahun = $tahun ?: (int) Carbon::now(self::ZONA_WAKTU)->year;
        $mode = $mode ?: 'tahun';
        $effectiveTrendMode = ($mode === 'bulan') ? 'tahun' : $mode;

        $computeTrends = function () use ($tahun, $effectiveTrendMode, $tanggalMulai, $tanggalSelesai) {
            $isSqlite = DB::connection()->getDriverName() === 'sqlite';

            $incomingMonthExpr = $isSqlite
                ? "CAST(strftime('%m', tanggal_laporan_kejadian) AS INTEGER) as bulan"
                : 'MONTH(tanggal_laporan_kejadian) as bulan';

            $incomingGroupExpr = $isSqlite
                ? "CAST(strftime('%m', tanggal_laporan_kejadian) AS INTEGER)"
                : 'MONTH(tanggal_laporan_kejadian)';

            $incomingQuery = Perkara::query();
            $this->applyPerkaraDateFilter($incomingQuery, $effectiveTrendMode, $tahun, null, $tanggalMulai, $tanggalSelesai, 'tanggal_laporan_kejadian');

            $incomingRaw = $incomingQuery
                ->selectRaw("{$incomingMonthExpr}, COUNT(*) as total")
                ->groupByRaw($incomingGroupExpr)
                ->pluck('total', 'bulan')
                ->all();

            $completedMonthExpr = $isSqlite
                ? "CAST(strftime('%m', tanggal_surat) AS INTEGER) as bulan"
                : 'MONTH(tanggal_surat) as bulan';

            $completedGroupExpr = $isSqlite
                ? "CAST(strftime('%m', tanggal_surat) AS INTEGER)"
                : 'MONTH(tanggal_surat)';

            $completedQuery = TahapanPerkara::query()
                ->where('jenis_tahap', JenisTahap::TAHAP_2->value)
                ->where('status', 'final');

            if ($effectiveTrendMode === 'semua') {
                $completedQuery->whereNotNull('tanggal_surat')
                    ->whereHas('berkasPerkara', function ($q) {
                        $q->selesai()->whereHas('perkara');
                    });
            } elseif ($effectiveTrendMode === 'rentang' && $tanggalMulai && $tanggalSelesai) {
                $completedQuery->whereBetween('tanggal_surat', [$tanggalMulai, $tanggalSelesai])
                    ->whereHas('berkasPerkara', function ($q) use ($tanggalMulai, $tanggalSelesai) {
                        $q->selesai()->whereHas('perkara', function ($pq) use ($tanggalMulai, $tanggalSelesai) {
                            $pq->whereBetween('tanggal_laporan_kejadian', [$tanggalMulai, $tanggalSelesai]);
                        });
                    });
            } else {
                $completedQuery->whereYear('tanggal_surat', $tahun)
                    ->whereHas('berkasPerkara', function ($q) {
                        $q->selesai();
                    });
            }

            $completedRaw = $completedQuery
                ->selectRaw("{$completedMonthExpr}, COUNT(*) as total")
                ->groupByRaw($completedGroupExpr)
                ->pluck('total', 'bulan')
                ->all();

            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $incoming = [];
            $completed = [];

            for ($m = 1; $m <= 12; $m++) {
                $incoming[] = (int) ($incomingRaw[$m] ?? 0);
                $completed[] = (int) ($completedRaw[$m] ?? 0);
            }

            $totalIncoming = array_sum($incoming);
            $totalCompleted = array_sum($completed);
            $clearanceRate = $totalIncoming > 0
                ? round(($totalCompleted / $totalIncoming) * 100, 1)
                : 0.0;

            return [
                'labels' => $labels,
                'incoming' => $incoming,
                'completed' => $completed,
                'totalIncoming' => $totalIncoming,
                'totalCompleted' => $totalCompleted,
                'clearanceRate' => $clearanceRate,
            ];
        };

        if (in_array($effectiveTrendMode, ['semua', 'rentang'], true)) {
            return $computeTrends();
        }

        return Cache::remember("dashboard_monthly_cases_{$tahun}", 120, $computeTrends);
    }

    /**
     * Mengambil daftar tahun yang memiliki data Laporan Kejadian untuk filter dropdown.
     *
     * @return array<int, int>
     */
    public function getAvailableYears(): array
    {
        $currentYear = (int) Carbon::now(self::ZONA_WAKTU)->year;

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearExpression = $isSqlite
            ? "CAST(strftime('%Y', tanggal_laporan_kejadian) AS INTEGER)"
            : 'YEAR(tanggal_laporan_kejadian)';

        $yearsFromPerkara = Perkara::query()
            ->whereNotNull('tanggal_laporan_kejadian')
            ->selectRaw("DISTINCT {$yearExpression} as tahun")
            ->pluck('tahun')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->all();

        $yearsFromBerkas = BerkasPerkara::query()
            ->whereHas('perkara', fn ($q) => $q->whereNotNull('tanggal_laporan_kejadian'))
            ->join('perkara', 'berkas_perkara.perkara_id', '=', 'perkara.id')
            ->selectRaw("DISTINCT {$yearExpression} as tahun")
            ->pluck('tahun')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->all();

        $allYears = array_unique(array_merge([$currentYear], $yearsFromPerkara, $yearsFromBerkas));
        rsort($allYears);

        return array_values($allYears);
    }

    /**
     * Menghitung persentase integer bulat yang jumlah totalnya selalu tepat 100%.
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
    public function getCaseStatusData(
        ?int $tahun = null,
        ?int $bulan = null,
        ?string $mode = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null
    ): array {
        $tahun = $tahun ?: (int) Carbon::now(self::ZONA_WAKTU)->year;
        $effectiveMode = $mode ?: (($bulan && $bulan >= 1 && $bulan <= 12) ? 'bulan' : 'tahun');
        $bulan = ($bulan && $bulan >= 1 && $bulan <= 12) ? $bulan : null;

        $today = Carbon::today(self::ZONA_WAKTU)->toDateString();
        $approachingDate = Carbon::today(self::ZONA_WAKTU)->addDays(self::AMBANG_MENDEKATI_SLA_HARI)->toDateString();

        $baseQuery = BerkasPerkara::query();
        $this->applyBerkasPeriodFilter($baseQuery, $effectiveMode, $tahun, $bulan, $tanggalMulai, $tanggalSelesai);

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

        if (in_array($effectiveMode, ['semua', 'rentang'], true)) {
            $comparison = [
                'direction' => 'none',
                'text' => $effectiveMode === 'semua' ? 'Akumulasi semua data' : 'Filter rentang khusus',
                'diff' => 0,
            ];
        } else {
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
        }

        if ($effectiveMode === 'semua') {
            $periodeText = 'Semua Data';
        } elseif ($effectiveMode === 'rentang' && $tanggalMulai && $tanggalSelesai) {
            $periodeText = "{$tanggalMulai} s/d {$tanggalSelesai}";
        } else {
            $bulanNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $periodeText = $bulan && isset($bulanNames[$bulan])
                ? "{$bulanNames[$bulan]} {$tahun}"
                : "Tahun {$tahun}";
        }

        $updatedAt = Carbon::now(self::ZONA_WAKTU)->translatedFormat('d M Y, H:i').' WITA';

        $dpoAktif = (clone $baseQuery)
            ->dalamProses()
            ->where('is_dpo', true)
            ->count();

        return [
            'totalPerkara' => $total,
            'selesai' => $selesai,
            'dalamProses' => $dalamProses,
            'tidakSelesai' => $tidakSelesai,
            'dpoAktif' => $dpoAktif,
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
     * Mengukur selisih hari kerja antar-tahap berurutan.
     *
     * @return array<int, array{name: string, value: string, sample: int, active_count: int, active_label: string}>
     */
    public function getStageAverageDays(
        ?int $tahun = null,
        ?int $bulan = null,
        ?string $mode = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null
    ): array {
        $tahun = $tahun ?: (int) Carbon::now(self::ZONA_WAKTU)->year;
        $effectiveMode = $mode ?: (($bulan && $bulan >= 1 && $bulan <= 12) ? 'bulan' : 'tahun');
        $bulan = ($bulan && $bulan >= 1 && $bulan <= 12) ? $bulan : null;

        $computeStageAverages = function () use ($tahun, $bulan, $effectiveMode, $tanggalMulai, $tanggalSelesai) {
            $berkasQuery = BerkasPerkara::query();
            $this->applyBerkasPeriodFilter($berkasQuery, $effectiveMode, $tahun, $bulan, $tanggalMulai, $tanggalSelesai);

            $berkasList = $berkasQuery
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
                ->get(['id', 'perkara_id', 'tanggal_spdp', 'status_proses', 'status_formal']);

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

            $spdpActiveCount = $berkasList->filter(fn ($b) => $b->status_formal === null || $b->status_formal === BerkasPerkara::STATUS_FORMAL_DALAM_PROSES)
                ->filter(fn ($b) => in_array($b->status_proses, ['SPDP', 'P-16', 'P-17P'], true))
                ->count();

            $tahap1ActiveCount = $berkasList->filter(fn ($b) => $b->status_formal === null || $b->status_formal === BerkasPerkara::STATUS_FORMAL_DALAM_PROSES)
                ->filter(fn ($b) => in_array($b->status_proses, ['TAHAP I', 'P-18P', 'P-19'], true))
                ->count();

            $p21ActiveCount = $berkasList->filter(fn ($b) => $b->status_formal === null || $b->status_formal === BerkasPerkara::STATUS_FORMAL_DALAM_PROSES)
                ->filter(fn ($b) => $b->status_proses === 'P-21/P21a')
                ->count();

            $tahap2DoneCount = $berkasList->filter(fn ($b) => $b->status_formal === BerkasPerkara::STATUS_FORMAL_SELESAI)
                ->count();

            return [
                [
                    'name' => 'SPDP',
                    'value' => $formatAverage($spdpDays),
                    'sample' => count($spdpDays),
                    'active_count' => $spdpActiveCount,
                    'active_label' => "{$spdpActiveCount} berkas aktif",
                ],
                [
                    'name' => 'TAHAP I',
                    'value' => $formatAverage($tahap1Days),
                    'sample' => count($tahap1Days),
                    'active_count' => $tahap1ActiveCount,
                    'active_label' => "{$tahap1ActiveCount} berkas aktif",
                ],
                [
                    'name' => 'P-21',
                    'value' => $formatAverage($p21Days),
                    'sample' => count($p21Days),
                    'active_count' => $p21ActiveCount,
                    'active_label' => "{$p21ActiveCount} berkas aktif",
                ],
                [
                    'name' => 'TAHAP II',
                    'value' => $formatAverage($tahap2Days),
                    'sample' => count($tahap2Days),
                    'active_count' => $tahap2DoneCount,
                    'active_label' => "{$tahap2DoneCount} selesai",
                ],
            ];
        };

        if ($mode === null && $bulan === null) {
            return Cache::remember("dashboard_stage_averages_{$tahun}", 120, $computeStageAverages);
        }

        return $computeStageAverages();
    }

    /**
     * Mengambil sebaran wilayah TKP (Top Kabupaten/Kota) dan proporsi modus peredaran (Offline vs Online).
     *
     * @return array{
     *     totalWithTkp: int,
     *     offlineCount: int,
     *     onlineCount: int,
     *     offlinePercent: int,
     *     onlinePercent: int,
     *     regions: array<int, array{name: string, count: int, percent: int}>
     * }
     */
    public function getTkpDistribution(
        ?int $tahun = null,
        int $limit = 5,
        ?int $bulan = null,
        ?string $mode = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null
    ): array {
        $tahun = $tahun ?: (int) Carbon::now(self::ZONA_WAKTU)->year;
        $effectiveMode = $mode ?: (($bulan && $bulan >= 1 && $bulan <= 12) ? 'bulan' : 'tahun');
        $bulan = ($bulan && $bulan >= 1 && $bulan <= 12) ? $bulan : null;

        $computeTkp = function () use ($tahun, $limit, $bulan, $effectiveMode, $tanggalMulai, $tanggalSelesai) {
            $perkaraQuery = Perkara::query()->whereNotNull('tkp_id');
            $this->applyPerkaraDateFilter($perkaraQuery, $effectiveMode, $tahun, $bulan, $tanggalMulai, $tanggalSelesai, 'tanggal_laporan_kejadian');

            $perkaras = $perkaraQuery
                ->with(['tkp.alamat'])
                ->get(['id', 'tkp_id']);

            $offlineCount = 0;
            $onlineCount = 0;
            $regionCounts = [];

            foreach ($perkaras as $perkara) {
                $tkp = $perkara->tkp;
                if (! $tkp) {
                    continue;
                }

                $modus = strtolower((string) ($tkp->modus ?? ''));
                if ($modus === 'offline') {
                    $offlineCount++;
                } elseif ($modus === 'online') {
                    $onlineCount++;
                }

                $kabupaten = trim((string) ($tkp->alamat?->kabupaten_nama ?? ''));
                if ($kabupaten !== '') {
                    $regionCounts[$kabupaten] = ($regionCounts[$kabupaten] ?? 0) + 1;
                }
            }

            $modusPct = $this->calculatePercentages([
                'offline' => $offlineCount,
                'online' => $onlineCount,
            ]);

            $totalWithRegion = array_sum($regionCounts);

            uksort($regionCounts, function (string $a, string $b) use ($regionCounts) {
                $cmp = $regionCounts[$b] <=> $regionCounts[$a];

                return $cmp !== 0 ? $cmp : strcmp($a, $b);
            });

            $topRegions = array_slice($regionCounts, 0, $limit, true);
            $regions = [];

            foreach ($topRegions as $name => $count) {
                $regions[] = [
                    'name' => $name,
                    'count' => $count,
                    'percent' => $totalWithRegion > 0 ? (int) round(($count / $totalWithRegion) * 100) : 0,
                ];
            }

            return [
                'totalWithTkp' => $perkaras->count(),
                'offlineCount' => $offlineCount,
                'onlineCount' => $onlineCount,
                'offlinePercent' => $modusPct['offline'],
                'onlinePercent' => $modusPct['online'],
                'regions' => $regions,
            ];
        };

        if ($mode === null && $bulan === null) {
            return Cache::remember("dashboard_tkp_dist_{$tahun}", 120, $computeTkp);
        }

        return $computeTkp();
    }

    /**
     * Mengambil daftar Pasal Undang-Undang yang paling banyak disangkakan pada berkas perkara tahun berjalan.
     *
     * @return array<int, array{id: int, title: string, description: string, count: int}>
     */
    public function getTopPasals(
        ?int $tahun = null,
        int $limit = 4,
        ?int $bulan = null,
        ?string $mode = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null
    ): array {
        $tahun = $tahun ?: (int) Carbon::now(self::ZONA_WAKTU)->year;
        $effectiveMode = $mode ?: (($bulan && $bulan >= 1 && $bulan <= 12) ? 'bulan' : 'tahun');
        $bulan = ($bulan && $bulan >= 1 && $bulan <= 12) ? $bulan : null;

        $computeTopPasals = function () use ($tahun, $limit, $bulan, $effectiveMode, $tanggalMulai, $tanggalSelesai) {
            $pasalQuery = DB::table('berkas_pasal')
                ->join('pasal', 'pasal.id', '=', 'berkas_pasal.pasal_id')
                ->join('berkas_perkara', 'berkas_perkara.id', '=', 'berkas_pasal.berkas_perkara_id')
                ->join('perkara', 'perkara.id', '=', 'berkas_perkara.perkara_id')
                ->whereNull('pasal.deleted_at')
                ->whereNull('berkas_perkara.deleted_at')
                ->whereNull('perkara.deleted_at');

            $this->applyPerkaraDateFilter($pasalQuery, $effectiveMode, $tahun, $bulan, $tanggalMulai, $tanggalSelesai, 'perkara.tanggal_laporan_kejadian');

            $rows = $pasalQuery
                ->select([
                    'pasal.id',
                    'pasal.pasal',
                    'pasal.nama_pasal',
                    'pasal.nomor',
                    'pasal.tahun',
                    'pasal.isi',
                    DB::raw('COUNT(DISTINCT berkas_pasal.berkas_perkara_id) as total_berkas'),
                ])
                ->groupBy('pasal.id', 'pasal.pasal', 'pasal.nama_pasal', 'pasal.nomor', 'pasal.tahun', 'pasal.isi')
                ->orderByDesc('total_berkas')
                ->orderBy('pasal.id')
                ->limit($limit)
                ->get();

            return $rows->map(function ($row) {
                $uuParts = array_filter([
                    $row->nama_pasal,
                    $row->nomor ? "No. {$row->nomor}" : null,
                    $row->tahun ? "Tahun {$row->tahun}" : null,
                ]);
                $uuText = implode(' ', $uuParts);
                $title = $uuText !== '' && $row->pasal !== $row->nama_pasal
                    ? "{$row->pasal} ({$uuText})"
                    : ($row->pasal ?: $row->nama_pasal);

                return [
                    'id' => (int) $row->id,
                    'title' => $title,
                    'description' => trim((string) ($row->isi ?: $row->nama_pasal ?: '-')),
                    'count' => (int) $row->total_berkas,
                ];
            })->all();
        };

        if ($mode === null && $bulan === null) {
            return Cache::remember("dashboard_top_pasal_{$tahun}", 120, $computeTopPasals);
        }

        return $computeTopPasals();
    }

    /**
     * Menghitung rata-rata hari penyelesaian dari tanggal SPDP ke Tahap 2.
     */
    public function calculateAverageCompletionDays(?int $tahun = null): ?float
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

                if ($tglTahap2->lessThan($tglSpdp)) {
                    continue;
                }

                $durations[] = $tglSpdp->diffInDays($tglTahap2);
            }
        }

        return ! empty($durations) ? (array_sum($durations) / count($durations)) : null;
    }
}
