<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\User;
use App\Policies\DokumenPolicy;
use App\Policies\PerkaraPolicy;
use App\Policies\UserPolicy;
use App\Services\DashboardService;
use Illuminate\Database\Eloquent\Model;
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
        Model::preventLazyLoading(! $this->app->isProduction());

        // Invalidation cache statistik dashboard seketika saat data perkara berubah (D-050)
        $clearDashboardCache = fn () => DashboardService::clearCache();
        Perkara::saved($clearDashboardCache);
        Perkara::deleted($clearDashboardCache);
        BerkasPerkara::saved($clearDashboardCache);
        BerkasPerkara::deleted($clearDashboardCache);
        TahapanPerkara::saved($clearDashboardCache);
        TahapanPerkara::deleted($clearDashboardCache);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Perkara::class, PerkaraPolicy::class);
        Gate::policy(Dokumen::class, DokumenPolicy::class);

        // UC-05: Hanya Administrator yang dapat mengelola pengguna
        Gate::define('manage-users', fn (User $user): bool => $user->isAdmin());

        // PRD Hak Akses: Semua role dapat melihat perkara (termasuk Umum)
        Gate::define('view-cases', fn (User $user): bool => in_array(
            $user->role,
            [UserRole::PENYIDIK, UserRole::PIMPINAN, UserRole::ADMINISTRATOR, UserRole::UMUM],
            true
        ));

        // PRD Hak Akses: Penyidik & Administrator dapat menambah perkara/berkas; Pimpinan tidak
        Gate::define('create-cases', fn (User $user): bool => $user->isPenyidik() || $user->isAdmin());

        // Poin 10: Hak Akses Ekspor Excel (Hanya Admin dan Penyidik; Pemimpin dan Umum tidak)
        Gate::define('export-excel', fn (User $user): bool => $user->isPenyidik() || $user->isAdmin());

        // PRD F-24 s.d. F-27 & Hak Akses: Toggle DPO (Penyidik & Administrator bisa, Pimpinan tidak)
        Gate::define('toggle-dpo', fn (User $user): bool => $user->isPenyidik() || $user->isAdmin());

        // Poin 7: Master Data RBAC
        // Pemimpin & Umum: diblokir total
        // Penyidik: hanya Pasal dan Instansi Hukum
        // Administrator: seluruh master data
        Gate::define('view-master-data', function (User $user, ?string $type = null): bool {
            if ($user->isAdmin()) {
                return true;
            }

            if ($user->isPenyidik()) {
                return $type === null || in_array($type, ['pasal', 'instansi-hukum'], true);
            }

            return false;
        });

        Gate::define('manage-master-data', function (User $user, ?string $type = null): bool {
            if ($user->isAdmin()) {
                return true;
            }

            if ($user->isPenyidik()) {
                return $type === null || in_array($type, ['pasal', 'instansi-hukum'], true);
            }

            return false;
        });

        // Poin 20: Halaman Progres Perkara hanya untuk Pemimpin dan Umum. Untuk Penyidik diblokir.
        Gate::define('view-progress-perkara', function (User $user): bool {
            if ($user->isPenyidik()) {
                return false;
            }

            return $user->isPimpinan() || $user->isUmum() || $user->isAdmin();
        });

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
