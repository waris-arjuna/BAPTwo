<?php

namespace App\Http\Controllers;

use App\Models\BerkasPerkara;
use App\Models\TahapanPerkara;
use App\Services\TahapanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SpdpController extends Controller
{
    public function __construct(
        protected TahapanService $tahapanService
    ) {}

    /**
     * Menampilkan daftar tahapan SPDP (termasuk sub-tahap opsional P-16 dan P-17P).
     */
    public function index(Request $request): View
    {
        $spdps = $this->tahapanService->getSpdpList($request);
        $tabCounts = $this->tahapanService->getSpdpTabCounts();

        if ($request->header('X-Filter-Partial')) {
            return view('pages.spdp._table-partial', compact('spdps', 'tabCounts'));
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForSpdp();

        return view('pages.spdp.index', compact('spdps', 'tabCounts', 'availableBerkas'));
    }

    /**
     * Tampilkan form tambah SPDP atau redirect jika berkas_id telah ditentukan.
     */
    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create-cases');

        if ($berkasId = $request->integer('berkas_id')) {
            $berkas = BerkasPerkara::findOrFail($berkasId);

            return redirect()->route('berkas-perkara.tahapan.create', [
                'berkas' => $berkas,
                'jenis' => 'spdp',
            ]);
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForSpdp();

        return view('pages.spdp.create', compact('availableBerkas'));
    }

    /**
     * Menampilkan detail SPDP.
     */
    public function show(mixed $id): View
    {
        $tahap = TahapanPerkara::with('berkasPerkara')->find($id);

        return view('pages.spdp.show', [
            'id' => $id,
            'tahap' => $tahap,
        ]);
    }
}
