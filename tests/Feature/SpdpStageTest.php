<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->user->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Budi Santoso',
        'created_by' => $this->user->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/BPOM',
        'created_by' => $this->user->id,
    ]);
});

test('halaman spdp dapat diakses oleh user terautentikasi dan menampilkan data riil', function () {
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'B-001/SPDP/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'locked_at' => now(),
        'finalized_by' => $this->user->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('spdp.index'));

    $response->assertOk()
        ->assertSee('SPDP &amp; Sub-Tahap Penyidikan', false)
        ->assertSee('B-001/SPDP/X/2026')
        ->assertSee('BP/001/X/2026/BPOM')
        ->assertSee('Budi Santoso');
});

test('tab filter sub-tahap memfilter data spdp, p16, dan p17p secara akurat', function () {
    $spdp = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'B-SPDP-001/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'locked_at' => now(),
        'created_by' => $this->user->id,
    ]);

    $p16 = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::P16->value,
        'nomor_surat' => 'B-P16-002/X/2026',
        'tanggal_surat' => '2026-10-06',
        'status' => 'final',
        'locked_at' => now(),
        'created_by' => $this->user->id,
    ]);

    // Request Semua (default)
    $responseAll = $this->actingAs($this->user)->get(route('spdp.index'));
    $responseAll->assertOk()
        ->assertSee('B-SPDP-001/X/2026')
        ->assertSee('B-P16-002/X/2026');

    // Request filter sub_stage=spdp
    $responseSpdp = $this->actingAs($this->user)->get(route('spdp.index', ['sub_stage' => 'spdp']));
    $responseSpdp->assertOk()
        ->assertSee('B-SPDP-001/X/2026')
        ->assertDontSee('B-P16-002/X/2026');

    // Request filter sub_stage=p16
    $responseP16 = $this->actingAs($this->user)->get(route('spdp.index', ['sub_stage' => 'p16']));
    $responseP16->assertOk()
        ->assertDontSee('B-SPDP-001/X/2026')
        ->assertSee('B-P16-002/X/2026');
});

test('request ajax dengan header X-Filter-Partial mengembalikan partial tabel spdp', function () {
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'B-PARTIAL-001/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'locked_at' => now(),
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('spdp.index'), [
        'X-Filter-Partial' => '1',
    ]);

    $response->assertOk()
        ->assertSee('B-PARTIAL-001/X/2026')
        ->assertDontSee('<!DOCTYPE html>', false);
});

test('filter pencarian dan tanggal berfungsi pada daftar spdp', function () {
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'B-SEARCH-999/X/2026',
        'tanggal_surat' => '2026-10-10',
        'status' => 'final',
        'locked_at' => now(),
        'created_by' => $this->user->id,
    ]);

    // Search query matches
    $resSearch = $this->actingAs($this->user)->get(route('spdp.index', ['search' => '999']));
    $resSearch->assertOk()->assertSee('B-SEARCH-999/X/2026');

    // Search query doesn't match
    $resNotFound = $this->actingAs($this->user)->get(route('spdp.index', ['search' => 'NOMOR_TIDAK_ADA']));
    $resNotFound->assertOk()->assertDontSee('B-SEARCH-999/X/2026');

    // Date filter
    $resDate = $this->actingAs($this->user)->get(route('spdp.index', ['tanggal_dari' => '2026-10-15']));
    $resDate->assertOk()->assertDontSee('B-SEARCH-999/X/2026');
});

test('route spdp.create mengalihkan ke index jika tanpa berkas_id atau redirect ke form jika ada berkas_id', function () {
    // Tanpa berkas_id -> redirect ke halaman index SPDP
    $resNoBerkas = $this->actingAs($this->user)->get(route('spdp.create'));
    $resNoBerkas->assertRedirect(route('spdp.index'));

    // Dengan berkas_id -> redirect ke berkas-perkara.tahapan.create
    $resWithBerkas = $this->actingAs($this->user)->get(route('spdp.create', ['berkas_id' => $this->berkas->id]));
    $resWithBerkas->assertRedirect(route('berkas-perkara.tahapan.create', [
        'berkas' => $this->berkas,
        'jenis' => 'spdp',
    ]));
});

test('route spdp.show menampilkan halaman detail spdp', function () {
    $tahap = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'B-SHOW-001/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'draft',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('spdp.show', $tahap->id));

    $response->assertRedirect(route('berkas-perkara.tahapan.show', [
        'berkas' => $this->berkas->id,
        'tahapan' => $tahap->id,
    ]));
});

test('modal tambah spdp hanya memuat berkas perkara yang siap memulai spdp', function () {
    // $this->berkas belum punya SPDP, sehingga harus muncul di modal
    $response = $this->actingAs($this->user)->get(route('spdp.index'));
    $response->assertOk()
        ->assertSee('Pilih Berkas Perkara untuk SPDP')
        ->assertSee($this->berkas->nomor_berkas);

    // Setelah dibuatkan SPDP, berkas tidak boleh muncul lagi di modal
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'B-SPDP-EXISTING/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'locked_at' => now(),
        'created_by' => $this->user->id,
    ]);

    $responseAfter = $this->actingAs($this->user)->get(route('spdp.index'));
    $responseAfter->assertOk()
        ->assertSee('Tidak Ada Berkas yang Siap');
});
