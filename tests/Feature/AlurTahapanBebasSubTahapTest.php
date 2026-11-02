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
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('private');

    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $this->umum = User::factory()->create(['role' => 'umum']);

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
        'nama' => 'Ahmad Subari',
        'nik' => '6371012345670002',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/TEST',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tahapanService = app(TahapanService::class);
});

test('alur tahapan utama berjalan linier murni: SPDP -> Tahap 1 -> P-21 -> Tahap 2 -> Keputusan Pengadilan', function () {
    // 1. Berkas baru -> hanya SPDP yang tersedia
    expect($this->tahapanService->getNextAvailableStages($this->berkas))->toBe([JenisTahap::SPDP]);

    // Finalkan SPDP
    $spdp = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/01/2026',
        'tanggal_surat' => '2026-10-03',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas->refresh();

    // Setelah SPDP, langsung Tahap 1 (sub-tahap P-16/P-17P tidak menginterupsi alur utama)
    expect($this->tahapanService->getNextAvailableStages($this->berkas))->toBe([JenisTahap::TAHAP_1]);

    // Finalkan Tahap 1
    $tahap1 = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'nomor_surat' => 'THP1/01/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas->refresh();

    // Setelah Tahap 1, langsung P-21 (sub-tahap P-18P/P-19 tidak menginterupsi alur utama)
    expect($this->tahapanService->getNextAvailableStages($this->berkas))->toBe([JenisTahap::P21]);

    // Finalkan P-21
    $p21 = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::P21,
        'nomor_surat' => 'P21/01/2026',
        'tanggal_surat' => '2026-10-10',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas->refresh();

    // Setelah P-21, langsung Tahap 2
    expect($this->tahapanService->getNextAvailableStages($this->berkas))->toBe([JenisTahap::TAHAP_2]);

    // Finalkan Tahap 2
    $tahap2 = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'nomor_surat' => 'THP2/01/2026',
        'tanggal_surat' => '2026-10-15',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas->refresh();

    // Setelah Tahap 2, langsung Keputusan Pengadilan
    expect($this->tahapanService->getNextAvailableStages($this->berkas))->toBe([JenisTahap::KEPUTUSAN_PENGADILAN]);

    // Finalkan Keputusan Pengadilan
    $putusan = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN,
        'nomor_surat' => 'PUT/01/2026',
        'tanggal_surat' => '2026-10-20',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas->refresh();

    // Setelah Putusan Pengadilan, tidak ada tahapan lagi
    expect($this->tahapanService->getNextAvailableStages($this->berkas))->toBeEmpty();
});

test('tombol input tahap selanjutnya pada halaman detail tahapan mengarah ke tahapan utama berikutnya', function () {
    // 1. SPDP final
    $spdp = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/01/2026',
        'tanggal_surat' => '2026-10-03',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)->get(route('berkas-perkara.tahapan.show', [$this->berkas, $spdp]));
    $response->assertOk();
    // Tombol tahap selanjutnya harus mengarah ke create dengan jenis tahap_1
    $targetUrl = route('berkas-perkara.tahapan.create', [
        'berkas' => $this->berkas->id,
        'jenis' => JenisTahap::TAHAP_1->value,
    ]);
    $response->assertSee($targetUrl, false);
});

test('sub-tahap P-16 dan P-17P dapat disimpan mandiri melalui endpoint sub-tahap', function () {
    // SPDP final
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/01/2026',
        'tanggal_surat' => '2026-10-03',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $pdfFileP16 = UploadedFile::fake()->create('p16.pdf', 200, 'application/pdf');

    $response = $this->actingAs($this->penyidik)
        ->from(route('berkas-perkara.tahapan.show', [$this->berkas, $this->berkas->tahapan()->first()]))
        ->post(route('berkas-perkara.tahapan.sub-tahap', $this->berkas), [
            'sub_tahap_group' => 'spdp',
            'p16_nomor_surat' => 'B-P16/001/2026',
            'p16_tanggal_surat' => '2026-10-04',
            'p16_dokumen' => $pdfFileP16,
            'p17p_nomor_surat' => 'B-P17P/001/2026',
            'p17p_tanggal_surat' => '2026-10-05',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Pastikan record P16 dan P17P tersimpan di database
    $p16 = TahapanPerkara::where('berkas_perkara_id', $this->berkas->id)
        ->where('jenis_tahap', JenisTahap::P16)
        ->first();

    expect($p16)->not->toBeNull()
        ->and($p16->nomor_surat)->toBe('B-P16/001/2026')
        ->and($p16->status)->toBe('final')
        ->and($p16->dokumen()->exists())->toBeTrue();

    $p17p = TahapanPerkara::where('berkas_perkara_id', $this->berkas->id)
        ->where('jenis_tahap', JenisTahap::P17P)
        ->first();

    expect($p17p)->not->toBeNull()
        ->and($p17p->nomor_surat)->toBe('B-P17P/001/2026')
        ->and($p17p->status)->toBe('final');

    // Verifikasi alur utama tetap mengarah ke Tahap 1
    expect($this->tahapanService->getNextAvailableStages($this->berkas->fresh()))->toBe([JenisTahap::TAHAP_1]);
});

test('sub-tahap P-18P dan P-19 dapat disimpan mandiri setelah Tahap 1 difinalkan', function () {
    // SPDP & Tahap 1 final
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/01/2026',
        'tanggal_surat' => '2026-10-03',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $tahap1 = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'nomor_surat' => 'THP1/01/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.sub-tahap', $this->berkas), [
            'sub_tahap_group' => 'tahap_1',
            'p18p_nomor_surat' => 'B-P18P/001/2026',
            'p18p_tanggal_surat' => '2026-10-06',
            'p19_nomor_surat' => 'B-P19/001/2026',
            'p19_tanggal_surat' => '2026-10-07',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $p18p = TahapanPerkara::where('berkas_perkara_id', $this->berkas->id)
        ->where('jenis_tahap', JenisTahap::P18P)
        ->first();

    $p19 = TahapanPerkara::where('berkas_perkara_id', $this->berkas->id)
        ->where('jenis_tahap', JenisTahap::P19)
        ->first();

    expect($p18p)->not->toBeNull()
        ->and($p18p->nomor_surat)->toBe('B-P18P/001/2026')
        ->and($p19)->not->toBeNull()
        ->and($p19->nomor_surat)->toBe('B-P19/001/2026');

    // Verifikasi alur utama tetap mengarah ke P-21
    expect($this->tahapanService->getNextAvailableStages($this->berkas->fresh()))->toBe([JenisTahap::P21]);
});

test('pengguna role umum diblokir dari aksi simpan sub-tahap', function () {
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/01/2026',
        'tanggal_surat' => '2026-10-03',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->umum)
        ->post(route('berkas-perkara.tahapan.sub-tahap', $this->berkas), [
            'sub_tahap_group' => 'spdp',
            'p16_nomor_surat' => 'B-P16/001/2026',
            'p16_tanggal_surat' => '2026-10-04',
        ]);

    $response->assertForbidden();
});
