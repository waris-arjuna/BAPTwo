<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanKejadianRequest;
use App\Http\Requests\UpdateLaporanKejadianRequest;
use App\Models\Perkara;
use App\Services\PerkaraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LaporanKejadianController extends Controller
{
    public function __construct(
        protected PerkaraService $perkaraService
    ) {}

    /**
     * Tampilkan daftar laporan kejadian
     */
    public function index(Request $request): View
    {
        $laporanKejadian = $this->perkaraService->getLaporanKejadianList($request);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.laporan-kejadian._table-partial', compact('laporanKejadian'));
        }

        return view('pages.laporan-kejadian.index', compact('laporanKejadian'));
    }

    /**
     * Tampilkan form tambah laporan kejadian
     */
    public function create(): View
    {
        Gate::authorize('create-cases');

        return view('pages.laporan-kejadian.create');
    }

    /**
     * Simpan laporan kejadian baru
     */
    public function store(StoreLaporanKejadianRequest $request): RedirectResponse
    {
        Gate::authorize('create-cases');

        $perkara = $this->perkaraService->createLaporanKejadian(
            $request->validated(),
            $request->file('dokumen_laporan'),
            $request->user()
        );

        return redirect()
            ->route('laporan-kejadian.show', $perkara)
            ->with('success', 'Laporan kejadian berhasil disimpan.');
    }

    /**
     * Tampilkan detail laporan kejadian
     */
    public function show(mixed $perkara): View
    {
        $this->perkaraService->ensureBaselinePerkara();

        if (! $perkara instanceof Perkara) {
            $perkara = Perkara::findOrFail($perkara);
        }

        $perkara->load(['dokumenLaporan', 'dokumenSprindik', 'creator', 'updater', 'auditLogs.user']);

        return view('pages.laporan-kejadian.show', compact('perkara'));
    }

    /**
     * Tampilkan form edit laporan kejadian
     */
    public function edit(Perkara $perkara): View
    {
        Gate::authorize('edit-draft');

        $perkara->load(['dokumenLaporan']);

        return view('pages.laporan-kejadian.edit', compact('perkara'));
    }

    /**
     * Perbarui laporan kejadian
     */
    public function update(UpdateLaporanKejadianRequest $request, Perkara $perkara): RedirectResponse
    {
        Gate::authorize('edit-draft');

        $this->perkaraService->updateLaporanKejadian(
            $perkara,
            $request->validated(),
            $request->file('dokumen_laporan'),
            $request->user()
        );

        return redirect()
            ->route('laporan-kejadian.show', $perkara)
            ->with('success', 'Laporan kejadian berhasil diperbarui.');
    }

    /**
     * Hapus (soft delete) laporan kejadian
     */
    public function destroy(Request $request, Perkara $perkara): RedirectResponse
    {
        Gate::authorize('soft-delete');

        $this->perkaraService->deleteLaporanKejadian($perkara, $request->user());

        return redirect()
            ->route('laporan-kejadian.index')
            ->with('success', 'Laporan kejadian berhasil dihapus.');
    }
}
