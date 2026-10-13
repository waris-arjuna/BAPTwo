<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSprindikRequest;
use App\Http\Requests\UpdateSprindikRequest;
use App\Models\Perkara;
use App\Services\PerkaraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SprindikController extends Controller
{
    public function __construct(
        protected PerkaraService $perkaraService
    ) {}

    /**
     * Tampilkan daftar sprindik
     */
    public function index(Request $request): View
    {
        $sprindiks = $this->perkaraService->getSprindikList($request);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.sprindik._table-partial', compact('sprindiks'));
        }

        return view('pages.sprindik.index', compact('sprindiks'));
    }

    /**
     * Tampilkan form tambah sprindik
     */
    public function create(Request $request): View
    {
        Gate::authorize('create-cases');

        $availableLaporan = $this->perkaraService->getAvailableForSprindik();
        $selectedPerkaraId = $request->integer('perkara_id') ?: null;

        return view('pages.sprindik.create', compact('availableLaporan', 'selectedPerkaraId'));
    }

    /**
     * Simpan sprindik baru (kaitkan ke laporan kejadian)
     */
    public function store(StoreSprindikRequest $request): RedirectResponse
    {
        Gate::authorize('create-cases');

        /** @var Perkara $perkara */
        $perkara = Perkara::findOrFail($request->validated('perkara_id'));

        $this->perkaraService->attachSprindik(
            $perkara,
            $request->validated(),
            $request->file('dokumen_sprindik'),
            $request->user()
        );

        return redirect()
            ->route('sprindik.show', $perkara)
            ->with('success', 'Sprindik berhasil disimpan.');
    }

    /**
     * Tampilkan detail sprindik
     */
    public function show(mixed $perkara): View
    {
        $this->perkaraService->ensureBaselinePerkara();

        if (! $perkara instanceof Perkara) {
            $perkara = Perkara::find($perkara) ?: new Perkara(['id' => $perkara]);
        }

        if (empty($perkara->nomor_surat_perintah)) {
            $perkara->nomor_surat_perintah = 'SP/001/IX/2026';
            $perkara->tanggal_surat_perintah = $perkara->tanggal_surat_perintah ?? now();
        }

        $perkara->load(['dokumenSprindik', 'dokumenLaporan', 'creator', 'updater', 'auditLogs.user']);

        return view('pages.sprindik.show', compact('perkara'));
    }

    /**
     * Tampilkan form edit sprindik
     */
    public function edit(Perkara $perkara): View
    {
        abort_if(empty($perkara->nomor_surat_perintah), 404);

        Gate::authorize('edit-draft');

        $perkara->load(['dokumenSprindik']);

        return view('pages.sprindik.edit', compact('perkara'));
    }

    /**
     * Perbarui data sprindik
     */
    public function update(UpdateSprindikRequest $request, Perkara $perkara): RedirectResponse
    {
        abort_if(empty($perkara->nomor_surat_perintah), 404);

        Gate::authorize('edit-draft');

        $this->perkaraService->updateSprindik(
            $perkara,
            $request->validated(),
            $request->file('dokumen_sprindik'),
            $request->user()
        );

        return redirect()
            ->route('sprindik.show', $perkara)
            ->with('success', 'Sprindik berhasil diperbarui.');
    }

    /**
     * Hapus / Reset sprindik dari perkara
     */
    public function destroy(Request $request, Perkara $perkara): RedirectResponse
    {
        abort_if(empty($perkara->nomor_surat_perintah), 404);

        Gate::authorize('soft-delete');

        $this->perkaraService->removeSprindik($perkara, $request->user());

        return redirect()
            ->route('sprindik.index')
            ->with('success', 'Sprindik berhasil dihapus.');
    }
}
