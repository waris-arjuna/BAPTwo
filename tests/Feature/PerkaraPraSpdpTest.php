<?php

use App\Models\AuditLog;
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

it('allows Penyidik to create Laporan Kejadian with PDF and writes audit log', function () {
    $penyidik = User::factory()->penyidik()->create();

    $pdfFile = UploadedFile::fake()->create('laporan_uji.pdf', 500, 'application/pdf');

    $response = $this->actingAs($penyidik)->post(route('laporan-kejadian.store'), [
        'nomor_laporan' => 'LK/999/X/2026',
        'tanggal_laporan' => '2026-10-13',
        'dokumen_laporan' => $pdfFile,
        'deskripsi' => 'Dugaan peredaran obat tradisional ilegal.',
    ]);

    $response->assertRedirect();

    $perkara = Perkara::where('nomor_laporan_kejadian', 'LK/999/X/2026')->first();
    expect($perkara)->not->toBeNull()
        ->and($perkara->status_laporan)->toBe('Belum Dilanjutkan')
        ->and($perkara->created_by)->toBe($penyidik->id);

    // Verify dokumen PDF saved
    $dokumen = $perkara->dokumenLaporan;
    expect($dokumen)->not->toBeNull()
        ->and($dokumen->kategori)->toBe('lapkej')
        ->and($dokumen->nama_file_asli)->toBe('laporan_uji.pdf');

    Storage::disk('local')->assertExists($dokumen->file_path);

    // Verify audit log
    $auditLog = AuditLog::where('perkara_id', $perkara->id)->where('aksi', 'create_laporan_kejadian')->first();
    expect($auditLog)->not->toBeNull()
        ->and($auditLog->user_id)->toBe($penyidik->id);
});

it('rejects non-PDF files for Laporan Kejadian per D-007', function () {
    $penyidik = User::factory()->penyidik()->create();

    $docxFile = UploadedFile::fake()->create('laporan.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $this->actingAs($penyidik)
        ->post(route('laporan-kejadian.store'), [
            'nomor_laporan' => 'LK/888/X/2026',
            'tanggal_laporan' => '2026-10-13',
            'dokumen_laporan' => $docxFile,
        ])
        ->assertSessionHasErrors(['dokumen_laporan']);

    expect(Perkara::where('nomor_laporan_kejadian', 'LK/888/X/2026')->exists())->toBeFalse();
});

it('enforces uniqueness of nomor laporan kejadian', function () {
    $penyidik = User::factory()->penyidik()->create();
    Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DUPLIKAT/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
    ]);

    $this->actingAs($penyidik)
        ->post(route('laporan-kejadian.store'), [
            'nomor_laporan' => 'LK/DUPLIKAT/2026',
            'tanggal_laporan' => '2026-10-13',
        ])
        ->assertSessionHasErrors(['nomor_laporan']);
});

it('allows Penyidik to update Laporan Kejadian draft', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/111/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'deskripsi' => 'Deskripsi lama',
    ]);

    $this->actingAs($penyidik)
        ->put(route('laporan-kejadian.update', $perkara), [
            'nomor_laporan' => 'LK/111/X/2026',
            'tanggal_laporan' => '2026-10-11',
            'deskripsi' => 'Deskripsi baru yang telah diperbarui',
        ])
        ->assertRedirect(route('laporan-kejadian.show', $perkara));

    $perkara->refresh();
    expect($perkara->deskripsi)->toBe('Deskripsi baru yang telah diperbarui');

    $auditLog = AuditLog::where('perkara_id', $perkara->id)->where('aksi', 'update_laporan_kejadian')->first();
    expect($auditLog)->not->toBeNull();
});

