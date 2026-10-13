<?php

use App\Enums\JenisTahap;
use App\Enums\UserRole;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');

    $this->penyidik = User::factory()->create([
        'role' => UserRole::PENYIDIK,
    ]);

    $this->admin = User::factory()->create([
        'role' => UserRole::ADMINISTRATOR,
    ]);

    $this->pimpinan = User::factory()->create([
        'role' => UserRole::PIMPINAN,
    ]);

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
        'nama' => 'Hendra Pratama',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/BPOM',
        'status_proses' => 'Baru',
        'progress_persen' => 0,
        'created_by' => $this->penyidik->id,
    ]);
});

test('penyidik dapat menyimpan data tahapan sebagai draft tanpa menaikkan progress atau mengunci tahap', function () {
    $response = $this->actingAs($this->penyidik)->post(route('berkas-perkara.tahapan.store', $this->berkas), [
        'action_type' => 'draft',
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => '', // kosong diperbolehkan saat draft
        'tanggal_surat' => '',
        'catatan' => 'Draft awal SPDP belum ada nomor resmi',
    ]);

    $tahap = TahapanPerkara::where('berkas_perkara_id', $this->berkas->id)->first();
    expect($tahap)->not->toBeNull()
        ->and($tahap->status)->toBe('draft')
        ->and($tahap->finalized_at)->toBeNull()
        ->and($tahap->isLocked())->toBeFalse();

    $this->berkas->refresh();
    // Draft tidak menaikkan progress atau mengubah status proses
    expect($this->berkas->progress_persen)->toBe(0)
        ->and($this->berkas->status_proses)->toBe('Baru');

    $response->assertRedirect(route('berkas-perkara.tahapan.show', [$this->berkas, $tahap]));
    $this->assertDatabaseHas('audit_log', [
        'aksi' => 'simpan_draft_tahap',
        'berkas_perkara_id' => $this->berkas->id,
    ]);
});

test('simpan dan lanjut mewajibkan nomor dan tanggal surat serta mengunci tahapan secara permanen', function () {
    // 1. Gagal jika nomor dan tanggal kosong saat Simpan & Lanjut
    $this->actingAs($this->penyidik)->post(route('berkas-perkara.tahapan.store', $this->berkas), [
        'action_type' => 'lanjut',
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => '',
        'tanggal_surat' => '',
    ])->assertSessionHasErrors(['nomor_surat', 'tanggal_surat']);

    // 2. Berhasil saat field wajib lengkap
    $filePdf = UploadedFile::fake()->create('spdp.pdf', 500, 'application/pdf');

    $response = $this->actingAs($this->penyidik)->post(route('berkas-perkara.tahapan.store', $this->berkas), [
        'action_type' => 'lanjut',
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'SPDP/001/X/2026/BPOM',
        'tanggal_surat' => '2026-10-05',
        'dokumen' => $filePdf,
        'catatan' => 'SPDP resmi telah dikirim ke Kejaksaan',
    ]);

    $tahap = TahapanPerkara::where('berkas_perkara_id', $this->berkas->id)->first();
    expect($tahap)->not->toBeNull()
        ->and($tahap->status)->toBe('final')
        ->and($tahap->finalized_at)->not->toBeNull()
        ->and($tahap->finalized_by)->toBe($this->penyidik->id)
        ->and($tahap->isLocked())->toBeTrue();

    // Progress berkas perkara naik ke 15% dan status proses menjadi SPDP
    $this->berkas->refresh();
    expect($this->berkas->progress_persen)->toBe(15)
        ->and($this->berkas->status_proses)->toBe('SPDP')
        ->and($this->berkas->tanggal_spdp?->format('Y-m-d'))->toBe('2026-10-05')
        ->and($this->berkas->sla_deadline?->format('Y-m-d'))->toBe('2026-11-05');

    $response->assertRedirect(route('berkas-perkara.tahapan.show', [$this->berkas, $tahap]));

    // Dokumen tersimpan
    $this->assertDatabaseHas('dokumen', [
        'berkas_perkara_id' => $this->berkas->id,
        'tahapan_perkara_id' => $tahap->id,
        'kategori' => 'tahapan',
        'nama_file_asli' => 'spdp.pdf',
    ]);

    // Audit log tersimpan
    $this->assertDatabaseHas('audit_log', [
        'aksi' => 'finalisasi_tahap',
        'berkas_perkara_id' => $this->berkas->id,
    ]);
});

