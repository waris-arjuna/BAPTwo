<?php

namespace App\Http\Controllers;

use App\Models\BerkasPerkara;
use App\Models\TahapanPerkara;
use App\Services\TahapanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class Tahap2Controller extends Controller
{
    public function __construct(
        protected TahapanService $tahapanService
    ) {}

    /**
     * Menampilkan daftar tahapan Tahap 2 (Pelimpahan Tersangka & Barang Bukti).
     */
    public function index(Request $request): View
    {
        $tahap2s = $this->tahapanService->getTahap2List($request);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.tahap-2._table-partial', compact('tahap2s'));
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForTahap2();

        return view('pages.tahap-2.index', compact('tahap2s', 'availableBerkas'));
    }

    /**
     * Tampilkan form tambah Tahap 2 atau redirect jika berkas_id telah ditentukan.
     */
    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create-cases');

        if ($berkasId = $request->integer('berkas_id')) {
            $berkas = BerkasPerkara::findOrFail($berkasId);

            if ($berkas->hasTahap(\App\Enums\JenisTahap::TAHAP_2)) {
                return redirect()->route('tahap-2.index')
                    ->with('error', 'Berkas perkara ini sudah memiliki tahapan Tahap 2 dan tidak dapat dipilih lagi.');
            }

            if (! $this->tahapanService->canProceedTo($berkas, \App\Enums\JenisTahap::TAHAP_2)) {
                return redirect()->route('tahap-2.index')
                    ->with('error', 'Berkas perkara ini belum memenuhi syarat untuk memulai Tahap 2 (P-21 belum final atau status DPO aktif).');
            }

            return redirect()->route('berkas-perkara.tahapan.create', [
                'berkas' => $berkas,
                'jenis' => 'tahap_2',
            ]);
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForTahap2();

        return view('pages.tahap-2.create', compact('availableBerkas'));
    }

    /**
     * Menampilkan detail Tahap 2.
     */
    public function show(mixed $id): View|RedirectResponse
    {
        $tahap = TahapanPerkara::with('berkasPerkara')->find($id);

        if ($tahap && $tahap->berkas_perkara_id) {
            return redirect()->route('berkas-perkara.tahapan.show', [
                'berkas' => $tahap->berkas_perkara_id,
                'tahapan' => $tahap->id,
            ]);
        }

        return redirect()->route('tahap-2.index')->with('warning', 'Data Tahap 2 tidak ditemukan.');
    }
}
