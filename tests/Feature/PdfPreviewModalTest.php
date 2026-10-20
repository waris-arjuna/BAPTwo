<?php

use App\Models\Dokumen;
use App\Models\Perkara;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

it('renders global pdf preview modal in app layout', function () {
    $user = User::factory()->penyidik()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('open-pdf-modal.window', false)
        ->assertSee('Pratinjau Dokumen PDF', false)
        ->assertSee('Dokumen Resmi Penanganan Perkara', false)
        ->assertSee('__pdfPreviewListenerBound', false);
});

it('includes data-pdf-preview attributes on document links in detail and list pages', function () {
    $penyidik = User::factory()->penyidik()->create();

    $fakeFile = UploadedFile::fake()->create('berkas_laporan.pdf', 300, 'application/pdf');
    $path = $fakeFile->store('dokumen/perkara', 'local');

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/100/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-14',
        'status_laporan' => 'Dilanjutkan',
        'created_by' => $penyidik->id,
    ]);

    $dokumen = Dokumen::create([
        'perkara_id' => $perkara->id,
        'nama_dokumen' => 'Dokumen Laporan Kejadian',
        'nama_file_asli' => 'berkas_laporan.pdf',
        'file_path' => $path,
        'file_size' => 300 * 1024,
        'kategori' => 'lapkej',
        'created_by' => $penyidik->id,
    ]);

    // Test di Halaman Detail Laporan Kejadian
    $responseDetail = $this->actingAs($penyidik)->get(route('laporan-kejadian.show', $perkara));
    $responseDetail->assertOk()
        ->assertSee('data-pdf-preview', false)
        ->assertSee('berkas_laporan.pdf', false);

    // Test di Halaman Index Laporan Kejadian (Tabel Partial)
    $responseIndex = $this->actingAs($penyidik)->get(route('laporan-kejadian.index'));
    $responseIndex->assertOk()
        ->assertSee('data-pdf-preview', false)
        ->assertSee('data-pdf-title="berkas_laporan.pdf"', false)
        ->assertSee(route('dokumen.preview', $dokumen), false);
});
