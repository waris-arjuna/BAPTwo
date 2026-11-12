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
     * Alihkan ke form tambah Tahap 2 dengan berkas_id atau kembali ke daftar Tahap 2.
     */
    public function create(Request $request): RedirectResponse
    {
        Gate::authorize('create-cases');

        if ($berkasId = $request->integer('berkas_id')) {
            $berkas = BerkasPerkara::findOrFail($berkasId);

            if ($berkas->hasTahap(JenisTahap::TAHAP_2)) {
                return redirect()->route('tahap-2.index')
                    ->with('error', [
                        'title' => 'Tahap 2 Sudah Ada',
                        'message' => 'Berkas perkara '.$berkas->nomor_berkas.' sudah memiliki tahapan Tahap 2 dan tidak dapat dipilih lagi.',
                    ]);
            }

            if (! $this->tahapanService->canProceedTo($berkas, JenisTahap::TAHAP_2)) {
                return redirect()->route('tahap-2.index')
                    ->with('error', [
                        'title' => 'Syarat Belum Terpenuhi',
                        'message' => 'Berkas perkara '.$berkas->nomor_berkas.' belum memenuhi syarat untuk memulai Tahap 2 (tahapan P-21 belum final atau status DPO aktif).',
                    ]);
            }

            return redirect()->route('berkas-perkara.tahapan.create', [
                'berkas' => $berkas,
                'jenis' => 'tahap_2',
            ]);
        }

        return redirect()->route('tahap-2.index');
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

        return redirect()->route('tahap-2.index')->with('warning', [
            'title' => 'Data Tidak Ditemukan',
            'message' => 'Data tahapan Tahap 2 yang dimaksud tidak ditemukan dalam sistem.',
        ]);
    }
}
