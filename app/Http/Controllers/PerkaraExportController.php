<?php

namespace App\Http\Controllers;

use App\Exports\PerkaraRekapExport;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerkaraExportController extends Controller
{
    /**
     * Ekspor data rekap perkara ke format Excel (.xlsx).
     * Dibatasi hanya untuk peran Administrator dan Penyidik (Poin 10).
     */
    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('export-excel');

        $validated = $request->validate([
            'mode' => ['nullable', 'string', 'in:semua,tahun,bulan,rentang'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $mode = $validated['mode'] ?? 'semua';
        $tahun = ! empty($validated['tahun']) ? (int) $validated['tahun'] : (int) now()->year;
        $bulan = ! empty($validated['bulan']) ? (int) $validated['bulan'] : (int) now()->month;
        $tanggalMulai = $validated['tanggal_mulai'] ?? null;
        $tanggalSelesai = $validated['tanggal_selesai'] ?? null;

        $timestamp = now()->format('Ymd_His');

        if ($mode === 'semua') {
            $filename = "rekap_seluruh_perkara_{$timestamp}.xlsx";
        } elseif ($mode === 'bulan') {
            $bulanStr = str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);
            $filename = "rekap_perkara_{$tahun}_{$bulanStr}_{$timestamp}.xlsx";
        } elseif ($mode === 'rentang' && $tanggalMulai && $tanggalSelesai) {
            $filename = "rekap_perkara_{$tanggalMulai}_sd_{$tanggalSelesai}_{$timestamp}.xlsx";
        } else {
            $filename = "rekap_perkara_tahun_{$tahun}_{$timestamp}.xlsx";
        }

        $export = new PerkaraRekapExport(
            mode: $mode,
            tahun: $tahun,
            bulan: $bulan,
            tanggalMulai: $tanggalMulai,
            tanggalSelesai: $tanggalSelesai
        );

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
            ]
        );

        return $export->download($filename);
    }
}
