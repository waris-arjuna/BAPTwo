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
    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create-cases');

        $availableLaporan = $this->perkaraService->getAvailableForSprindik();
        $selectedPerkaraId = $request->integer('perkara_id') ?: null;

        if ($selectedPerkaraId && ! $availableLaporan->contains('id', $selectedPerkaraId)) {
            return redirect()
                ->route('sprindik.create')
                ->with('error', [
                    'title' => 'Laporan Tidak Valid',
                    'message' => 'Laporan kejadian yang dipilih sudah terhubung ke Sprindik lain atau tidak valid.',
                ]);
        }

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

        try {
            $this->perkaraService->attachSprindik(
                $perkara,
                $validated,
                $request->file('dokumen_sprindik'),
                $request->user()
            );
        } catch (\DomainException $e) {
            return redirect()
                ->route('sprindik.create')
                ->with('error', [
                    'title' => 'Gagal Menyimpan Sprindik',
                    'message' => $e->getMessage(),
                ]);
        }

        $payload = $isDraft
            ? [
                'title' => 'Draft Sprindik Tersimpan',
                'message' => 'Draft Sprindik untuk perkara '.($perkara->nomor_laporan_kejadian ?: '').' berhasil disimpan.',
            ]
            : [
                'title' => 'Sprindik Final',
                'message' => 'Sprindik untuk perkara '.($perkara->nomor_laporan_kejadian ?: '').' berhasil difinalkan dan dikunci permanen.',
            ];

        return redirect()
            ->route('sprindik.show', $perkara)
            ->with('success', $payload);
    }

    /**
     * Tampilkan detail sprindik
     */
    public function show(Perkara $perkara): View
    {
        abort_if(empty($perkara->nomor_surat_perintah) && empty($perkara->tahap_sprindik_status), 404);

        $perkara->load([
            'dokumenSprindik',
            'dokumenLaporan',
            'creator',
            'updater',
            'laporanFinalizer',
            'sprindikFinalizer',
            'auditLogs' => fn ($query) => $query->with(['user', 'berkasPerkara.tersangka'])->orderByDesc('id'),
        ]);

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

        $payload = $isDraft
            ? [
                'title' => 'Draft Sprindik Diperbarui',
                'message' => 'Draft Sprindik untuk perkara '.($perkara->nomor_laporan_kejadian ?: '').' berhasil diperbarui.',
            ]
            : [
                'title' => 'Sprindik Final',
                'message' => 'Sprindik untuk perkara '.($perkara->nomor_laporan_kejadian ?: '').' berhasil difinalkan dan dikunci permanen.',
            ];

        return redirect()
            ->route('sprindik.show', $perkara)
            ->with('success', $payload);
    }
}
