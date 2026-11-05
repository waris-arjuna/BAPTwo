<?php

namespace App\Services\Queries;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\TahapanPerkara;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TahapanListQuery
{
    /**
     * Helper query terpadu untuk daftar tahapan perkara dengan filter & paginasi.
     *
     * @param  array<string>|string  $jenisTahap
     * @param  array<string, mixed>  $filters
     */
    public function paginateTahapanQuery(
        array|string $jenisTahap,
        array $filters = [],
        int $perPage = 10,
        ?string $subStage = null
    ): LengthAwarePaginator {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $tanggalDari = trim((string) ($filters['tanggal_dari'] ?? $filters['tanggal'] ?? ''));
        $tanggalSampai = trim((string) ($filters['tanggal_sampai'] ?? ''));
        $subStage = $subStage ?? ($filters['sub_stage'] ?? null);

        $query = TahapanPerkara::query();

        if (is_array($jenisTahap)) {
            $query->whereIn('jenis_tahap', $jenisTahap);
            if ($subStage !== null && $subStage !== 'all' && $subStage !== '') {
                $query->where('jenis_tahap', $subStage);
            }
        } else {
            $query->where('jenis_tahap', $jenisTahap);
        }

        return $query
            ->when($status !== '', fn ($q) => $q->where('status', strtolower($status)))
            ->when($tanggalDari !== '', fn ($q) => $q->whereDate('tanggal_surat', '>=', $tanggalDari))
            ->when($tanggalSampai !== '', fn ($q) => $q->whereDate('tanggal_surat', '<=', $tanggalSampai))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('berkasPerkara', function (Builder $bQ) use ($search) {
                            $bQ->where('nomor_berkas', 'like', "%{$search}%")
                                ->orWhereHas('tersangka', function (Builder $tQ) use ($search) {
                                    $tQ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nik', 'like', "%{$search}%");
                                })
                                ->orWhereHas('perkara', function (Builder $pQ) use ($search) {
                                    $pQ->where('nomor_surat_perintah', 'like', "%{$search}%")
                                        ->orWhere('nomor_laporan_kejadian', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->with([
                'berkasPerkara.perkara.penuntutUmumInstansi',
                'berkasPerkara.tersangka',
                'dokumen',
                'pengadilanInstansi',
                'createdBy',
                'finalizedBy',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Helper query berkas perkara yang siap lanjut ke tahapan berikutnya.
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function queryAvailableBerkas(
        JenisTahap $requiredPreviousStage,
        JenisTahap $targetStage,
        bool $dpoCheck = true
    ): Collection {
        return BerkasPerkara::query()
            ->whereHas('tahapan', function (Builder $q) use ($requiredPreviousStage) {
                $q->where('jenis_tahap', $requiredPreviousStage->value)->where('status', 'final');
            })
            ->whereDoesntHave('tahapan', function (Builder $q) use ($targetStage) {
                $q->where('jenis_tahap', $targetStage->value);
            })
            ->whereDoesntHave('tahapan', function (Builder $q) {
                $q->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN->value)->where('status', 'final');
            })
            ->whereDoesntHave('sp3')
            ->where(fn (Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->when($dpoCheck, fn ($q) => $q->where('is_dpo', false))
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Mengambil daftar tahapan SPDP (termasuk sub-tahap opsional P-16 dan P-17P) dengan filter dan paginasi.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getSpdpList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $subStage = trim((string) ($filters['sub_stage'] ?? 'all'));

        return $this->paginateTahapanQuery(
            [JenisTahap::SPDP->value, JenisTahap::P16->value, JenisTahap::P17P->value],
            $filters,
            $perPage,
            $subStage
        );
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai SPDP (pra-SPDP final & belum ada SPDP).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForSpdp(): Collection
    {
        return BerkasPerkara::query()
            ->whereHas('perkara', function (Builder $q) {
                $q->where('tahap_perkara_status', 'final');
            })
            ->whereDoesntHave('tahapan', function (Builder $q) {
                $q->where('jenis_tahap', JenisTahap::SPDP->value);
            })
            ->whereDoesntHave('sp3')
            ->where('is_dpo', false)
            ->where(fn (Builder $q) => $q->whereNull('status_formal')->orWhere('status_formal', '!=', 'tidak_selesai'))
            ->with(['perkara', 'tersangka'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Menghitung jumlah tahapan per sub-tahap untuk tab filter (Semua, SPDP, P-16, P-17P).
     *
     * @return array{all: int, spdp: int, p16: int, p17p: int}
     */
    public function getSpdpTabCounts(): array
    {
        $base = TahapanPerkara::query()->whereIn('jenis_tahap', [JenisTahap::SPDP->value, JenisTahap::P16->value, JenisTahap::P17P->value]);

        return [
            'all' => (clone $base)->count(),
            'spdp' => (clone $base)->where('jenis_tahap', JenisTahap::SPDP->value)->count(),
            'p16' => (clone $base)->where('jenis_tahap', JenisTahap::P16->value)->count(),
            'p17p' => (clone $base)->where('jenis_tahap', JenisTahap::P17P->value)->count(),
        ];
    }

    /**
     * Mengambil daftar tahapan Tahap 1 (termasuk P-18P dan P-19) dengan filter dan paginasi.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getTahap1List(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $subStage = trim((string) ($filters['sub_stage'] ?? 'all'));

        return $this->paginateTahapanQuery(
            [JenisTahap::TAHAP_1->value, JenisTahap::P18P->value, JenisTahap::P19->value],
            $filters,
            $perPage,
            $subStage
        );
    }

    /**
     * Menghitung tab counts untuk halaman Tahap 1 (Semua, Tahap 1, P-18P, P-19).
     *
     * @return array{all: int, tahap_1: int, p18p: int, p19: int}
     */
    public function getTahap1TabCounts(): array
    {
        $base = TahapanPerkara::query()->whereIn('jenis_tahap', [JenisTahap::TAHAP_1->value, JenisTahap::P18P->value, JenisTahap::P19->value]);

        return [
            'all' => (clone $base)->count(),
            'tahap_1' => (clone $base)->where('jenis_tahap', JenisTahap::TAHAP_1->value)->count(),
            'p18p' => (clone $base)->where('jenis_tahap', JenisTahap::P18P->value)->count(),
            'p19' => (clone $base)->where('jenis_tahap', JenisTahap::P19->value)->count(),
        ];
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai Tahap 1 (SPDP final & belum ada Tahap 1 final).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForTahap1(): Collection
    {
        return $this->queryAvailableBerkas(JenisTahap::SPDP, JenisTahap::TAHAP_1);
    }

    /**
     * Mengambil daftar tahapan P-21 dengan filter dan paginasi.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getP21List(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginateTahapanQuery(JenisTahap::P21->value, $filters, $perPage);
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai P-21 (Tahap 1 final & belum ada P-21 final).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForP21(): Collection
    {
        return $this->queryAvailableBerkas(JenisTahap::TAHAP_1, JenisTahap::P21);
    }

    /**
     * Mengambil daftar tahapan Tahap 2 dengan filter dan paginasi.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getTahap2List(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginateTahapanQuery(JenisTahap::TAHAP_2->value, $filters, $perPage);
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap memulai Tahap 2 (P-21 final & belum ada Tahap 2).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForTahap2(): Collection
    {
        return $this->queryAvailableBerkas(JenisTahap::P21, JenisTahap::TAHAP_2, true);
    }

    /**
     * Mengambil daftar Putusan Perkara (Keputusan Pengadilan) dengan filter dan paginasi.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPutusanPerkaraList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginateTahapanQuery(JenisTahap::KEPUTUSAN_PENGADILAN->value, $filters, $perPage);
    }

    /**
     * Mengambil daftar Berkas Perkara yang siap mencatat Putusan Pengadilan (Tahap 2 final & belum ada Putusan).
     *
     * @return Collection<int, BerkasPerkara>
     */
    public function getAvailableBerkasForPutusan(): Collection
    {
        return $this->queryAvailableBerkas(JenisTahap::TAHAP_2, JenisTahap::KEPUTUSAN_PENGADILAN);
    }
}
