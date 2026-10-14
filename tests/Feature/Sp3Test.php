<?php

use App\Models\BerkasPerkara;
use App\Models\Perkara;
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
        'nama' => 'Ahmad Kasus SP3',
        'nik' => '6371012345679999',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/SP3/001/X/2026',
        'status_formal' => null,
        'created_by' => $this->penyidik->id,
    ]);
});

test('penyidik dapat menerbitkan SP3 dengan nomor surat, tanggal, alasan, dan file PDF valid', function () {
    Storage::fake('local');
    $pdf = UploadedFile::fake()->create('surat_sp3_resmi.pdf', 800, 'application/pdf');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.sp3.store', $this->berkas), [
            'nomor_surat' => 'SPPP/01/BPOM/BJM/2026',
            'tanggal_sp3' => '2026-10-14',
            'alasan' => 'Tidak diperoleh bukti yang cukup',
            'keterangan' => 'Berdasarkan hasil gelar perkara, alat bukti tidak mencukupi unsur pasal.',
            'dokumen' => $pdf,
        ]);

    $response->assertRedirect(route('perkara.show', $this->perkara));
    $response->assertSessionHas('success', 'Surat Perintah Penghentian Penyidikan (SP3) berhasil diterbitkan. Status berkas kini menjadi Tidak Selesai.');

    $this->berkas->refresh();
    expect($this->berkas->status_formal)->toBe('tidak_selesai');
    expect($this->berkas->hasSp3())->toBeTrue();
    expect($this->berkas->isTidakSelesai())->toBeTrue();

    $this->assertDatabaseHas('sp3', [
        'berkas_perkara_id' => $this->berkas->id,
        'nomor_surat' => 'SPPP/01/BPOM/BJM/2026',
        'alasan' => 'Tidak diperoleh bukti yang cukup',
        'created_by' => $this->penyidik->id,
    ]);

    expect($this->berkas->sp3->tanggal_sp3->format('Y-m-d'))->toBe('2026-10-14');

    $this->assertDatabaseHas('dokumen', [
        'berkas_perkara_id' => $this->berkas->id,
        'kategori' => 'sp3',
        'nama_file_asli' => 'surat_sp3_resmi.pdf',
    ]);

    $this->assertDatabaseHas('audit_log', [
        'berkas_perkara_id' => $this->berkas->id,
        'aksi' => 'terbitkan_sp3',
        'user_id' => $this->penyidik->id,
    ]);
});

test('administrator dapat menerbitkan SP3', function () {
    Storage::fake('local');
    $pdf = UploadedFile::fake()->create('sp3_admin.pdf', 300, 'application/pdf');

    $response = $this->actingAs($this->admin)
        ->post(route('berkas-perkara.sp3.store', $this->berkas), [
            'nomor_surat' => 'SPPP/ADMIN/01/2026',
            'tanggal_sp3' => '2026-10-14',
            'alasan' => 'Peristiwa bukan merupakan tindak pidana',
            'dokumen' => $pdf,
        ]);

    $response->assertRedirect(route('perkara.show', $this->perkara));
    $this->berkas->refresh();
    expect($this->berkas->status_formal)->toBe('tidak_selesai');
});

test('pimpinan dilarang menerbitkan SP3 (403 Forbidden)', function () {
    Storage::fake('local');
    $pdf = UploadedFile::fake()->create('sp3.pdf', 300, 'application/pdf');

    $response = $this->actingAs($this->pimpinan)
        ->post(route('berkas-perkara.sp3.store', $this->berkas), [
            'nomor_surat' => 'SPPP/02/2026',
            'tanggal_sp3' => '2026-10-14',
            'alasan' => 'Tidak cukup bukti',
            'dokumen' => $pdf,
        ]);

    $response->assertForbidden();
});

test('validasi input SP3 menolak data kosong dan file non-PDF', function () {
    Storage::fake('local');
    $txt = UploadedFile::fake()->create('bukan_pdf.txt', 100, 'text/plain');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.sp3.store', $this->berkas), [
            'nomor_surat' => '',
            'tanggal_sp3' => '',
            'alasan' => '',
            'dokumen' => $txt,
        ]);

    $response->assertSessionHasErrors(['nomor_surat', 'tanggal_sp3', 'alasan', 'dokumen']);
});

test('berkas perkara yang telah SP3 dikunci dari penambahan tahapan lanjutan', function () {
    Storage::fake('local');
    $pdf = UploadedFile::fake()->create('sp3_lock.pdf', 200, 'application/pdf');

    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.sp3.store', $this->berkas), [
            'nomor_surat' => 'SPPP/LOCK/01/2026',
            'tanggal_sp3' => '2026-10-14',
            'alasan' => 'Dihentikan demi hukum',
            'dokumen' => $pdf,
        ]);

    $this->berkas->refresh();
    $tahapanService = app(TahapanService::class);
    $availableStages = $tahapanService->getNextAvailableStages($this->berkas);

    expect($availableStages)->toBeEmpty();
});

test('tidak dapat menerbitkan SP3 ganda pada berkas yang sudah diterbitkan SP3', function () {
    Storage::fake('local');
    $pdf1 = UploadedFile::fake()->create('sp3_pertama.pdf', 200, 'application/pdf');
    $pdf2 = UploadedFile::fake()->create('sp3_kedua.pdf', 200, 'application/pdf');

    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.sp3.store', $this->berkas), [
            'nomor_surat' => 'SPPP/01/2026',
            'tanggal_sp3' => '2026-10-14',
            'alasan' => 'Tidak cukup bukti',
            'dokumen' => $pdf1,
        ]);

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.sp3.store', $this->berkas), [
            'nomor_surat' => 'SPPP/02/2026',
            'tanggal_sp3' => '2026-10-14',
            'alasan' => 'Tidak cukup bukti',
            'dokumen' => $pdf2,
        ]);

    $response->assertSessionHas('error');
});
