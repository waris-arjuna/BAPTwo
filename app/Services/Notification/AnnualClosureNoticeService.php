<?php

namespace App\Services\Notification;

use App\Models\AuditLog;
use Carbon\Carbon;

class AnnualClosureNoticeService
{
    /**
     * Zona waktu operasional BPOM Kalimantan Selatan (WITA / UTC+8).
     */
    public const ZONA_WAKTU = 'Asia/Makassar';

    /**
     * Mengambil informasi pemberitahuan penutupan perkara awal tahun (khusus Januari untuk Penyidik & Admin).
     *
     * @return array{tahun_lalu: int, tahun_ini: int, jumlah_berkas: int, pesan: string, url_daftar: string}|null
     */
    public function getAnnualClosureNoticeInfo(): ?array
    {
        $user = auth()->user();
        if (! $user || (! $user->isPenyidik() && ! $user->isAdmin())) {
            return null;
        }

        $now = Carbon::now(self::ZONA_WAKTU);
        if ($now->month !== 1) {
            return null;
        }

        $currentYear = $now->year;
        $previousYear = $currentYear - 1;

        $count = AuditLog::query()
            ->where('aksi', 'sistem_tidak_selesai')
            ->whereYear('created_at', $currentYear)
            ->distinct('berkas_perkara_id')
            ->count('berkas_perkara_id');

        if ($count === 0) {
            return null;
        }

        return [
            'tahun_lalu' => $previousYear,
            'tahun_ini' => $currentYear,
            'jumlah_berkas' => $count,
            'pesan' => __('Pemberitahuan Sistem: Sebanyak :count berkas perkara tahun :tahun_lalu telah ditutup otomatis menjadi Tidak Selesai per 1 Januari :tahun_ini karena melewati batas akhir tahun penyidikan (31 Desember).', [
                'count' => $count,
                'tahun_lalu' => $previousYear,
                'tahun_ini' => $currentYear,
            ]),
            'url_daftar' => route('data-perkara.index', [
                'status' => 'tidak_selesai',
                'tahun' => $previousYear,
            ]),
        ];
    }
}
