<?php

namespace App\Services\Queries;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;

class PriorityProgressQuery
{
    /**
     * Ambil data berkas perkara untuk tabel prioritas progres di Dashboard
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function get(array $filters = [], int $limit = 5): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $tanggalDari = trim((string) ($filters['tanggal_dari'] ?? $filters['tanggal'] ?? ''));
        $tanggalSampai = trim((string) ($filters['tanggal_sampai'] ?? ''));

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
                if (strtolower($status) === 'dalam proses') {
                    $q->dalamProses();
                } elseif (strtolower($status) === 'selesai') {
                    $q->selesai();
                } elseif (strtolower($status) === 'tidak selesai') {
                    $q->tidakSelesai();
                }
            }, function ($q) {
                // default (tidak ada filter status dipilih): hanya tampilkan berkas dalam proses (sembunyikan Selesai DAN Tidak Selesai)
                $q->dalamProses();
            })
            ->when($tanggalDari !== '', function ($q) use ($tanggalDari) {
                $q->whereDate('tanggal_spdp', '>=', $tanggalDari);
            })
            ->when($tanggalSampai !== '', function ($q) use ($tanggalSampai) {
                $q->whereDate('tanggal_spdp', '<=', $tanggalSampai);
            })
            ->orderByRaw('sla_deadline IS NULL')
            ->orderBy('sla_deadline', 'asc')
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

            // Estimasi Tenggang Waktu (SLA)
            $estimasi = $b->effective_deadline ? $b->effective_deadline->format('d-m-Y') : '-';

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
                'tahapan_list' => $b->ordered_tahapan->map(fn ($t) => [
                    'jenis' => $t->jenis_tahap->label(),
                    'nomor' => $t->nomor_surat ?: '-',
                    'tanggal' => $t->tanggal_surat?->format('d M Y') ?? '-',
                    'status' => $t->isFinal() ? 'Final' : 'Draft',
                ])->toArray(),
            ];
        })->toArray();
    }
}
