<?php

namespace App\Http\Controllers;

use App\Enums\JenisTahap;
use App\Http\Requests\StoreTahapanRequest;
use App\Http\Requests\UpdateTahapanRequest;
use App\Models\BerkasPerkara;
use App\Models\TahapanPerkara;
use App\Services\TahapanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TahapanPerkaraController extends Controller
{
    public function __construct(
        protected TahapanService $tahapanService
    ) {}

    /**
     * Tampilkan formulir tambah tahapan baru pada Berkas Perkara.
     */
    public function create(Request $request, BerkasPerkara $berkas): View|RedirectResponse
    {
        Gate::authorize('create-cases');

        // Jika sudah ada tahapan draft aktif pada berkas perkara, arahkan ke edit draft tersebut
        $draft = $berkas->currentDraftTahap();
        if ($draft !== null) {
            return redirect()
                ->route('berkas-perkara.tahapan.edit', [$berkas, $draft])
                ->with('warning', 'Berkas perkara ini memiliki tahapan yang masih berstatus draft. Silakan selesaikan atau lanjutkan draft tersebut.');
        }

        $availableStages = $this->tahapanService->getNextAvailableStages($berkas);

        if ($availableStages === []) {
            return redirect()
                ->route('data-perkara.index')
                ->with('warning', 'Seluruh tahapan progress pada Berkas Perkara ini telah selesai.');
        }

        $selectedJenis = JenisTahap::tryFrom((string) $request->query('jenis')) ?? $availableStages[0];

        $references = $this->tahapanService->getPreviousStagesReference($berkas);

        return view('pages.tahapan.form', [
            'berkas' => $berkas->load(['perkara', 'tersangka']),
            'tahap' => null,
            'selectedJenis' => $selectedJenis,
            'availableStages' => $availableStages,
            'references' => $references,
            'mode' => 'create',
            'isLocked' => false,
        ]);
    }

    /**
     * Simpan tahapan baru (Draft atau Simpan & Lanjut).
     */
    public function store(StoreTahapanRequest $request, BerkasPerkara $berkas): RedirectResponse
    {
        Gate::authorize('create-cases');

        $validated = $request->validated();
        $isDraft = ($validated['action_type'] ?? 'lanjut') === 'draft';

        $tahap = $this->tahapanService->saveStage(
            $berkas,
            $validated,
            $request->file('dokumen'),
            $request->user()
        );

        $message = $isDraft
            ? "Draft tahapan {$tahap->jenis_tahap->label()} berhasil disimpan. Anda masih dapat mengedit data ini."
            : "Tahapan {$tahap->jenis_tahap->label()} berhasil disimpan dan dikunci permanen. Progress berkas perkara telah diperbarui.";

        return redirect()
            ->route('berkas-perkara.tahapan.show', [$berkas, $tahap])
            ->with('success', $message);
    }

    /**
     * Tampilkan detail tahapan (mode Read-Only / Terkunci).
     */
    public function show(BerkasPerkara $berkas, TahapanPerkara $tahapan): View
    {
        $tahapan->load(['dokumen', 'finalizedBy', 'createdBy', 'updatedBy']);
        $references = $this->tahapanService->getPreviousStagesReference($berkas);

        return view('pages.tahapan.show', [
            'berkas' => $berkas->load(['perkara', 'tersangka']),
            'tahap' => $tahapan,
            'references' => $references,
            'isLocked' => $tahapan->isFinal(),
        ]);
    }

    /**
     * Tampilkan formulir edit tahapan draft.
     */
    public function edit(BerkasPerkara $berkas, TahapanPerkara $tahapan): View|RedirectResponse
    {
        if ($tahapan->isFinal()) {
            return redirect()
                ->route('berkas-perkara.tahapan.show', [$berkas, $tahapan])
                ->with('warning', 'Tahapan ini telah difinalkan dan terkunci permanen, tidak dapat diedit.');
        }

        Gate::authorize('edit-draft');

        $references = $this->tahapanService->getPreviousStagesReference($berkas);

        return view('pages.tahapan.form', [
            'berkas' => $berkas->load(['perkara', 'tersangka']),
            'tahap' => $tahapan->load('dokumen'),
            'selectedJenis' => $tahapan->jenis_tahap,
            'availableStages' => [$tahapan->jenis_tahap],
            'references' => $references,
            'mode' => 'edit',
            'isLocked' => false,
        ]);
    }

    /**
     * Update data tahapan draft atau simpan & lanjut.
     */
    public function update(UpdateTahapanRequest $request, BerkasPerkara $berkas, TahapanPerkara $tahapan): RedirectResponse
    {
        if ($tahapan->isFinal()) {
            abort(403, 'Tahapan telah difinalkan dan terkunci permanen.');
        }

        $validated = $request->validated();
        $isDraft = ($validated['action_type'] ?? 'lanjut') === 'draft';

        $tahap = $this->tahapanService->saveStage(
            $berkas,
            $validated,
            $request->file('dokumen'),
            $request->user(),
            $tahapan
        );

        $message = $isDraft
            ? "Perubahan draft tahapan {$tahap->jenis_tahap->label()} berhasil disimpan."
            : "Tahapan {$tahap->jenis_tahap->label()} berhasil difinalkan dan dikunci permanen. Progress berkas perkara telah diperbarui.";

        return redirect()
            ->route('berkas-perkara.tahapan.show', [$berkas, $tahap])
            ->with('success', $message);
    }
}
