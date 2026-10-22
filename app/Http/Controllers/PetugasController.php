<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PetugasController extends Controller
{
    /**
     * Tampilkan tabel data petugas (pengguna non-admin).
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $role = $request->string('role')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $petugasQuery = User::query()
            ->where('role', '!=', UserRole::ADMINISTRATOR)
            ->with(['jabatan', 'bidang', 'panggol'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($q) => $q->where('role', $role))
            ->when($status === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($status === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('nama');

        $petugasList = $petugasQuery->paginate(15)->withQueryString();

        // Daftar pengguna non-admin untuk dropdown pilihan penugasan
        $availableUsers = User::where('role', '!=', UserRole::ADMINISTRATOR)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip', 'role', 'is_active']);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.data-petugas._table-partial', compact('petugasList'));
        }

        return view('pages.data-petugas.index', compact('petugasList', 'availableUsers'));
    }

    /**
     * Simpan pembaruan role dan status penugasan petugas.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('user', 'id')->where(function ($q) {
                    $q->where('role', '!=', UserRole::ADMINISTRATOR->value);
                }),
            ],
            'role' => [
                'required',
                Rule::in([UserRole::PENYIDIK->value, UserRole::PIMPINAN->value, UserRole::UMUM->value]),
            ],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'user_id.required' => 'Pilih petugas yang akan ditugaskan.',
            'user_id.exists' => 'Petugas yang dipilih tidak valid atau merupakan Administrator.',
            'role.required' => 'Pilih peran penugasan yang diberikan.',
            'role.in' => 'Peran yang dipilih tidak valid.',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $oldRole = $user->role->value;
        $oldActive = $user->is_active;

        $user->role = UserRole::from($validated['role']);
        if ($request->has('is_active')) {
            $user->is_active = (bool) $validated['is_active'];
        }
        $user->save();

        app(AuditTrailService::class)->log(
            aksi: 'atur_petugas',
            tabel: 'user',
            recordId: $user->id,
            dataLama: ['role' => $oldRole, 'is_active' => $oldActive],
            dataBaru: ['role' => $user->role->value, 'is_active' => $user->is_active]
        );

        $roleLabel = $user->role->label();

        return redirect()->route('data-petugas.index')->with('success', [
            'title' => 'Penugasan Berhasil Disimpan',
            'message' => "Role petugas {$user->nama} berhasil diperbarui menjadi {$roleLabel}.",
        ]);
    }
}
