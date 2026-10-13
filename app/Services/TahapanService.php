<?php

namespace App\Services;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\TahapanPerkara;
use DomainException;

class TahapanService
{
    /**
     * Mendapatkan daftar jenis tahapan berikutnya yang valid untuk dicatat pada Berkas Perkara.
     * Mendukung tahapan wajib dan skipping tahapan opsional (PRD F-10 s.d. F-13).
     *
     * @return list<JenisTahap>
     */
    public function getNextAvailableStages(BerkasPerkara $berkas): array
    {
        $latest = $berkas->latestFinalTahap();

        if ($latest === null) {
            // Belum ada tahapan progress, tahap pembuka wajib SPDP
            return $berkas->hasTahap(JenisTahap::SPDP) ? [] : [JenisTahap::SPDP];
        }

        $candidates = match ($latest->jenis_tahap) {
            JenisTahap::SPDP => [
                JenisTahap::P16,
                JenisTahap::P17P,
                JenisTahap::TAHAP_1,
            ],
            JenisTahap::P16 => [
                JenisTahap::P17P,
                JenisTahap::TAHAP_1,
            ],
            JenisTahap::P17P => [
                JenisTahap::TAHAP_1,
            ],
            JenisTahap::TAHAP_1 => [
                JenisTahap::P18P,
                JenisTahap::P19,
                JenisTahap::P21,
            ],
            JenisTahap::P18P => [
                JenisTahap::P19,
                JenisTahap::P21,
            ],
            JenisTahap::P19 => [
                JenisTahap::P21,
            ],
            JenisTahap::P21 => [
                JenisTahap::TAHAP_2,
            ],
            JenisTahap::TAHAP_2 => [
                JenisTahap::KEPUTUSAN_PENGADILAN,
            ],
            JenisTahap::KEPUTUSAN_PENGADILAN => [],
        };

        // Filter: tahapan yang sudah pernah dicatat tidak boleh dipilih lagi (maksimal 1x per berkas perkara - PRD F-11)
        return array_values(array_filter(
            $candidates,
            fn (JenisTahap $tahap): bool => ! $berkas->hasTahap($tahap)
        ));
    }

    /**
     * Memeriksa apakah transisi ke tahapan target valid.
     */
    public function canProceedTo(BerkasPerkara $berkas, JenisTahap $targetTahap): bool
    {
        return in_array($targetTahap, $this->getNextAvailableStages($berkas), true);
    }

    /**
     * Memvalidasi aturan urutan dan maksimal 1x untuk tahapan berikutnya.
     * Melempar DomainException jika melanggar aturan PRD.
     *
     * @throws DomainException
     */
    public function validateStageOrder(BerkasPerkara $berkas, JenisTahap $targetTahap): void
    {
        if ($berkas->hasTahap($targetTahap)) {
            throw new DomainException("Tahapan {$targetTahap->label()} sudah pernah dicatat pada Berkas Perkara ini. Satu jenis tahapan maksimal 1 kali.");
        }

        $available = $this->getNextAvailableStages($berkas);

        if (! in_array($targetTahap, $available, true)) {
            $latest = $berkas->latestFinalTahap();
            $posisi = $latest !== null ? $latest->jenis_tahap->label() : 'Pra-SPDP';

            throw new DomainException("Tahapan {$targetTahap->label()} tidak valid untuk urutan progress saat ini (posisi terakhir: {$posisi}).");
        }
    }

    /**
     * Menghitung persentase progress berbobot (PRD F-30):
     * - Pra-SPDP: 0%
     * - SPDP: 15%
     * - TAHAP I: 55%
     * - P-21/P21a: 85%
     * - TAHAP II & Keputusan Pengadilan: 100%
     */
    public function calculateProgress(BerkasPerkara $berkas): int
    {
        $finalStages = $berkas->tahapan()
            ->where('status', 'final')
            ->pluck('jenis_tahap')
            ->all();

        $maxProgress = 0;
        foreach ($finalStages as $stage) {
            $enum = $stage instanceof JenisTahap ? $stage : JenisTahap::tryFrom((string) $stage);
            if ($enum !== null && $enum->bobot() > $maxProgress) {
                $maxProgress = $enum->bobot();
            }
        }

        return $maxProgress;
    }

    /**
     * Menentukan label status proses berdasarkan tahapan progress terakhir yang difinalkan (PRD F-29).
     */
    public function determineStatusProses(BerkasPerkara $berkas): string
    {
        $latest = $berkas->latestFinalTahap();

        return $latest !== null ? $latest->jenis_tahap->label() : 'Baru';
    }

    /**
     * Memvalidasi penguncian permanen: tahap yang sudah final tidak boleh diedit atau diubah statusnya (PRD F-14 s.d. F-19).
     *
     * @throws DomainException
     */
    public function assertStageNotLocked(TahapanPerkara $tahap): void
    {
        if ($tahap->isFinal()) {
            throw new DomainException("Tahapan {$tahap->jenis_tahap->label()} telah difinalkan dan terkunci permanen. Administrator pun tidak dapat mengedit atau memundurkan tahap yang telah dikunci.");
        }
    }
}
