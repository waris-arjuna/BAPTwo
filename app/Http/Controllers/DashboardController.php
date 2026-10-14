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

        return view('pages.dashboard.ecommerce', [
            'title' => 'Dashboard BAPTwo',
            'tahun' => $tahun,
            'statistics' => $statistics,
            'caseStatus' => $caseStatus,
            'stageAverages' => $stageAverages,
            'perkaraList' => $perkaraList,
        ]);
    }
}
