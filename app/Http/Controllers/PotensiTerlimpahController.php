<?php

namespace App\Http\Controllers;

use App\Models\BerkasPerkara;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PotensiTerlimpahController extends Controller
{
    /**
     * Tampilkan Dashboard Monitoring Potensi Terlimpah (PRD F-41)
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $kategori = $request->string('kategori', $request->string('status')->toString())->trim()->toString();
        $ppnsId = $request->integer('ppns_id');
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        // Base query: seluruh berkas yang masih aktif berjalan (belum Selesai dan belum Tidak Selesai)
        $baseActiveQuery = BerkasPerkara::query()
            ->where(function (Builder $q) {
                $q->whereNull('status_formal')
                    ->orWhere('status_formal', 'dalam_proses');
            });

        // Hitung Ringkasan Metrik Potensi Terlimpah
        $totalPotensi = (clone $baseActiveQuery)->count();
        $siapTahap2Count = (clone $baseActiveQuery)->where('progress_persen', '>=', 55)->count();
        $terkendalaDpoCount = (clone $baseActiveQuery)->where('is_dpo', true)->count();

        // Hitung evaluasi SLA dari berkas yang telah terbit SPDP
        $activeWithSpdp = (clone $baseActiveQuery)->whereNotNull('tanggal_spdp')->get();
        $kritisCount = $activeWithSpdp->filter(fn (BerkasPerkara $b) => $b->sla_status === 'lewat_sla')->count();
        $mendekatiBatasCount = $activeWithSpdp->filter(fn (BerkasPerkara $b) => $b->sla_status === 'mendekati_sla')->count();

        // Query Utama untuk Tabel
        $query = (clone $baseActiveQuery)
            ->with([
                'perkara.dokumenSprindik',
                'perkara.dokumenLaporan',
                'perkara.ppnsUtama',
                'perkara.tkp.alamat',
                'tersangka',
                'pasals',
                'tahapan',
            ]);

        // Filter Kategori Urgensi
        if ($kategori === 'kritis') {
            $kritisIds = $activeWithSpdp->filter(fn (BerkasPerkara $b) => $b->sla_status === 'lewat_sla')->pluck('id');
            $query->whereIn('id', $kritisIds);
        } elseif ($kategori === 'mendekati_batas') {
            $mendekatiIds = $activeWithSpdp->filter(fn (BerkasPerkara $b) => $b->sla_status === 'mendekati_sla')->pluck('id');
            $query->whereIn('id', $mendekatiIds);
        } elseif ($kategori === 'siap_tahap2') {
            $query->where('progress_persen', '>=', 55);
        } elseif ($kategori === 'dpo') {
            $query->where('is_dpo', true);
        }

        // Filter Teks
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('nomor_berkas', 'like', "%{$search}%")
                    ->orWhere('status_proses', 'like', "%{$search}%")
                    ->orWhereHas('tersangka', function (Builder $tskQ) use ($search) {
                        $tskQ->where('nama', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%")
                            ->orWhere('alias', 'like', "%{$search}%");
                    })
                    ->orWhereHas('perkara', function (Builder $perkQ) use ($search) {
                        $perkQ->where('nomor_laporan_kejadian', 'like', "%{$search}%")
                            ->orWhere('nomor_surat_perintah', 'like', "%{$search}%")
                            ->orWhere('deskripsi', 'like', "%{$search}%");
                    })
                    ->orWhereHas('perkara.ppnsUtama', function (Builder $ppnsQ) use ($search) {
                        $ppnsQ->where('nama', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
                    });
            });
        }

        // Filter Penyidik PPNS
        if ($ppnsId > 0) {
            $query->whereHas('perkara.ppnsUtama', function (Builder $q) use ($ppnsId) {
                $q->where('user.id', $ppnsId);
            });
        }

        // Filter Tanggal SPDP
        if ($tanggalDari !== '') {
            $query->whereDate('tanggal_spdp', '>=', $tanggalDari);
        }
        if ($tanggalSampai !== '') {
            $query->whereDate('tanggal_spdp', '<=', $tanggalSampai);
        }

        // Urutkan: progress tertinggi lebih dulu, kemudian tanggal SPDP tertua (prioritas penyelesaian)
        $berkasList = $query
            ->orderByDesc('progress_persen')
            ->orderBy('tanggal_spdp')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $ppnsOptions = User::query()
            ->where('role', 'penyidik')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip']);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.potensi-terlimpah._table-partial', compact('berkasList'));
        }

        return view('pages.potensi-terlimpah.index', compact(
            'berkasList',
            'ppnsOptions',
            'totalPotensi',
            'kritisCount',
            'mendekatiBatasCount',
            'siapTahap2Count',
            'terkendalaDpoCount',
            'kategori',
            'search',
            'ppnsId',
            'tanggalDari',
            'tanggalSampai'
        ));
    }
}
