<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\InstansiHukum;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PUTUSAN/MULTI/2026',
        'tanggal_laporan_kejadian' => '2026-01-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/PUTUSAN/MULTI/2026',
        'tanggal_surat_perintah' => '2026-01-12',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Multi Dokumen Putusan',
        'nik' => '6371012345671111',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/PUTUSAN/MULTI/2026',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);

    $stages = [
        JenisTahap::SPDP->value => 'B-01/SPDP/2026',
        JenisTahap::TAHAP_1->value => 'B-02/THP1/2026',
        JenisTahap::P21->value => 'B-03/P21/2026',
        JenisTahap::TAHAP_2->value => 'B-04/THP2/2026',
    ];

    foreach ($stages as $stage => $nomor) {
        TahapanPerkara::create([
            'berkas_perkara_id' => $this->berkas->id,
            'jenis_tahap' => $stage,
            'nomor_surat' => $nomor,
            'tanggal_surat' => '2026-02-01',
            'status' => 'final',
            'finalized_at' => now(),
            'finalized_by' => $this->penyidik->id,
            'created_by' => $this->penyidik->id,
        ]);
    }

    $this->pengadilan = InstansiHukum::create([
        'nama' => 'Pengadilan Negeri Banjarmasin',
        'jenis' => 'pengadilan',
    ]);
});

test('penyidik dapat mengunggah multi-file kombinasi PDF dan Foto sekaligus pada Keputusan Pengadilan', function () {
    Storage::fake('local');

    $pdf = UploadedFile::fake()->create('salinan_resmi_putusan.pdf', 1800, 'application/pdf'); // 1.8 MB
    $foto1 = UploadedFile::fake()->create('foto_sidang_putusan.jpg', 1200, 'image/jpeg');       // 1.2 MB
    $foto2 = UploadedFile::fake()->create('foto_akta_vonis.png', 1500, 'image/png');           // 1.5 MB

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '88/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-04-01',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Terdakwa dipidana 2 tahun penjara.',
            'dokumen' => [$pdf, $foto1, $foto2],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $tahap = $this->berkas->tahapan()->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN)->first();
    expect($tahap)->not->toBeNull();
    expect($tahap->dokumen)->toHaveCount(3);

    $this->assertDatabaseHas('dokumen', [
        'tahapan_perkara_id' => $tahap->id,
        'nama_file_asli' => 'salinan_resmi_putusan.pdf',
    ]);
    $this->assertDatabaseHas('dokumen', [
        'tahapan_perkara_id' => $tahap->id,
        'nama_file_asli' => 'foto_sidang_putusan.jpg',
    ]);
    $this->assertDatabaseHas('dokumen', [
        'tahapan_perkara_id' => $tahap->id,
        'nama_file_asli' => 'foto_akta_vonis.png',
    ]);
});

test('validasi menolak jika akumulasi total ukuran seluruh berkas Keputusan Pengadilan melebihi 5 MB', function () {
    Storage::fake('local');

    $pdf = UploadedFile::fake()->create('putusan_besar.pdf', 3000, 'application/pdf'); // 3 MB
    $foto = UploadedFile::fake()->create('foto_besar.jpg', 2500, 'image/jpeg');        // 2.5 MB (Total = 5.5 MB > 5 MB)

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '89/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-04-02',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Vonis pidana.',
            'dokumen' => [$pdf, $foto],
        ]);

    $response->assertSessionHasErrors(['dokumen']);
});

