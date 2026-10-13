<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        // UC-05: Hanya Administrator yang dapat mengelola pengguna
        Gate::define('manage-users', fn (User $user): bool => $user->isAdmin());

        // PRD Hak Akses: Semua role dapat melihat perkara
        Gate::define('view-cases', fn (User $user): bool => in_array(
            $user->role,
            [UserRole::PENYIDIK, UserRole::PIMPINAN, UserRole::ADMINISTRATOR],
            true
        ));

        // PRD Hak Akses: Penyidik & Administrator dapat menambah perkara/berkas; Pimpinan tidak
        Gate::define('create-cases', fn (User $user): bool => $user->isPenyidik() || $user->isAdmin());

        // Master Data: Penyidik & Administrator dapat tambah/edit/soft-delete; Pimpinan hanya lihat
        Gate::define('manage-master-data', fn (User $user): bool => $user->isPenyidik() || $user->isAdmin());

        // PRD F-18 & Hak Akses: Edit hanya saat masih Draft; jika sudah lanjut ($isFinalized = true) terkunci mutlak termasuk untuk Administrator
        Gate::define('edit-draft', function (User $user, bool $isFinalized = false): bool {
            if ($isFinalized) {
                return false;
            }

            return $user->isPenyidik() || $user->isAdmin();
        });

        // PRD F-44 & Hak Akses: Soft delete hanya Penyidik & Administrator
        Gate::define('soft-delete', fn (User $user): bool => $user->isPenyidik() || $user->isAdmin());

        // PRD F-44: Hard delete dilarang mutlak untuk role mana pun
        Gate::define('hard-delete', fn (?User $user = null): bool => false);
    }
}
