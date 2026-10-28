<?php

namespace App\Http\Controllers;

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeluruhPerkaraController extends Controller
{
    /**
     * Tampilkan Halaman Seluruh Perkara dengan Ringkasan Statistik Metrik (PRD F-38, F-39, F-43)
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Admin & Penyidik dialihkan langsung ke Daftar Perkara (Poin 3A)
        if ($request->user() && ($request->user()->isAdmin() || $request->user()->isPenyidik())) {
            return redirect()->route('data-perkara.index', $request->query());
        }
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tahun = $request->integer('tahun');
        $bulan = $request->integer('bulan');
        $sla = $request->string('sla')->trim()->toString();
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
                'berkasPerkaras',
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
            ->when($sla !== '', function (Builder $query) use ($sla) {
                $today = Carbon::today(DashboardService::ZONA_WAKTU)->toDateString();
                $approachingDate = Carbon::today(DashboardService::ZONA_WAKTU)->addDays(DashboardService::AMBANG_MENDEKATI_SLA_HARI)->toDateString();

                $query->whereHas('berkasPerkaras', function ($q) use ($sla, $today, $approachingDate) {
                    $q->where(function ($sub) {
                        $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                    });

                    if ($sla === 'belum_spdp') {
                        $q->whereNull('sla_deadline');
                    } elseif ($sla === 'dalam_sla') {
                        $q->whereNotNull('sla_deadline')->where('sla_deadline', '>', $approachingDate);
                    } elseif ($sla === 'mendekati_sla') {
                        $q->whereNotNull('sla_deadline')->where('sla_deadline', '>=', $today)->where('sla_deadline', '<=', $approachingDate);
                    } elseif ($sla === 'lewat_sla') {
                        $q->whereNotNull('sla_deadline')->where('sla_deadline', '<', $today);
                    }
                });
            })
            ->when($tahun > 0, function (Builder $query) use ($tahun) {
                $query->whereYear('tanggal_laporan_kejadian', $tahun);
            })
            ->when($bulan > 0 && $bulan <= 12, function (Builder $query) use ($bulan) {
                $query->whereMonth('tanggal_laporan_kejadian', $bulan);
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
