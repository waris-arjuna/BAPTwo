<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
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
        'nomor_laporan_kejadian' => 'LK/PUTUSAN/2026',
        'tanggal_laporan_kejadian' => '2026-01-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/PUTUSAN/2026',
        'tanggal_surat_perintah' => '2026-01-12',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Putusan',
        'nik' => '6371012345678888',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/PUTUSAN/2026',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas telah melalui SPDP, Tahap 1, P21, dan Tahap 2
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

test('penyidik dapat menyimpan tahap Keputusan Pengadilan dengan dokumen gambar JPG hingga 5 MB', function () {
    Storage::fake('local');
    $imageJpg = UploadedFile::fake()->create('salinan_putusan.jpg', 4500, 'image/jpeg'); // 4.5 MB

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '123/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-03-15',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Menyatakan Terdakwa terbukti secara sah dan meyakinkan bersalah.',
            'dokumen' => $imageJpg,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tahapan_perkara', [
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
        'nomor_surat' => '123/Pid.Sus/2026/PN.Bjm',
        'amar_putusan' => 'Menyatakan Terdakwa terbukti secara sah dan meyakinkan bersalah.',
        'status' => 'final',
    ]);

    $this->assertDatabaseHas('dokumen', [
        'berkas_perkara_id' => $this->berkas->id,
        'nama_file_asli' => 'salinan_putusan.jpg',
    ]);
});

test('penyidik dapat menyimpan tahap Keputusan Pengadilan dengan dokumen gambar PNG atau WebP', function () {
    Storage::fake('local');
    $imagePng = UploadedFile::fake()->create('putusan_foto.png', 4000, 'image/png'); // 4 MB

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '124/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-03-16',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Pidana penjara 1 tahun.',
            'dokumen' => $imagePng,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('dokumen', [
        'berkas_perkara_id' => $this->berkas->id,
        'nama_file_asli' => 'putusan_foto.png',
    ]);
});

test('penyidik dapat menyimpan tahap Keputusan Pengadilan dengan dokumen PDF hingga 5 MB', function () {
    Storage::fake('local');
    $pdf = UploadedFile::fake()->create('salinan_resmi_putusan.pdf', 4800, 'application/pdf'); // 4.8 MB

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '125/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-03-17',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Denda Rp 50.000.000.',
            'dokumen' => $pdf,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('dokumen', [
        'berkas_perkara_id' => $this->berkas->id,
        'nama_file_asli' => 'salinan_resmi_putusan.pdf',
    ]);
});

test('validasi input Keputusan Pengadilan menolak file gambar lebih dari 5 MB', function () {
    Storage::fake('local');
    $largeImage = UploadedFile::fake()->create('putusan_terlalu_besar.jpg', 6000, 'image/jpeg'); // 6 MB > 5 MB

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '126/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-03-18',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'dokumen' => $largeImage,
        ]);

    $response->assertSessionHasErrors(['dokumen']);
});

test('validasi input Keputusan Pengadilan menolak format file selain PDF atau Gambar', function () {
    Storage::fake('local');
    $txt = UploadedFile::fake()->create('catatan_sidang.txt', 500, 'text/plain');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '127/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-03-19',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'dokumen' => $txt,
        ]);

    $response->assertSessionHasErrors(['dokumen']);
});

test('tahapan lain tetap menolak dokumen gambar dan hanya menerima PDF', function () {
    Storage::fake('local');
    $freshTersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Baru SPDP',
        'nik' => '6371012345677777',
        'created_by' => $this->penyidik->id,
    ]);

    $freshBerkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $freshTersangka->id,
        'nomor_berkas' => 'BP/SPDP/BARU/2026',
        'created_by' => $this->penyidik->id,
    ]);

    $image = UploadedFile::fake()->create('spdp.jpg', 2000, 'image/jpeg');

    $response = $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $freshBerkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::SPDP->value,
            'nomor_surat' => 'B-01/SPDP/2026',
            'tanggal_surat' => '2026-01-20',
            'dokumen' => $image,
        ]);

    $response->assertSessionHasErrors(['dokumen']);
});

test('halaman show tahap Keputusan Pengadilan merender pratinjau gambar visual dan modal lightbox zoom', function () {
    Storage::fake('local');
    $image = UploadedFile::fake()->create('foto_akta_putusan.png', 1500, 'image/png');

    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $this->berkas), [
            'action_type' => 'lanjut',
            'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
            'nomor_surat' => '999/Pid.Sus/2026/PN.Bjm',
            'tanggal_surat' => '2026-03-20',
            'pengadilan_instansi_id' => $this->pengadilan->id,
            'amar_putusan' => 'Vonis berkekuatan hukum tetap.',
            'dokumen' => $image,
        ]);

    $putusan = $this->berkas->tahapan()->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN)->first();

    $response = $this->actingAs($this->penyidik)
        ->get(route('berkas-perkara.tahapan.show', [$this->berkas, $putusan]));

    $response->assertOk()
        ->assertSee('Galeri Foto')
        ->assertSee('foto_akta_putusan.png')
        ->assertSee('x-show="isLightboxOpen"', false)
        ->assertSee('Unduh Gambar')
        ->assertSee('Vonis berkekuatan hukum tetap.');
});
