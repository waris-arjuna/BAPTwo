<?php

namespace App\Http\Controllers;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PencarianPerkaraController extends Controller
{
    /**
     * Hub Pencarian Cepat & Multi-Kriteria Perkara (PRD F-38, F-39)
     */
    public function index(Request $request): View
    {
        $search = $request->string('search', $request->string('q')->toString())->trim()->toString();
        $statusProses = $request->string('status_proses')->trim()->toString();
        $statusFormal = $request->string('status_formal')->trim()->toString();
        $isDpo = $request->string('is_dpo')->trim()->toString();
        $ppnsId = $request->integer('ppns_id');
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();
        $preset = $request->string('preset')->trim()->toString();

        // Penerapan Shortcut Filter Chips
        if ($preset === 'berjalan') {
            $statusFormal = 'dalam_proses';
        } elseif ($preset === 'selesai') {
            $statusFormal = 'selesai';
        } elseif ($preset === 'tidak_selesai') {
            $statusFormal = 'tidak_selesai';
        } elseif ($preset === 'dpo') {
            $isDpo = '1';
        }

        // Query Utama Pencarian Berkas Perkara
        $query = BerkasPerkara::query()
            ->with([
                'perkara.dokumenSprindik',
                'perkara.dokumenLaporan',
                'perkara.ppnsUtama',
                'perkara.tkp.alamat',
                'tersangka',
                'pasals',
                'tahapan',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
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
                                ->orWhere('deskripsi', 'like', "%{$search}%")
                                ->orWhereHas('tkp', function (Builder $tkpQ) use ($search) {
                                    $tkpQ->where('klasifikasi', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHas('pasals', function (Builder $pasQ) use ($search) {
                            $pasQ->where('nama_pasal', 'like', "%{$search}%")
                                ->orWhere('pasal', 'like', "%{$search}%");
                        })
                        ->orWhereHas('perkara.ppnsUtama', function (Builder $ppnsQ) use ($search) {
                            $ppnsQ->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                        });
                });
            })
            ->when($statusProses !== '', function (Builder $query) use ($statusProses) {
                $query->where('status_proses', $statusProses);
            })
            ->when($statusFormal !== '', function (Builder $query) use ($statusFormal) {
                if ($statusFormal === 'dalam_proses') {
                    $query->where(function (Builder $q) {
                        $q->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                    });
                } elseif ($statusFormal === 'selesai') {
                    $query->where('status_formal', 'selesai');
                } elseif ($statusFormal === 'tidak_selesai') {
                    $query->where('status_formal', 'tidak_selesai');
                }
            })
            ->when($isDpo !== '', function (Builder $query) use ($isDpo) {
                if ($isDpo === '1') {
                    $query->where('is_dpo', true);
                } elseif ($isDpo === '0') {
                    $query->where('is_dpo', false);
                }
            })
            ->when($ppnsId > 0, function (Builder $query) use ($ppnsId) {
                $query->whereHas('perkara.ppnsUtama', function (Builder $q) use ($ppnsId) {
                    $q->where('user.id', $ppnsId);
                });
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->where(function (Builder $q) use ($tanggalDari) {
                    $q->whereDate('tanggal_spdp', '>=', $tanggalDari)
                        ->orWhereHas('perkara', fn (Builder $pQ) => $pQ->whereDate('tanggal_laporan_kejadian', '>=', $tanggalDari));
                });
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->where(function (Builder $q) use ($tanggalSampai) {
                    $q->whereDate('tanggal_spdp', '<=', $tanggalSampai)
                        ->orWhereHas('perkara', fn (Builder $pQ) => $pQ->whereDate('tanggal_laporan_kejadian', '<=', $tanggalSampai));
                });
            })
            ->orderByDesc('created_at');

        $berkasList = $query->paginate(10)->withQueryString();

        // Opsi Dropdown Filter
        $ppnsOptions = User::query()
            ->where('role', 'penyidik')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip']);

        $tahapOptions = array_map(fn (JenisTahap $t) => $t->value, JenisTahap::cases());

        if ($request->header('X-Filter-Partial')) {
            return view('pages.pencarian-perkara._table-partial', compact('berkasList'));
        }

        return view('pages.pencarian-perkara.index', compact(
            'berkasList',
            'ppnsOptions',
            'tahapOptions',
            'search',
            'statusProses',
            'statusFormal',
            'isDpo',
            'ppnsId',
            'tanggalDari',
            'tanggalSampai',
            'preset'
        ));
    }
}
