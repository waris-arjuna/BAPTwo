<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AuditTrailService
{
    /**
     * @param  array<string, mixed>|null  $dataLama
     * @param  array<string, mixed>|null  $dataBaru
     */
    public function log(
        string $aksi,
        string $tabel,
        int $recordId,
        ?array $dataLama = null,
        ?array $dataBaru = null,
        ?int $perkaraId = null,
        ?int $berkasPerkaraId = null,
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'perkara_id' => $perkaraId,
            'berkas_perkara_id' => $berkasPerkaraId,
            'aksi' => $aksi,
            'tabel' => $tabel,
            'record_id' => $recordId,
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Mengambil daftar audit log global dengan filter kategori, aksi, pencarian, dan rentang tanggal.
     *
     * @param  array{
     *     search?: string,
     *     kategori?: string,
     *     aksi?: string,
     *     tanggal_dari?: string,
     *     tanggal_sampai?: string
     * }  $filters
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function getGlobalLogs(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $kategori = trim((string) ($filters['kategori'] ?? ''));
        $aksi = trim((string) ($filters['aksi'] ?? ''));
        $tanggalDari = trim((string) ($filters['tanggal_dari'] ?? ''));
        $tanggalSampai = trim((string) ($filters['tanggal_sampai'] ?? ''));

        $appTimezone = (string) config('app.timezone', 'UTC');

        return AuditLog::query()
            ->with(['user', 'perkara', 'berkasPerkara'])
            ->when($kategori === 'sistem', function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->whereNull('perkara_id')
                        ->orWhereIn('aksi', AuditLog::SYSTEM_SECURITY_ACTIONS);
                });
            })
            ->when($kategori === 'perkara', function (Builder $query) {
                $query->whereNotNull('perkara_id')
                    ->whereNotIn('aksi', AuditLog::NON_CASE_ACTIONS);
            })
            ->when($aksi !== '', fn (Builder $query) => $query->where('aksi', $aksi))
            ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari, $appTimezone) {
                try {
                    $start = Carbon::parse($tanggalDari, DashboardService::ZONA_WAKTU)
                        ->startOfDay()
                        ->setTimezone($appTimezone);
                    $query->where('created_at', '>=', $start);
                } catch (\Throwable) {
                    // Abaikan format tanggal filter yang tidak valid
                }
            })
            ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai, $appTimezone) {
                try {
                    $end = Carbon::parse($tanggalSampai, DashboardService::ZONA_WAKTU)
                        ->endOfDay()
                        ->setTimezone($appTimezone);
                    $query->where('created_at', '<=', $end);
                } catch (\Throwable) {
                    // Abaikan format tanggal filter yang tidak valid
                }
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $like = "%{$search}%";
                $query->where(function (Builder $q) use ($like) {
                    $q->where('aksi', 'like', $like)
                        ->orWhere('tabel', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('data_baru', 'like', $like)
                        ->orWhereHas('user', fn (Builder $u) => $u->where('nama', 'like', $like)->orWhere('email', 'like', $like))
                        ->orWhereHas('perkara', fn (Builder $p) => $p->where('nomor_laporan_kejadian', 'like', $like))
                        ->orWhereHas('berkasPerkara', fn (Builder $b) => $b->where('nomor_berkas', 'like', $like));
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Statistik ringkas audit log global untuk kartu informasi halaman Administrator.
     *
     * @return array{total: int, keamanan_sistem: int, login_gagal_7_hari: int, aktivitas_perkara: int}
     */
    public function getGlobalSummaryStats(): array
    {
        $sevenDaysAgo = Carbon::now(DashboardService::ZONA_WAKTU)
            ->subDays(7)
            ->setTimezone((string) config('app.timezone', 'UTC'));

        return [
            'total' => AuditLog::query()->count(),
            'keamanan_sistem' => AuditLog::query()
                ->where(function (Builder $q) {
                    $q->whereNull('perkara_id')
                        ->orWhereIn('aksi', AuditLog::SYSTEM_SECURITY_ACTIONS);
                })
                ->count(),
            'login_gagal_7_hari' => AuditLog::query()
                ->whereIn('aksi', ['login_failed', 'login_inactive'])
                ->where('created_at', '>=', $sevenDaysAgo)
                ->count(),
            'aktivitas_perkara' => AuditLog::query()
                ->whereNotNull('perkara_id')
                ->whereNotIn('aksi', AuditLog::NON_CASE_ACTIONS)
                ->count(),
        ];
    }

    /**
     * Daftar opsi aksi untuk filter dropdown (gabungan kamus resmi & aksi aktual di DB).
     *
     * @return array<string, string>
     */
    public function getActionOptions(): array
    {
        $options = AuditLog::actionLabels();

        $dbActions = AuditLog::query()
            ->select('aksi')
            ->distinct()
            ->pluck('aksi');

        foreach ($dbActions as $aksi) {
            if (is_string($aksi) && $aksi !== '' && ! isset($options[$aksi])) {
                $options[$aksi] = AuditLog::actionMeta($aksi)['label'];
            }
        }

        return $options;
    }
}
