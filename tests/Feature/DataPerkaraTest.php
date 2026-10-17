<?php

use App\Helpers\MenuHelper;
use App\Models\BerkasPerkara;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders Data Perkara dropdown menu with Daftar Perkara and Progress Perkara in MenuHelper', function () {
    $menuGroups = MenuHelper::getMenuGroups();

    $perkaraGroup = collect($menuGroups)->firstWhere('title', 'Perkara');
    expect($perkaraGroup)->not->toBeNull();

    $dataPerkaraItem = collect($perkaraGroup['items'])->firstWhere('name', 'Data Perkara');
    expect($dataPerkaraItem)->not->toBeNull()
        ->and($dataPerkaraItem['subItems'])->toHaveCount(3)
        ->and($dataPerkaraItem['subItems'][0]['name'])->toBe('Daftar Perkara')
        ->and($dataPerkaraItem['subItems'][0]['path'])->toBe('/data-perkara')
        ->and($dataPerkaraItem['subItems'][1]['name'])->toBe('Progress Perkara')
        ->and($dataPerkaraItem['subItems'][1]['path'])->toBe('/progress-perkara')
        ->and($dataPerkaraItem['subItems'][2]['name'])->toBe('Potensi Terlimpah')
        ->and($dataPerkaraItem['subItems'][2]['path'])->toBe('/potensi-terlimpah');
});

it('allows authenticated users to view Daftar Perkara and shows Tambah Perkara modal only for authorized roles', function () {
    $penyidik = User::factory()->penyidik()->create();
    $pimpinan = User::factory()->pimpinan()->create();

    $sprindikFinal = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DP-001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/DP-001/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
    ]);

    $perkaraFinal = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DP-002/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/DP-002/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkaraFinal->id,
        'nama' => 'Tersangka Uji Daftar',
        'nik' => '6371011234567890',
        'jenis_kelamin' => 'laki-laki',
    ]);

    BerkasPerkara::create([
        'perkara_id' => $perkaraFinal->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/DP-002/2026',
        'status_proses' => 'Baru',
        'progress_persen' => 0,
    ]);

    // 1. Penyidik can view page and see Tambah Perkara button + modal option
    $this->actingAs($penyidik)
        ->get(route('data-perkara.index'))
        ->assertOk()
        ->assertSee('Daftar Perkara')
        ->assertSee('Tambah Perkara')
        ->assertSee('SP/DP-002/X/2026')
        ->assertSee('Tersangka Uji Daftar')
        ->assertSee('Perkara Terbentuk');

    // 2. Pimpinan can view page but CANNOT see Tambah Perkara button
    $this->actingAs($pimpinan)
        ->get(route('data-perkara.index'))
        ->assertOk()
        ->assertSee('Daftar Perkara')
        ->assertDontSee('Tambah Perkara')
        ->assertSee('SP/DP-002/X/2026');
});

it('supports AJAX live filtering on Daftar Perkara table', function () {
    $penyidik = User::factory()->penyidik()->create();

    $perkara1 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SEARCH-1/X/2026',
        'nomor_surat_perintah' => 'SP/SEARCH-KOSMETIK/X/2026',
        'tanggal_surat_perintah' => '2026-10-10',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
    ]);

    $perkara2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SEARCH-2/X/2026',
        'nomor_surat_perintah' => 'SP/SEARCH-FARMASI/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'draft',
    ]);

    // Test search filter with AJAX partial header
    $response = $this->actingAs($penyidik)
        ->withHeader('X-Filter-Partial', '1')
        ->get(route('data-perkara.index', ['search' => 'KOSMETIK']));

    $response->assertOk()
        ->assertSee('SP/SEARCH-KOSMETIK/X/2026')
        ->assertDontSee('SP/SEARCH-FARMASI/X/2026');

    // Test status filter
    $responseStatus = $this->actingAs($penyidik)
        ->withHeader('X-Filter-Partial', '1')
        ->get(route('data-perkara.index', ['status' => 'Draft Perkara']));

    $responseStatus->assertOk()
        ->assertSee('SP/SEARCH-FARMASI/X/2026')
        ->assertDontSee('SP/SEARCH-KOSMETIK/X/2026');
});

it('allows authenticated users to view Progress Perkara and filter by status', function () {
    $penyidik = User::factory()->penyidik()->create();

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PROG-01/X/2026',
        'nomor_surat_perintah' => 'SP/PROG-01/X/2026',
        'tanggal_surat_perintah' => '2026-10-10',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Budi Santoso',
        'nik' => '6371019988776655',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $pasal = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'pasal' => 'Pasal 435',
        'nomor' => '17',
        'tahun' => '2023',
        'status' => 'aktif',
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/PROG-01/2026',
        'status_formal' => null,
        'status_proses' => 'TAHAP I',
        'progress_persen' => 55,
        'is_dpo' => false,
    ]);
    $berkas->pasals()->attach($pasal->id);

    // Full page view
    $this->actingAs($penyidik)
        ->get(route('progress-perkara.index'))
        ->assertOk()
        ->assertSee('Progress Perkara')
        ->assertSee('BP/PROG-01/2026')
        ->assertSee('Budi Santoso')
        ->assertSee('55%')
        ->assertSee('Dalam Proses');

    // Partial AJAX filter view
    $response = $this->actingAs($penyidik)
        ->withHeader('X-Filter-Partial', '1')
        ->get(route('progress-perkara.index', ['status' => 'Dalam Proses']));

    $response->assertOk()
        ->assertSee('BP/PROG-01/2026')
        ->assertSee('55%');
});
