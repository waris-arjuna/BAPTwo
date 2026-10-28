<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->penyidik()->create();

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
        'nama' => 'Budi Santoso',
        'nik' => '6371012345670001',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/BPOM',
        'progress_persen' => 0,
        'status_proses' => 'Baru',
        'tahun' => 2026,
        'created_by' => $this->penyidik->id,
    ]);
});

test('upload dokumen laporan kejadian melebihi 5 MB ditolak dengan pesan kustom', function () {
    // 6 MB = 6144 KB
    $oversizedFile = UploadedFile::fake()->create('laporan_besar.pdf', 6144, 'application/pdf');

    $this->actingAs($this->penyidik)
        ->post(route('laporan-kejadian.store'), [
            'action_type' => 'lanjut',
            'nomor_laporan' => 'LK/999/X/2026',
            'tanggal_laporan' => '2026-10-10',
            'deskripsi' => 'Dugaan tindak pidana',
            'dokumen_laporan' => $oversizedFile,
        ])
        ->assertSessionHasErrors([
            'dokumen_laporan' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
        ]);
});

test('upload dokumen sprindik melebihi 5 MB ditolak dengan pesan kustom', function () {
    $oversizedFile = UploadedFile::fake()->create('sprindik_besar.pdf', 6144, 'application/pdf');

    $this->actingAs($this->penyidik)
        ->post(route('sprindik.store'), [
            'action_type' => 'lanjut',
            'perkara_id' => $this->perkara->id,
            'nomor_sprindik' => 'SP/999/X/2026',
            'tanggal_sprindik' => '2026-10-10',
            'dokumen_sprindik' => $oversizedFile,
        ])
        ->assertSessionHasErrors([
            'dokumen_sprindik' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
        ]);
});

test('upload dokumen tahapan perkara melebihi 5 MB ditolak dengan pesan kustom', function () {
    $oversizedFile = UploadedFile::fake()->create('spdp_besar.pdf', 6144, 'application/pdf');

    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::SPDP->value,
            'nomor_surat' => 'SPDP/001/X/2026',
            'tanggal_surat' => '2026-10-05',
            'dokumen' => $oversizedFile,
        ])
        ->assertSessionHasErrors([
            'dokumen' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
        ]);
});

test('halaman form dokumen merender note standar 5 MB untuk form baru dan form edit', function () {
    // 1. Form create laporan kejadian (form baru)
    $this->actingAs($this->penyidik)
        ->get(route('laporan-kejadian.create'))
        ->assertOk()
        ->assertSee('Format file: PDF maks. 5 MB')
        ->assertDontSee('Maksimal 20 MB');

    // 2. Form edit laporan kejadian (form edit draft dengan existing file)
    $draftPerkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DRAFT/001/2026',
        'tanggal_laporan_kejadian' => '2026-10-15',
        'tahap_laporan_status' => 'draft',
        'created_by' => $this->penyidik->id,
    ]);

    $draftPerkara->dokumenLaporan()->create([
        'perkara_id' => $draftPerkara->id,
        'kategori' => 'lapkej',
        'nama_dokumen' => 'Laporan Kejadian Draft',
        'nama_file_asli' => 'laporan_awal.pdf',
        'file_path' => 'dokumen/laporan_awal.pdf',
        'file_size' => 102400,
        'created_by' => $this->penyidik->id,
    ]);

    $this->actingAs($this->penyidik)
        ->get(route('laporan-kejadian.edit', $draftPerkara))
        ->assertOk()
        ->assertSee('Dokumen saat ini: laporan_awal.pdf. Biarkan kosong jika tidak ingin mengubah. Format file: PDF maks. 5 MB')
        ->assertSee('Lihat file saat ini')
        ->assertDontSee('Maksimal 20 MB');

    // 3. Form tahapan SPDP (form baru)
    $this->actingAs($this->penyidik)
        ->get(route('berkas-perkara.tahapan.create', ['berkas' => $this->berkas, 'jenis' => 'spdp']))
        ->assertOk()
        ->assertSee('Format file: PDF maks. 5 MB')
        ->assertDontSee('Maksimal 20 MB');
});
