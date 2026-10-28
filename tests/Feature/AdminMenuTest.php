<?php

use App\Helpers\MenuHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('role administrator memiliki struktur menu utama, perkara 7 tahapan, dan sistem terpadu', function () {
    $admin = User::factory()->administrator()->create();
    $this->actingAs($admin);

    $groups = MenuHelper::getMenuGroups();

    // 1. Tepat 3 grup: Menu Utama, Perkara, Sistem
    expect($groups)->toHaveCount(3);
    $titles = array_column($groups, 'title');
    expect($titles)->toEqual(['Menu Utama', 'Perkara', 'Sistem']);

    // 2. Menu Utama: Dashboard dan Data Perkara (Daftar Perkara & Potensi Terlimpah)
    $menuUtama = $groups[0]['items'];
    expect($menuUtama)->toHaveCount(2)
        ->and($menuUtama[0]['name'])->toBe('Dashboard')
        ->and($menuUtama[0]['path'])->toBe('/')
        ->and($menuUtama[1]['name'])->toBe('Data Perkara')
        ->and(array_column($menuUtama[1]['subItems'], 'name'))->toEqual(['Daftar Perkara', 'Potensi Terlimpah'])
        ->and(array_column($menuUtama[1]['subItems'], 'path'))->toEqual(['/data-perkara', '/potensi-terlimpah']);

    // 3. Perkara: 7 tahapan berurutan
    $perkaraItems = $groups[1]['items'];
    expect($perkaraItems)->toHaveCount(7);
    $perkaraNames = array_column($perkaraItems, 'name');
    $perkaraPaths = array_column($perkaraItems, 'path');

    expect($perkaraNames)->toEqual([
        'Laporan Kejadian',
        'Sprindik',
        'SPDP',
        'Tahap 1',
        'P21',
        'Tahap 2',
        'Putusan Perkara',
    ])->and($perkaraPaths)->toEqual([
        '/laporan-kejadian',
        '/sprindik',
        '/spdp',
        '/tahap-1',
        '/p21',
        '/tahap-2',
        '/putusan-perkara',
    ]);

    // 4. Sistem: Master Data (5 subitem), Pengaturan (User, Petugas, Umum), Recovery Data
    $sistemItems = $groups[2]['items'];
    expect($sistemItems)->toHaveCount(3);

    // Master Data
    $masterData = $sistemItems[0];
    expect($masterData['name'])->toBe('Master Data');
    $masterNames = array_column($masterData['subItems'], 'name');
    expect($masterNames)->toEqual([
        'Jabatan',
        'Bidang',
        'Pangkat & Golongan',
        'Pasal / UU',
        'Instansi Hukum',
    ]);

    // Pengaturan
    $pengaturan = $sistemItems[1];
    expect($pengaturan['name'])->toBe('Pengaturan');
    $pengaturanNames = array_column($pengaturan['subItems'], 'name');
    $pengaturanPaths = array_column($pengaturan['subItems'], 'path');
    expect($pengaturanNames)->toEqual(['User', 'Petugas', 'Umum'])
        ->and($pengaturanPaths)->toEqual(['/pengaturan', '/data-petugas', '/pengaturan/umum']);

    // Recovery Data
    $recoveryData = $sistemItems[2];
    expect($recoveryData['name'])->toBe('Recovery Data')
        ->and($recoveryData['path'])->toBe('/recovery-data');

    // 5. Verifikasi HTML sidebar render
    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Data Perkara')
        ->assertSee('Daftar Perkara')
        ->assertSee('Potensi Terlimpah')
        ->assertSee('Laporan Kejadian')
        ->assertSee('Sprindik')
        ->assertSee('SPDP')
        ->assertSee('Tahap 1')
        ->assertSee('P21')
        ->assertSee('Tahap 2')
        ->assertSee('Putusan Perkara')
        ->assertSee('Master Data')
        ->assertSee('Pengaturan')
        ->assertSee('Recovery Data');
});

test('halaman recovery data dapat diakses via route recovery-data dan alias sampah dengan teks resmi', function () {
    $admin = User::factory()->administrator()->create();

    // 1. Akses via /recovery-data
    $responseRecovery = $this->actingAs($admin)->get('/recovery-data');
    $responseRecovery->assertOk()
        ->assertSee('Recovery Data')
        ->assertSee('Informasi Pemulihan Data (Soft Delete)')
        ->assertDontSee('title="Sampah"');

    // 2. Akses via /sampah
    $responseSampah = $this->actingAs($admin)->get('/sampah');
    $responseSampah->assertOk()
        ->assertSee('Recovery Data');
});
