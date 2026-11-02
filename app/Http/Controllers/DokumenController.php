<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenController extends Controller
{
    /**
     * Download dokumen legal PDF privat
     */
    public function download(Dokumen $dokumen): StreamedResponse
    {
        Gate::authorize('download', $dokumen);

        if (! Storage::disk('local')->exists($dokumen->file_path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        return Storage::disk('local')->download($dokumen->file_path, $dokumen->nama_file_asli);
    }

    /**
     * Preview dokumen PDF atau Gambar langsung di browser
     */
    public function preview(Dokumen $dokumen): mixed
    {
        Gate::authorize('view', $dokumen);

        if (! Storage::disk('local')->exists($dokumen->file_path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $mimeType = Storage::disk('local')->mimeType($dokumen->file_path) ?? 'application/pdf';

        return Storage::disk('local')->response($dokumen->file_path, $dokumen->nama_file_asli, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$dokumen->nama_file_asli.'"',
        ]);
    }
}
