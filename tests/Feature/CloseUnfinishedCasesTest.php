<?php

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2025',
        'tanggal_laporan_kejadian' => '2025-05-10',
        'tahap_laporan_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka1 = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Tahun Lalu 1',
        'nik' => '6371012345670001',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka2 = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Selesai',
        'nik' => '6371012345670002',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka3 = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Tahun Ini',
        'nik' => '6371012345670003',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 1: SPDP tahun 2025, belum selesai (status_formal = null)
    $this->berkasTahunLalu = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka1->id,
        'nomor_berkas' => 'BP/001/V/2025/BPOM',
        'tanggal_spdp' => '2025-06-01',
        'status_formal' => null,
        'created_at' => Carbon::create(2025, 6, 1, 10, 0, 0),
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 2: SPDP tahun 2025, sudah SELESAI
    $this->berkasSelesai = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka2->id,
        'nomor_berkas' => 'BP/002/V/2025/BPOM',
        'tanggal_spdp' => '2025-06-01',
        'status_formal' => 'selesai',
        'created_at' => Carbon::create(2025, 6, 1, 10, 0, 0),
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 3: SPDP tahun sekarang (2026), belum selesai
    $this->berkasTahunIni = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka3->id,
        'nomor_berkas' => 'BP/003/V/2026/BPOM',
        'tanggal_spdp' => Carbon::now()->toDateString(),
        'status_formal' => null,
        'created_by' => $this->penyidik->id,
    ]);
});

test('command perkara:close-unfinished menutup berkas tahun sebelumnya menjadi Tidak Selesai dan mencatat audit log sistem', function () {
    $this->artisan('perkara:close-unfinished', ['--year' => 2025])
        ->expectsOutputToContain('Ditemukan 1 berkas perkara yang belum selesai')
        ->assertSuccessful();

    $this->berkasTahunLalu->refresh();
    expect($this->berkasTahunLalu->status_formal)->toBe('tidak_selesai');

    // Berkas selesai tidak boleh disentuh
    $this->berkasSelesai->refresh();
    expect($this->berkasSelesai->status_formal)->toBe('selesai');

    // Berkas tahun ini tidak boleh disentuh
    $this->berkasTahunIni->refresh();
    expect($this->berkasTahunIni->status_formal)->toBeNull();

    // Audit log tersimpan dengan user_id null (sistem scheduler)
    $this->assertDatabaseHas('audit_log', [
        'berkas_perkara_id' => $this->berkasTahunLalu->id,
        'user_id' => null,
        'aksi' => 'sistem_tidak_selesai',
    ]);
});

test('command perkara:close-unfinished dengan opsi --dry-run tidak mengubah database', function () {
    $this->artisan('perkara:close-unfinished', ['--year' => 2025, '--dry-run' => true])
        ->expectsOutputToContain('Mode simulasi (--dry-run) aktif')
        ->assertSuccessful();

    $this->berkasTahunLalu->refresh();
    expect($this->berkasTahunLalu->status_formal)->toBeNull();

    $this->assertDatabaseMissing('audit_log', [
        'berkas_perkara_id' => $this->berkasTahunLalu->id,
        'aksi' => 'sistem_tidak_selesai',
    ]);
});

test('command perkara:close-unfinished default tahun berjalan menutup tahun sebelumnya', function () {
    // Jalankan tanpa --year, secara default menargetkan tahun lalu (current year - 1)
    $lastYear = Carbon::now()->year - 1;

    $this->artisan('perkara:close-unfinished')
        ->assertSuccessful();

    $this->berkasTahunIni->refresh();
    expect($this->berkasTahunIni->status_formal)->toBeNull();
});

test('command perkara:close-unfinished juga menutup berkas tahun sebelumnya yang belum berstatus formal', function () {
    $tersangka4 = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka 4',
        'nik' => '6371012345670004',
        'created_by' => $this->penyidik->id,
    ]);

    $berkasDalamProses = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $tersangka4->id,
        'nomor_berkas' => 'BP/DP/2025/BPOM',
        'tanggal_spdp' => '2025-07-01',
        'status_formal' => null,
        'created_at' => Carbon::create(2025, 7, 1, 10, 0, 0),
        'created_by' => $this->penyidik->id,
    ]);

    $this->artisan('perkara:close-unfinished', ['--year' => 2025])
        ->assertSuccessful();

    expect($berkasDalamProses->fresh()->status_formal)->toBe('tidak_selesai');
});