test('penyidik dapat menyimpan draft Keputusan Pengadilan, menghapus berkas lama satuan dan menambah berkas baru saat update draft', function () {
    Storage::fake('local');

    $pdf1 = UploadedFile::fake()->create('draft_salinan_awal.pdf', 1000, 'application/pdf');
    $foto1 = UploadedFile::fake()->create('draft_foto_awal.jpg', 1500, 'image/jpeg');

    // 1. Simpan Draft awal dengan 2 file
    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'draft',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => 'DRAFT/90/PN.Bjm',
            'tanggal_surat' => '2026-04-03',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Draft amar putusan.',
            'dokumen' => [$pdf1, $foto1],
        ]);

    $response->assertRedirect();
    $tahap = $this->berkas->tahapan()->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN)->first();
    expect($tahap->status)->toBe('draft');
    expect($tahap->dokumen)->toHaveCount(2);

    $docPdf = $tahap->dokumen()->where('nama_file_asli', 'draft_salinan_awal.pdf')->first();
    $docFoto = $tahap->dokumen()->where('nama_file_asli', 'draft_foto_awal.jpg')->first();

    // 2. Edit Draft: Hapus draft_salinan_awal.pdf (via deleted_dokumen_ids) dan tambahkan file baru foto_tambahan.png
    $fotoBaru = UploadedFile::fake()->create('foto_tambahan.png', 1200, 'image/png');

    $responseUpdate = $this->actingAs($this->penyidik)
        ->put(route('berkas-perkara.tahapan.update', [$this->berkas, $tahap]), [
            'action_type' => 'draft',
            'nomor_surat' => 'DRAFT/90/PN.Bjm',
            'tanggal_surat' => '2026-04-03',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Draft amar putusan direvisi.',
            'deleted_dokumen_ids' => [$docPdf->id],
            'dokumen' => [$fotoBaru],
        ]);

    $responseUpdate->assertRedirect();
    $tahap->refresh();

    // draft_salinan_awal.pdf ter-soft delete
    $this->assertSoftDeleted('dokumen', ['id' => $docPdf->id]);

    // draft_foto_awal.jpg tetap bertahan
    $this->assertDatabaseHas('dokumen', [
        'id' => $docFoto->id,
        'deleted_at' => null,
    ]);

    // foto_tambahan.png baru tersimpan
    $this->assertDatabaseHas('dokumen', [
        'tahapan_perkara_id' => $tahap->id,
        'nama_file_asli' => 'foto_tambahan.png',
        'deleted_at' => null,
    ]);

    expect($tahap->dokumen()->count())->toBe(2);
});

test('finalisasi Keputusan Pengadilan ditolak jika seluruh berkas dihapus tanpa ada berkas baru', function () {
    Storage::fake('local');

    $pdf = UploadedFile::fake()->create('draft_tunggal.pdf', 1000, 'application/pdf');

    // Buat Draft awal dengan 1 file
    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'draft',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '91/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-04-04',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Draft amar.',
            'dokumen' => [$pdf],
        ]);

    $tahap = $this->berkas->tahapan()->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN)->first();
    $doc = $tahap->dokumen->first();

    // Coba simpan & lanjut (Finalisasi) sambil menghapus satu-satunya dokumen tanpa upload pengganti
    $response = $this->actingAs($this->penyidik)
        ->put(route('berkas-perkara.tahapan.update', [$this->berkas, $tahap]), [
            'action_type' => 'lanjut',
            'nomor_surat' => '91/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-04-04',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Vonis final.',
            'deleted_dokumen_ids' => [$doc->id],
        ]);

    $response->assertSessionHasErrors(['dokumen']);
});

test('halaman show tahap Keputusan Pengadilan merender daftar dokumen PDF dan galeri foto dengan Lightbox Next Prev', function () {
    Storage::fake('local');

    $pdf = UploadedFile::fake()->create('putusan_tercetak.pdf', 1000, 'application/pdf');
    $foto1 = UploadedFile::fake()->create('foto_sidang_1.jpg', 1200, 'image/jpeg');
    $foto2 = UploadedFile::fake()->create('foto_sidang_2.png', 1300, 'image/png');

    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '92/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-04-05',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Terbukti bersalah secara sah.',
            'dokumen' => [$pdf, $foto1, $foto2],
        ]);

    $tahap = $this->berkas->tahapan()->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN)->first();

    $response = $this->actingAs($this->penyidik)
        ->get(route('berkas-perkara.tahapan.show', [$this->berkas, $tahap]));

    $response->assertOk()
        ->assertSee('Salinan Dokumen Putusan (PDF)')
        ->assertSee('putusan_tercetak.pdf')
        ->assertSee('Galeri Foto &amp; Dokumentasi Putusan (2 Foto)', false)
        ->assertSee('foto_sidang_1.jpg')
        ->assertSee('foto_sidang_2.png')
        ->assertSee('prevImage()', false)
        ->assertSee('nextImage()', false)
        ->assertSee('openLightbox(', false);
});
