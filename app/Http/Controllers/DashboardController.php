<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Tampilkan Dashboard Monitoring Utama BAPTwo
     */
    public function index(Request $request): View
    {
        $tahun = $request->integer('tahun') ?: (int) date('Y');

        if ($request->header('X-Filter-Partial')) {
            $perkaraList = $this->dashboardService->getPriorityProgressPerkara($request);

            return view('components.dashboard._progress-partial', compact('perkaraList'));
        }

        $statistics = $this->dashboardService->getStatistics($tahun);
        $caseStatus = $this->dashboardService->getCaseStatusData($tahun);
        $stageAverages = $this->dashboardService->getStageAverageDays($tahun);
        $perkaraList = $this->dashboardService->getPriorityProgressPerkara($request);
        $availableYears = $this->dashboardService->getAvailableYears();

        return view('pages.dashboard.ecommerce', [
            'title' => 'Dashboard BAPTwo',
            'tahun' => $tahun,
            'statistics' => $statistics,
            'caseStatus' => $caseStatus,
            'stageAverages' => $stageAverages,
            'perkaraList' => $perkaraList,
            'availableYears' => $availableYears,
        ]);
    }

    /**
     * Endpoint AJAX status perkara untuk Donut Chart.
     */
    public function caseStatus(Request $request)
    {
        $tahun = $request->integer('tahun') ?: (int) date('Y');
        $bulan = $request->integer('bulan') ?: null;

        $caseStatus = $this->dashboardService->getCaseStatusData($tahun, $bulan);

        if ($request->wantsJson()) {
            return response()->json($caseStatus);
        }

        return response()->view('components.dashboard._case-status-content', [
            'data' => $caseStatus,
            'tahun' => $tahun,
            'bulan' => $bulan,
        ])->withHeaders([
            'X-Periode-Text' => $caseStatus['periodeText'],
            'X-Updated-At' => $caseStatus['updatedAt'],
        ]);
    }
}
