<?php

namespace App\Http\Controllers;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\TahapanPerkara;
use App\Services\TahapanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class Tahap1Controller extends Controller
{
    public function __construct(
        protected TahapanService $tahapanService
    ) {}

    /**
     * Menampilkan daftar tahapan Tahap 1 (termasuk sub-tahap opsional P-18P dan P-19).
     */
    public function index(Request $request): View
    {
        $tahap1 = $this->tahapanService->getTahap1List($request);
        $tabCounts = $this->tahapanService->getTahap1TabCounts();

        if ($request->header('X-Filter-Partial')) {
            return view('pages.tahap-1._table-partial', compact('tahap1', 'tabCounts'));
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForTahap1();

        return view('pages.tahap-1.index', compact('tahap1', 'tabCounts', 'availableBerkas'));
    }

    /**
     * Tampilkan form tambah Tahap 1 atau redirect jika berkas_id telah ditentukan.
     */
    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create-cases');

        if ($berkasId = $request->integer('berkas_id')) {
            $berkas = BerkasPerkara::findOrFail($berkasId);

            if ($berkas->hasTahap(JenisTahap::TAHAP_1)) {
                return redirect()->route('tahap-1.index')
                    ->with('error', [
                        'title' => 'Tahap 1 Sudah Ada',
                        'message' => 'Berkas perkara '.$berkas->nomor_berkas.' sudah memiliki tahapan Tahap 1 dan tidak dapat dipilih lagi.',
                    ]);
            }

            if (! $this->tahapanService->canProceedTo($berkas, JenisTahap::TAHAP_1)) {
                return redirect()->route('tahap-1.index')
                    ->with('error', [
                        'title' => 'Syarat Belum Terpenuhi',
                        'message' => 'Berkas perkara '.$berkas->nomor_berkas.' belum memenuhi syarat untuk memulai Tahap 1 (tahapan SPDP belum final).',
                    ]);
            }

            return redirect()->route('berkas-perkara.tahapan.create', [
                'berkas' => $berkas,
                'jenis' => 'tahap_1',
            ]);
        }

        $availableBerkas = $this->tahapanService->getAvailableBerkasForTahap1();

        return view('pages.tahap-1.create', compact('availableBerkas'));
    }

    /**
     * Menampilkan detail Tahap 1.
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

        return redirect()->route('tahap-1.index')->with('warning', [
            'title' => 'Data Tidak Ditemukan',
            'message' => 'Data tahapan Tahap 1 yang dimaksud tidak ditemukan dalam sistem.',
        ]);
    }
}
