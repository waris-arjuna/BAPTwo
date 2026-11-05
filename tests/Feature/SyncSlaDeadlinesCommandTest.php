<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('command perkara:sync-sla memperbarui sla_deadline yang salah akibat overflow tanggal 31', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SYNC/01',
        'nomor_surat_perintah' => 'SP/SYNC/01',
        'tanggal_surat_perintah' => '2026-01-31',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Sync', 'created_by' => $penyidik->id]);

    // Berkas dengan tanggal_spdp 31 Jan 2026 tetapi sla_deadline lama bernilai 2026-03-03 (overflow)
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/SYNC/01',
        'tanggal_spdp' => '2026-01-31',
        'sla_deadline' => '2026-03-03',
        'created_by' => $penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'SPDP/SYNC/01',
        'tanggal_surat' => '2026-01-31',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    // Jalankan command
    $this->artisan('perkara:sync-sla')
        ->assertSuccessful()
        ->expectsOutputToContain('Sinkronisasi Tenggang Waktu (SLA) Selesai');

    $berkas->refresh();
    expect($berkas->sla_deadline->toDateString())->toBe('2026-02-28');
});

test('command perkara:sync-sla mendukung opsi dry-run', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SYNC/02',
        'nomor_surat_perintah' => 'SP/SYNC/02',
        'tanggal_surat_perintah' => '2026-01-31',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Dry', 'created_by' => $penyidik->id]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/SYNC/02',
        'tanggal_spdp' => '2026-01-31',
        'sla_deadline' => '2026-03-03',
        'created_by' => $penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'SPDP/SYNC/02',
        'tanggal_surat' => '2026-01-31',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    $this->artisan('perkara:sync-sla --dry-run')
        ->assertSuccessful()
        ->expectsOutputToContain('DRY-RUN');

    $berkas->refresh();
    // Nilai tidak berubah karena dry-run
    expect($berkas->sla_deadline->toDateString())->toBe('2026-03-03');
});
