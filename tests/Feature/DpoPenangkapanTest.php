<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
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
    Storage::fake('local');

    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $this->admin = User::factory()->create(['role' => 'administrator']);
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
        'nama' => 'Joko DPO',
        'nik' => '6371012345670009',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/009/X/2026/TEST',
        'is_dpo' => true,
        'created_by' => $this->penyidik->id,
    ]);

    $this->dpo = Dpo::create([
        'berkas_perkara_id' => $this->berkas->id,
        'nomor_surat' => 'B/DPO/009/X/2026',
        'tanggal_penetapan' => '2026-10-05',
        'status' => 'aktif',
        'keterangan' => 'Tersangka melarikan diri ke luar kota.',
        'created_by' => $this->penyidik->id,
    ]);
});

test('penyidik dapat mencatat penangkapan saja dengan 1 berkas PDF tanpa penahanan', function () {
    $pdf = UploadedFile::fake()->create('sp_kap.pdf', 300, 'application/pdf');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => 'Sp.Kap/009/X/2026/BPOM',
            'tanggal_penangkapan' => '2026-10-16',
            'dokumen_penangkapan' => [$pdf],
            'keterangan_penangkapan' => 'Tersangka berhasil ditangkap tim gabungan di Banjarmasin.',
        ]);

    $response->assertRedirect(route('perkara.show', $this->perkara));
    $response->assertSessionHas('success');

    $this->berkas->refresh();
    expect($this->berkas->is_dpo)->toBeFalse();

    $this->dpo->refresh();
    expect($this->dpo->status)->toBe('nonaktif')
        ->and($this->dpo->nomor_surat_penangkapan)->toBe('Sp.Kap/009/X/2026/BPOM')
        ->and($this->dpo->tanggal_penangkapan?->toDateString())->toBe('2026-10-16')
        ->and($this->dpo->nomor_surat_penahanan)->toBeNull()
        ->and($this->dpo->tanggal_penahanan)->toBeNull()
        ->and($this->dpo->dokumen_penangkapan_id)->not->toBeNull()
        ->and($this->dpo->dokumenPenangkapanList)->toHaveCount(1);

    // Verifikasi relasi dokumen penangkapan
    expect($this->dpo->dokumenPenangkapan)->not->toBeNull()
        ->and($this->dpo->dokumenPenangkapan->nama_file_asli)->toBe('sp_kap.pdf');

    // Verifikasi audit log
    $this->assertDatabaseHas('audit_log', [
        'berkas_perkara_id' => $this->berkas->id,
        'aksi' => 'penangkapan_dpo',
        'user_id' => $this->penyidik->id,
    ]);
});

test('penyidik dapat mencatat penangkapan dan penahanan lengkap dengan multi-file PDF', function () {
    $pdf1 = UploadedFile::fake()->create('sp_kap_resmi.pdf', 500, 'application/pdf');
    $pdf2 = UploadedFile::fake()->create('sp_han_resmi.pdf', 600, 'application/pdf');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => 'Sp.Kap/111/X/2026/BPOM',
            'tanggal_penangkapan' => '2026-10-18',
            'nomor_surat_penahanan' => 'Sp.Han/111/X/2026/BPOM',
            'tanggal_penahanan' => '2026-10-19',
            'dokumen_penangkapan' => [$pdf1, $pdf2],
            'keterangan_penangkapan' => 'Tersangka ditangkap dan langsung dilakukan penahanan di rutan.',
        ]);

    $response->assertRedirect(route('perkara.show', $this->perkara));
    $response->assertSessionHas('success');

    $this->dpo->refresh();
    expect($this->dpo->status)->toBe('nonaktif')
        ->and($this->dpo->nomor_surat_penangkapan)->toBe('Sp.Kap/111/X/2026/BPOM')
        ->and($this->dpo->tanggal_penangkapan?->toDateString())->toBe('2026-10-18')
        ->and($this->dpo->nomor_surat_penahanan)->toBe('Sp.Han/111/X/2026/BPOM')
        ->and($this->dpo->tanggal_penahanan?->toDateString())->toBe('2026-10-19')
        ->and($this->dpo->dokumenPenangkapanList)->toHaveCount(2);

    $this->assertDatabaseHas('dokumen', [
        'dpo_id' => $this->dpo->id,
        'nama_file_asli' => 'sp_kap_resmi.pdf',
    ]);
    $this->assertDatabaseHas('dokumen', [
        'dpo_id' => $this->dpo->id,
        'nama_file_asli' => 'sp_han_resmi.pdf',
    ]);
});

test('validasi penahanan menolak jika nomor dan tanggal diisi hanya salah satunya (berpasangan)', function () {
    $pdf = UploadedFile::fake()->create('dokumen.pdf', 200, 'application/pdf');

    // Kasus 1: nomor surat penahanan diisi, tanggal penahanan kosong
    $response1 = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => 'Sp.Kap/009/X/2026/BPOM',
            'tanggal_penangkapan' => '2026-10-16',
            'nomor_surat_penahanan' => 'Sp.Han/009/X/2026/BPOM',
            'tanggal_penahanan' => '',
            'dokumen_penangkapan' => [$pdf],
        ]);
    $response1->assertSessionHasErrors(['tanggal_penahanan']);

    // Kasus 2: tanggal penahanan diisi, nomor surat penahanan kosong
    $response2 = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => 'Sp.Kap/009/X/2026/BPOM',
            'tanggal_penangkapan' => '2026-10-16',
            'nomor_surat_penahanan' => '',
            'tanggal_penahanan' => '2026-10-17',
            'dokumen_penangkapan' => [$pdf],
        ]);
    $response2->assertSessionHasErrors(['nomor_surat_penahanan']);
});

