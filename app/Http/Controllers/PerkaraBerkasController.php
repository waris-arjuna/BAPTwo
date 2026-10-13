<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePerkaraBerkasRequest;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\User;
use App\Services\PerkaraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PerkaraBerkasController extends Controller
{
    public function __construct(
        protected PerkaraService $perkaraService
    ) {}

    /**
     * Tampilkan form Buat / Edit Draft Perkara & Berkas Perkara Pra-SPDP
     * (TKP, PPNS Utama/Anggota, Tersangka, Berkas Perkara, Pasal Berlapis)
     */
    public function edit(Perkara $perkara): View
    {
        abort_if(! $perkara->isSprindikLocked(), 403, 'Tahap Sprindik harus difinalkan terlebih dahulu.');

        Gate::authorize('edit-draft', $perkara->isPerkaraLocked());

        $perkara->load([
            'dokumenLaporan',
            'dokumenSprindik',
            'tkp.alamat',
            'ppnsUtama',
            'ppnsAnggota',
            'tersangkas.berkasPerkara.pasals',
        ]);

        $pasalList = Pasal::where('status', 'aktif')->orderBy('nama_pasal')->get();
        $ppnsUsers = User::where('is_active', true)->orderBy('nama')->get();

        return view('pages.perkara.form', compact('perkara', 'pasalList', 'ppnsUsers'));
    }

    /**
     * Simpan Draft atau Finalkan Perkara & Berkas Perkara Pra-SPDP
     */
    public function update(SavePerkaraBerkasRequest $request, Perkara $perkara): RedirectResponse
    {
        abort_if(! $perkara->isSprindikLocked(), 403, 'Tahap Sprindik harus difinalkan terlebih dahulu.');

        Gate::authorize('edit-draft', $perkara->isPerkaraLocked());

        $validated = $request->validated();
        $isDraft = ($validated['action_type'] ?? 'lanjut') === 'draft';

        $this->perkaraService->savePerkaraBerkas($perkara, $validated, $request->user());

        $message = $isDraft
            ? 'Draft data Perkara & Berkas Perkara berhasil disimpan. Anda masih dapat mengedit data ini.'
            : 'Data Perkara & Berkas Perkara berhasil difinalkan dan dikunci permanen. Silakan lanjutkan ke tahap SPDP.';

        return redirect()
            ->route('perkara.show', $perkara)
            ->with('success', $message);
    }

    /**
     * Tampilkan detail Perkara & Daftar Berkas Perkara per Tersangka
     */
    public function show(Perkara $perkara): View
    {
        $perkara->load([
            'dokumenLaporan',
            'dokumenSprindik',
            'tkp.alamat',
            'ppnsUtama',
            'ppnsAnggota',
            'tersangkas.berkasPerkara.pasals',
            'creator',
            'perkaraFinalizer',
            'auditLogs.user',
        ]);

        return view('pages.perkara.show', compact('perkara'));
    }
}
