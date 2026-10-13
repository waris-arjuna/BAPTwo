<?php

use App\Enums\JenisTahap;
use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Dpo;
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
    $this->admin = User::factory()->create(['role' => 'administrator']);
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $this->pimpinan = User::factory()->create(['role' => 'pimpinan']);

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
        'nama' => 'Joko Susilo',
        'nik' => '6371012345670002',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/002/X/2026/BPOM',
        'created_by' => $this->penyidik->id,
    ]);
});

test('penyidik dan administrator dapat menetapkan status DPO dengan nomor surat dan dokumen PDF', function () {
    Storage::fake('local');
    $pdf = UploadedFile::fake()->create('surat_dpo.pdf', 500, 'application/pdf');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.store', $this->berkas), [
            'nomor_surat' => 'B/DPO/01/X/2026',
            'tanggal_penetapan' => '2026-10-14',
            'keterangan' => 'Tersangka melarikan diri saat pemanggilan kedua.',
            'dokumen' => $pdf,
        ]);

    $response->assertRedirect(route('perkara.show', $this->perkara));
    $response->assertSessionHas('success', 'Status DPO berhasil ditetapkan untuk tersangka.');

    $this->berkas->refresh();
    expect($this->berkas->is_dpo)->toBeTrue();

    $this->assertDatabaseHas('dpo', [
        'berkas_perkara_id' => $this->berkas->id,
        'nomor_surat' => 'B/DPO/01/X/2026',
        'status' => 'aktif',
        'keterangan' => 'Tersangka melarikan diri saat pemanggilan kedua.',
        'created_by' => $this->penyidik->id,
    ]);

    $this->assertDatabaseHas('dokumen', [
        'berkas_perkara_id' => $this->berkas->id,
        'kategori' => 'dpo',
        'nama_file_asli' => 'surat_dpo.pdf',
    ]);

    $this->assertDatabaseHas('audit_log', [
        'berkas_perkara_id' => $this->berkas->id,
        'aksi' => 'aktifkan_dpo',
        'user_id' => $this->penyidik->id,
    ]);
});

test('pimpinan dilarang menetapkan atau mencabut status DPO (403 Forbidden)', function () {
    $response = $this->actingAs($this->pimpinan)
        ->post(route('berkas-perkara.dpo.store', $this->berkas), [
            'nomor_surat' => 'B/DPO/02/X/2026',
            'tanggal_penetapan' => '2026-10-14',
        ]);

    $response->assertForbidden();

    $responseRevoke = $this->actingAs($this->pimpinan)
        ->post(route('berkas-perkara.dpo.destroy', $this->berkas), [
            'tanggal_pencabutan' => '2026-10-14',
            'alasan_pencabutan' => 'Tertangkap',
        ]);

    $responseRevoke->assertForbidden();
});

test('validasi penetapan DPO mewajibkan nomor surat dan tanggal serta menolak file non-PDF', function () {
    $textDoc = UploadedFile::fake()->create('surat.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.store', $this->berkas), [
            'nomor_surat' => '',
            'tanggal_penetapan' => '',
            'dokumen' => $textDoc,
        ]);

    $response->assertSessionHasErrors(['nomor_surat', 'tanggal_penetapan', 'dokumen']);
});

test('penyidik dapat mencabut status DPO dan mengembalikan berkas ke kondisi normal', function () {
    // 1. Tetapkan DPO dulu
    $dpo = Dpo::create([
        'berkas_perkara_id' => $this->berkas->id,
        'nomor_surat' => 'B/DPO/01/X/2026',
        'tanggal_penetapan' => '2026-10-10',
        'status' => 'aktif',
        'keterangan' => 'Buron',
        'created_by' => $this->penyidik->id,
    ]);
    $this->berkas->update(['is_dpo' => true]);

    // 2. Cabut status DPO
    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.destroy', $this->berkas), [
            'tanggal_pencabutan' => '2026-10-14',
            'alasan_pencabutan' => 'Tersangka berhasil diamankan oleh tim PPNS di Banjarmasin.',
        ]);

    $response->assertRedirect(route('perkara.show', $this->perkara));
    $response->assertSessionHas('success', 'Status DPO berhasil dicabut. Berkas perkara dapat dilanjutkan normal.');

    $this->berkas->refresh();
    expect($this->berkas->is_dpo)->toBeFalse();

    $dpo->refresh();
    expect($dpo->status)->toBe('nonaktif')
        ->and($dpo->tanggal_pencabutan->toDateString())->toBe('2026-10-14')
        ->and($dpo->alasan_pencabutan)->toBe('Tersangka berhasil diamankan oleh tim PPNS di Banjarmasin.');

    $this->assertDatabaseHas('audit_log', [
        'berkas_perkara_id' => $this->berkas->id,
        'aksi' => 'nonaktifkan_dpo',
        'user_id' => $this->penyidik->id,
    ]);
});

