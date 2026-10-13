<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
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

        $users = User::withTrashed()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->orderBy('nama')
            ->get();

        return view('pages.pengaturan.index', [
            'title' => __('Manajemen Pengguna'),
            'users' => $users,
            'roles' => UserRole::cases(),
            'search' => $search,
            'selectedRole' => $role,
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
