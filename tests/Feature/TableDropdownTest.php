<?php

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->penyidik()->create();
    $this->admin = User::factory()->administrator()->create();

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Budi Santoso',
        'nik' => '6371012345670001',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/BPOM',
        'progress_persen' => 15,
        'status_proses' => 'SPDP',
        'status_formal' => null,
        'tahun' => (int) date('Y'),
        'created_by' => $this->penyidik->id,
    ]);
});

it('renders table-dropdown component with portal to body and auto-flip logic', function () {
    $view = $this->blade(
        '<x-common.table-dropdown label="Opsi">
            <a href="/test">Link Aksi</a>
        </x-common.table-dropdown>'
    );

    $view->assertSee('Opsi')
        ->assertSee('x-teleport="body"', false)
        ->assertSee('openUp = spaceBelow', false)
        ->assertSee('window.addEventListener(\'scroll\', this.scrollListener, true)', false)
        ->assertSee('Link Aksi')
        ->assertSee('fi fi-rr-angle-small-down', false);
});

it('renders reusable table dropdown across Laporan Kejadian, Sprindik, Data Perkara, and Dashboard tables', function () {
    // 1. Laporan Kejadian
    $this->actingAs($this->penyidik)
        ->get(route('laporan-kejadian.index'))
        ->assertOk()
        ->assertSee('x-teleport="body"', false)
        ->assertSee('Aksi')
        ->assertSee('Lihat')
        ->assertSee('Lihat Sprindik');

    // 2. Sprindik
    $this->actingAs($this->penyidik)
        ->get(route('sprindik.index'))
        ->assertOk()
        ->assertSee('x-teleport="body"', false)
        ->assertSee('Aksi')
        ->assertSee('Lihat Perkara');

    // 3. Data Perkara
    $this->actingAs($this->penyidik)
        ->get(route('data-perkara.index'))
        ->assertOk()
        ->assertSee('x-teleport="body"', false)
        ->assertSee('Aksi')
        ->assertSee('Lihat Detail');

    // 4. Dashboard Monitoring
    $this->actingAs($this->penyidik)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('x-teleport="body"', false)
        ->assertSee('Ringkasan Cepat')
        ->assertSee('Lihat Perkara');

    // 5. Progress Perkara
    $this->actingAs($this->penyidik)
        ->get(route('progress-perkara.index'))
        ->assertOk()
        ->assertSee('x-teleport="body"', false)
        ->assertSee('Lihat Detail Perkara');
});