test('tahapan yang sudah difinalkan terkunci permanen dan ditolak saat diedit bahkan oleh administrator', function () {
    $tahapFinal = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/001/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'finalized_at' => now(),
        'finalized_by' => $this->penyidik->id,
        'created_by' => $this->penyidik->id,
    ]);

    // Percobaan edit oleh Penyidik ditolak (redirect with warning / 403)
    $this->actingAs($this->penyidik)
        ->get(route('berkas-perkara.tahapan.edit', [$this->berkas, $tahapFinal]))
        ->assertRedirect(route('berkas-perkara.tahapan.show', [$this->berkas, $tahapFinal]));

    $this->actingAs($this->penyidik)
        ->put(route('berkas-perkara.tahapan.update', [$this->berkas, $tahapFinal]), [
            'action_type' => 'draft',
            'nomor_surat' => 'EDIT-PENYIDIK',
        ])
        ->assertForbidden();

    // Percobaan edit oleh Administrator JUGA DITOLAK (zero override - PRD F-18)
    $this->actingAs($this->admin)
        ->get(route('berkas-perkara.tahapan.edit', [$this->berkas, $tahapFinal]))
        ->assertRedirect(route('berkas-perkara.tahapan.show', [$this->berkas, $tahapFinal]));

    $this->actingAs($this->admin)
        ->put(route('berkas-perkara.tahapan.update', [$this->berkas, $tahapFinal]), [
            'action_type' => 'lanjut',
            'nomor_surat' => 'EDIT-ADMIN',
            'tanggal_surat' => '2026-10-06',
        ])
        ->assertForbidden();
});

test('halaman formulir dan detail menampilkan Card Referensi tahapan sebelumnya dan visual indicator', function () {
    // Buat SPDP final
    $spdp = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/001/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    // Buka form tahap berikutnya (Tahap 1)
    $response = $this->actingAs($this->penyidik)
        ->get(route('berkas-perkara.tahapan.create', ['berkas' => $this->berkas, 'jenis' => 'tahap_1']))
        ->assertOk();

    // Memuat Card Referensi tahapan sebelumnya
    $response->assertSee('Referensi Tahapan Sebelumnya')
        ->assertSee('Laporan Kejadian')
        ->assertSee('Surat Perintah Penyidikan (Sprindik)')
        ->assertSee('SPDP')
        ->assertSee('SPDP/001/X/2026')
        // Memuat 3 tombol aksi
        ->assertSee('Batal')
        ->assertSee('Simpan sebagai Draft')
        ->assertSee('Simpan &amp; Lanjut Tahap Berikutnya', false);

    // Buka show SPDP yang sudah terkunci
    $showResponse = $this->actingAs($this->penyidik)
        ->get(route('berkas-perkara.tahapan.show', [$this->berkas, $spdp]))
        ->assertOk();

    // Memuat visual indicator terkunci
    $showResponse->assertSee('Tahap Selesai / Terkunci Permanen');
});

test('upload dokumen selain format PDF ditolak per aturan D-007', function () {
    $fileDocx = UploadedFile::fake()->create('dokumen.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $this->actingAs($this->penyidik)->post(route('berkas-perkara.tahapan.store', $this->berkas), [
        'action_type' => 'lanjut',
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'SPDP/002/X/2026',
        'tanggal_surat' => '2026-10-05',
        'dokumen' => $fileDocx,
    ])->assertSessionHasErrors(['dokumen']);
});

test('pimpinan dibatasi hanya dapat melihat show tahapan dan diblokir dari aksi store update', function () {
    $spdp = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/001/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // Pimpinan bisa melihat detail (show)
    $this->actingAs($this->pimpinan)
        ->get(route('berkas-perkara.tahapan.show', [$this->berkas, $spdp]))
        ->assertOk();

    // Pimpinan diblokir dari halaman create
    $this->actingAs($this->pimpinan)
        ->get(route('berkas-perkara.tahapan.create', $this->berkas))
        ->assertForbidden();

    // Pimpinan diblokir dari store
    $this->actingAs($this->pimpinan)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'jenis_tahap' => JenisTahap::SPDP->value,
            'nomor_surat' => 'SPDP/003/X/2026',
            'tanggal_surat' => '2026-10-05',
        ])
        ->assertForbidden();
});
