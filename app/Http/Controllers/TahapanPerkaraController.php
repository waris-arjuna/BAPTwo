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
                ->with('warning', [
                    'title' => 'Draft Masih Berjalan',
                    'message' => 'Berkas perkara '.$berkas->nomor_berkas.' memiliki tahapan yang masih berstatus draft. Silakan selesaikan atau lanjutkan draft tersebut.',
                ]);
        }

        $availableStages = $this->tahapanService->getNextAvailableStages($berkas);

        if ($availableStages === []) {
            return redirect()
                ->route('data-perkara.index')
                ->with('warning', [
                    'title' => 'Tahapan Lengkap',
                    'message' => 'Seluruh tahapan progress pada Berkas Perkara '.$berkas->nomor_berkas.' telah selesai.',
                ]);
        }

        $requestedJenis = JenisTahap::tryFrom((string) $request->query('jenis'));
        if ($requestedJenis !== null && ! in_array($requestedJenis, $availableStages, true)) {
            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('error', [
                    'title' => 'Tahapan Tidak Valid',
                    'message' => "Tahapan {$requestedJenis->label()} tidak dapat dipilih karena sudah pernah dicatat atau belum memenuhi urutan tahapan berkas perkara ini.",
                ]);
        }

        $selectedJenis = $requestedJenis ?? $availableStages[0];

        $references = $this->tahapanService->getPreviousStagesReference($berkas);

        $subTahapan = TahapanPerkara::where('berkas_perkara_id', $berkas->id)
            ->whereIn('jenis_tahap', [
                JenisTahap::P16->value,
                JenisTahap::P17P->value,
                JenisTahap::P18P->value,
                JenisTahap::P19->value,
            ])
            ->with('dokumen')
            ->get()
            ->keyBy(fn ($t) => $t->jenis_tahap->value);

        return view('pages.tahapan.form', [
            'berkas' => $berkas->load(['perkara', 'tersangka']),
            'tahap' => null,
            'subTahapan' => $subTahapan,
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

        try {
            $tahap = $this->tahapanService->saveStage(
                $berkas,
                $validated,
                $request->file('dokumen'),
                $request->user()
            );
        } catch (\DomainException $e) {
            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('error', [
                    'title' => 'Gagal Menyimpan Tahapan',
                    'message' => $e->getMessage(),
                ]);
        }

        $payload = $isDraft
            ? [
                'title' => 'Draft Tahapan Tersimpan',
                'message' => "Draft tahapan {$tahap->jenis_tahap->label()} untuk berkas {$berkas->nomor_berkas} berhasil disimpan.",
            ]
            : [
                'title' => 'Tahapan Difinalkan',
                'message' => "Tahapan {$tahap->jenis_tahap->label()} untuk berkas {$berkas->nomor_berkas} berhasil disimpan dan dikunci permanen. Progress berkas perkara telah diperbarui.",
            ];

        return redirect()
            ->route('berkas-perkara.tahapan.show', [$berkas, $tahap])
            ->with('success', $payload);
    }

    /**
     * Tampilkan detail tahapan (mode Read-Only / Terkunci).
     */
    public function show(BerkasPerkara $berkas, TahapanPerkara $tahapan): View
    {
        $tahapan->load(['dokumen', 'finalizedBy', 'createdBy', 'updatedBy']);
        $references = $this->tahapanService->getPreviousStagesReference($berkas);

        $subTahapan = TahapanPerkara::where('berkas_perkara_id', $berkas->id)
            ->whereIn('jenis_tahap', [
                JenisTahap::P16->value,
                JenisTahap::P17P->value,
                JenisTahap::P18P->value,
                JenisTahap::P19->value,
            ])
            ->with(['dokumen', 'createdBy', 'finalizedBy'])
            ->get()
            ->keyBy(fn ($t) => $t->jenis_tahap->value);

        return view('pages.tahapan.show', [
            'berkas' => $berkas->load(['perkara', 'tersangka']),
            'tahap' => $tahapan,
            'references' => $references,
            'isLocked' => $tahapan->isFinal(),
            'subTahapan' => $subTahapan,
        ]);
    }

    /**
     * Tampilkan formulir edit tahapan draft.
     */
    public function edit(BerkasPerkara $berkas, TahapanPerkara $tahapan): View|RedirectResponse
    {
        $isSubStageEligible = in_array($tahapan->jenis_tahap, [JenisTahap::SPDP, JenisTahap::TAHAP_1], true);

        if ($tahapan->isFinal() && ! $isSubStageEligible) {
            return redirect()
                ->route('berkas-perkara.tahapan.show', [$berkas, $tahapan])
                ->with('warning', [
                    'title' => 'Tahapan Terkunci',
                    'message' => "Tahapan {$tahapan->jenis_tahap->label()} telah difinalkan dan terkunci permanen, tidak dapat diedit.",
                ]);
        }

        Gate::authorize('edit-draft');

        $references = $this->tahapanService->getPreviousStagesReference($berkas);

        $subTahapan = TahapanPerkara::where('berkas_perkara_id', $berkas->id)
            ->whereIn('jenis_tahap', [
                JenisTahap::P16->value,
                JenisTahap::P17P->value,
                JenisTahap::P18P->value,
                JenisTahap::P19->value,
            ])
            ->with('dokumen')
            ->get()
            ->keyBy(fn ($t) => $t->jenis_tahap->value);

        return view('pages.tahapan.form', [
            'berkas' => $berkas->load(['perkara', 'tersangka']),
            'tahap' => $tahapan->load('dokumen'),
            'subTahapan' => $subTahapan,
            'selectedJenis' => $tahapan->jenis_tahap,
            'availableStages' => [$tahapan->jenis_tahap],
            'references' => $references,
            'mode' => 'edit',
            'isLocked' => $tahapan->isFinal(),
        ]);
    }

    /**
     * Update data tahapan draft atau simpan & lanjut.
     */
    public function update(UpdateTahapanRequest $request, BerkasPerkara $berkas, TahapanPerkara $tahapan): RedirectResponse
    {
        $isSubStageEligible = in_array($tahapan->jenis_tahap, [JenisTahap::SPDP, JenisTahap::TAHAP_1], true);

        if ($tahapan->isFinal() && ! $isSubStageEligible) {
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

        $payload = $isDraft
            ? [
                'title' => 'Draft Tahapan Diperbarui',
                'message' => "Perubahan draft tahapan {$tahap->jenis_tahap->label()} untuk berkas {$berkas->nomor_berkas} berhasil disimpan.",
            ]
            : [
                'title' => 'Tahapan Difinalkan',
                'message' => "Tahapan {$tahap->jenis_tahap->label()} untuk berkas {$berkas->nomor_berkas} berhasil difinalkan dan dikunci permanen. Progress berkas perkara telah diperbarui.",
            ];

        return redirect()
            ->route('berkas-perkara.tahapan.show', [$berkas, $tahap])
            ->with('success', $payload);
    }
}
