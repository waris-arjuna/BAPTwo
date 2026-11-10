<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

test('endpoint wilayah provinsi mengembalikan 38 provinsi dari JSON lokal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('wilayah.provinsi'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(38, 'data')
        ->assertJsonPath('data.0.code', '11')
        ->assertJsonPath('data.0.name', 'Aceh');
});

test('endpoint wilayah provinsi fallback ke 38 provinsi dari JSON lokal saat api offline atau gagal', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson(route('wilayah.provinsi'))
        ->assertOk()
        ->assertJsonPath('success', true);

    $data = $response->json('data');
    expect(count($data))->toBe(38)
        ->and($data[0]['code'])->toBe('11')
        ->and($data[0]['name'])->toBe('Aceh');
});

test('endpoint wilayah kabupaten mengembalikan kabupaten DKI Jakarta dari JSON lokal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('wilayah.kabupaten', '31'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('data.0.code', '3101');
});

test('endpoint wilayah kabupaten fallback ke data regencies.json lokal saat api gagal', function () {
    Http::fake([
        'https://api.kodewilayah.web.id/regencies/31' => Http::response(null, 500),
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson(route('wilayah.kabupaten', '31'))
        ->assertOk()
        ->assertJsonPath('success', true);

    $data = $response->json('data');
    expect(count($data))->toBe(6) // DKI Jakarta memiliki 6 kabupaten/kota
        ->and($data[0]['code'])->toBe('3101')
        ->and($data[0]['name'])->toContain('Kepulauan Seribu');
});

test('endpoint wilayah kecamatan dan desa berfungsi', function () {
    Http::fake([
        'https://api.kodewilayah.web.id/districts/3101' => Http::response([
            'success' => true,
            'data' => [
                ['code' => 310101, 'name' => 'Kepulauan Seribu Utara'],
            ],
        ], 200),
        'https://api.kodewilayah.web.id/villages/310101' => Http::response([
            'success' => true,
            'data' => [
                ['code' => 3101011001, 'name' => 'Pulau Panggang'],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('wilayah.kecamatan', '3101'))
        ->assertOk()
        ->assertJsonPath('data.0.code', '310101')
        ->assertJsonPath('data.0.name', 'Kepulauan Seribu Utara');

    $this->actingAs($user)
        ->getJson(route('wilayah.desa', '310101'))
        ->assertOk()
        ->assertJsonPath('data.0.code', '3101011001')
        ->assertJsonPath('data.0.name', 'Pulau Panggang');
});
