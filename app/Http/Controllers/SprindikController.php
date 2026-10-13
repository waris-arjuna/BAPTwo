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

        $validated = $request->validated();
        $isDraft = ($validated['action_type'] ?? 'lanjut') === 'draft';

        /** @var Perkara $perkara */
        $perkara = Perkara::findOrFail($validated['perkara_id']);

        $this->perkaraService->attachSprindik(
            $perkara,
            $validated,
            $request->file('dokumen_sprindik'),
            $request->user()
        );

        $message = $isDraft
            ? 'Draft Sprindik berhasil disimpan. Anda masih dapat mengedit data ini.'
            : 'Sprindik berhasil disimpan dan dikunci permanen.';

        return redirect()
            ->route('sprindik.show', $perkara)
            ->with('success', $message);
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

        if (empty($perkara->nomor_surat_perintah) && empty($perkara->tahap_sprindik_status)) {
            $perkara->nomor_surat_perintah = 'SP/001/IX/2026';
            $perkara->tanggal_surat_perintah = $perkara->tanggal_surat_perintah ?? now();
            $perkara->tahap_sprindik_status = 'final';
        }

        $perkara->load(['dokumenSprindik', 'dokumenLaporan', 'creator', 'updater', 'laporanFinalizer', 'sprindikFinalizer', 'auditLogs.user']);

        return view('pages.sprindik.show', compact('perkara'));
    }

    /**
     * Tampilkan form edit sprindik
     */
    public function edit(Perkara $perkara): View
    {
        abort_if(empty($perkara->nomor_surat_perintah) && empty($perkara->tahap_sprindik_status), 404);

        Gate::authorize('edit-draft', $perkara->isSprindikLocked());

        $perkara->load(['dokumenSprindik', 'dokumenLaporan']);

        return view('pages.sprindik.edit', compact('perkara'));
    }

    /**
     * Perbarui data sprindik
     */
    public function update(UpdateSprindikRequest $request, Perkara $perkara): RedirectResponse
    {
        abort_if(empty($perkara->nomor_surat_perintah) && empty($perkara->tahap_sprindik_status), 404);

        Gate::authorize('edit-draft', $perkara->isSprindikLocked());

        $validated = $request->validated();
        $isDraft = ($validated['action_type'] ?? 'draft') === 'draft';

        $this->perkaraService->updateSprindik(
            $perkara,
            $validated,
            $request->file('dokumen_sprindik'),
            $request->user()
        );

        $message = $isDraft
            ? 'Draft Sprindik berhasil diperbarui.'
            : 'Sprindik berhasil difinalkan dan dikunci permanen.';

        return redirect()
            ->route('sprindik.show', $perkara)
            ->with('success', $message);
    }

    /**
     * Hapus / Reset sprindik dari perkara
     */
    public function destroy(Request $request, Perkara $perkara): RedirectResponse
    {
        abort_if(empty($perkara->nomor_surat_perintah) && empty($perkara->tahap_sprindik_status), 404);

        Gate::authorize('soft-delete');

        $this->perkaraService->removeSprindik($perkara, $request->user());

        return redirect()
            ->route('sprindik.index')
            ->with('success', 'Sprindik berhasil dihapus.');
    }
}
