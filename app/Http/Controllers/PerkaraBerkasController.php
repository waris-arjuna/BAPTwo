<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\SavePerkaraBerkasRequest;
use App\Models\InstansiHukum;
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
            'penuntutUmumInstansi',
            'tersangkas.alamat',
            'tersangkas.berkasPerkara.pasals',
        ]);

        $pasalList = Pasal::where('status', 'aktif')->orderBy('nama_pasal')->get();
        $kejaksaanList = InstansiHukum::where('jenis', 'kejaksaan')->orderBy('nama')->get();

        $existingPpnsIds = array_filter(array_merge(
            [(int) $perkara->ppns_utama_id],
            $perkara->ppnsAnggota->pluck('id')->all()
        ));

        $ppnsUsers = User::with('panggol')
            ->where('is_active', true)
            ->where(function ($q) use ($existingPpnsIds) {
                $q->where('role', UserRole::PENYIDIK);
                if (! empty($existingPpnsIds)) {
                    $q->orWhereIn('id', $existingPpnsIds);
                }
            })
            ->orderBy('nama')
            ->get();

        return view('pages.perkara.form', compact('perkara', 'pasalList', 'ppnsUsers', 'kejaksaanList'));
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

        $payload = $isDraft
            ? [
                'title' => 'Draft Perkara Tersimpan',
                'message' => 'Draft data Perkara & Berkas Perkara untuk '.($perkara->nomor_laporan ?: '').' berhasil disimpan.',
            ]
            : [
                'title' => 'Berkas Perkara Final',
                'message' => 'Data Perkara & Berkas Perkara untuk '.($perkara->nomor_laporan ?: '').' berhasil difinalkan dan dikunci permanen. Silakan lanjutkan ke tahap SPDP.',
            ];

        return redirect()
            ->route('perkara.show', $perkara)
            ->with('success', $payload);
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
            'penuntutUmumInstansi',
            'tersangkas.alamat',
            'tersangkas.berkasPerkara.pasals',
            'tersangkas.berkasPerkara.tahapan.dokumen',
            'tersangkas.berkasPerkara.dpos.dokumen',
            'tersangkas.berkasPerkara.dpos.creator',
            'tersangkas.berkasPerkara.sp3.dokumen',
            'creator',
            'perkaraFinalizer',
            'auditLogs' => fn ($query) => $query->with(['user', 'berkasPerkara.tersangka'])->orderBy('id', 'desc'),
        ]);

        return view('pages.perkara.show', compact('perkara'));
    }
}
