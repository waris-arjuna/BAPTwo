<?php

namespace App\Http\Controllers;

use App\Models\BerkasPerkara;
use App\Models\TahapanPerkara;
use App\Services\TahapanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PutusanPerkaraController extends Controller
{
    public function __construct(
        protected TahapanService $tahapanService
    ) {}

    /**
     * Menampilkan daftar Keputusan Pengadilan (Putusan Perkara).
     */
    public function index(Request $request): View
    {
        $putusans = $this->tahapanService->getPutusanPerkaraList($request);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.putusan-perkara._table-partial', compact('putusans'));
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForPutusan();

        return view('pages.putusan-perkara.index', compact('putusans', 'availableBerkas'));
    }

    /**
     * Tampilkan form tambah Putusan Perkara atau redirect jika berkas_id telah ditentukan.
     */
    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create-cases');

        if ($berkasId = $request->integer('berkas_id')) {
            $berkas = BerkasPerkara::findOrFail($berkasId);

            if ($berkas->hasTahap(\App\Enums\JenisTahap::KEPUTUSAN_PENGADILAN)) {
                return redirect()->route('putusan-perkara.index')
                    ->with('error', 'Berkas perkara ini sudah memiliki tahapan Keputusan Pengadilan dan tidak dapat dipilih lagi.');
            }

            if (! $this->tahapanService->canProceedTo($berkas, \App\Enums\JenisTahap::KEPUTUSAN_PENGADILAN)) {
                return redirect()->route('putusan-perkara.index')
                    ->with('error', 'Berkas perkara ini belum memenuhi syarat untuk mencatat Keputusan Pengadilan (Tahap 2 belum final).');
            }

            return redirect()->route('berkas-perkara.tahapan.create', [
                'berkas' => $berkas,
                'jenis' => 'keputusan_pengadilan',
            ]);
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForPutusan();

        return view('pages.putusan-perkara.create', compact('availableBerkas'));
    }

    /**
     * Menampilkan detail Putusan Perkara.
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

        return redirect()->route('putusan-perkara.index')->with('warning', 'Data Putusan Perkara tidak ditemukan.');
    }
}
