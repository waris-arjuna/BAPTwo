<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\TahapanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('kalkulasi SLA tidak mengalami month overflow pada tanggal 31', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/TGL31/01',
        'nomor_surat_perintah' => 'SP/TGL31/01',
        'tanggal_surat_perintah' => '2026-01-30',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka 31', 'created_by' => $penyidik->id]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/TGL31/01',
        'created_by' => $penyidik->id,
    ]);

    $tahapanService = app(TahapanService::class);

    // SPDP pada 31 Januari 2026
    $tahapanService->saveStage($berkas, [
        'jenis_tahap' => JenisTahap::SPDP->value,
        'action_type' => 'lanjut',
        'nomor_surat' => 'SPDP/31JAN',
        'tanggal_surat' => '2026-01-31',
    ], null, $penyidik);

    $berkas->refresh();
    // Harus jatuh di 28 Februari 2026 (akhir bulan Februari), BUKAN 3 Maret 2026
    expect($berkas->sla_deadline->toDateString())->toBe('2026-02-28');
    expect($berkas->effective_deadline->toDateString())->toBe('2026-02-28');
});

test('deadline_keterangan mengembalikan Batas Tahun Mutlak 31 Des saat tahap aktif di bulan Desember', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DES/01',
        'nomor_surat_perintah' => 'SP/DES/01',
        'tanggal_surat_perintah' => '2026-12-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Des', 'created_by' => $penyidik->id]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/DES/01',
        'created_by' => $penyidik->id,
    ]);

    $tahapanService = app(TahapanService::class);

    // SPDP pada 15 Desember 2026
    $tahapanService->saveStage($berkas, [
        'jenis_tahap' => JenisTahap::SPDP->value,
        'action_type' => 'lanjut',
        'nomor_surat' => 'SPDP/15DES',
        'tanggal_surat' => '2026-12-15',
    ], null, $penyidik);

    $berkas->refresh();
    expect($berkas->sla_deadline->toDateString())->toBe('2026-12-31');
    expect($berkas->deadline_keterangan)->toBe('Batas Tahun Mutlak 31 Des');

    // Berkas lain pada bulan Maret -> Tenggang Waktu 1 Bulan Tahap Aktif
    $tersangkaMaret = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Maret', 'created_by' => $penyidik->id]);
    $berkasMaret = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangkaMaret->id,
        'nomor_berkas' => 'BP/MAR/01',
        'created_by' => $penyidik->id,
    ]);
    $tahapanService->saveStage($berkasMaret, [
        'jenis_tahap' => JenisTahap::SPDP->value,
        'action_type' => 'lanjut',
        'nomor_surat' => 'SPDP/15MAR',
        'tanggal_surat' => '2026-03-15',
    ], null, $penyidik);

    $berkasMaret->refresh();
    expect($berkasMaret->deadline_keterangan)->toBe('Tenggang Waktu 1 Bulan Tahap Aktif');
});
