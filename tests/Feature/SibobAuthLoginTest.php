<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\MathCaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function validSibobCaptchaAnswer(): string
{
    app(MathCaptchaService::class)->generate();

    return (string) session('math_captcha_answer');
}

it('authenticates a new SIBOB user via API, fetches profile, provisions local account, and logs in', function () {
    Http::fake([
        '*/api/v1/login' => Http::response([
            'status' => 'success',
            'data' => [
                'token' => 'sibob-jwt-token-xyz',
                'user' => [
                    'id' => 15,
                    'no_pegawai' => '199204152015021001',
                    'name' => 'Benny Ismayandi, S,Farm, Apt',
                    'email' => 'benyy.ismayandi@pom.go.id',
                    'tempat_lhr' => 'Banjarmasin',
                    'tgl_lhr' => '1992-04-15',
                    'nikah' => 'Y',
                    'jkel' => 'L',
                    'telp' => '081234567890',
                    'status' => 'PNS',
                    'aktif' => 'Y',
                ],
            ],
        ], 200),
        '*/api/v1/profile' => Http::response([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => 15,
                    'name' => 'Benny Ismayandi, S,Farm, Apt',
                    'email' => 'benyy.ismayandi@pom.go.id',
                    'divisi' => 'Penindakan',
                    'jabasn' => 'Pengawas Farmasi dan Makanan Ahli Muda',
                ],
                'permissions' => [],
            ],
        ], 200),
    ]);

    $response = $this->post(route('masuk.attempt'), [
        'email' => 'benyy.ismayandi@pom.go.id',
        'password' => 'Rahasia123!',
        'captcha' => validSibobCaptchaAnswer(),
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::where('email', 'benyy.ismayandi@pom.go.id')->firstOrFail();
    expect($user->nip)->toBe('199204152015021001')
        ->and($user->role)->toBe(UserRole::PENYIDIK)
        ->and($user->tempat_lahir)->toBe('Banjarmasin')
        ->and($user->status_pernikahan)->toBe('kawin')
        ->and($user->no_telp)->toBe('081234567890')
        ->and(session('sibob_token'))->toBe('sibob-jwt-token-xyz');
});

it('falls back to local database login when SIBOB API is unreachable', function () {
    Http::fake([
        '*/api/v1/login' => Http::response('Server Error', 502),
    ]);

    $localAdmin = User::factory()->create([
        'email' => 'admin.lokal@pom.go.id',
        'password' => Hash::make('password'),
        'role' => UserRole::ADMINISTRATOR,
        'is_active' => true,
    ]);

    $response = $this->post(route('masuk.attempt'), [
        'email' => 'admin.lokal@pom.go.id',
        'password' => 'password',
        'captcha' => validSibobCaptchaAnswer(),
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($localAdmin);
});

it('blocks login when SIBOB user has status OUT or is deactivated in BAPTwo', function () {
    Http::fake([
        '*/api/v1/login' => Http::response([
            'status' => 'success',
            'data' => [
                'token' => 'token-out',
                'user' => [
                    'id' => 99,
                    'no_pegawai' => '196501011990031001',
                    'name' => 'Pegawai Purna',
                    'email' => 'purna@pom.go.id',
                    'status' => 'OUT',
                    'aktif' => 'Y',
                ],
            ],
        ], 200),
        '*/api/v1/profile' => Http::response(['status' => 'success', 'data' => ['user' => []]], 200),
    ]);

    $response = $this->from(route('masuk'))->post(route('masuk.attempt'), [
        'email' => 'purna@pom.go.id',
        'password' => 'Rahasia123!',
        'captcha' => validSibobCaptchaAnswer(),
    ]);

    $response->assertRedirect(route('masuk'));
    $response->assertSessionHas('account_inactive');
    $this->assertGuest();
});
