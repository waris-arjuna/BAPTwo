<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('akses url create tahapan tanpa berkas_id mengalihkan langsung ke halaman index masing-masing', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);

    $routes = [
        'spdp.create' => 'spdp.index',
        'tahap-1.create' => 'tahap-1.index',
        'p21.create' => 'p21.index',
        'tahap-2.create' => 'tahap-2.index',
        'putusan-perkara.create' => 'putusan-perkara.index',
    ];

    foreach ($routes as $createRoute => $indexRoute) {
        $response = $this->actingAs($penyidik)->get(route($createRoute));
        $response->assertRedirect(route($indexRoute));
    }
});

test('view standalone select berkas dan create tahapan lama sudah dihapus', function () {
    $views = [
        resource_path('views/pages/tahapan/_select-berkas.blade.php'),
        resource_path('views/pages/spdp/create.blade.php'),
        resource_path('views/pages/tahap-1/create.blade.php'),
        resource_path('views/pages/p21/create.blade.php'),
        resource_path('views/pages/tahap-2/create.blade.php'),
        resource_path('views/pages/putusan-perkara/create.blade.php'),
    ];

    foreach ($views as $viewPath) {
        expect(file_exists($viewPath))->toBeFalse();
    }
});
