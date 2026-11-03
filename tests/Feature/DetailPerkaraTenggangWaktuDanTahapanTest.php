<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create([
        'role' => 'penyidik',
        'is_active' => true,
    ]);
});

test('halaman detail perkara menampilkan card tenggang waktu yang diperbesar dan istilah Tenggang Waktu', function () {
    Carbon::setTestNow('2026-10-25');

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/TW/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-20',
        'created_by' => $this->penyidik->id,
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Budi Santoso',
        'nik' => '6371012345678901',
        'jenis_kelamin' => 'laki-laki',
        'created_by' => $this->penyidik->id,
    ]);

    BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/TW/01/2026',
        'tanggal_spdp' => '2026-10-22',
        'sla_deadline' => '2026-11-22',
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)
        ->get(route('perkara.show', $perkara));

    $response->assertOk()
        ->assertSee('Tanggal Terbit SPDP')
        ->assertSee('22-10-2026')
        ->assertSee('Batas Waktu Penyelesaian')
        ->assertSee('22-11-2026')
        ->assertSee('(Tenggang Waktu 1 Bulan Tahap Aktif)')
        ->assertSee('Tenggang Waktu Berjalan')
        ->assertDontSee('SLA 1 Bulan Kalender');

    Carbon::setTestNow();
});

test('riwayat tahapan progress pada halaman detail perkara selalu berurutan SPDP -> TAHAP I -> P-21/P21a -> TAHAP II dengan ikon panah', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/URUT/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'created_by' => $this->penyidik->id,
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Andi Wijaya',
        'nik' => '6371012345678902',
        'jenis_kelamin' => 'laki-laki',
        'created_by' => $this->penyidik->id,
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/URUT/01/2026',
        'tanggal_spdp' => '2026-10-02',
        'sla_deadline' => '2026-11-02',
        'status_formal' => 'selesai',
        'status_proses' => 'TAHAP II',
        'progress_persen' => 100,
        'created_by' => $this->penyidik->id,
    ]);

    // Sengaja dibuat dengan urutan tidak berurutan untuk memastikan pengurutan bekerja
    foreach ([JenisTahap::P21, JenisTahap::TAHAP_2, JenisTahap::SPDP, JenisTahap::TAHAP_1] as $jenis) {
        TahapanPerkara::create([
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => $jenis,
            'nomor_surat' => 'SRT/'.$jenis->value,
            'tanggal_surat' => '2026-10-10',
            'status' => 'final',
            'finalized_at' => now(),
            'finalized_by' => $this->penyidik->id,
            'created_by' => $this->penyidik->id,
        ]);
    }

    $response = $this->actingAs($this->penyidik)
        ->get(route('perkara.show', $perkara));

    $response->assertOk()
        ->assertSeeInOrder([
            'Riwayat Tahapan Progress (4 Tahap)',
            'SPDP (Final)',
            'fi-rr-arrow-small-right',
            'TAHAP I (Final)',
            'fi-rr-arrow-small-right',
            'P-21/P21a (Final)',
            'fi-rr-arrow-small-right',
            'TAHAP II (Final)',
        ], false);
});