it('allows Penyidik to attach Sprindik to existing Laporan Kejadian and updates status', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/222/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
    ]);

    $pdfFile = UploadedFile::fake()->create('sprindik.pdf', 300, 'application/pdf');

    $response = $this->actingAs($penyidik)->post(route('sprindik.store'), [
        'perkara_id' => $perkara->id,
        'nomor_sprindik' => 'SP/222/X/2026',
        'tanggal_sprindik' => '2026-10-12',
        'dokumen_sprindik' => $pdfFile,
    ]);

    $response->assertRedirect(route('sprindik.show', $perkara));

    $perkara->refresh();
    expect($perkara->nomor_surat_perintah)->toBe('SP/222/X/2026')
        ->and($perkara->status_laporan)->toBe('Sprindik Tersedia')
        ->and($perkara->status_sprindik)->toBe('Belum Menjadi Perkara');

    $dokumenSprindik = $perkara->dokumenSprindik;
    expect($dokumenSprindik)->not->toBeNull()
        ->and($dokumenSprindik->kategori)->toBe('sprindik');
    Storage::disk('local')->assertExists($dokumenSprindik->file_path);
});

it('allows Penyidik to update Sprindik details', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/333/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'nomor_surat_perintah' => 'SP/333/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
    ]);

    $this->actingAs($penyidik)
        ->put(route('sprindik.update', $perkara), [
            'nomor_sprindik' => 'SP/333/X/2026-REV',
            'tanggal_sprindik' => '2026-10-12',
            'deskripsi' => 'Revisi nomor sprindik',
        ])
        ->assertRedirect(route('sprindik.show', $perkara));

    $perkara->refresh();
    expect($perkara->nomor_surat_perintah)->toBe('SP/333/X/2026-REV');
});

it('allows soft deleting Laporan Kejadian and prevents hard deletion', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/444/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
    ]);

    $this->actingAs($penyidik)
        ->delete(route('laporan-kejadian.destroy', $perkara))
        ->assertRedirect(route('laporan-kejadian.index'));

    expect(Perkara::find($perkara->id))->toBeNull()
        ->and(Perkara::withTrashed()->find($perkara->id))->not->toBeNull();

    $auditLog = AuditLog::where('perkara_id', $perkara->id)->where('aksi', 'delete_laporan_kejadian')->first();
    expect($auditLog)->not->toBeNull();
});

it('allows soft deleting / resetting Sprindik from Perkara', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/555/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'nomor_surat_perintah' => 'SP/555/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
    ]);

    $this->actingAs($penyidik)
        ->delete(route('sprindik.destroy', $perkara))
        ->assertRedirect(route('sprindik.index'));

    $perkara->refresh();
    expect($perkara->nomor_surat_perintah)->toBeNull()
        ->and($perkara->status_laporan)->toBe('Belum Dilanjutkan');
});

it('allows authenticated users to preview and download uploaded private documents', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/666/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
    ]);

    $path = Storage::disk('local')->putFile('dokumen/lapkej', UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'));

    $dokumen = Dokumen::create([
        'perkara_id' => $perkara->id,
        'kategori' => 'lapkej',
        'nama_dokumen' => 'laporan.pdf',
        'nama_file_asli' => 'laporan.pdf',
        'file_path' => $path,
        'file_size' => 102400,
        'created_by' => $penyidik->id,
    ]);

    $this->actingAs($penyidik)
        ->get(route('dokumen.preview', $dokumen))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $this->actingAs($penyidik)
        ->get(route('dokumen.download', $dokumen))
        ->assertOk();
});

it('blocks Pimpinan from store, update, and delete actions on Perkara and Sprindik', function () {
    $pimpinan = User::factory()->pimpinan()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/777/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'nomor_surat_perintah' => 'SP/777/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
    ]);

    $this->actingAs($pimpinan)
        ->post(route('laporan-kejadian.store'), ['nomor_laporan' => 'LK/TEST/2026'])
        ->assertForbidden();

    $this->actingAs($pimpinan)
        ->put(route('laporan-kejadian.update', $perkara), ['nomor_laporan' => 'LK/EDIT/2026'])
        ->assertForbidden();

    $this->actingAs($pimpinan)
        ->delete(route('laporan-kejadian.destroy', $perkara))
        ->assertForbidden();

    $this->actingAs($pimpinan)
        ->post(route('sprindik.store'), ['perkara_id' => $perkara->id])
        ->assertForbidden();

    $this->actingAs($pimpinan)
        ->put(route('sprindik.update', $perkara), ['nomor_sprindik' => 'SP/EDIT/2026'])
        ->assertForbidden();

    $this->actingAs($pimpinan)
        ->delete(route('sprindik.destroy', $perkara))
        ->assertForbidden();
});
