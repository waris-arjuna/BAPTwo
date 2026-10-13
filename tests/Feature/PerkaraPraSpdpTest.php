<?php

use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Pasal;
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

it('allows Penyidik to save incomplete Laporan Kejadian as Draft and edit it later', function () {
    $penyidik = User::factory()->penyidik()->create();

    // Save as draft without nomor_laporan
    $response = $this->actingAs($penyidik)->post(route('laporan-kejadian.store'), [
        'action_type' => 'draft',
        'nomor_laporan' => null,
        'tanggal_laporan' => null,
        'deskripsi' => 'Draft kronologi awal belum ada nomor surat.',
    ]);

    $response->assertRedirect();

    $perkara = Perkara::latest('id')->first();
    expect($perkara)->not->toBeNull()
        ->and($perkara->tahap_laporan_status)->toBe('draft')
        ->and($perkara->isLaporanLocked())->toBeFalse()
        ->and($perkara->status_laporan)->toBe('Draft');

    // Can access edit page while still draft
    $this->actingAs($penyidik)
        ->get(route('laporan-kejadian.edit', $perkara))
        ->assertOk()
        ->assertSee('Simpan Draft')
        ->assertSee('Simpan & Lanjut Tahap Berikutnya');

    // Finalize via Simpan & Lanjut
    $this->actingAs($penyidik)
        ->put(route('laporan-kejadian.update', $perkara), [
            'action_type' => 'lanjut',
            'nomor_laporan' => 'LK/DRAFT-FINAL/X/2026',
            'tanggal_laporan' => '2026-10-13',
            'deskripsi' => 'Kronologi lengkap dan difinalkan.',
        ])
        ->assertRedirect(route('laporan-kejadian.show', $perkara));

    $perkara->refresh();
    expect($perkara->tahap_laporan_status)->toBe('final')
        ->and($perkara->isLaporanLocked())->toBeTrue()
        ->and($perkara->laporan_locked_at)->not->toBeNull()
        ->and($perkara->laporan_finalized_by)->toBe($penyidik->id)
        ->and($perkara->status_laporan)->toBe('Belum Dilanjutkan');
});

it('permanently locks finalized Laporan Kejadian and Sprindik against edits even by Administrator per PRD F-18', function () {
    $penyidik = User::factory()->penyidik()->create();
    $admin = User::factory()->administrator()->create();

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/LOCKED/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'laporan_finalized_by' => $penyidik->id,
        'nomor_surat_perintah' => 'SP/LOCKED/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'sprindik_finalized_by' => $penyidik->id,
    ]);

    // Penyidik cannot edit locked Laporan Kejadian or Sprindik
    $this->actingAs($penyidik)->get(route('laporan-kejadian.edit', $perkara))->assertForbidden();
    $this->actingAs($penyidik)->put(route('laporan-kejadian.update', $perkara), [
        'nomor_laporan' => 'LK/HACK/2026',
        'tanggal_laporan' => '2026-10-12',
    ])->assertForbidden();

    $this->actingAs($penyidik)->get(route('sprindik.edit', $perkara))->assertForbidden();
    $this->actingAs($penyidik)->put(route('sprindik.update', $perkara), [
        'nomor_sprindik' => 'SP/HACK/2026',
        'tanggal_sprindik' => '2026-10-12',
    ])->assertForbidden();

    // Administrator ALSO cannot override stage lock (PRD F-18)
    $this->actingAs($admin)->get(route('laporan-kejadian.edit', $perkara))->assertForbidden();
    $this->actingAs($admin)->put(route('laporan-kejadian.update', $perkara), [
        'nomor_laporan' => 'LK/ADMIN-OVERRIDE/2026',
        'tanggal_laporan' => '2026-10-12',
    ])->assertForbidden();

    $this->actingAs($admin)->get(route('sprindik.edit', $perkara))->assertForbidden();
    $this->actingAs($admin)->put(route('sprindik.update', $perkara), [
        'nomor_sprindik' => 'SP/ADMIN-OVERRIDE/2026',
        'tanggal_sprindik' => '2026-10-12',
    ])->assertForbidden();
});

it('allows Penyidik to save Sprindik as Draft and later finalize it', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/333/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
    ]);

    // Save Sprindik as draft
    $this->actingAs($penyidik)
        ->post(route('sprindik.store'), [
            'action_type' => 'draft',
            'perkara_id' => $perkara->id,
            'nomor_sprindik' => 'SP/333-DRAFT/X/2026',
            'tanggal_sprindik' => null,
            'deskripsi' => 'Draft sprindik sementara',
        ])
        ->assertRedirect(route('sprindik.show', $perkara));

    $perkara->refresh();
    expect($perkara->tahap_sprindik_status)->toBe('draft')
        ->and($perkara->isSprindikLocked())->toBeFalse()
        ->and($perkara->status_sprindik)->toBe('Draft');

    // Finalize Sprindik via Simpan & Lanjut
    $this->actingAs($penyidik)
        ->put(route('sprindik.update', $perkara), [
            'action_type' => 'lanjut',
            'nomor_sprindik' => 'SP/333/X/2026-FINAL',
            'tanggal_sprindik' => '2026-10-12',
            'deskripsi' => 'Sprindik final',
        ])
        ->assertRedirect(route('sprindik.show', $perkara));

    $perkara->refresh();
    expect($perkara->nomor_surat_perintah)->toBe('SP/333/X/2026-FINAL')
        ->and($perkara->tahap_sprindik_status)->toBe('final')
        ->and($perkara->isSprindikLocked())->toBeTrue()
        ->and($perkara->status_sprindik)->toBe('Belum Menjadi Perkara');
});

