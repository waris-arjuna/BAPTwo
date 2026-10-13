<?php

namespace App\Services;

use App\Models\User;
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
}
