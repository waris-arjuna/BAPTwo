<?php

use App\Helpers\MenuHelper;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'administrator']);
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $this->pimpinan = User::factory()->create(['role' => 'pimpinan']);
    $this->umum = User::factory()->create(['role' => 'umum']);

    // Kasus 1: Perkara & Berkas 1 (Dalam Proses, DPO Aktif)
    $this->perkara1 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026/BPOM',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'nomor_surat_perintah' => 'SP.Gas/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_laporan_status' => 'lanjut',
        'tahap_sprindik_status' => 'lanjut',
        'tahap_perkara_status' => 'final',
        'deskripsi' => 'Tindak pidana peredaran obat ilegal tanpa izin edar di Banjarmasin.',
        'created_by' => $this->penyidik->id,
    ]);
    $this->perkara1->ppnsUsers()->attach($this->penyidik->id, ['peran' => 'utama']);

    $this->tersangka1 = Tersangka::create([
        'perkara_id' => $this->perkara1->id,
        'nama' => 'Budi Santoso',
        'nik' => '6371012345670001',
        'alias' => 'Bos Budi',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas1 = BerkasPerkara::create([
        'perkara_id' => $this->perkara1->id,
        'tersangka_id' => $this->tersangka1->id,
        'nomor_berkas' => 'BP/001/X/2026',
        'status_proses' => 'TAHAP I',
        'progress_persen' => 55,
        'is_dpo' => true,
        'tanggal_spdp' => '2026-10-05',
        'created_by' => $this->penyidik->id,
    ]);

    // Kasus 2: Perkara & Berkas 2 (Selesai di Tahap 2)
    $this->perkara2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/002/X/2026/BPOM',
        'tanggal_laporan_kejadian' => '2026-10-02',
        'nomor_surat_perintah' => 'SP.Gas/002/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
        'tahap_laporan_status' => 'lanjut',
        'tahap_sprindik_status' => 'lanjut',
        'tahap_perkara_status' => 'final',
        'deskripsi' => 'Peredaran kosmetik berbahaya mengandung merkuri di Banjarbaru.',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka2 = Tersangka::create([
        'perkara_id' => $this->perkara2->id,
        'nama' => 'Siti Rahma',
        'nik' => '6371012345670002',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas2 = BerkasPerkara::create([
        'perkara_id' => $this->perkara2->id,
        'tersangka_id' => $this->tersangka2->id,
        'nomor_berkas' => 'BP/002/X/2026',
        'status_proses' => 'TAHAP II',
        'progress_persen' => 100,
        'status_formal' => 'selesai',
        'is_dpo' => false,
        'tanggal_spdp' => '2026-10-06',
        'created_by' => $this->penyidik->id,
    ]);
});

test('halaman seluruh perkara dapat diakses oleh peran pimpinan dan umum serta mengalihkan penyidik dan admin', function () {
    // Pimpinan
    $this->actingAs($this->pimpinan)
        ->get(route('seluruh-perkara.index'))
        ->assertOk()
        ->assertSee('Seluruh Perkara')
        ->assertSee('SP.Gas/001/X/2026')
        ->assertSee('Budi Santoso')
        ->assertSee('BP/001/X/2026')
        ->assertSee('Total Perkara')
        ->assertSee('Dalam Proses');

    // Umum
    $this->actingAs($this->umum)
        ->get(route('seluruh-perkara.index'))
        ->assertOk()
        ->assertSee('Seluruh Perkara');

    // Penyidik & Administrator dialihkan ke Data Perkara sesuai Poin 3A (Revisi 27 Okt)
    $this->actingAs($this->penyidik)
        ->get(route('seluruh-perkara.index'))
        ->assertRedirect(route('data-perkara.index'));

    $this->actingAs($this->admin)
        ->get(route('seluruh-perkara.index'))
        ->assertRedirect(route('data-perkara.index'));
});

test('halaman seluruh perkara merender partial tabel saat request ajax X-Filter-Partial oleh pimpinan', function () {
    $response = $this->actingAs($this->pimpinan)
        ->withHeaders(['X-Filter-Partial' => '1'])
        ->get(route('seluruh-perkara.index'));

    $response->assertOk();
    $response->assertViewIs('pages.seluruh-perkara._table-partial');
    $response->assertSee('SP.Gas/001/X/2026');
    $response->assertSee('SP.Gas/002/X/2026');
});

test('filter pencarian dan status pada seluruh perkara memfilter data secara akurat', function () {
    // Cari 'Budi' oleh Pimpinan
    $responseBudi = $this->actingAs($this->pimpinan)
        ->get(route('seluruh-perkara.index', ['search' => 'Budi']));

    $responseBudi->assertOk();
    $responseBudi->assertSee('SP.Gas/001/X/2026');
    $responseBudi->assertDontSee('SP.Gas/002/X/2026');

    // Filter status 'Selesai' oleh Pimpinan
    $responseSelesai = $this->actingAs($this->pimpinan)
        ->get(route('seluruh-perkara.index', ['status' => 'Selesai']));

    $responseSelesai->assertOk();
    $responseSelesai->assertSee('SP.Gas/002/X/2026');
    $responseSelesai->assertDontSee('SP.Gas/001/X/2026');
});

test('halaman pencarian perkara dapat diakses oleh seluruh peran', function () {
    $this->actingAs($this->penyidik)
        ->get(route('pencarian-perkara.index'))
        ->assertOk()
        ->assertSee('Pencarian Perkara')
        ->assertSee('Ketik nomor berkas, nama tersangka, NIK, sprindik, pasal');

    $this->actingAs($this->pimpinan)
        ->get(route('pencarian-perkara.index'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('pencarian-perkara.index'))
        ->assertOk();
});

test('pencarian multi-kriteria pada pencarian perkara menemukan data yang cocok', function () {
    // 1. Cari berdasarkan NIK tersangka
    $resNik = $this->actingAs($this->penyidik)
        ->get(route('pencarian-perkara.index', ['search' => '6371012345670001']));

    $resNik->assertOk();
    $resNik->assertSee('BP/001/X/2026');
    $resNik->assertDontSee('BP/002/X/2026');

    // 2. Cari berdasarkan kata kunci deskripsi perkara 'merkuri'
    $resMerkuri = $this->actingAs($this->penyidik)
        ->get(route('pencarian-perkara.index', ['search' => 'merkuri']));

    $resMerkuri->assertOk();
    $resMerkuri->assertSee('BP/002/X/2026');
    $resMerkuri->assertDontSee('BP/001/X/2026');
});

test('filter status proses, formal, dan DPO aktif memfilter hasil pencarian dengan tepat', function () {
    // Filter DPO Aktif
    $resDpo = $this->actingAs($this->penyidik)
        ->get(route('pencarian-perkara.index', ['is_dpo' => '1']));

    $resDpo->assertOk();
    $resDpo->assertSee('BP/001/X/2026');
    $resDpo->assertDontSee('BP/002/X/2026');

    // Filter Status Proses 'TAHAP II'
    $resTahap2 = $this->actingAs($this->penyidik)
        ->get(route('pencarian-perkara.index', ['status_proses' => 'TAHAP II']));

    $resTahap2->assertOk();
    $resTahap2->assertSee('BP/002/X/2026');
    $resTahap2->assertDontSee('BP/001/X/2026');

    // Filter Status Formal 'selesai'
    $resFormalSelesai = $this->actingAs($this->penyidik)
        ->get(route('pencarian-perkara.index', ['status_formal' => 'selesai']));

    $resFormalSelesai->assertOk();
    $resFormalSelesai->assertSee('BP/002/X/2026');
    $resFormalSelesai->assertDontSee('BP/001/X/2026');
});

test('shortcut preset chips pada pencarian perkara berfungsi dengan baik', function () {
    // Preset 'dpo'
    $resPresetDpo = $this->actingAs($this->penyidik)
        ->get(route('pencarian-perkara.index', ['preset' => 'dpo']));

    $resPresetDpo->assertOk();
    $resPresetDpo->assertSee('BP/001/X/2026');
    $resPresetDpo->assertDontSee('BP/002/X/2026');

    // Preset 'selesai'
    $resPresetSelesai = $this->actingAs($this->penyidik)
        ->get(route('pencarian-perkara.index', ['preset' => 'selesai']));

    $resPresetSelesai->assertOk();
    $resPresetSelesai->assertSee('BP/002/X/2026');
    $resPresetSelesai->assertDontSee('BP/001/X/2026');
});

test('halaman pencarian perkara merender partial tabel saat request ajax X-Filter-Partial', function () {
    $response = $this->actingAs($this->penyidik)
        ->withHeaders(['X-Filter-Partial' => '1'])
        ->get(route('pencarian-perkara.index'));

    $response->assertOk();
    $response->assertViewIs('pages.pencarian-perkara._table-partial');
    $response->assertSee('Hasil Pencarian');
    $response->assertSee('BP/001/X/2026');
});

test('menu helper navigasi memuat menu Seluruh Perkara dan Pencarian Perkara dengan icon valid', function () {
    $allCasesIcon = MenuHelper::getIconSvg('all-cases');
    expect($allCasesIcon)->toContain('fi fi-rr-layers');

    $searchCasesIcon = MenuHelper::getIconSvg('search-cases');
    expect($searchCasesIcon)->toContain('fi fi-rr-search');

    $pimpinanGroups = MenuHelper::getMenuGroups();
    // Default guest / pimpinan test
    $pimpinanUser = User::factory()->pimpinan()->make();
    $this->actingAs($pimpinanUser);
    $pimpinanItems = MenuHelper::getMenuGroups()[0]['items'];
    $paths = array_column($pimpinanItems, 'path');
    expect($paths)->toContain('/pencarian-perkara');
});
