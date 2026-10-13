<?php

namespace App\Http\Controllers;

use App\Models\BerkasPerkara;
use App\Models\TahapanPerkara;
use App\Services\TahapanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class P21Controller extends Controller
{
    public function __construct(
        protected TahapanService $tahapanService
    ) {}

    /**
     * Menampilkan daftar tahapan P-21.
     */
    public function index(Request $request): View
    {
        $p21s = $this->tahapanService->getP21List($request);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.p21._table-partial', compact('p21s'));
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForP21();

        return view('pages.p21.index', compact('p21s', 'availableBerkas'));
    }

    /**
     * Tampilkan form tambah P-21 atau redirect jika berkas_id telah ditentukan.
     */
    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create-cases');

        if ($berkasId = $request->integer('berkas_id')) {
            $berkas = BerkasPerkara::findOrFail($berkasId);

            if ($berkas->hasTahap(\App\Enums\JenisTahap::P21)) {
                return redirect()->route('p21.index')
                    ->with('error', 'Berkas perkara ini sudah memiliki tahapan P-21 dan tidak dapat dipilih lagi.');
            }

            if (! $this->tahapanService->canProceedTo($berkas, \App\Enums\JenisTahap::P21)) {
                return redirect()->route('p21.index')
                    ->with('error', 'Berkas perkara ini belum memenuhi syarat untuk memulai P-21 (Tahap 1 belum final).');
            }

            return redirect()->route('berkas-perkara.tahapan.create', [
                'berkas' => $berkas,
                'jenis' => 'p21',
            ]);
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForP21();

        return view('pages.p21.create', compact('availableBerkas'));
    }

    /**
     * Menampilkan detail P-21.
     */
    public function show(mixed $id): View
    {
        $tahap = TahapanPerkara::with('berkasPerkara')->find($id);

        return view('pages.p21.show', [
            'id' => $id,
            'tahap' => $tahap,
        ]);
    }
}
