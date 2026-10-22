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

    // Kasus 1: Berkas Siap Pelimpahan Tahap II (P-21 / 85%)
    $this->perkara1 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/011/X/2026/BPOM',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'nomor_surat_perintah' => 'SP.Gas/011/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_laporan_status' => 'lanjut',
        'tahap_sprindik_status' => 'lanjut',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    $this->perkara1->ppnsUsers()->attach($this->penyidik->id, ['peran' => 'utama']);

    $this->tersangka1 = Tersangka::create([
        'perkara_id' => $this->perkara1->id,
        'nama' => 'Ahmad Suhendra',
        'nik' => '6371012345671111',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas1 = BerkasPerkara::create([
        'perkara_id' => $this->perkara1->id,
        'tersangka_id' => $this->tersangka1->id,
        'nomor_berkas' => 'BP/011/X/2026',
        'status_proses' => 'P-21/P21a',
        'progress_persen' => 85,
        'is_dpo' => false,
        'tanggal_spdp' => '2026-10-03',
        'created_by' => $this->penyidik->id,
    ]);

    // Kasus 2: Berkas Terkendala DPO (Tahap I / 55%, DPO Aktif)
    $this->perkara2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/012/X/2026/BPOM',
        'tanggal_laporan_kejadian' => '2026-10-02',
        'nomor_surat_perintah' => 'SP.Gas/012/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
        'tahap_laporan_status' => 'lanjut',
        'tahap_sprindik_status' => 'lanjut',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka2 = Tersangka::create([
        'perkara_id' => $this->perkara2->id,
        'nama' => 'DPO Pelarian',
        'nik' => '6371012345672222',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas2 = BerkasPerkara::create([
        'perkara_id' => $this->perkara2->id,
        'tersangka_id' => $this->tersangka2->id,
        'nomor_berkas' => 'BP/012/X/2026',
        'status_proses' => 'TAHAP I',
        'progress_persen' => 55,
        'is_dpo' => true,
        'tanggal_spdp' => '2026-10-04',
        'created_by' => $this->penyidik->id,
    ]);

    // Kasus 3: Berkas yang Sudah Selesai (Tidak boleh muncul di potensi terlimpah)
    $this->perkara3 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/013/X/2026/BPOM',
        'tanggal_laporan_kejadian' => '2026-10-03',
        'tahap_laporan_status' => 'lanjut',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka3 = Tersangka::create([
        'perkara_id' => $this->perkara3->id,
        'nama' => 'Kasus Selesai',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas3 = BerkasPerkara::create([
        'perkara_id' => $this->perkara3->id,
        'tersangka_id' => $this->tersangka3->id,
        'nomor_berkas' => 'BP/013/X/2026',
        'status_proses' => 'TAHAP II',
        'progress_persen' => 100,
        'status_formal' => 'selesai',
        'is_dpo' => false,
        'created_by' => $this->penyidik->id,
    ]);
});

test('halaman potensi terlimpah dapat diakses oleh seluruh peran dan hanya menampilkan berkas aktif', function () {
    // Penyidik
    $this->actingAs($this->penyidik)
        ->get(route('potensi-terlimpah.index'))
        ->assertOk()
        ->assertSee('Monitoring Potensi Terlimpah')
        ->assertSee('BP/011/X/2026')
        ->assertSee('BP/012/X/2026')
        ->assertDontSee('BP/013/X/2026'); // Berkas yang sudah selesai dikecualikan

    // Pimpinan
    $this->actingAs($this->pimpinan)
        ->get(route('potensi-terlimpah.index'))
        ->assertOk()
        ->assertSee('Peringatan Dini Batas Waktu &amp; Pelimpahan Akhir Tahun', false);

    // Administrator
    $this->actingAs($this->admin)
        ->get(route('potensi-terlimpah.index'))
        ->assertOk();
});

test('filter kategori urgensi siap_tahap2 menampilkan berkas dengan progress minimal 55 persen', function () {
    $response = $this->actingAs($this->penyidik)
        ->get(route('potensi-terlimpah.index', ['kategori' => 'siap_tahap2']));

    $response->assertOk();
    $response->assertSee('BP/011/X/2026'); // 85%
    $response->assertSee('BP/012/X/2026'); // 55%
});

test('filter kategori dpo menampilkan berkas yang tersangkanya berstatus DPO', function () {
    $response = $this->actingAs($this->penyidik)
        ->get(route('potensi-terlimpah.index', ['kategori' => 'dpo']));

    $response->assertOk();
    $response->assertSee('BP/012/X/2026');
    $response->assertDontSee('BP/011/X/2026');
});

test('halaman potensi terlimpah merender partial tabel saat request ajax X-Filter-Partial', function () {
    $response = $this->actingAs($this->penyidik)
        ->withHeaders(['X-Filter-Partial' => '1'])
        ->get(route('potensi-terlimpah.index'));

    $response->assertOk();
    $response->assertViewIs('pages.potensi-terlimpah._table-partial');
    $response->assertSee('BP/011/X/2026');
});

test('menu navigasi data perkara memuat submenu Potensi Terlimpah', function () {
    $mainItems = MenuHelper::getMainNavItems();
    $dataPerkaraItem = collect($mainItems)->firstWhere('name', 'Data Perkara');

    expect($dataPerkaraItem)->not->toBeNull();
    $subPaths = array_column($dataPerkaraItem['subItems'], 'path');

    expect($subPaths)->toContain('/potensi-terlimpah');
});
