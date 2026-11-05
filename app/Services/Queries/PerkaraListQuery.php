<?php

namespace App\Services\Queries;

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PerkaraListQuery
{
    /**
     * Daftar Laporan Kejadian dengan filter dan paginasi
     *
     * @param  array<string, mixed>  $filters
     */
    public function getLaporanKejadianList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $tanggalDari = trim((string) ($filters['tanggal_dari'] ?? $filters['tanggal'] ?? ''));
        $tanggalSampai = trim((string) ($filters['tanggal_sampai'] ?? ''));

        return Perkara::query()
            ->with(['dokumenLaporan', 'creator'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_laporan_kejadian', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('dokumenLaporan', function (Builder $docQ) use ($search) {
                            $docQ->where('nama_file_asli', 'like', "%{$search}%")
                                ->orWhere('nama_dokumen', 'like', "%{$search}%");
                        });

                    $lowerSearch = strtolower($search);
                    if (str_contains('draft', $lowerSearch)) {
                        $q->orWhere('tahap_laporan_status', 'draft');
                    }
                    if (str_contains('belum dilanjutkan', $lowerSearch)) {
                        $q->orWhere(function (Builder $sub) {
                            $sub->where('tahap_laporan_status', 'final')
                                ->whereNull('nomor_surat_perintah');
                        });
                    }
                    if (str_contains('sprindik tersedia', $lowerSearch)) {
                        $q->orWhereNotNull('nomor_surat_perintah');
                    }
                    if (str_contains('perkara terbentuk', $lowerSearch)) {
                        $q->orWhere('tahap_perkara_status', 'final');
                    }
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Draft') {
                    $query->where('tahap_laporan_status', 'draft');
                } elseif ($status === 'Belum Dilanjutkan') {
                    $query->where('tahap_laporan_status', 'final')
                        ->whereNull('nomor_surat_perintah');
                } elseif ($status === 'Sprindik Tersedia') {
                    $query->whereNotNull('nomor_surat_perintah')
                        ->where(function (Builder $q) {
                            $q->whereNull('tahap_perkara_status')
                                ->orWhere('tahap_perkara_status', '!=', 'final');
                        });
                } elseif ($status === 'Perkara Terbentuk') {
                    $query->where('tahap_perkara_status', 'final');
                }
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_laporan_kejadian', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_laporan_kejadian', '<=', $tanggalSampai);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Hitung ringkasan status Laporan Kejadian untuk cards
     *
     * @return array{total: int, draft: int, final: int, selesai: int}
     */
    public function getLaporanKejadianSummary(): array
    {
        return [
            'total' => Perkara::count(),
            'draft' => Perkara::where('tahap_laporan_status', 'draft')->count(),
            'final' => Perkara::where('tahap_laporan_status', 'final')
                ->whereNull('nomor_surat_perintah')
                ->count(),
            'selesai' => Perkara::whereNotNull('nomor_surat_perintah')->count(),
        ];
    }

    /**
     * Laporan Kejadian yang sudah final dan belum memiliki Sprindik untuk dropdown tambah Sprindik
     *
     * @return Collection<int, Perkara>
     */
    public function getAvailableForSprindik(): Collection
    {
        return Perkara::query()
            ->with('dokumenLaporan')
            ->where(function ($q) {
                $q->where('tahap_laporan_status', 'final')
                    ->orWhereNull('tahap_laporan_status');
            })
            ->where(function ($q) {
                $q->whereNull('nomor_surat_perintah')
                    ->orWhere('nomor_surat_perintah', '');
            })
            ->whereNull('tahap_sprindik_status')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Daftar Sprindik dengan filter dan paginasi
     *
     * @param  array<string, mixed>  $filters
     */
    public function getSprindikList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $tanggalDari = trim((string) ($filters['tanggal_dari'] ?? $filters['tanggal'] ?? ''));
        $tanggalSampai = trim((string) ($filters['tanggal_sampai'] ?? ''));

        return Perkara::query()
            ->where(function (Builder $q) {
                $q->whereNotNull('nomor_surat_perintah')
                    ->orWhereNotNull('tahap_sprindik_status');
            })
            ->with(['dokumenSprindik', 'dokumenLaporan', 'creator', 'berkasPerkaras'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_surat_perintah', 'like', "%{$search}%")
                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('dokumenSprindik', function (Builder $docQ) use ($search) {
                            $docQ->where('nama_file_asli', 'like', "%{$search}%")
                                ->orWhere('nama_dokumen', 'like', "%{$search}%");
                        });

                    $lowerSearch = strtolower($search);
                    if (str_contains('draft', $lowerSearch)) {
                        $q->orWhere('tahap_sprindik_status', 'draft')
                            ->orWhere('tahap_perkara_status', 'draft');
                    }
                    if (str_contains('belum menjadi perkara', $lowerSearch)) {
                        $q->orWhere(function (Builder $sub) {
                            $sub->where('tahap_sprindik_status', 'final')
                                ->whereNull('tahap_perkara_status');
                        });
                    }
                    if (str_contains('perkara terbentuk', $lowerSearch)) {
                        $q->orWhere('tahap_perkara_status', 'final');
                    }
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Draft') {
                    $query->where('tahap_sprindik_status', 'draft');
                } elseif ($status === 'Draft Perkara') {
                    $query->where('tahap_perkara_status', 'draft');
                } elseif ($status === 'Belum Menjadi Perkara') {
                    $query->where('tahap_sprindik_status', 'final')
                        ->whereNull('tahap_perkara_status');
                } elseif ($status === 'Perkara Terbentuk') {
                    $query->where('tahap_perkara_status', 'final');
                }
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat_perintah', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat_perintah', '<=', $tanggalSampai);
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Hitung ringkasan status Sprindik untuk cards
     *
     * @return array{total: int, draft: int, final: int, selesai: int}
     */
    public function getSprindikSummary(): array
    {
        return [
            'total' => Perkara::whereNotNull('nomor_surat_perintah')->count(),
            'draft' => Perkara::where('tahap_sprindik_status', 'draft')->count(),
            'final' => Perkara::where('tahap_sprindik_status', 'final')
                ->where(function (Builder $q) {
                    $q->whereNull('tahap_perkara_status')
                        ->orWhere('tahap_perkara_status', '!=', 'final');
                })
                ->count(),
            'selesai' => Perkara::where('tahap_perkara_status', 'final')->count(),
        ];
    }

    /**
     * Sprindik yang sudah final dan belum memiliki data Perkara lengkap untuk dropdown tambah Perkara
     *
     * @return Collection<int, Perkara>
     */
    public function getAvailableForPerkara(): Collection
    {
        return Perkara::query()
            ->with(['dokumenSprindik', 'dokumenLaporan'])
            ->where('tahap_sprindik_status', 'final')
            ->whereNull('tahap_perkara_status')
            ->whereDoesntHave('tersangkas')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Hitung ringkasan 5 card informasi di halaman Data Perkara
     *
     * @return array{total: int, dalam_proses: int, dpo_aktif: int, selesai: int, tidak_selesai: int}
     */
    public function getDataPerkaraSummaryCards(): array
    {
        return [
            'total' => Perkara::whereNotNull('tahap_perkara_status')->count(),
            'dalam_proses' => Perkara::whereNotNull('tahap_perkara_status')->filterStatusFormal('dalam_proses')->count(),
            'dpo_aktif' => Perkara::whereNotNull('tahap_perkara_status')->filterStatusFormal('dpo_aktif')->count(),
            'selesai' => Perkara::whereNotNull('tahap_perkara_status')->filterStatusFormal('selesai')->count(),
            'tidak_selesai' => Perkara::whereNotNull('tahap_perkara_status')->filterStatusFormal('tidak_selesai')->count(),
        ];
    }

    /**
     * Hitung 4 kartu metrik kesiapan pelimpahan
     *
     * @return array{siap_tahap_1: int, siap_tahap_2: int, terkendala_dpo: int, melebihi_sla: int}
     */
    public function getPotensiTerlimpahCards(): array
    {
        $today = Carbon::today(DashboardService::ZONA_WAKTU)->toDateString();
        $basePerkaraQuery = Perkara::query()->whereNotNull('tahap_perkara_status');

        return [
            'siap_tahap_1' => (clone $basePerkaraQuery)->whereHas('berkasPerkaras', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                })->where('progress_persen', 15)->where('is_dpo', false);
            })->count(),

            'siap_tahap_2' => (clone $basePerkaraQuery)->whereHas('berkasPerkaras', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                })->where('progress_persen', 85)->where('is_dpo', false);
            })->count(),

            'terkendala_dpo' => (clone $basePerkaraQuery)->whereHas('berkasPerkaras', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                })->where('is_dpo', true);
            })->count(),

            'melebihi_sla' => (clone $basePerkaraQuery)->whereHas('berkasPerkaras', function ($q) use ($today) {
                $q->where(function ($sub) {
                    $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                })->whereNotNull('sla_deadline')->where('sla_deadline', '<', $today);
            })->count(),
        ];
    }

    /**
     * Ambil daftar PPNS Utama yang memiliki perkara untuk filter dropdown (urut abjad)
     *
     * @return Collection<int, User>
     */
    public function getAvailablePpnsForFilter(): Collection
    {
        return User::query()
            ->whereHas('perkaraUtama')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip']);
    }

    /**
     * Ambil daftar Perkara untuk halaman Data Perkara
     *
     * @param  array<string, mixed>  $filters
     */
    public function getDataPerkaraList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $tahap = trim((string) ($filters['tahap'] ?? ''));
        $kesiapan = trim((string) ($filters['kesiapan'] ?? ''));
        $ppnsId = ! empty($filters['ppns_id']) ? (int) $filters['ppns_id'] : 0;
        $sla = trim((string) ($filters['sla'] ?? ''));
        $tahun = ! empty($filters['tahun']) ? (int) $filters['tahun'] : 0;
        $bulan = ! empty($filters['bulan']) ? (int) $filters['bulan'] : 0;
        $tanggalDari = trim((string) ($filters['tanggal_dari'] ?? $filters['tanggal'] ?? ''));
        $tanggalSampai = trim((string) ($filters['tanggal_sampai'] ?? ''));

        return Perkara::query()
            ->whereNotNull('tahap_perkara_status')
            ->with([
                'dokumenSprindik',
                'dokumenLaporan',
                'tkp.alamat',
                'ppnsUtama',
                'penuntutUmumInstansi',
                'tersangkas.berkasPerkara.pasals',
                'tersangkas.berkasPerkara.sp3',
                'berkasPerkaras.pasals',
                'berkasPerkaras.sp3',
                'berkasPerkaras.tahapan',
                'creator',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_surat_perintah', 'like', "%{$search}%")
                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('tkp', function (Builder $tkpQ) use ($search) {
                            $tkpQ->where('klasifikasi', 'like', "%{$search}%")
                                ->orWhere('legalitas', 'like', "%{$search}%")
                                ->orWhere('modus', 'like', "%{$search}%")
                                ->orWhereHas('alamat', function (Builder $almQ) use ($search) {
                                    $almQ->where('kabupaten_nama', 'like', "%{$search}%")
                                        ->orWhere('kecamatan_nama', 'like', "%{$search}%")
                                        ->orWhere('desa_nama', 'like', "%{$search}%")
                                        ->orWhere('alamat_detail', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHas('ppnsUtama', function (Builder $userQ) use ($search) {
                            $userQ->where('nama', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tersangkas', function (Builder $tskQ) use ($search) {
                            $tskQ->where('nama', 'like', "%{$search}%")
                                ->orWhere('alias', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%")
                                ->orWhereHas('berkasPerkara', function (Builder $bpQ) use ($search) {
                                    $bpQ->where('nomor_berkas', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($ppnsId > 0 && auth()->user()?->isAdmin(), function (Builder $query) use ($ppnsId) {
                $query->whereHas('ppnsUtama', function (Builder $q) use ($ppnsId) {
                    $q->where('user.id', $ppnsId);
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'Draft' || $status === 'Draft Perkara') {
                    $query->where('tahap_perkara_status', 'draft');
                } elseif ($status === 'Perkara Terbentuk') {
                    $query->where('tahap_perkara_status', 'final');
                } else {
                    $query->filterStatusFormal($status);
                }
            })
            ->when($tahap !== '', function (Builder $query) use ($tahap) {
                $query->filterTahapTerakhir($tahap);
            })
            ->when($kesiapan !== '', function (Builder $query) use ($kesiapan) {
                $today = Carbon::today(DashboardService::ZONA_WAKTU)->toDateString();

                if ($kesiapan === 'siap_tahap_1') {
                    $query->whereHas('berkasPerkaras', function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                        })->where('progress_persen', 15)->where('is_dpo', false);
                    });
                } elseif ($kesiapan === 'siap_tahap_2') {
                    $query->whereHas('berkasPerkaras', function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                        })->where('progress_persen', 85)->where('is_dpo', false);
                    });
                } elseif ($kesiapan === 'terkendala_dpo') {
                    $query->whereHas('berkasPerkaras', function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                        })->where('is_dpo', true);
                    });
                } elseif ($kesiapan === 'melebihi_sla') {
                    $query->whereHas('berkasPerkaras', function ($q) use ($today) {
                        $q->where(function ($sub) {
                            $sub->whereNull('status_formal')->orWhere('status_formal', 'dalam_proses');
                        })->whereNotNull('sla_deadline')->where('sla_deadline', '<', $today);
                    });
                }
            })
            ->when($tahun > 0, function (Builder $query) use ($tahun) {
                $query->whereYear('tanggal_laporan_kejadian', $tahun);
            })
            ->when($bulan > 0, function (Builder $query) use ($bulan) {
                $query->whereMonth('tanggal_laporan_kejadian', $bulan);
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
                    } elseif ($sla === 'dalam_sla' || $sla === 'sesuai_sla') {
                        $q->whereNotNull('sla_deadline')->where('sla_deadline', '>', $approachingDate);
                    } elseif ($sla === 'mendekati_sla') {
                        $q->whereNotNull('sla_deadline')->where('sla_deadline', '>=', $today)->where('sla_deadline', '<=', $approachingDate);
                    } elseif ($sla === 'lewat_sla') {
                        $q->whereNotNull('sla_deadline')->where('sla_deadline', '<', $today);
                    }
                });
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_surat_perintah', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_surat_perintah', '<=', $tanggalSampai);
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ambil daftar Berkas Perkara untuk monitoring Progress Perkara
     *
     * @param  array<string, mixed>  $filters
     */
    public function getProgressPerkaraList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $tanggalDari = trim((string) ($filters['tanggal_dari'] ?? $filters['tanggal'] ?? ''));
        $tanggalSampai = trim((string) ($filters['tanggal_sampai'] ?? ''));

        return BerkasPerkara::query()
            ->with([
                'perkara.dokumenSprindik',
                'perkara.ppnsUtama',
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
                                ->orWhere('alias', 'like', "%{$search}%");
                        })
                        ->orWhereHas('perkara', function (Builder $perkQ) use ($search) {
                            $perkQ->where('nomor_surat_perintah', 'like', "%{$search}%");
                        })
                        ->orWhereHas('pasals', function (Builder $pasQ) use ($search) {
                            $pasQ->where('nama_pasal', 'like', "%{$search}%")
                                ->orWhere('pasal', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                $st = strtolower(str_replace(' ', '_', $status));
                if ($st === 'dalam_proses') {
                    $query->dalamProses();
                } elseif ($st === 'selesai') {
                    $query->selesai();
                } elseif ($st === 'tidak_selesai') {
                    $query->tidakSelesai();
                } elseif ($st === 'dpo') {
                    $query->where('is_dpo', true);
                }
            })
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                $query->whereDate('tanggal_spdp', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                $query->whereDate('tanggal_spdp', '<=', $tanggalSampai);
            })
            ->orderBy('progress_persen')
            ->orderBy('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
