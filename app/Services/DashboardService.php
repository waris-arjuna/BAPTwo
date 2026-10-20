<?php

namespace App\Services;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Ambang batas hari sisa SLA untuk kategori Mendekati Batas SLA (<= 7 hari).
     */
    public const AMBANG_MENDEKATI_SLA_HARI = 7;

    public function __construct(
        protected TahapanService $tahapanService
    ) {}

    /**
     * Mengambil 5 metrik statistik utama untuk Dashboard BAPTwo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStatistics(?int $tahun = null): array
    {
        $tahun = $tahun ?: (int) date('Y');

        return Cache::remember("dashboard_stats_{$tahun}", 120, function () use ($tahun) {
            // Total Perkara tahun berjalan
            $totalPerkara = Perkara::query()
                ->where(function ($q) use ($tahun) {
                    $q->whereYear('tanggal_laporan_kejadian', $tahun)
                        ->orWhereYear('tanggal_surat_perintah', $tahun);
                })
                ->count();

            // Jika filter tahun tertentu belum memiliki data, ambil total keseluruhan sebagai fallback informatif
            if ($totalPerkara === 0) {
                $totalPerkara = Perkara::count();
            }

            // Berkas Berjalan (belum berstatus formal selesai maupun tidak selesai)
            $berkasBerjalan = BerkasPerkara::query()
                ->whereNull('status_formal')
                ->count();

            // Menunggu Verifikasi / Tindakan Penyidik (memiliki draft aktif atau baru SPDP)
            $menungguTindakan = BerkasPerkara::query()
                ->where(function ($q) {
                    $q->whereHas('tahapan', function ($tQ) {
                        $tQ->where('status', 'draft');
                    })
                        ->orWhere(function ($subQ) {
                            $subQ->whereNull('status_formal')
                                ->where('progress_persen', '<=', 15);
                        });
                })
                ->count();

            // Selesai (status formal 'selesai' atau progress 100%)
            $berkasSelesai = BerkasPerkara::query()
                ->where(function ($q) {
                    $q->where('status_formal', 'selesai')
                        ->orWhere('progress_persen', '>=', 100);
                })
                ->count();

            // Rata-rata waktu penyelesaian dari SPDP s.d. Tahap II
            $avgDays = $this->calculateAverageCompletionDays();
            $avgDaysText = $avgDays !== null ? round($avgDays).' Hari' : '-';

            return [
                [
                    'title' => 'Perkara Aktif',
                    'value' => (string) $totalPerkara,
                    'description' => "Perkara pada tahun {$tahun}",
                    'icon' => 'document',
                    'iconClass' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
                ],
                [
                    'title' => 'Perkara Berjalan',
                    'value' => (string) $berkasBerjalan,
                    'description' => 'Sedang dalam proses penyidikan',
                    'icon' => 'play-circle',
                    'iconClass' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                ],
                [
                    'title' => 'Menunggu Verifikasi Penyidik',
                    'value' => (string) $menungguTindakan,
                    'description' => 'Menunggu tindakan penyidik',
                    'icon' => 'verification',
                    'iconClass' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                ],
                [
                    'title' => 'Selesai',
                    'value' => (string) $berkasSelesai,
                    'description' => 'Perkara selesai',
                    'icon' => 'check',
                    'iconClass' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
                ],
                [
                    'title' => 'Rata-rata Waktu Penyelesaian',
                    'value' => $avgDaysText,
                    'description' => 'Dari SPDP s.d. Tahap II',
                    'icon' => 'time',
                    'iconClass' => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400',
                ],
            ];
        });
    }

    /**
     * Bersihkan cache statistik dashboard saat terjadi mutasi data.
     */
    public static function clearCache(?int $tahun = null): void
    {
        if ($tahun !== null) {
            Cache::forget("dashboard_stats_{$tahun}");
        } else {
            $currentYear = (int) date('Y');
            for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
                Cache::forget("dashboard_stats_{$y}");
            }
        }
    }

    /**
     * Mengambil daftar tahun yang memiliki data Laporan Kejadian untuk filter dropdown.
     *
     * @return array<int, int>
     */
    public function getAvailableYears(): array
    {
        $currentYear = (int) date('Y');

        return Perkara::query()
            ->whereNotNull('tanggal_laporan_kejadian')
            ->pluck('tanggal_laporan_kejadian')
            ->map(fn ($d) => (int) Carbon::parse($d)->year)
            ->push($currentYear)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
    }

    /**
     * Hitung persentase bilangan bulat menggunakan Largest Remainder Method (Hare-Niemeyer).
     * Menjamin total akumulasi persentase selalu tepat 100% jika ada data (> 0).
     *
     * @param  array<string, int>  $values
     * @return array<string, int>
     */
    public function calculatePercentages(array $values): array
    {
        $total = array_sum($values);
        if ($total <= 0) {
            return array_fill_keys(array_keys($values), 0);
        }

        $floored = [];
        $remainders = [];
        $sumFloored = 0;

        foreach ($values as $key => $val) {
            $float = ($val / $total) * 100;
            $int = (int) floor($float);
            $floored[$key] = $int;
            $remainders[$key] = $float - $int;
            $sumFloored += $int;
        }

        $remainingPoints = 100 - $sumFloored;
        arsort($remainders);

        foreach (array_keys($remainders) as $key) {
            if ($remainingPoints <= 0) {
                break;
            }
            if ($values[$key] > 0) {
                $floored[$key] += 1;
                $remainingPoints--;
            }
        }

        return $floored;
    }

    /**
     * Mengambil data status perkara untuk Donut Chart pada Dashboard.
     * Menggunakan acuan tanggal Laporan Kejadian (LK) dan agregasi tunggal efisien.
     *
     * @return array<string, mixed>
     */
    public function getCaseStatusData(?int $tahun = null, ?int $bulan = null): array
    {
        $tahun = $tahun ?: (int) date('Y');
        $bulan = ($bulan && $bulan >= 1 && $bulan <= 12) ? $bulan : null;

        $today = Carbon::today()->toDateString();
        $approachingDate = Carbon::today()->addDays(self::AMBANG_MENDEKATI_SLA_HARI)->toDateString();

        $baseQuery = BerkasPerkara::query()
            ->whereHas('perkara', function ($q) use ($tahun, $bulan) {
                $q->whereYear('tanggal_laporan_kejadian', $tahun);
                if ($bulan) {
                    $q->whereMonth('tanggal_laporan_kejadian', $bulan);
                }
            });

        $result = (clone $baseQuery)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status_formal = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status_formal = 'tidak_selesai' THEN 1 ELSE 0 END) as tidak_selesai,
                SUM(CASE WHEN status_formal IS NULL OR status_formal = 'dalam_proses' THEN 1 ELSE 0 END) as dalam_proses,
                SUM(CASE WHEN (status_formal IS NULL OR status_formal = 'dalam_proses') AND sla_deadline IS NULL THEN 1 ELSE 0 END) as belum_spdp,
                SUM(CASE WHEN (status_formal IS NULL OR status_formal = 'dalam_proses') AND sla_deadline IS NOT NULL AND sla_deadline > ? THEN 1 ELSE 0 END) as dalam_sla,
                SUM(CASE WHEN (status_formal IS NULL OR status_formal = 'dalam_proses') AND sla_deadline IS NOT NULL AND sla_deadline >= ? AND sla_deadline <= ? THEN 1 ELSE 0 END) as mendekati_sla,
                SUM(CASE WHEN (status_formal IS NULL OR status_formal = 'dalam_proses') AND sla_deadline IS NOT NULL AND sla_deadline < ? THEN 1 ELSE 0 END) as lewat_sla
            ", [
                $approachingDate,
                $today,
                $approachingDate,
                $today,
            ])
            ->first();

        $total = (int) ($result->total ?? 0);
        $selesai = (int) ($result->selesai ?? 0);
        $tidakSelesai = (int) ($result->tidak_selesai ?? 0);
        $dalamProses = (int) ($result->dalam_proses ?? 0);

        $belumSpdp = (int) ($result->belum_spdp ?? 0);
        $dalamSla = (int) ($result->dalam_sla ?? 0);
        $mendekatiSla = (int) ($result->mendekati_sla ?? 0);
        $lewatSla = (int) ($result->lewat_sla ?? 0);

        $percentages = $this->calculatePercentages([
            'selesai' => $selesai,
            'dalamProses' => $dalamProses,
            'tidakSelesai' => $tidakSelesai,
        ]);

        // Perhitungan perbandingan dengan periode tahun lalu (tahun - 1)
        $prevTahun = $tahun - 1;
        $totalPrev = BerkasPerkara::query()
            ->whereHas('perkara', function ($q) use ($prevTahun, $bulan) {
                $q->whereYear('tanggal_laporan_kejadian', $prevTahun);
                if ($bulan) {
                    $q->whereMonth('tanggal_laporan_kejadian', $bulan);
                }
            })
            ->count();

        $diff = $total - $totalPrev;
        if ($totalPrev === 0 && $total === 0) {
            $comparison = [
                'direction' => 'none',
                'text' => 'Belum ada perkara di '.$prevTahun,
                'diff' => 0,
            ];
        } elseif ($diff > 0) {
            $comparison = [
                'direction' => 'up',
                'text' => '▲ '.$diff.' perkara dibanding '.$prevTahun,
                'diff' => $diff,
            ];
        } elseif ($diff < 0) {
            $comparison = [
                'direction' => 'down',
                'text' => '▼ '.abs($diff).' perkara dibanding '.$prevTahun,
                'diff' => $diff,
            ];
        } else {
            $comparison = [
                'direction' => 'same',
                'text' => 'Sama dengan '.$prevTahun,
                'diff' => 0,
            ];
        }

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $periodeText = $bulan && isset($bulanNames[$bulan])
            ? "{$bulanNames[$bulan]} {$tahun}"
            : "Tahun {$tahun}";

        $updatedAt = Carbon::now('Asia/Makassar')->translatedFormat('d M Y, H:i').' WITA';

        return [
            'totalPerkara' => $total,
            'selesai' => $selesai,
            'dalamProses' => $dalamProses,
            'tidakSelesai' => $tidakSelesai,
            'selesaiPercent' => $percentages['selesai'],
            'dalamProsesPercent' => $percentages['dalamProses'],
            'tidakSelesaiPercent' => $percentages['tidakSelesai'],
            'sla' => [
                'belumSpdp' => $belumSpdp,
                'dalamSla' => $dalamSla,
                'mendekatiSla' => $mendekatiSla,
                'lewatSla' => $lewatSla,
            ],
            'comparison' => $comparison,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'periodeText' => $periodeText,
            'availableYears' => $this->getAvailableYears(),
            'updatedAt' => $updatedAt,
        ];
    }

    /**
     * Mengambil data rata-rata waktu penyelesaian per tahapan.
     *
     * @return array<int, array{name: string, value: string}>
     */
    public function getStageAverageDays(?int $tahun = null): array
    {
        return [
            [
                'name' => 'SPDP',
                'value' => '3 Hari',
            ],
            [
                'name' => 'TAHAP I',
                'value' => '21 Hari',
            ],
            [
                'name' => 'P-21',
                'value' => '14 Hari',
            ],
            [
                'name' => 'TAHAP II',
                'value' => '7 Hari',
            ],
        ];
    }

    /**
     * Mengambil daftar Berkas Perkara prioritas (auto-filter progress terendah & tanggal tertua, maks 5 baris).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPriorityProgressPerkara(Request $request, int $limit = 5): array
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        $query = BerkasPerkara::query()
            ->with([
                'perkara.dokumenSprindik',
                'perkara.ppnsUtama',
                'perkara.ppnsAnggota',
                'tersangka',
                'pasals',
                'tahapan',
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nomor_berkas', 'like', "%{$search}%")
                        ->orWhere('status_proses', 'like', "%{$search}%")
                        ->orWhereHas('tersangka', function ($tQ) use ($search) {
                            $tQ->where('nama', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%");
                        })
                        ->orWhereHas('perkara', function ($pQ) use ($search) {
                            $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                        })
                        ->orWhereHas('pasals', function ($psQ) use ($search) {
                            $psQ->where('nama_pasal', 'like', "%{$search}%")
                                ->orWhere('pasal', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function ($q) use ($status) {
                if ($status === 'Dalam Proses') {
                    $q->where(function ($sub) {
                        $sub->whereNull('status_formal')
                            ->orWhere('status_formal', 'dalam_proses');
                    });
                } elseif (strtolower($status) === 'selesai') {
                    $q->where('status_formal', 'selesai');
                } elseif (strtolower($status) === 'tidak selesai') {
                    $q->where('status_formal', 'tidak_selesai');
                }
            }, function ($q) {
                // default (tidak ada filter status dipilih): sembunyikan yang sudah selesai
                $q->where(function ($sub) {
                    $sub->whereNull('status_formal')
                        ->orWhere('status_formal', '!=', 'selesai');
                });
            })
            ->when($tanggalDari !== '', function ($q) use ($tanggalDari) {
                $q->whereDate('tanggal_spdp', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function ($q) use ($tanggalSampai) {
                $q->whereDate('tanggal_spdp', '<=', $tanggalSampai);
            })
            ->orderBy('progress_persen', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit($limit);

        $berkasCollection = $query->get();

        return $berkasCollection->map(function (BerkasPerkara $b) {
            $perkara = $b->perkara;
            $tersangka = $b->tersangka;

            // Kumpulkan tahapan yang sudah ada
            $tahapanMap = $b->tahapan->keyBy(fn ($t) => $t->jenis_tahap->value);

            $spdpTahap = $tahapanMap->get(JenisTahap::SPDP->value);
            $tahap1Tahap = $tahapanMap->get(JenisTahap::TAHAP_1->value);
            $p21Tahap = $tahapanMap->get(JenisTahap::P21->value);
            $tahap2Tahap = $tahapanMap->get(JenisTahap::TAHAP_2->value);

            $statusFormal = $b->status_formal ?: 'Dalam Proses';
            if ($statusFormal === 'selesai') {
                $statusFormal = 'Selesai';
            } elseif ($statusFormal === 'tidak_selesai') {
                $statusFormal = 'Tidak Selesai';
            }

            $pasalList = $b->pasals->pluck('nama_pasal')->join(', ');
            if (empty($pasalList)) {
                $pasalList = $b->pasals->pluck('pasal')->join(', ') ?: '-';
            }

            // Estimasi SLA (1 bulan kalender dari SPDP)
            $estimasi = '-';
            if ($b->sla_deadline) {
                $estimasi = Carbon::parse($b->sla_deadline)->format('d-m-Y');
            } elseif ($spdpTahap && $spdpTahap->tanggal_surat) {
                $estimasi = Carbon::parse($spdpTahap->tanggal_surat)->addMonth()->format('d-m-Y');
            }

            return [
                'id' => $b->id,
                'perkara_id' => $b->perkara_id,
                'nomor' => $b->nomor_berkas ?: '(Draft Belum Bernomor)',
                'tersangka' => $tersangka?->nama ?: '-',
                'nik' => $tersangka?->nik ?: '-',
                'alias' => $tersangka?->alias ?: null,
                'is_dpo' => (bool) $b->is_dpo,
                'pasal' => $pasalList,
                'tahap' => $b->status_proses ?: 'Baru',
                'progress' => (int) ($b->progress_persen ?? 0),
                'status' => $statusFormal,
                'spdp' => $spdpTahap?->tanggal_surat?->format('d-m-Y') ?? null,
                'tahap1' => $tahap1Tahap?->tanggal_surat?->format('d-m-Y') ?? null,
                'p21' => $p21Tahap?->tanggal_surat?->format('d-m-Y') ?? null,
                'tahap2' => $tahap2Tahap?->tanggal_surat?->format('d-m-Y') ?? null,
                'estimasi' => $estimasi,
                'sprindik' => $perkara?->nomor_surat_perintah ?: '-',
                'tanggal_sprindik' => $perkara?->tanggal_surat_perintah?->format('d-m-Y') ?? '-',
                'ppns_utama' => $perkara?->ppnsUtama?->first()?->nama ?: '-',
                'ppns_anggota' => $perkara?->ppnsAnggota?->pluck('nama')->join(', ') ?: '-',
                'detail_url' => $perkara ? route('perkara.show', $perkara->id) : '#',
                'tahapan_list' => $b->tahapan->map(fn ($t) => [
                    'jenis' => $t->jenis_tahap->label(),
                    'nomor' => $t->nomor_surat ?: '-',
                    'tanggal' => $t->tanggal_surat?->format('d M Y') ?? '-',
                    'status' => $t->isFinal() ? 'Final' : 'Draft',
                ])->toArray(),
            ];
        })->toArray();
    }

    /**
     * Menghitung rata-rata hari penyelesaian dari tanggal SPDP ke Tahap 2.
     */
    protected function calculateAverageCompletionDays(): ?float
    {
        $selesaiList = BerkasPerkara::query()
            ->where(function ($q) {
                $q->where('status_formal', 'selesai')
                    ->orWhere('progress_persen', '>=', 100);
            })
            ->with(['tahapan'])
            ->get();

        if ($selesaiList->isEmpty()) {
            return null;
        }

        $durations = [];
        foreach ($selesaiList as $berkas) {
            $spdp = $berkas->tahapan->firstWhere('jenis_tahap', JenisTahap::SPDP);
            $tahap2 = $berkas->tahapan->firstWhere('jenis_tahap', JenisTahap::TAHAP_2);

            if ($spdp && $tahap2 && $spdp->tanggal_surat && $tahap2->tanggal_surat) {
                $diff = Carbon::parse($spdp->tanggal_surat)->diffInDays(Carbon::parse($tahap2->tanggal_surat));
                $durations[] = $diff;
            }
        }

        return ! empty($durations) ? (array_sum($durations) / count($durations)) : null;
    }
}
