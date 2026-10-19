<?php

namespace App\Http\Controllers;

use App\Services\PerkaraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProgressPerkaraController extends Controller
{
    public function __construct(
        protected PerkaraService $perkaraService
    ) {}

    /**
     * Tampilkan monitoring seluruh tahapan Progress Perkara
     */
    public function index(Request $request): View
    {
        Gate::authorize('view-progress-perkara');

        $berkasList = $this->perkaraService->getProgressPerkaraList($request);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.progress-perkara._table-partial', compact('berkasList'));
        }

        return view('pages.progress-perkara.index', compact('berkasList'));
    }
}
