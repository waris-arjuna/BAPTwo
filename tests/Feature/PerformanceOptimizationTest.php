<?php

use App\Enums\UserRole;
use App\Models\Perkara;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('model preventLazyLoading aktif di lingkungan non produksi', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();
});

test('statistik dashboard menggunakan cache ttl 120 detik dan invalidasi saat data perkara dimutasi', function () {
    $tahun = (int) date('Y');
    Cache::flush();

    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK]);

    $service = app(DashboardService::class);

    // 1. Panggilan awal mengisi cache
    expect(Cache::has("dashboard_stats_{$tahun}"))->toBeFalse();
    $stats1 = $service->getStatistics($tahun);
    expect(Cache::has("dashboard_stats_{$tahun}"))->toBeTrue();

    // 2. Buat perkara baru; model event harus menghapus cache secara otomatis
    Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PERF/01/2026',
        'tanggal_laporan_kejadian' => "{$tahun}-01-10",
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/PERF/01/2026',
        'tanggal_surat_perintah' => "{$tahun}-01-11",
        'tahap_sprindik_status' => 'final',
        'created_by' => $penyidik->id,
    ]);

    // Cache harus ter-invalisir
    expect(Cache::has("dashboard_stats_{$tahun}"))->toBeFalse();

    // 3. Panggilan kedua mengisi cache kembali dengan data terkini
    $stats2 = $service->getStatistics($tahun);
    expect(Cache::has("dashboard_stats_{$tahun}"))->toBeTrue();
});

test('layout app dan fullscreen hanya memuat paket flaticon regular-rounded tanpa paket solid atau bold', function () {
    $guestResponse = $this->get('/masuk');
    $guestResponse->assertOk();
    $guestResponse->assertSee('uicons-regular-rounded.css');
    $guestResponse->assertDontSee('uicons-solid-rounded.css');
    $guestResponse->assertDontSee('uicons-bold-rounded.css');

    $user = User::factory()->create(['role' => UserRole::PENYIDIK]);
    $appResponse = $this->actingAs($user)->get('/');
    $appResponse->assertOk();
    $appResponse->assertSee('uicons-regular-rounded.css');
    $appResponse->assertDontSee('uicons-solid-rounded.css');
    $appResponse->assertDontSee('uicons-bold-rounded.css');
});

test('elemen gambar logo dan avatar memiliki atribut width dan height numerik', function () {
    $user = User::factory()->create([
        'role' => UserRole::PENYIDIK,
        'nama' => 'Penyidik Uji',
        'foto_profil' => 'profile-photos/test.jpg',
    ]);

    $response = $this->actingAs($user)->get('/');
    $response->assertOk();

    // Pastikan tag img menyertakan width dan height
    $content = $response->getContent();
    expect($content)->toContain('width="150" height="32"');
    expect($content)->toContain('width="44" height="44"');
});