test('validasi menolak jika total ukuran berkas gabungan melebihi 5 MB', function () {
    // 2 file masing-masing 3 MB = total 6 MB (> 5 MB)
    $pdf1 = UploadedFile::fake()->create('berkas_besar_1.pdf', 3072, 'application/pdf');
    $pdf2 = UploadedFile::fake()->create('berkas_besar_2.pdf', 3072, 'application/pdf');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => 'Sp.Kap/009/X/2026/BPOM',
            'tanggal_penangkapan' => '2026-10-16',
            'dokumen_penangkapan' => [$pdf1, $pdf2],
        ]);

    $response->assertSessionHasErrors(['dokumen_penangkapan']);
});

test('pencatatan penangkapan DPO membuka kunci tahapan perkara yang sebelumnya terblokir', function () {
    $tahapanService = app(TahapanService::class);
    $pdf = UploadedFile::fake()->create('sp_kap.pdf', 200, 'application/pdf');

    // Finalkan SPDP saat DPO masih aktif
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/009/2026',
        'tanggal_surat' => '2026-10-04',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    // Saat DPO aktif, getNextAvailableStages harus kosong karena DPO memblokir semua tahapan berikutnya
    expect($tahapanService->getNextAvailableStages($this->berkas))->toBeEmpty();

    // Catat penangkapan
    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => 'Sp.Kap/009/X/2026/BPOM',
            'tanggal_penangkapan' => '2026-10-16',
            'dokumen_penangkapan' => [$pdf],
        ]);

    $this->berkas->refresh();
    expect($this->berkas->is_dpo)->toBeFalse();

    // Setelah penangkapan dicatat, tahapan berikutnya (Tahap 1) kembali tersedia
    expect($tahapanService->getNextAvailableStages($this->berkas))->toBe([JenisTahap::TAHAP_1]);
});

test('validasi penangkapan DPO mewajibkan nomor surat dan tanggal penangkapan serta minimal 1 file PDF', function () {
    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => '',
            'tanggal_penangkapan' => '',
            'dokumen_penangkapan' => [],
        ]);

    $response->assertSessionHasErrors([
        'nomor_surat_penangkapan',
        'tanggal_penangkapan',
        'dokumen_penangkapan',
    ]);
});

test('pengguna role umum diblokir dari aksi mencatat penangkapan DPO (403 Forbidden)', function () {
    $pdf = UploadedFile::fake()->create('sp_kap.pdf', 200, 'application/pdf');

    $response = $this->actingAs($this->umum)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => 'Sp.Kap/009/X/2026/BPOM',
            'tanggal_penangkapan' => '2026-10-16',
            'dokumen_penangkapan' => [$pdf],
        ]);

    $response->assertForbidden();
});

test('halaman detail perkara menampilkan tombol dan riwayat penangkapan serta penahanan DPO', function () {
    // 1. Saat DPO aktif, tombol Catat Penangkapan & Penahanan tampil
    $response = $this->actingAs($this->penyidik)->get(route('perkara.show', $this->perkara));
    $response->assertOk();
    $response->assertSee('Catat Penangkapan', false);
    $response->assertSee('Catat Penangkapan &amp; Penahanan DPO', false);

    $pdf1 = UploadedFile::fake()->create('sp_kap_bukti.pdf', 200, 'application/pdf');
    $pdf2 = UploadedFile::fake()->create('sp_han_bukti.pdf', 300, 'application/pdf');

    // 2. Catat penangkapan dan penahanan dengan 2 berkas PDF
    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.dpo.penangkapan', $this->berkas), [
            'nomor_surat_penangkapan' => 'Sp.Kap/009/X/2026/BPOM',
            'tanggal_penangkapan' => '2026-10-16',
            'nomor_surat_penahanan' => 'Sp.Han/009/X/2026/BPOM',
            'tanggal_penahanan' => '2026-10-17',
            'dokumen_penangkapan' => [$pdf1, $pdf2],
            'keterangan_penangkapan' => 'Ditangkap di rumah kos daerah Banjarmasin.',
        ]);

    // 3. Setelah tertangkap, card riwayat DPO tertangkap tampil lengkap
    $responseAfter = $this->actingAs($this->penyidik)->get(route('perkara.show', $this->perkara));
    $responseAfter->assertOk();
    $responseAfter->assertSee('Riwayat DPO: Tersangka Telah Ditangkap / Ditahan');
    $responseAfter->assertSee('Sp.Kap/009/X/2026/BPOM');
    $responseAfter->assertSee('Sp.Han/009/X/2026/BPOM');
    $responseAfter->assertSee('16-10-2026');
    $responseAfter->assertSee('sp_kap_bukti.pdf');
    $responseAfter->assertSee('sp_han_bukti.pdf');
    $responseAfter->assertSee('Ditangkap di rumah kos daerah Banjarmasin.');
});
