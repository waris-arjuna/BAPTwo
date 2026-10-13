<?php

namespace App\Services;

use App\Models\AuditLog;
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
}