test('DPO gate memblokir finalisasi Tahap II di server dan membuka blokir setelah DPO dicabut', function () {
    $tahapanService = app(TahapanService::class);

    // Siapkan tahapan berurutan hingga P-21
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/01/2026',
        'tanggal_surat' => '2026-10-02',
        'status' => 'final',
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'nomor_surat' => 'T1/01/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::P21,
        'nomor_surat' => 'P21/01/2026',
        'tanggal_surat' => '2026-10-08',
        'status' => 'final',
    ]);

    // Set DPO aktif
    $this->berkas->update(['is_dpo' => true]);

    // Coba simpan & lanjut TAHAP II -> DITOLAK
    expect(function () use ($tahapanService) {
        $tahapanService->saveStage(
            $this->berkas,
            [
                'action_type' => 'lanjut',
                'jenis_tahap' => JenisTahap::TAHAP_2,
                'nomor_surat' => 'T2/01/2026',
                'tanggal_surat' => '2026-10-14',
            ],
            null,
            $this->penyidik
        );
    })->toThrow(DomainException::class, 'Tidak dapat melanjutkan ke Tahap II (Pelimpahan Tersangka & Barang Bukti) karena Tersangka berstatus DPO.');

    // Namun simpan draft TAHAP II tetap diizinkan
    $draftTahap2 = $tahapanService->saveStage(
        $this->berkas,
        [
            'action_type' => 'draft',
            'jenis_tahap' => JenisTahap::TAHAP_2,
            'nomor_surat' => 'T2/DRAFT/2026',
            'tanggal_surat' => '2026-10-14',
        ],
        null,
        $this->penyidik
    );
    expect($draftTahap2->status)->toBe('draft');

    // Cabut DPO
    $this->berkas->update(['is_dpo' => false]);

    // Sekarang simpan & lanjut TAHAP II berhasil dan memicu status Selesai
    $finalTahap2 = $tahapanService->saveStage(
        $this->berkas,
        [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::TAHAP_2,
            'nomor_surat' => 'T2/FINAL/2026',
            'tanggal_surat' => '2026-10-14',
        ],
        null,
        $this->penyidik,
        $draftTahap2
    );

    expect($finalTahap2->status)->toBe('final');
    $this->berkas->refresh();
    expect($this->berkas->status_formal)->toBe('selesai')
        ->and($this->berkas->progress_persen)->toBe(100);
});

test('halaman detail perkara merender badge DPO aktif, card peringatan, dan timeline audit log', function () {
    // 1. Buat DPO aktif
    Dpo::create([
        'berkas_perkara_id' => $this->berkas->id,
        'nomor_surat' => 'B/DPO/99/X/2026',
        'tanggal_penetapan' => '2026-10-12',
        'status' => 'aktif',
        'keterangan' => 'Buron ke luar pulau.',
        'created_by' => $this->penyidik->id,
    ]);
    $this->berkas->update(['is_dpo' => true]);

    // 2. Buat log aktivitas
    AuditLog::create([
        'user_id' => $this->penyidik->id,
        'perkara_id' => $this->perkara->id,
        'berkas_perkara_id' => $this->berkas->id,
        'aksi' => 'aktifkan_dpo',
        'tabel' => 'dpo',
        'record_id' => 1,
        'data_baru' => [
            'nomor_surat' => 'B/DPO/99/X/2026',
            'tanggal_penetapan' => '2026-10-12',
            'keterangan' => 'Buron ke luar pulau.',
        ],
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->penyidik)->get(route('perkara.show', $this->perkara));

    $response->assertOk();
    $response->assertSee('DPO Aktif');
    $response->assertSee('B/DPO/99/X/2026');
    $response->assertSee('Buron ke luar pulau.');
    $response->assertSee('Pelimpahan ke Tahap II (Pelimpahan Tersangka &amp; Barang Bukti) dinonaktifkan di sistem', false);
    $response->assertSee('Cabut Status DPO');
    $response->assertSee('Riwayat Penanganan Perkara (Case History)');
    $response->assertSee('Penetapan Status DPO');
});
