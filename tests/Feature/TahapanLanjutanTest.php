<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\TahapanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $this->pimpinan = User::factory()->create(['role' => 'pimpinan']);

    $this->perkara = Perkara::create([
        'nomor_laporan' => 'LK/001/X/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_sprindik' => 'SP/001/X/2026',
        'tanggal_sprindik' => '2026-10-02',
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
        'created_by' => $this->penyidik->id,
    ]);

    $this->tahapanService = app(TahapanService::class);
});

test('halaman tahap-1, p21, tahap-2, dan putusan-perkara dapat diakses dan merender partial AJAX', function () {
    // 1. /tahap-1
    $response = $this->actingAs($this->penyidik)->get(route('tahap-1.index'));
    $response->assertOk()->assertSee('Tahap 1');

    $ajaxResponse = $this->actingAs($this->penyidik)->get(route('tahap-1.index'), ['X-Filter-Partial' => '1']);
    $ajaxResponse->assertOk()->assertViewIs('pages.tahap-1._table-partial');

    // 2. /p21
    $response = $this->actingAs($this->penyidik)->get(route('p21.index'));
    $response->assertOk()->assertSee('P-21 / P-21a');

    $ajaxResponse = $this->actingAs($this->penyidik)->get(route('p21.index'), ['X-Filter-Partial' => '1']);
    $ajaxResponse->assertOk()->assertViewIs('pages.p21._table-partial');

    // 3. /tahap-2
    $response = $this->actingAs($this->penyidik)->get(route('tahap-2.index'));
    $response->assertOk()->assertSee('Tahap 2');

    $ajaxResponse = $this->actingAs($this->penyidik)->get(route('tahap-2.index'), ['X-Filter-Partial' => '1']);
    $ajaxResponse->assertOk()->assertViewIs('pages.tahap-2._table-partial');

    // 4. /putusan-perkara
    $response = $this->actingAs($this->penyidik)->get(route('putusan-perkara.index'));
    $response->assertOk()->assertSee('Putusan Perkara');

    $ajaxResponse = $this->actingAs($this->penyidik)->get(route('putusan-perkara.index'), ['X-Filter-Partial' => '1']);
    $ajaxResponse->assertOk()->assertViewIs('pages.putusan-perkara._table-partial');
});

test('penyimpan SPDP sekaligus menyimpan sub-tahap P-16 dan P-17P dalam satu form', function () {
    $pdfMain = UploadedFile::fake()->create('spdp.pdf', 500, 'application/pdf');
    $pdfP16 = UploadedFile::fake()->create('p16.pdf', 300, 'application/pdf');

    $response = $this->actingAs($this->penyidik)->post(route('berkas-perkara.tahapan.store', $this->berkas), [
        'jenis_tahap' => 'spdp',
        'nomor_surat' => 'SPDP/100/X/2026',
        'tanggal_surat' => '2026-10-05',
        'catatan' => 'SPDP utama',
        'action_type' => 'lanjut',
        'dokumen' => $pdfMain,

        // Sub-tahap P-16
        'p16_nomor_surat' => 'PRINT-12/O.3.10/Fd.1/10/2026',
        'p16_tanggal_surat' => '2026-10-07',
        'p16_catatan' => 'JPU Ahmad S.H.',
        'p16_dokumen' => $pdfP16,

        // Sub-tahap P-17P
        'p17p_nomor_surat' => 'B-450/O.3.10/Fd.1/10/2026',
        'p17p_tanggal_surat' => '2026-10-10',
        'p17p_catatan' => 'Permintaan perkembangan',
    ]);

    $response->assertSessionHasNoErrors();

    // Verifikasi SPDP tersimpan final
    $this->assertDatabaseHas('tahapan_perkara', [
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => 'spdp',
        'nomor_surat' => 'SPDP/100/X/2026',
        'status' => 'final',
    ]);

    // Verifikasi P-16 tersimpan
    $this->assertDatabaseHas('tahapan_perkara', [
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => 'p16',
        'nomor_surat' => 'PRINT-12/O.3.10/Fd.1/10/2026',
        'catatan' => 'JPU Ahmad S.H.',
    ]);

    // Verifikasi P-17P tersimpan
    $this->assertDatabaseHas('tahapan_perkara', [
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => 'p17p',
        'nomor_surat' => 'B-450/O.3.10/Fd.1/10/2026',
    ]);

    // Berkas progress naik ke 15%
    $this->berkas->refresh();
    expect($this->tahapanService->calculateProgress($this->berkas))->toBe(15);
});

