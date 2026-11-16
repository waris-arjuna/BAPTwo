<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\PerkaraService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataPerkaraController extends Controller
{
    public function __construct(
        protected PerkaraService $perkaraService,
        protected DashboardService $dashboardService
    ) {}

    /**
     * Tampilkan tabel Daftar Seluruh Perkara & Berkas Perkara
     */
    public function index(Request $request): View
    {
        $currentYear = (int) Carbon::now(DashboardService::ZONA_WAKTU)->year;
        $rawTahun = trim((string) $request->query('tahun', ''));

        if ($rawTahun === 'semua') {
            $selectedTahun = null;
            $tahunFilterValue = 'semua';
        } elseif ($rawTahun !== '' && is_numeric($rawTahun)) {
            $selectedTahun = (int) $rawTahun;
            $tahunFilterValue = (string) $selectedTahun;
        } else {
            $selectedTahun = $currentYear;
            $tahunFilterValue = (string) $currentYear;
        }

        $filters = $request->all();
        $filters['tahun'] = $tahunFilterValue;

        $perkaras = $this->perkaraService->getDataPerkaraList($filters);
        $availableSprindiks = $this->perkaraService->getAvailableForPerkara();
        $summaryCards = $this->perkaraService->getDataPerkaraSummaryCards($selectedTahun);
        $kesiapanCards = $this->perkaraService->getPotensiTerlimpahCards($selectedTahun);
        $ppnsList = $this->perkaraService->getAvailablePpnsForFilter();
        $availableYears = $this->dashboardService->getAvailableYears();

        if ($request->header('X-Filter-Partial')) {
            return view('pages.data-perkara._table-partial', compact('perkaras'));
        }

        return view('pages.data-perkara.index', compact(
            'perkaras',
            'availableSprindiks',
            'summaryCards',
            'kesiapanCards',
            'ppnsList',
            'availableYears',
            'selectedTahun',
            'currentYear',
            'tahunFilterValue'
        ));
    }
}
