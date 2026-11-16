<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Petugas\AssignPetugasRequest;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetugasController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Tampilkan tabel data petugas (pengguna non-admin).
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin() || $request->user()?->isPenyidik(), 403);

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
            ->orderByRaw('CASE WHEN is_active = 0 THEN 1 ELSE 0 END ASC')
            ->orderBy('nama');

        $petugasList = $petugasQuery->paginate(15)->withQueryString();

        // Daftar pengguna non-admin untuk dropdown pilihan penugasan
        $availableUsers = User::where('role', '!=', UserRole::ADMINISTRATOR)
            ->orderByRaw('CASE WHEN is_active = 0 THEN 1 ELSE 0 END ASC')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip', 'role', 'is_active']);

        if ($request->header('X-Filter-Partial')) {
            return view('pages.data-petugas._table-partial', compact('petugasList'));
        }

        $stats = $this->userService->getPetugasStats();

        return view('pages.data-petugas.index', compact('petugasList', 'availableUsers', 'stats'));
    }

    /**
     * Simpan pembaruan role dan status penugasan petugas.
     */
    public function store(AssignPetugasRequest $request): RedirectResponse
    {
        $validated = $request->validated();

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
