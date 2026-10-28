<?php

namespace App\Http\Controllers;

use App\Exports\PerkaraRekapExport;
use App\Services\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerkaraExportController extends Controller
{
    /**
     * Ekspor data rekap perkara ke format Excel (.xlsx) dengan template LAPJU.
     * Dibatasi hanya untuk peran Administrator dan Penyidik (Poin 10).
     */
    public function export(Request $request): StreamedResponse|RedirectResponse|JsonResponse
    {
        Gate::authorize('export-excel');

        $validated = $request->validate([
            'mode' => ['nullable', 'string', 'in:semua,tahun,bulan,rentang'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'check' => ['nullable', 'boolean'],
        ]);

        $mode = $validated['mode'] ?? 'semua';
        $tahun = ! empty($validated['tahun']) ? (int) $validated['tahun'] : (int) now()->year;
        $bulan = ! empty($validated['bulan']) ? (int) $validated['bulan'] : (int) now()->month;
        $tanggalMulai = $validated['tanggal_mulai'] ?? null;
        $tanggalSelesai = $validated['tanggal_selesai'] ?? null;

        $export = new PerkaraRekapExport(
            mode: $mode,
            tahun: $tahun,
            bulan: $bulan,
            tanggalMulai: $tanggalMulai,
            tanggalSelesai: $tanggalSelesai
        );

        $totalPerkara = $export->query()->count();

        // 1. Jika request eksplisit pre-check jumlah data
        if ($request->boolean('check')) {
            return response()->json([
                'count' => $totalPerkara,
                'message' => $totalPerkara === 0 ? 'Tidak ada data pada periode ini' : 'Data tersedia',
            ]);
        }

        // 2. Jika request via fetch download dan periode kosong: kembalikan 422 JSON
        if ($totalPerkara === 0 && ($request->hasHeader('X-Export-Fetch') || $request->wantsJson() || $request->ajax())) {
            return response()->json([
                'message' => 'Tidak ada data pada periode ini',
            ], 422);
        }

        // 3. Jika periode tidak memiliki perkara: JANGAN unduh file, tampilkan pop-up warning
        if ($totalPerkara === 0) {
            return redirect()->back()->with('warning', 'Tidak ada data pada periode ini');
        }

        $filename = $export->getFilename();

        app(AuditTrailService::class)->log(
            aksi: 'export_excel',
            tabel: 'perkara',
            recordId: 0,
            dataBaru: [
                'mode' => $mode,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'filename' => $filename,
                'total_perkara' => $totalPerkara,
            ]
        );

        $response = $export->download($filename);
        $response->headers->set('X-Export-Filename', $filename);

        return $response;
    }
}