test('penyimpan Tahap 1 sekaligus menyimpan sub-tahap P-18 dan P-19 dalam satu form', function () {
    // Siapkan SPDP final terlebih dahulu
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/100/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)->post(route('berkas-perkara.tahapan.store', $this->berkas), [
        'jenis_tahap' => 'tahap_1',
        'nomor_surat' => 'BAP/01/X/2026',
        'tanggal_surat' => '2026-10-12',
        'dokumen' => UploadedFile::fake()->create('tahap1.pdf', 300, 'application/pdf'),
        'action_type' => 'lanjut',

        // Sub-tahap P-18
        'p18p_nomor_surat' => 'B-512/O.3.10/Fd.1/10/2026',
        'p18p_tanggal_surat' => '2026-10-15',
        'p18p_catatan' => 'Berkas kurang saksi ahli',

        // Sub-tahap P-19
        'p19_nomor_surat' => 'B-602/O.3.10/Fd.1/10/2026',
        'p19_tanggal_surat' => '2026-10-16',
        'p19_catatan' => 'Petunjuk pemeriksaan lab',
    ]);

    $response->assertSessionHasNoErrors();

    // Verifikasi Tahap 1 tersimpan final
    $this->assertDatabaseHas('tahapan_perkara', [
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => 'tahap_1',
        'nomor_surat' => 'BAP/01/X/2026',
        'status' => 'final',
    ]);

    // Verifikasi P-18 tersimpan
    $this->assertDatabaseHas('tahapan_perkara', [
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => 'p18p',
        'nomor_surat' => 'B-512/O.3.10/Fd.1/10/2026',
    ]);

    // Verifikasi P-19 tersimpan
    $this->assertDatabaseHas('tahapan_perkara', [
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => 'p19',
        'nomor_surat' => 'B-602/O.3.10/Fd.1/10/2026',
    ]);

    // Progress naik ke 55%
    $this->berkas->refresh();
    expect($this->tahapanService->calculateProgress($this->berkas))->toBe(55);
});

test('finalisasi Tahap 2 memicu status formal Selesai dan progress 100 persen', function () {
    // Siapkan tahapan hingga P-21
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/100/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'nomor_surat' => 'BAP/01/X/2026',
        'tanggal_surat' => '2026-10-12',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::P21,
        'nomor_surat' => 'P21/01/X/2026',
        'tanggal_surat' => '2026-10-20',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    // Simpan & Lanjut Tahap 2
    $response = $this->actingAs($this->penyidik)->post(route('berkas-perkara.tahapan.store', $this->berkas), [
        'jenis_tahap' => 'tahap_2',
        'nomor_surat' => 'BA-PELIMPAHAN/01/X/2026',
        'tanggal_surat' => '2026-10-25',
        'dokumen' => UploadedFile::fake()->create('tahap2.pdf', 300, 'application/pdf'),
        'action_type' => 'lanjut',
    ]);

    $response->assertSessionHasNoErrors();

    $this->berkas->refresh();
    expect($this->berkas->status_formal)->toBe('selesai');
    expect($this->tahapanService->calculateProgress($this->berkas))->toBe(100);
});

test('dpo gate memblokir simpan dan lanjut pada Tahap 2 ketika tersangka berstatus DPO', function () {
    // Siapkan tahapan hingga P-21
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'status' => 'final',
        'nomor_surat' => 'SPDP-1',
        'tanggal_surat' => '2026-10-01',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'status' => 'final',
        'nomor_surat' => 'T1-1',
        'tanggal_surat' => '2026-10-05',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::P21,
        'status' => 'final',
        'nomor_surat' => 'P21-1',
        'tanggal_surat' => '2026-10-10',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    // Set tersangka sebagai DPO
    $this->berkas->update(['is_dpo' => true]);

    // Percobaan Simpan & Lanjut Tahap 2 ditolak oleh DPO Gate
    expect(function () {
        $this->tahapanService->saveStage($this->berkas, [
            'jenis_tahap' => JenisTahap::TAHAP_2,
            'nomor_surat' => 'PELIMPAHAN-DPO',
            'tanggal_surat' => '2026-10-12',
            'action_type' => 'lanjut',
        ], null, $this->penyidik);
    })->toThrow(DomainException::class, 'berstatus DPO');

    // Namun menyimpan sebagai DRAFT tetap diizinkan
    $draft = $this->tahapanService->saveStage($this->berkas, [
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'nomor_surat' => 'DRAFT-PELIMPAHAN',
        'action_type' => 'draft',
    ], null, $this->penyidik);

    expect($draft->isDraft())->toBeTrue();
});