it('allows soft deleting Laporan Kejadian and prevents hard deletion', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/444/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
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
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/555/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
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
        'tahap_laporan_status' => 'final',
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
        'tahap_laporan_status' => 'draft',
        'nomor_surat_perintah' => 'SP/777/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'draft',
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

it('creates 1 Berkas Perkara per Tersangka with manual Nomor Berkas, Pasal Berlapis, TKP, PPNS Utama/Anggota, and supports Draft vs Final locking (T-021, T-022, T-023, T-025)', function () {
    $penyidikUtama = User::factory()->penyidik()->create(['nama' => 'PPNS Utama Satu']);
    $penyidikAnggota1 = User::factory()->penyidik()->create(['nama' => 'PPNS Anggota Satu']);
    $penyidikAnggota2 = User::factory()->penyidik()->create(['nama' => 'PPNS Anggota Dua']);
    $admin = User::factory()->administrator()->create();

    $pasal1 = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'pasal' => 'Pasal 435',
        'nomor' => '17',
        'tahun' => '2023',
        'isi' => 'Setiap orang yang memproduksi atau mengedarkan sediaan farmasi tanpa izin edar.',
        'status' => 'aktif',
    ]);
    $pasal2 = Pasal::create([
        'nama_pasal' => 'UU Perlindungan Konsumen',
        'pasal' => 'Pasal 62 Ayat (1)',
        'nomor' => '8',
        'tahun' => '1999',
        'isi' => 'Pelaku usaha yang melanggar ketentuan sebagaimana dimaksud dalam Pasal 8.',
        'status' => 'aktif',
    ]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/888/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/888/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
    ]);

    // 1. Save as Draft first (action_type = draft)
    $this->actingAs($penyidikUtama)
        ->put(route('perkara.update', $perkara), [
            'action_type' => 'draft',
            'tkp_provinsi_kode' => '63',
            'tkp_provinsi_nama' => 'Kalimantan Selatan',
            'tkp_kabupaten_kode' => '63.71',
            'tkp_kabupaten_nama' => 'Kota Banjarmasin',
            'tkp_alamat_detail' => 'Jl. Ahmad Yani Km 5 (Draft)',
            'tkp_klasifikasi' => 'distributor',
            'ppns_utama_id' => $penyidikUtama->id,
            'ppns_anggota_ids' => [$penyidikAnggota1->id],
            'tersangka' => [
                [
                    'nomor_berkas' => 'DRAFT-BP-01',
                    'nama' => 'Tersangka Draft Satu',
                    'jenis_kelamin' => 'laki-laki',
                    'pasal_ids' => [$pasal1->id],
                ],
            ],
        ])
        ->assertRedirect(route('perkara.show', $perkara));

    $perkara->refresh();
    expect($perkara->tahap_perkara_status)->toBe('draft')
        ->and($perkara->isPerkaraLocked())->toBeFalse()
        ->and($perkara->status_sprindik)->toBe('Draft Perkara')
        ->and($perkara->tersangkas()->count())->toBe(1)
        ->and($perkara->berkasPerkaras()->count())->toBe(1);

    $existingTersangkaId = $perkara->tersangkas()->first()->id;

    // 2. Finalize (action_type = lanjut) with 2 Tersangkas -> 2 separate Berkas Perkara, manual Nomor Berkas, and Pasal Berlapis
    $this->actingAs($penyidikUtama)
        ->put(route('perkara.update', $perkara), [
            'action_type' => 'lanjut',
            'tkp_provinsi_kode' => '63',
            'tkp_provinsi_nama' => 'Kalimantan Selatan',
            'tkp_kabupaten_kode' => '63.71',
            'tkp_kabupaten_nama' => 'Kota Banjarmasin',
            'tkp_kecamatan_kode' => '63.71.01',
            'tkp_kecamatan_nama' => 'Banjarmasin Selatan',
            'tkp_desa_kode' => '63.71.01.1001',
            'tkp_desa_nama' => 'Kelayan Selatan',
            'tkp_alamat_detail' => 'Pergudangan Distribusi Blok C No. 12',
            'tkp_klasifikasi' => 'distributor',
            'tkp_legalitas' => 'ilegal',
            'tkp_modus' => 'online',
            'ppns_utama_id' => $penyidikUtama->id,
            'ppns_anggota_ids' => [$penyidikAnggota1->id, $penyidikAnggota2->id],
            'tersangka' => [
                [
                    'id' => $existingTersangkaId,
                    'nomor_berkas' => 'BP/BEBAS-FORMAT/001/X/2026',
                    'nama' => 'Ahmad Tersangka Satu',
                    'alias' => 'Mat Kosmetik',
                    'bin_binti' => 'Bin Abdullah',
                    'nik' => '6371011234560001',
                    'tempat_lahir' => 'Banjarmasin',
                    'tanggal_lahir' => '1988-05-12',
                    'jenis_kelamin' => 'laki-laki',
                    'agama' => 'islam',
                    'pekerjaan' => 'Wiraswasta',
                    'kewarganegaraan' => 'WNI',
                    'pasal_ids' => [$pasal1->id, $pasal2->id],
                ],
                [
                    'id' => null,
                    'nomor_berkas' => 'NO-POLA-KHUSUS#2026-B',
                    'nama' => 'Siti Tersangka Dua',
                    'alias' => 'Siti',
                    'bin_binti' => 'Binti Rahman',
                    'nik' => '6371016543210002',
                    'tempat_lahir' => 'Martapura',
                    'tanggal_lahir' => '1992-08-20',
                    'jenis_kelamin' => 'perempuan',
                    'agama' => 'islam',
                    'pekerjaan' => 'Pedagang',
                    'kewarganegaraan' => 'WNI',
                    'pasal_ids' => [$pasal1->id],
                ],
            ],
        ])
        ->assertRedirect(route('perkara.show', $perkara));

    $perkara->refresh();
    expect($perkara->tahap_perkara_status)->toBe('final')
        ->and($perkara->isPerkaraLocked())->toBeTrue()
        ->and($perkara->perkara_locked_at)->not->toBeNull()
        ->and($perkara->perkara_finalized_by)->toBe($penyidikUtama->id)
        ->and($perkara->status_sprindik)->toBe('Perkara Terbentuk')
        ->and($perkara->status_laporan)->toBe('Perkara Terbentuk')
        ->and($perkara->tkp)->not->toBeNull()
        ->and($perkara->tkp->klasifikasi)->toBe('distributor')
        ->and($perkara->tkp->alamat->kabupaten_nama)->toBe('Kota Banjarmasin')
        ->and($perkara->ppnsUtama()->count())->toBe(1)
        ->and($perkara->ppnsAnggota()->count())->toBe(2)
        ->and($perkara->tersangkas()->count())->toBe(2)
        ->and($perkara->berkasPerkaras()->count())->toBe(2);

    $berkas1 = BerkasPerkara::where('nomor_berkas', 'BP/BEBAS-FORMAT/001/X/2026')->first();
    $berkas2 = BerkasPerkara::where('nomor_berkas', 'NO-POLA-KHUSUS#2026-B')->first();

    expect($berkas1)->not->toBeNull()
        ->and($berkas1->pasals()->count())->toBe(2)
        ->and($berkas1->status_proses)->toBe('Baru')
        ->and($berkas1->progress_persen)->toBe(0)
        ->and($berkas2)->not->toBeNull()
        ->and($berkas2->pasals()->count())->toBe(1);

    // 3. Stage is permanently locked — neither Penyidik nor Administrator can edit it (PRD F-18)
    $this->actingAs($penyidikUtama)->get(route('perkara.edit', $perkara))->assertForbidden();
    $this->actingAs($admin)->get(route('perkara.edit', $perkara))->assertForbidden();
    $this->actingAs($admin)->put(route('perkara.update', $perkara), [
        'action_type' => 'lanjut',
    ])->assertForbidden();
});

it('prevents creating Perkara & Berkas Perkara if Sprindik is still Draft', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/901/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/901-DRAFT/X/2026',
        'tahap_sprindik_status' => 'draft',
    ]);

    $this->actingAs($penyidik)
        ->get(route('perkara.edit', $perkara))
        ->assertForbidden();
});

it('allows Penyidik to save Perkara and Berkas Perkara draft with empty or partial inputs', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DRAFT-EMPTY/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/DRAFT-EMPTY/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
    ]);

    // Submit draft with empty inputs
    $response = $this->actingAs($penyidik)->put(route('perkara.update', $perkara), [
        'action_type' => 'draft',
        'tkp_klasifikasi' => null,
        'tkp_legalitas' => null,
        'tkp_modus' => null,
        'ppns_utama_id' => null,
        'ppns_anggota_ids' => [],
        'tersangka' => [],
    ]);

    $response->assertRedirect(route('perkara.show', $perkara));

    $perkara->refresh();
    expect($perkara->tahap_perkara_status)->toBe('draft')
        ->and($perkara->isPerkaraLocked())->toBeFalse();

    // Verify draft can still be edited
    $this->actingAs($penyidik)
        ->get(route('perkara.edit', $perkara))
        ->assertOk();
});
