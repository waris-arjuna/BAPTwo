<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.sibob.base_url' => 'https://sibob.bbpombanjarmasin.com',
        'services.sibob.service_email' => 'magangit@mail.com',
        'services.sibob.service_password' => 'magang14!T',
    ]);

    Http::fake([
        '*/api/v1/login' => Http::response([
            'status' => 'success',
            'data' => [
                'token' => 'service-token-999',
                'user' => ['id' => 1209, 'email' => 'magangit@mail.com', 'name' => 'MAGANG-IT'],
            ],
        ], 200),
        '*/api/v1/users' => Http::response([
            [
                'id' => 21,
                'nip' => '',
                'no_pegawai' => '199407222019031002',
                'name' => 'Juli Lomo Simanjuntak, SH',
                'email' => 'juli.simanjuntak@pom.go.id',
                'jenis_kelamin' => 'L',
                'divisi_name' => 'Penindakan',
                'jabasn_name' => 'Pengawas Farmasi dan Makanan Ahli Pertama',
                'golongan' => 'III/b',
                'status' => 'PNS',
                'aktif' => 'Y',
            ],
        ], 200),
    ]);
});

it('runs php artisan sibob:sync-users command successfully', function () {
    $this->artisan('sibob:sync-users')
        ->expectsOutputToContain('Sinkronisasi SIBOB berhasil')
        ->assertSuccessful();

    expect(User::where('email', 'juli.simanjuntak@pom.go.id')->exists())->toBeTrue();
});

it('allows administrator and penyidik to trigger SIBOB sync via web endpoint and blocks umum role', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMINISTRATOR, 'is_active' => true]);
    $umum = User::factory()->create(['role' => UserRole::UMUM, 'is_active' => true]);

    $this->actingAs($umum)
        ->post(route('users.sync-sibob'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->from(route('users.index'))
        ->post(route('users.sync-sibob'))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    expect(User::where('nip', '199407222019031002')->exists())->toBeTrue();
});
