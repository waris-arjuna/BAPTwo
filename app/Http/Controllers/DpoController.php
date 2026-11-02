<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResolveDpoPenangkapanRequest;
use App\Http\Requests\RevokeDpoRequest;
use App\Http\Requests\StoreDpoRequest;
use App\Models\BerkasPerkara;
use App\Services\DpoService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

class DpoController extends Controller
{
    public function __construct(
        protected DpoService $dpoService
    ) {}

    /**
     * Menetapkan status DPO pada Berkas Perkara (PRD F-24, F-25)
     */
    public function store(StoreDpoRequest $request, BerkasPerkara $berkas): RedirectResponse
    {
        Gate::authorize('toggle-dpo');

        try {
            $this->dpoService->activateDpo(
                $berkas,
                $request->validated(),
                $request->file('dokumen'),
                $request->user()
            );

            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('success', [
                    'title' => 'Penetapan DPO Berhasil',
                    'message' => 'Status DPO berhasil ditetapkan untuk tersangka pada berkas '.$berkas->nomor_berkas.'.',
                ]);
        } catch (DomainException $e) {
            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('error', [
                    'title' => 'Gagal Menetapkan DPO',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Mencatat penangkapan dan penahanan tersangka DPO (Poin 4, ref: D-053).
     */
    public function penangkapan(ResolveDpoPenangkapanRequest $request, BerkasPerkara $berkas): RedirectResponse
    {
        Gate::authorize('toggle-dpo');

        try {
            $files = $request->file('dokumen_penangkapan');
            if ($files instanceof UploadedFile) {
                $files = [$files];
            } elseif (! is_array($files)) {
                $files = [];
            }

            $this->dpoService->recordPenangkapan(
                $berkas,
                $request->validated(),
                $files,
                $request->user()
            );

            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('success', [
                    'title' => 'Penangkapan DPO Berhasil Dicatat',
                    'message' => 'Penangkapan dan penahanan tersangka pada berkas '.$berkas->nomor_berkas.' telah dicatat. Status DPO nonaktif dan tahapan perkara dapat dilanjutkan.',
                ]);
        } catch (DomainException $e) {
            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('error', [
                    'title' => 'Gagal Mencatat Penangkapan DPO',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Mencabut status DPO pada Berkas Perkara secara administratif (PRD F-26, F-27).
     */
    public function destroy(RevokeDpoRequest $request, BerkasPerkara $berkas): RedirectResponse
    {
        Gate::authorize('toggle-dpo');

        try {
            $this->dpoService->revokeDpo(
                $berkas,
                $request->validated(),
                $request->user()
            );

            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('success', [
                    'title' => 'Pencabutan DPO Berhasil',
                    'message' => 'Status DPO berhasil dicabut. Berkas perkara '.$berkas->nomor_berkas.' dapat dilanjutkan normal.',
                ]);
        } catch (DomainException $e) {
            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('error', [
                    'title' => 'Gagal Mencabut DPO',
                    'message' => $e->getMessage(),
                ]);
        }
    }
}
