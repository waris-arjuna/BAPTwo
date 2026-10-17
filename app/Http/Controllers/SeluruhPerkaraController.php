<?php

namespace App\Http\Controllers;

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeluruhPerkaraController extends Controller
{
    /**
     * Tampilkan Halaman Seluruh Perkara dengan Ringkasan Statistik Metrik (PRD F-38, F-39, F-43)
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        // 5 Kartu Statistik Metrik Perkara & Berkas
        $totalPerkara = Perkara::count();
        $totalBerkas = BerkasPerkara::count();
        $berkasDalamProses = BerkasPerkara::query()
            ->where(function ($q) {
                $q->whereNull('status_formal')
                    ->orWhere('status_formal', 'dalam_proses');
            })
            ->count();
        $berkasSelesai = BerkasPerkara::where('status_formal', 'selesai')->count();
        $berkasTidakSelesai = BerkasPerkara::where('status_formal', 'tidak_selesai')->count();
        $tersangkaDpo = BerkasPerkara::where('is_dpo', true)->count();

        // Query Daftar Seluruh Perkara
        $perkaras = Perkara::query()
            ->with([
                'dokumenSprindik',
                'dokumenLaporan',
                'tkp.alamat',
                'ppnsUtama',
                'tersangkas.berkasPerkara.pasals',
                'creator',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_surat_perintah', 'like', "%{$search}%")
                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('tkp', function (Builder $tkpQ) use ($search) {
                            $tkpQ->where('klasifikasi', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tersangkas', function (Builder $tskQ) use ($search) {
                            $tskQ->where('nama', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%");
                        })
                        ->orWhereHas('ppnsUtama', function (Builder $ppnsQ) use ($search) {
                            $ppnsQ->where('nama', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Dalam Proses') {
                    $query->whereHas('berkasPerkaras', function ($q) {
                        $q->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                    });
                } elseif ($status === 'Selesai') {
                    $query->whereHas('berkasPerkaras', function ($q) {
                        $q->where('status_formal', 'selesai');
                    });
                } elseif ($status === 'Tidak Selesai') {
                    $query->whereHas('berkasPerkaras', function ($q) {
                        $q->where('status_formal', 'tidak_selesai');
                    });
                } elseif ($status === 'DPO') {
                    $query->whereHas('berkasPerkaras', function ($q) {
                        $q->where('is_dpo', true);
                    });
                }
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->where(function ($q) use ($tanggalDari) {
                    $q->whereDate('tanggal_laporan_kejadian', '>=', $tanggalDari)
                        ->orWhereDate('tanggal_surat_perintah', '>=', $tanggalDari);
                });
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->where(function ($q) use ($tanggalSampai) {
                    $q->whereDate('tanggal_laporan_kejadian', '<=', $tanggalSampai)
                        ->orWhereDate('tanggal_surat_perintah', '<=', $tanggalSampai);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        if ($request->header('X-Filter-Partial')) {
            return view('pages.seluruh-perkara._table-partial', compact('perkaras'));
        }

        return view('pages.seluruh-perkara.index', compact(
            'perkaras',
            'totalPerkara',
            'totalBerkas',
            'berkasDalamProses',
            'berkasSelesai',
            'berkasTidakSelesai',
            'tersangkaDpo'
        ));
    }
}
