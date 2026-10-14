<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSp3Request;
use App\Models\BerkasPerkara;
use App\Services\Sp3Service;
use DomainException;
use Illuminate\Http\RedirectResponse;

class Sp3Controller extends Controller
{
    public function __construct(
        protected Sp3Service $sp3Service
    ) {}

    /**
     * Menerbitkan SP3 untuk berkas perkara
     */
    public function store(StoreSp3Request $request, BerkasPerkara $berkas): RedirectResponse
    {
        try {
            $file = $request->file('dokumen');
            if (! $file) {
                return redirect()->back()->with('error', 'Dokumen PDF SP3 wajib diunggah.');
            }

            $this->sp3Service->issueSp3(
                berkas: $berkas,
                data: $request->validated(),
                file: $file,
                actorId: $request->user()?->id
            );

            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('success', 'Surat Perintah Penghentian Penyidikan (SP3) berhasil diterbitkan. Status berkas kini menjadi Tidak Selesai.');
        } catch (DomainException $e) {
            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('error', $e->getMessage());
        }
    }
}
