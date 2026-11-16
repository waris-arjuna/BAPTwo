<?php

namespace App\Http\Controllers;

use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditTrailService $auditTrailService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('manage-users');

        $filters = [
            'search' => $request->string('search')->trim()->toString(),
            'kategori' => $request->string('kategori')->trim()->toString(),
            'aksi' => $request->string('aksi')->trim()->toString(),
            'tanggal_dari' => $request->string('tanggal_dari')->trim()->toString(),
            'tanggal_sampai' => $request->string('tanggal_sampai')->trim()->toString(),
        ];

        $logs = $this->auditTrailService->getGlobalLogs($filters);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.pengaturan.audit-log._table-partial', [
                'logs' => $logs,
            ]);
        }

        return view('pages.pengaturan.audit-log.index', [
            'title' => __('Audit Log Sistem'),
            'logs' => $logs,
            'stats' => $this->auditTrailService->getGlobalSummaryStats(),
            'actionOptions' => $this->auditTrailService->getActionOptions(),
            'filters' => $filters,
        ]);
    }
}
