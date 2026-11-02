<?php

use App\Enums\JenisTahap;
use App\Helpers\MenuHelper;
use App\Models\BerkasPerkara;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders Daftar Perkara menu with role-based visibility according to Poin 10 and Poin 11', function () {
    $pimpinan = User::factory()->pimpinan()->create();
    $penyidik = User::factory()->penyidik()->create();
    $admin = User::factory()->administrator()->create();

    // Untuk Pimpinan (Poin 10 & 11): Hanya 1 grup Menu Utama (Dashboard dan Daftar Perkara)
    $this->actingAs($pimpinan);
    $menuGroupsPimpinan = MenuHelper::getMenuGroups();
    expect($menuGroupsPimpinan)->toHaveCount(1)
        ->and($menuGroupsPimpinan[0]['title'])->toBe('Menu Utama')
        ->and(collect($menuGroupsPimpinan[0]['items'])->pluck('name')->all())
        ->toEqual(['Dashboard', 'Daftar Perkara']);

    // Untuk Admin (Poin 10 & 11): Menu Utama memuat Dashboard dan Daftar Perkara link langsung
    $this->actingAs($admin);
    $menuGroupsAdmin = MenuHelper::getMenuGroups();
    $mainGroupAdmin = collect($menuGroupsAdmin)->firstWhere('title', 'Menu Utama');
    $daftarPerkaraItemAdmin = collect($mainGroupAdmin['items'])->firstWhere('name', 'Daftar Perkara');
    expect($daftarPerkaraItemAdmin)->not->toBeNull()
        ->and($daftarPerkaraItemAdmin['path'])->toBe('/data-perkara');

    // Untuk Penyidik (Poin 10 & 11): Menu Utama memuat Dashboard dan Daftar Perkara link langsung
    $this->actingAs($penyidik);
    $menuGroupsPenyidik = MenuHelper::getMenuGroups();
    $mainGroupPenyidik = collect($menuGroupsPenyidik)->firstWhere('title', 'Menu Utama');
    $daftarPerkaraItemPenyidik = collect($mainGroupPenyidik['items'])->firstWhere('name', 'Daftar Perkara');
    expect($daftarPerkaraItemPenyidik)->not->toBeNull()
        ->and($daftarPerkaraItemPenyidik['path'])->toBe('/data-perkara');
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
    $pimpinan = User::factory()->pimpinan()->create();

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

    // Penyidik dialihkan ke Daftar Perkara sesuai Poin 3A (Revisi 27 Okt)
    $this->actingAs($penyidik)
        ->get(route('progress-perkara.index'))
        ->assertRedirect(route('data-perkara.index'));

    // Pemimpin diizinkan (200 OK) sesuai Poin 20
    $this->actingAs($pimpinan)
        ->get(route('progress-perkara.index'))
        ->assertOk()
        ->assertSee('Progress Perkara')
        ->assertSee('BP/PROG-01/2026')
        ->assertSee('Budi Santoso')
        ->assertSee('55%')
        ->assertSee('Dalam Proses');

    // Partial AJAX filter view oleh Pemimpin
    $response = $this->actingAs($pimpinan)
        ->withHeader('X-Filter-Partial', '1')
        ->get(route('progress-perkara.index', ['status' => 'Dalam Proses']));

    $response->assertOk()
        ->assertSee('BP/PROG-01/2026')
        ->assertSee('55%');
});

it('renders standard 8-column Daftar Perkara table with pasal truncation and vertical info', function () {
    $penyidik = User::factory()->penyidik()->create();

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/STANDAR-08/2026',
        'tanggal_laporan_kejadian' => '2026-10-15',
        'nomor_surat_perintah' => 'SP/STANDAR-08/2026',
        'tanggal_surat_perintah' => '2026-10-16',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Ahmad Suhendra',
        'alias' => 'Hendra',
        'nik' => '6371018899001122',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $pasal1 = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'pasal' => 'Pasal 196',
        'nomor' => '36',
        'tahun' => '2009',
        'status' => 'aktif',
    ]);
    $pasal2 = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'pasal' => 'Pasal 197',
        'nomor' => '36',
        'tahun' => '2009',
        'status' => 'aktif',
    ]);
    $pasal3 = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'pasal' => 'Pasal 198',
        'nomor' => '36',
        'tahun' => '2009',
        'status' => 'aktif',
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/STANDAR-08/2026',
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
    ]);
    $berkas->pasals()->attach([$pasal1->id, $pasal2->id, $pasal3->id]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'status' => 'final',
        'nomor_surat' => 'SPDP/STANDAR/01',
        'tanggal_surat' => '2026-10-15',
        'finalized_at' => now(),
    ]);

    $response = $this->actingAs($penyidik)->get(route('data-perkara.index'));

    $response->assertOk()
        // Header 8 kolom
        ->assertSee('Perkara')
        ->assertSee('TKP')
        ->assertSee('PPNS')
        ->assertSee('Tersangka')
        ->assertSee('Pasal')
        ->assertSee('Status')
        ->assertSee('Aksi')
        // Data Perkara vertikal
        ->assertSee('LK/STANDAR-08/2026')
        ->assertSee('15-10-2026')
        // Tersangka & alias
        ->assertSee('Ahmad Suhendra')
        ->assertSee('Hendra')
        // Pasal (maks 2 tampil, sisanya ...)
        ->assertSee('Pasal 196')
        ->assertSee('Pasal 197')
        ->assertSee('...')
        // Status & Progres angka persen
        ->assertSee('Dalam Proses')
        ->assertSee('SPDP')
        ->assertSee('15%');
});
