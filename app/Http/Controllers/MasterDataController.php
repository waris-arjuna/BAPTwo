<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterData\SaveMasterDataRequest;
use App\Models\InstansiHukum;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function __construct(
        private readonly MasterDataService $masterDataService
    ) {}

    public function redirectDefault(): RedirectResponse
    {
        return redirect()->route('master-data.index', ['type' => 'jabatan']);
    }

    public function index(Request $request, string $type): View
    {
        Gate::authorize('view-cases');

        $meta = $this->masterDataService->getMeta($type);
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $records = $this->masterDataService->list($type, $search, $status);

        $kejaksaanList = $type === 'jaksa-penuntut'
            ? InstansiHukum::where('jenis', 'kejaksaan')->orderBy('nama')->get()
            : collect();

        if ($request->header('X-Filter-Partial')) {
            return view('pages.master-data._table-partial', [
                'type' => $type,
                'records' => $records,
                'canManage' => Gate::allows('manage-master-data'),
            ]);
        }

        return view('pages.master-data.index', [
            'title' => __($meta['title']),
            'type' => $type,
            'meta' => $meta,
            'allTypes' => MasterDataService::TYPES,
            'records' => $records,
            'search' => $search,
            'status' => $status,
            'kejaksaanList' => $kejaksaanList,
            'canManage' => Gate::allows('manage-master-data'),
        ]);
    }

    public function store(SaveMasterDataRequest $request, string $type): RedirectResponse
    {
        $meta = $this->masterDataService->getMeta($type);
        $this->masterDataService->create($type, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('master-data.index', ['type' => $type])
            ->with('success', __(':item berhasil ditambahkan.', ['item' => __($meta['singular'])]));
    }

    public function update(SaveMasterDataRequest $request, string $type, int $id): RedirectResponse
    {
        $meta = $this->masterDataService->getMeta($type);
        $this->masterDataService->update($type, $id, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('master-data.index', ['type' => $type])
            ->with('success', __('Data :item berhasil diperbarui.', ['item' => __($meta['singular'])]));
    }

    public function destroy(Request $request, string $type, int $id): RedirectResponse
    {
        Gate::authorize('manage-master-data');

        $meta = $this->masterDataService->getMeta($type);
        $this->masterDataService->softDelete($type, $id);

        return redirect()
            ->route('master-data.index', ['type' => $type])
            ->with('success', __(':item berhasil dinonaktifkan (soft delete).', ['item' => __($meta['singular'])]));
    }

    public function restore(Request $request, string $type, int $id): RedirectResponse
    {
        Gate::authorize('manage-master-data');

        $meta = $this->masterDataService->getMeta($type);
        $this->masterDataService->restore($type, $id);

        return redirect()
            ->route('master-data.index', ['type' => $type])
            ->with('success', __(':item berhasil dipulihkan.', ['item' => __($meta['singular'])]));
    }
}
