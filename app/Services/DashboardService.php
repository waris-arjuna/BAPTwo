<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Services\Analytics\DashboardChartService;
use App\Services\Notification\AnnualClosureNoticeService;
use App\Services\Queries\PriorityProgressQuery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

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
        protected TahapanService $tahapanService,
        protected DashboardChartService $chartService,
        protected AnnualClosureNoticeService $annualClosureNoticeService
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
                    'description' => 'Rata-rata Waktu Total (SPDP s.d. Tahap II)',
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
            Cache::forget("dashboard_monthly_cases_{$tahun}");
            Cache::forget("dashboard_tkp_dist_{$tahun}");
            Cache::forget("dashboard_top_pasal_{$tahun}");
        } else {
            $currentYear = (int) Carbon::now(self::ZONA_WAKTU)->year;
            for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
                Cache::forget("dashboard_stats_{$y}");
                Cache::forget("dashboard_stage_averages_{$y}");
                Cache::forget("dashboard_monthly_cases_{$y}");
                Cache::forget("dashboard_tkp_dist_{$y}");
                Cache::forget("dashboard_top_pasal_{$y}");
            }
        }
    }

    /**
     * Terapkan filter periode pada query Perkara (atau tabel dengan kolom tanggal LK/surat).
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
        $this->chartService->applyPerkaraDateFilter($query, $mode, $tahun, $bulan, $tanggalMulai, $tanggalSelesai, $column);
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
        $this->chartService->applyBerkasPeriodFilter($query, $mode, $tahun, $bulan, $tanggalMulai, $tanggalSelesai);
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
        return $this->chartService->getMonthlyCasesTrends($tahun, $mode, $tanggalMulai, $tanggalSelesai);
    }

    /**
     * Mengambil daftar tahun yang memiliki data Laporan Kejadian untuk filter dropdown.
     *
     * @return array<int, int>
     */
    public function getAvailableYears(): array
    {
        return $this->chartService->getAvailableYears();
    }

    /**
     * Menghitung persentase integer bulat yang jumlah totalnya selalu tepat 100%.
     *
     * @param  array<string, int>  $values
     * @return array<string, int>
     */
    public function calculatePercentages(array $values): array
    {
        return $this->chartService->calculatePercentages($values);
    }

    /**
     * Mengambil data status perkara untuk Donut Chart pada Dashboard.
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
        return $this->chartService->getCaseStatusData($tahun, $bulan, $mode, $tanggalMulai, $tanggalSelesai);
    }

    /**
     * Mengambil data rata-rata waktu penyelesaian per tahapan (dalam Hari Kerja).
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
        return $this->chartService->getStageAverageDays($tahun, $bulan, $mode, $tanggalMulai, $tanggalSelesai);
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
        return $this->chartService->getTkpDistribution($tahun, $limit, $bulan, $mode, $tanggalMulai, $tanggalSelesai);
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
        return $this->chartService->getTopPasals($tahun, $limit, $bulan, $mode, $tanggalMulai, $tanggalSelesai);
    }

    /**
     * Ambil data berkas perkara untuk tabel prioritas progres di Dashboard
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPriorityProgressPerkara(mixed $requestOrFilters = [], int $limit = 5): array
    {
        $filters = is_object($requestOrFilters) && method_exists($requestOrFilters, 'all')
            ? $requestOrFilters->all()
            : (array) $requestOrFilters;

        return app(PriorityProgressQuery::class)->get($filters, $limit);
    }

    /**
     * Menghitung rata-rata hari penyelesaian dari tanggal SPDP ke Tahap 2.
     */
    public function calculateAverageCompletionDays(?int $tahun = null): ?float
    {
        return $this->chartService->calculateAverageCompletionDays($tahun);
    }

    /**
     * Mengambil daftar aktivitas penyidikan terkini untuk widget dashboard (Milestone & Semua).
     *
     * @return array{milestones: Collection<int, AuditLog>, all: Collection<int, AuditLog>}
     */
    public function getRecentActivities(?int $limit = 6): array
    {
        $limit = $limit ?: 6;

        $baseQuery = AuditLog::query()
            ->with(['user', 'perkara', 'berkasPerkara.tersangka']);

        $milestones = (clone $baseQuery)
            ->milestone()
            ->terkini($limit)
            ->get();

        $all = (clone $baseQuery)
            ->terkini($limit)
            ->get();

        return [
            'milestones' => $milestones,
            'all' => $all,
        ];
    }

    /**
     * Mengambil informasi pemberitahuan penutupan perkara awal tahun (khusus Januari untuk Penyidik & Admin).
     *
     * @return array{tahun_lalu: int, tahun_ini: int, jumlah_berkas: int, pesan: string, url_daftar: string}|null
     */
    public function getAnnualClosureNoticeInfo(): ?array
    {
        return $this->annualClosureNoticeService->getAnnualClosureNoticeInfo();
    }
}
