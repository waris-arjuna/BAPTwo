<?php

namespace App\Http\Controllers;

use App\Services\PerkaraService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataPerkaraController extends Controller
{
    public function __construct(
        protected PerkaraService $perkaraService
    ) {}

    /**
     * Tampilkan tabel Daftar Seluruh Perkara & Berkas Perkara
     */
    public function index(Request $request): View
    {
        $perkaras = $this->perkaraService->getDataPerkaraList($request);
        $availableSprindiks = $this->perkaraService->getAvailableForPerkara();
        $summaryCards = $this->perkaraService->getDataPerkaraSummaryCards();
        $kesiapanCards = $this->perkaraService->getPotensiTerlimpahCards();
        $ppnsList = $this->perkaraService->getAvailablePpnsForFilter();

        if ($request->header('X-Filter-Partial')) {
            return view('pages.data-perkara._table-partial', compact('perkaras'));
        }

        return view('pages.data-perkara.index', compact('perkaras', 'availableSprindiks', 'summaryCards', 'kesiapanCards', 'ppnsList'));
    }
}
