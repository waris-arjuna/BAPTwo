<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileAccountRequest;
use App\Http\Requests\Profile\UpdateProfilePersonalRequest;
use App\Http\Requests\Profile\UpdateProfilePhotoRequest;
use App\Models\Alamat;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user()->load(['jabatan', 'bidang', 'panggol', 'alamat']);

        return view('pages.profile', [
            'title' => __('Profil Saya'),
            'user' => $user,
        ]);
    }

    public function updatePhoto(UpdateProfilePhotoRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $path = $request->file('foto_profil')->store('profile-photos', 'public');

        $user->update([
            'foto_profil' => $path,
            'updated_by' => $user->id,
        ]);

        return back()->with('profile_success', __('Foto profil berhasil diperbarui.'));
    }

    public function updateAccount(UpdateProfileAccountRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $payload = [
            'email' => $validated['email'],
            'updated_by' => $user->id,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make((string) $validated['password']);
        }

        $user->update($payload);

        return back()->with('profile_success', __('Informasi akun (Email / Password) berhasil diperbarui.'));
    }

    public function updatePersonal(UpdateProfilePersonalRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated): void {
            $alamatData = [
                'provinsi_kode' => $validated['provinsi_kode'],
                'provinsi_nama' => $validated['provinsi_nama'],
                'kabupaten_kode' => $validated['kabupaten_kode'],
                'kabupaten_nama' => $validated['kabupaten_nama'],
                'kecamatan_kode' => $validated['kecamatan_kode'],
                'kecamatan_nama' => $validated['kecamatan_nama'],
                'desa_kode' => $validated['desa_kode'],
                'desa_nama' => $validated['desa_nama'],
                'rt' => $validated['rt'] ?? null,
                'rw' => $validated['rw'] ?? null,
                'alamat_detail' => $validated['alamat_detail'],
            ];

            if ($user->alamat_id && $user->alamat) {
                $user->alamat->update($alamatData);
                $alamatId = $user->alamat_id;
            } else {
                $alamat = Alamat::create($alamatData);
                $alamatId = $alamat->id;
            }

            $user->update([
                'no_telp' => $validated['no_telp'],
                'alamat_id' => $alamatId,
                'updated_by' => $user->id,
            ]);
        });

        return back()->with('profile_success', __('Data diri (No. Telp/WA & Alamat) berhasil diperbarui.'));
    }
}
