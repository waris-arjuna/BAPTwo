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

        $validated = $request->validated();
        $isDraft = ($validated['action_type'] ?? 'lanjut') === 'draft';

        $perkara = $this->perkaraService->createLaporanKejadian(
            $validated,
            $request->file('dokumen_laporan'),
            $request->user()
        );

        $payload = $isDraft
            ? [
                'title' => 'Draft Tersimpan',
                'message' => 'Draft Laporan Kejadian '.($perkara->nomor_laporan ?: '').' berhasil disimpan. Anda masih dapat mengedit data ini.',
            ]
            : [
                'title' => 'Laporan Kejadian Final',
                'message' => 'Laporan Kejadian '.($perkara->nomor_laporan ?: '').' berhasil difinalkan dan dikunci. Silakan lanjutkan ke tahap Sprindik.',
            ];

        return redirect()
            ->route('laporan-kejadian.show', $perkara)
            ->with('success', $payload);
    }

    /**
     * Tampilkan detail laporan kejadian
     */
    public function show(mixed $perkara): View
    {
        if (! $perkara instanceof Perkara) {
            $perkara = Perkara::findOrFail($perkara);
        }

        $perkara->load(['dokumenLaporan', 'dokumenSprindik', 'creator', 'updater', 'laporanFinalizer', 'auditLogs.user']);

        return view('pages.laporan-kejadian.show', compact('perkara'));
    }

    /**
     * Tampilkan form edit laporan kejadian
     */
    public function edit(Perkara $perkara): View
    {
        Gate::authorize('edit-draft', $perkara->isLaporanLocked());

        $perkara->load(['dokumenLaporan']);

        return view('pages.laporan-kejadian.edit', compact('perkara'));
    }

    /**
     * Perbarui laporan kejadian
     */
    public function update(UpdateLaporanKejadianRequest $request, Perkara $perkara): RedirectResponse
    {
        Gate::authorize('edit-draft', $perkara->isLaporanLocked());

        $validated = $request->validated();
        $isDraft = ($validated['action_type'] ?? 'draft') === 'draft';

        $this->perkaraService->updateLaporanKejadian(
            $perkara,
            $validated,
            $request->file('dokumen_laporan'),
            $request->user()
        );

        $payload = $isDraft
            ? [
                'title' => 'Draft Diperbarui',
                'message' => 'Draft Laporan Kejadian '.($perkara->nomor_laporan ?: '').' berhasil diperbarui.',
            ]
            : [
                'title' => 'Laporan Kejadian Final',
                'message' => 'Laporan Kejadian '.($perkara->nomor_laporan ?: '').' berhasil difinalkan dan dikunci permanen.',
            ];

        return redirect()
            ->route('laporan-kejadian.show', $perkara)
            ->with('success', $payload);
    }
}
