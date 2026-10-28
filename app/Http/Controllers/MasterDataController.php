<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterData\SaveMasterDataRequest;
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
        Gate::authorize('view-master-data');

        $defaultType = auth()->user()?->isPenyidik() ? 'pasal' : 'jabatan';

        return redirect()->route('master-data.index', ['type' => $defaultType]);
    }

    public function index(Request $request, string $type): View
    {
        Gate::authorize('view-master-data', $type);

        $meta = $this->masterDataService->getMeta($type);
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $records = $this->masterDataService->list($type, $search, $status);

        // Poin 7: Jika Penyidik, hanya tampilkan tab Pasal & Instansi Hukum
        $visibleTypes = auth()->user()?->isPenyidik()
            ? array_intersect_key(MasterDataService::TYPES, array_flip(['pasal', 'instansi-hukum']))
            : MasterDataService::TYPES;

        if ($request->header('X-Filter-Partial')) {
            return view('pages.master-data._table-partial', [
                'type' => $type,
                'records' => $records,
                'canManage' => Gate::allows('manage-master-data', $type),
            ]);
        }

        return view('pages.master-data.index', [
            'title' => __($meta['title']),
            'type' => $type,
            'meta' => $meta,
            'allTypes' => $visibleTypes,
            'records' => $records,
            'search' => $search,
            'status' => $status,
            'canManage' => Gate::allows('manage-master-data', $type),
        ]);
    }

    public function store(SaveMasterDataRequest $request, string $type): RedirectResponse
    {
        Gate::authorize('manage-master-data', $type);

        $meta = $this->masterDataService->getMeta($type);
        $this->masterDataService->create($type, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('master-data.index', ['type' => $type])
            ->with('success', [
                'title' => 'Master Data Ditambahkan',
                'message' => __(':item berhasil ditambahkan.', ['item' => __($meta['singular'])]),
            ]);
    }

    public function update(SaveMasterDataRequest $request, string $type, int $id): RedirectResponse
    {
        Gate::authorize('manage-master-data', $type);

        $meta = $this->masterDataService->getMeta($type);
        $this->masterDataService->update($type, $id, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('master-data.index', ['type' => $type])
            ->with('success', [
                'title' => 'Master Data Diperbarui',
                'message' => __('Data :item berhasil diperbarui.', ['item' => __($meta['singular'])]),
            ]);
    }

    public function destroy(Request $request, string $type, int $id): RedirectResponse
    {
        Gate::authorize('manage-master-data', $type);

        $meta = $this->masterDataService->getMeta($type);
        $this->masterDataService->softDelete($type, $id);

        return redirect()
            ->route('master-data.index', ['type' => $type])
            ->with('success', [
                'title' => 'Master Data Dinonaktifkan',
                'message' => __(':item berhasil dinonaktifkan (soft delete).', ['item' => __($meta['singular'])]),
            ]);
    }

    public function restore(Request $request, string $type, int $id): RedirectResponse
    {
        Gate::authorize('manage-master-data', $type);

        $meta = $this->masterDataService->getMeta($type);
        $this->masterDataService->restore($type, $id);

        return redirect()
            ->route('master-data.index', ['type' => $type])
            ->with('success', [
                'title' => 'Master Data Dipulihkan',
                'message' => __(':item berhasil dipulihkan.', ['item' => __($meta['singular'])]),
            ]);
    }
}
