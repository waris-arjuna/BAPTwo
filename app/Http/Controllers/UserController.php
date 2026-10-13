<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\Panggol;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $search = $request->string('search')->trim()->toString();
        $role = $request->string('role')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $users = User::withTrashed()
            ->with(['jabatan', 'bidang', 'panggol'])
            ->when($search !== '', function ($query) use ($search) {
                $searchLower = strtolower($search);
                $matchesActive = str_contains($searchLower, 'aktif') && ! str_contains($searchLower, 'non');
                $matchesInactive = str_contains($searchLower, 'nonaktif') || str_contains($searchLower, 'non') || str_contains($searchLower, 'terhapus');

                $query->where(function ($q) use ($search, $matchesActive, $matchesInactive) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");

                    if ($matchesActive) {
                        $q->orWhere(fn ($sub) => $sub->whereNull('deleted_at')->where('is_active', true));
                    }
                    if ($matchesInactive) {
                        $q->orWhere(fn ($sub) => $sub->whereNotNull('deleted_at')->orWhere('is_active', false));
                    }
                });
            })
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->when($status === 'aktif', fn ($query) => $query->whereNull('deleted_at')->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where(fn ($q) => $q->whereNotNull('deleted_at')->orWhere('is_active', false)))
            ->orderBy('nama')
            ->get();

        if ($request->header('X-Filter-Partial')) {
            return view('pages.pengaturan._table-partial', [
                'users' => $users,
                'roles' => UserRole::cases(),
            ]);
        }

        return view('pages.pengaturan.index', [
            'title' => __('Manajemen Pengguna'),
            'users' => $users,
            'roles' => UserRole::cases(),
            'jabatanList' => Jabatan::orderBy('nama_jabatan')->get(),
            'bidangList' => Bidang::orderBy('nama_bidang')->get(),
            'panggolList' => Panggol::orderBy('golongan')->get(),
            'search' => $search,
            'selectedRole' => $role,
            'selectedStatus' => $status,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated(), $request->user()?->id);

        return redirect()->route('users.index')->with('success', __('Pengguna berhasil ditambahkan.'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser($user, $request->validated(), $request->user()?->id);

        return redirect()->route('users.index')->with('success', __('Data pengguna berhasil diperbarui.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $this->userService->softDeleteUser($user, $request->user()?->id);

        return redirect()->route('users.index')->with('success', __('Pengguna berhasil dinonaktifkan (soft delete).'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = User::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $user);

        $this->userService->restoreUser($id, $request->user()?->id);

        return redirect()->route('users.index')->with('success', __('Pengguna berhasil dipulihkan.'));
    }
}
