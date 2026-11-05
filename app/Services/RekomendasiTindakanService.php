<?php

namespace App\Services;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;

class RekomendasiTindakanService
{
    /**
     * Menghasilkan rekomendasi tindakan petugas untuk satu Perkara.
     *
     * @return array{label: string, hari_label: ?string, tooltip: string, tone: 'error'|'warning'|'info'|'netral', badge_class: string, icon: string}
     */
    public function untukPerkara(Perkara $perkara): array
    {
        if (! $perkara->isPerkaraLocked()) {
            return [
                'label' => __('Finalkan data perkara'),
                'hari_label' => null,
                'tooltip' => __('Data perkara (TKP, PPNS, tersangka, pasal) masih draft. Finalkan agar berkas bisa masuk SPDP.'),
                'tone' => 'netral',
                'badge_class' => $this->badgeClass('netral'),
                'icon' => 'fi fi-rr-edit',
            ];
        }

        $berkases = $perkara->relationLoaded('berkasPerkaras')
            ? $perkara->berkasPerkaras
            : $perkara->berkasPerkaras()->get();

        if ($berkases->isEmpty()) {
            return [
                'label' => __('Kirim SPDP ke Kejaksaan'),
                'hari_label' => null,
                'tooltip' => __('Perkara sudah terbentuk. Catat SPDP setelah dikirim ke Kejaksaan.'),
                'tone' => 'netral',
                'badge_class' => $this->badgeClass('netral'),
                'icon' => 'fi fi-rr-envelope',
            ];
        }

        // Urutkan berkas paling mendesak:
        // [terminal ? 1 : 0, sisa_hari_sla ?? PHP_INT_MAX]
        $rekomendasiList = [];
        foreach ($berkases as $berkas) {
            $rec = $this->untukBerkas($berkas);
            $isTerminal = $berkas->hasSp3()
                || $berkas->isTidakSelesai()
                || $berkas->status_formal === 'selesai';
            $sisa = $berkas->sisa_hari_sla ?? PHP_INT_MAX;

            $rekomendasiList[] = [
                'terminal_weight' => $isTerminal ? 1 : 0,
                'sisa' => $sisa,
                'data' => $rec,
            ];
        }

        usort($rekomendasiList, function ($a, $b) {
            if ($a['terminal_weight'] !== $b['terminal_weight']) {
                return $a['terminal_weight'] <=> $b['terminal_weight'];
            }

            return $a['sisa'] <=> $b['sisa'];
        });

        return $rekomendasiList[0]['data'];
    }

