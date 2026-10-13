<?php

namespace App\Http\Controllers;

use App\Http\Requests\RevokeDpoRequest;
use App\Http\Requests\StoreDpoRequest;
use App\Models\BerkasPerkara;
use App\Services\DpoService;
use DomainException;
use Illuminate\Http\RedirectResponse;
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
                ->with('success', 'Status DPO berhasil ditetapkan untuk tersangka.');
        } catch (DomainException $e) {
            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mencabut status DPO pada Berkas Perkara (PRD F-26, F-27)
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
                ->with('success', 'Status DPO berhasil dicabut. Berkas perkara dapat dilanjutkan normal.');
        } catch (DomainException $e) {
            return redirect()
                ->route('perkara.show', $berkas->perkara_id)
                ->with('error', $e->getMessage());
        }
    }
}
