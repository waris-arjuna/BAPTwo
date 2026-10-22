<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders clickable links for previous stages in Referensi Tahapan Sebelumnya card', function () {
    $penyidik = User::factory()->penyidik()->create();

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/NAV-01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/NAV-01/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Navigasi',
        'nik' => '6371010101900001',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/NAV-01/2026',
        'status_formal' => null,
        'status_proses' => 'Baru',
        'progress_persen' => 0,
    ]);

    // 1. Saat di form tambah SPDP: LK dan Sprindik harus berupa link aktif
    $response = $this->actingAs($penyidik)->get(route('berkas-perkara.tahapan.create', [
        'berkas' => $berkas,
        'jenis' => 'spdp',
    ]));

    $response->assertOk()
        ->assertSee('Referensi Tahapan Sebelumnya')
        ->assertSee(route('laporan-kejadian.show', $perkara))
        ->assertSee('LK/NAV-01/2026')
        ->assertSee(route('sprindik.show', $perkara))
        ->assertSee('SP/NAV-01/2026');

    // 2. Tambahkan tahapan SPDP final
    $spdp = TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/NAV-01/X/2026',
        'tanggal_surat' => '2026-10-12',
        'status' => 'final',
        'finalized_at' => now(),
    ]);

    // Tambahkan tahapan Tahap 1 draft
    $tahap1 = TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'nomor_surat' => 'T1/NAV-01/X/2026',
        'tanggal_surat' => '2026-10-15',
        'status' => 'draft',
    ]);

    // 3. Saat melihat detail atau mengedit tahapan Tahap 1:
    // Nomor SPDP pada card Referensi harus berupa link aktif ke berkas-perkara.tahapan.show
    $expectedSpdpUrl = route('berkas-perkara.tahapan.show', [$berkas, $spdp]);

    $responseShow = $this->actingAs($penyidik)->get(route('berkas-perkara.tahapan.show', [
        'berkas' => $berkas,
        'tahapan' => $tahap1,
    ]));

    $responseShow->assertOk()
        ->assertSee('Referensi Tahapan Sebelumnya')
        ->assertSee($expectedSpdpUrl)
        ->assertSee('SPDP/NAV-01/X/2026');

    $responseEdit = $this->actingAs($penyidik)->get(route('berkas-perkara.tahapan.edit', [
        'berkas' => $berkas,
        'tahapan' => $tahap1,
    ]));

    $responseEdit->assertOk()
        ->assertSee('Referensi Tahapan Sebelumnya')
        ->assertSee($expectedSpdpUrl)
        ->assertSee('SPDP/NAV-01/X/2026');
});