    /**
     * Menghasilkan rekomendasi tindakan petugas untuk satu BerkasPerkara.
     *
     * @return array{label: string, hari_label: ?string, tooltip: string, tone: 'error'|'warning'|'info'|'netral', badge_class: string, icon: string}
     */
    public function untukBerkas(BerkasPerkara $berkas): array
    {
        $sisa = $berkas->sisa_hari_sla;
        $deadline = $berkas->effective_deadline;
        $tanggalLabel = $deadline ? $deadline->translatedFormat('j M Y') : null;

        // 1. SP3 sudah diterbitkan
        if ($berkas->hasSp3()) {
            return [
                'label' => __('Penyidikan dihentikan (SP3)'),
                'hari_label' => null,
                'tooltip' => __('SP3 sudah diterbitkan. Tahapan terkunci, tidak ada tindakan lanjutan.'),
                'tone' => 'netral',
                'badge_class' => $this->badgeClass('netral'),
                'icon' => 'fi fi-rr-ban',
            ];
        }

        // 2. Berkas tidak selesai (lewat 31 Desember)
        if ($berkas->isTidakSelesai()) {
            return [
                'label' => __('Lewat batas tahun'),
                'hari_label' => null,
                'tooltip' => __('Berkas tidak selesai sampai 31 Desember tahun SPDP. Tahapan terkunci, tidak ada tindakan lanjutan.'),
                'tone' => 'netral',
                'badge_class' => $this->badgeClass('netral'),
                'icon' => 'fi fi-rr-calendar-xmark',
            ];
        }

        // 3. Status selesai dan Keputusan Pengadilan sudah final
        if ($berkas->status_formal === 'selesai' && $berkas->hasFinalTahap(JenisTahap::KEPUTUSAN_PENGADILAN)) {
            return [
                'label' => __('Tidak ada tindakan'),
                'hari_label' => null,
                'tooltip' => __('Seluruh tahapan sudah selesai, termasuk Keputusan Pengadilan.'),
                'tone' => 'netral',
                'badge_class' => $this->badgeClass('netral'),
                'icon' => 'fi fi-rr-check-circle',
            ];
        }

        // 4. Status selesai tetapi belum ada putusan pengadilan final
        if ($berkas->status_formal === 'selesai') {
            return [
                'label' => __('Catat Keputusan Pengadilan'),
                'hari_label' => null,
                'tooltip' => __('TAHAP II sudah final. Catat Keputusan Pengadilan setelah putusan dibacakan.'),
                'tone' => 'netral',
                'badge_class' => $this->badgeClass('netral'),
                'icon' => 'fi fi-rr-gavel',
            ];
        }

        // 5. Tersangka DPO
        if ($berkas->is_dpo) {
            $hariLabel = $this->hitungHariLabel($sisa);

            return [
                'label' => __('Cari tersangka, cabut DPO jika ditemukan'),
                'hari_label' => $hariLabel,
                'tooltip' => __('Tersangka berstatus DPO. Tahapan berikutnya terkunci sampai DPO dicabut. Tenggang waktu tetap berjalan.'),
                'tone' => 'warning',
                'badge_class' => $this->badgeClass('warning'),
                'icon' => 'fi fi-rr-user-slash',
            ];
        }

        // 6. Ada draft tahapan yang tersimpan
        if ($draft = $berkas->currentDraftTahap()) {
            $tahapLabel = $draft->jenis_tahap->label();
            $hariLabel = $this->hitungHariLabel($sisa);
            $tone = $this->hitungTone($sisa);

            return [
                'label' => __('Finalkan draft :tahap', ['tahap' => $tahapLabel]),
                'hari_label' => $hariLabel,
                'tooltip' => __('Draft :tahap sudah tersimpan tetapi belum difinalkan. Lengkapi lalu pilih Simpan & Lanjut Tahap Berikutnya.', ['tahap' => $tahapLabel]),
                'tone' => $tone,
                'badge_class' => $this->badgeClass($tone),
                'icon' => 'fi fi-rr-edit',
            ];
        }

        // 7. Progress 0%
        if ($berkas->progress_persen === 0) {
            return [
                'label' => __('Kirim SPDP ke Kejaksaan'),
                'hari_label' => null,
                'tooltip' => __('Perkara sudah terbentuk. Catat SPDP setelah dikirim ke Kejaksaan.'),
                'tone' => 'netral',
                'badge_class' => $this->badgeClass('netral'),
                'icon' => 'fi fi-rr-envelope',
            ];
        }

        // 8. Progress 15% (SPDP final)
        if ($berkas->progress_persen === 15) {
            $hariLabel = $this->hitungHariLabel($sisa);
            $tone = $this->hitungTone($sisa);
            $tooltip = $tanggalLabel
                ? __('SPDP sudah final. Serahkan berkas perkara TAHAP I ke Kejaksaan paling lambat :tanggal.', ['tanggal' => $tanggalLabel])
                : __('SPDP sudah final. Serahkan berkas perkara TAHAP I ke Kejaksaan.');

            return [
                'label' => __('Serahkan berkas TAHAP I'),
                'hari_label' => $hariLabel,
                'tooltip' => $tooltip,
                'tone' => $tone,
                'badge_class' => $this->badgeClass($tone),
                'icon' => 'fi fi-rr-document-signed',
            ];
        }

        // 9. Progress 55% (Tahap I final)
        if ($berkas->progress_persen === 55) {
            $hariLabel = $this->hitungHariLabel($sisa);
            $tone = $this->hitungTone($sisa);
            $tooltip = $tanggalLabel
                ? __('TAHAP I sudah final. Tindak lanjuti P-18P atau P-19 jika ada, lalu catat P-21 paling lambat :tanggal.', ['tanggal' => $tanggalLabel])
                : __('TAHAP I sudah final. Tindak lanjuti P-18P atau P-19 jika ada, lalu catat P-21.');

            return [
                'label' => __('Pantau P-21 dari Kejaksaan'),
                'hari_label' => $hariLabel,
                'tooltip' => $tooltip,
                'tone' => $tone,
                'badge_class' => $this->badgeClass($tone),
                'icon' => 'fi fi-rr-hourglass-end',
            ];
        }

        // 10. Progress 85% (P-21 final)
        if ($berkas->progress_persen === 85) {
            $hariLabel = $this->hitungHariLabel($sisa);
            $tone = $this->hitungTone($sisa);
            $tooltip = $tanggalLabel
                ? __('P-21 sudah final. Lakukan penyerahan tersangka dan barang bukti paling lambat :tanggal.', ['tanggal' => $tanggalLabel])
                : __('P-21 sudah final. Lakukan penyerahan tersangka dan barang bukti.');

            return [
                'label' => __('Serahkan tersangka dan barang bukti (TAHAP II)'),
                'hari_label' => $hariLabel,
                'tooltip' => $tooltip,
                'tone' => $tone,
                'badge_class' => $this->badgeClass($tone),
                'icon' => 'fi fi-rr-paper-plane',
            ];
        }

        // 11. Lainnya
        return [
            'label' => __('Catat Keputusan Pengadilan'),
            'hari_label' => null,
            'tooltip' => __('TAHAP II sudah final. Catat Keputusan Pengadilan setelah putusan dibacakan.'),
            'tone' => 'netral',
            'badge_class' => $this->badgeClass('netral'),
            'icon' => 'fi fi-rr-gavel',
        ];
    }

    /**
     * Menghitung label sisa hari tenggang waktu.
     */
    private function hitungHariLabel(?int $sisa): ?string
    {
        if ($sisa === null) {
            return null;
        }

        if ($sisa < 0) {
            return __('Terlambat :n hari', ['n' => abs($sisa)]);
        }

        if ($sisa === 0) {
            return __('Hari ini');
        }

        return __(':n hari lagi', ['n' => $sisa]);
    }

    /**
     * Menghitung tone urgensi berdasarkan sisa hari.
     *
     * @return 'error'|'warning'|'info'|'netral'
     */
    private function hitungTone(?int $sisa): string
    {
        if ($sisa === null) {
            return 'netral';
        }

        if ($sisa < 0) {
            return 'error';
        }

        if ($sisa <= 7) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Menghasilkan CSS token Tailwind untuk pill badge.
     */
    private function badgeClass(string $tone): string
    {
        return match ($tone) {
            'error' => 'border-error-200 bg-error-50 text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400',
            'warning' => 'border-warning-200 bg-warning-50 text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400',
            'info' => 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400',
            default => 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }
}
