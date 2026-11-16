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

    // 2. Menu Utama: Dashboard dan Daftar Perkara (Poin 10 & 11)
    $menuUtama = $groups[0]['items'];
    expect($menuUtama)->toHaveCount(2)
        ->and($menuUtama[0]['name'])->toBe('Dashboard')
        ->and($menuUtama[0]['path'])->toBe('/')
        ->and($menuUtama[1]['name'])->toBe('Daftar Perkara')
        ->and($menuUtama[1]['path'])->toBe('/data-perkara');

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

    // 4. Sistem: Master Data (5 subitem), Pengaturan (User, Petugas, Umum, Audit Log)
    $sistemItems = $groups[2]['items'];
    expect($sistemItems)->toHaveCount(2);

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
    expect($pengaturanNames)->toEqual(['User', 'Petugas', 'Umum', 'Audit Log'])
        ->and($pengaturanPaths)->toEqual(['/pengaturan', '/data-petugas', '/pengaturan/umum', '/pengaturan/audit-log']);

    // 5. Verifikasi HTML sidebar render
    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Daftar Perkara')
        ->assertDontSee('Potensi Terlimpah')
        ->assertSee('Laporan Kejadian')
        ->assertSee('Sprindik')
        ->assertSee('SPDP')
        ->assertSee('Tahap 1')
        ->assertSee('P21')
        ->assertSee('Tahap 2')
        ->assertSee('Putusan Perkara')
        ->assertSee('Master Data')
        ->assertSee('Pengaturan')
        ->assertDontSee('Recovery Data');
});

test('route recovery-data dan sampah sudah dihapus dan mengembalikan 404', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)->get('/recovery-data')->assertNotFound();
    $this->actingAs($admin)->get('/sampah')->assertNotFound();
    $this->actingAs($admin)->post('/sampah/perkara/1/restore')->assertNotFound();
    $this->actingAs($admin)->post('/sampah/berkas/1/restore')->assertNotFound();
    $this->actingAs($admin)->post('/sampah/user/1/restore')->assertNotFound();
});
