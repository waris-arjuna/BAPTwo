<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data, ?int $actorId = null): User
    {
        return User::create([
            'nama' => $data['nama'],
            'nip' => $data['nip'] ?? null,
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
            'jabatan_id' => $data['jabatan_id'] ?? null,
            'bidang_id' => $data['bidang_id'] ?? null,
            'panggol_id' => $data['panggol_id'] ?? null,
            'password' => Hash::make((string) $data['password']),
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            'email_verified_at' => now(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data, ?int $actorId = null): User
    {
        $payload = [
            'nama' => $data['nama'],
            'nip' => $data['nip'] ?? null,
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
            'jabatan_id' => array_key_exists('jabatan_id', $data) ? $data['jabatan_id'] : $user->jabatan_id,
            'bidang_id' => array_key_exists('bidang_id', $data) ? $data['bidang_id'] : $user->bidang_id,
            'panggol_id' => array_key_exists('panggol_id', $data) ? $data['panggol_id'] : $user->panggol_id,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $user->is_active,
            'updated_by' => $actorId,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make((string) $data['password']);
        }

        $user->update($payload);

        return $user;
    }

    public function softDeleteUser(User $user, ?int $actorId = null): void
    {
        $user->update([
            'is_active' => false,
            'updated_by' => $actorId,
        ]);

        $user->delete();
    }

    public function restoreUser(int $id, ?int $actorId = null): User
    {
        /** @var User $user */
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        $user->update([
            'is_active' => true,
            'updated_by' => $actorId,
        ]);

        return $user;
    }

    /**
     * Ringkasan statistik pengguna untuk halaman Manajemen Pengguna (/pengaturan).
     *
     * @return array<string, mixed>
     */
    public function getPengaturanStats(): array
    {
        $total = User::withTrashed()->count();
        $aktif = User::query()->where('is_active', true)->count();
        $nonaktif = User::query()->where('is_active', false)->count();
        $dihapus = User::onlyTrashed()->count();
        $baruBulanIni = User::query()->where('created_at', '>=', $this->monthStartUtc())->count();

        $admin = User::query()->where('role', UserRole::ADMINISTRATOR->value)->count();
        $penyidik = User::query()->where('role', UserRole::PENYIDIK->value)->count();
        $umum = User::query()->where('role', UserRole::UMUM->value)->count();

        return [
            'total' => $total,
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
            'dihapus' => $dihapus,
            'baru_bulan_ini' => $baruBulanIni,
            'aktif_pct' => $total > 0 ? (int) round(($aktif / $total) * 100) : 0,
            'nonaktif_pct' => $total > 0 ? (int) round(($nonaktif / $total) * 100) : 0,
            'roles' => [
                ['label' => 'Admin', 'count' => $admin, 'color' => 'bg-brand-500'],
                ['label' => 'Penyidik', 'count' => $penyidik, 'color' => 'bg-blue-light-500'],
                ['label' => 'Umum', 'count' => $umum, 'color' => 'bg-orange-500'],
            ],
        ];
    }

    /**
     * Ringkasan statistik petugas non-admin untuk halaman Data Petugas (/data-petugas).
     *
     * @return array<string, mixed>
     */
    public function getPetugasStats(): array
    {
        $base = User::query()->where('role', '!=', UserRole::ADMINISTRATOR->value);

        $total = (clone $base)->count();
        $aktif = (clone $base)->where('is_active', true)->count();
        $nonaktif = (clone $base)->where('is_active', false)->count();
        $baruBulanIni = (clone $base)->where('created_at', '>=', $this->monthStartUtc())->count();
        $penyidik = (clone $base)->where('role', UserRole::PENYIDIK->value)->count();
        $umum = (clone $base)->where('role', UserRole::UMUM->value)->count();

        return [
            'total' => $total,
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
            'baru_bulan_ini' => $baruBulanIni,
            'aktif_pct' => $total > 0 ? (int) round(($aktif / $total) * 100) : 0,
            'nonaktif_pct' => $total > 0 ? (int) round(($nonaktif / $total) * 100) : 0,
            'roles' => [
                ['label' => 'Penyidik', 'count' => $penyidik, 'color' => 'bg-blue-light-500'],
                ['label' => 'Umum', 'count' => $umum, 'color' => 'bg-orange-500'],
            ],
        ];
    }

    private function monthStartUtc(): Carbon
    {
        return Carbon::now(DashboardService::ZONA_WAKTU)
            ->startOfMonth()
            ->setTimezone((string) config('app.timezone', 'UTC'));
    }
}
