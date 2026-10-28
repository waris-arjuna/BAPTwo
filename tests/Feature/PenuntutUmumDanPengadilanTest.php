<?php

use App\Enums\JenisTahap;
use App\Exports\PerkaraRekapExport;
use App\Models\BerkasPerkara;
use App\Models\InstansiHukum;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('persists Penuntut Umum input in Perkara and displays card only when filled', function () {
    $penyidik = User::factory()->penyidik()->create();
    $kejaksaan = InstansiHukum::create([
        'jenis' => 'kejaksaan',
        'nama' => 'Kejaksaan Negeri Banjarmasin',
        'wilayah' => 'Kota Banjarmasin',
    ]);

    $pasal = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'pasal' => 'Pasal 196',
        'nomor' => '36',
        'tahun' => '2009',
        'status' => 'aktif',
    ]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/JPU-01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/JPU-01/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
    ]);

    // 1. Perkara tanpa Penuntut Umum -> Card tidak tampil di show & Form tidak merender input pangkat
    $this->actingAs($penyidik)
        ->get(route('perkara.show', $perkara))
        ->assertOk()
        ->assertDontSee('Penuntut Umum (JPU)');

    $this->actingAs($penyidik)
        ->get(route('perkara.edit', $perkara))
        ->assertOk()
        ->assertSee('Ketik nama kejaksaan...')
        ->assertDontSee('name="penuntut_umum_pangkat"', false);

    // 2. Simpan form dengan input Penuntut Umum
    $payload = [
        'action_type' => 'draft',
        'tkp_klasifikasi' => 'retailer',
        'tkp_legalitas' => 'ilegal',
        'tkp_modus' => 'offline',
        'tkp_provinsi_kode' => '63',
        'tkp_kabupaten_kode' => '6371',
        'tkp_kecamatan_kode' => '637101',
        'tkp_desa_kode' => '6371011001',
        'tkp_alamat_detail' => 'Jl. Lambung Mangkurat No. 10',
        'ppns_utama_id' => $penyidik->id,
        'penuntut_umum_instansi_id' => $kejaksaan->id,
        'penuntut_umum_nama' => 'Bambang Trihatmojo, S.H.',
        'penuntut_umum_nip' => '198501012010011005',
        'tersangka' => [
            [
                'nama' => 'Tersangka JPU',
                'nik' => '6371010101850001',
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1985-01-01',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Wiraswasta',
                'kewarganegaraan' => 'WNI',
                'provinsi_kode' => '63',
                'kabupaten_kode' => '6371',
                'kecamatan_kode' => '637101',
                'desa_kode' => '6371011001',
                'alamat_detail' => 'Jl. Pangeran Samudera No. 5',
                'pasal_ids' => [$pasal->id],
            ],
        ],
    ];

    $this->actingAs($penyidik)
        ->put(route('perkara.update', $perkara), $payload)
        ->assertRedirect(route('perkara.show', $perkara));

    $perkara->refresh();
    expect($perkara->penuntut_umum_instansi_id)->toBe($kejaksaan->id)
        ->and($perkara->penuntut_umum_nama)->toBe('Bambang Trihatmojo, S.H.')
        ->and($perkara->penuntut_umum_nip)->toBe('198501012010011005');

    // 3. Card Penuntut Umum sekarang tampil di detail perkara
    $this->actingAs($penyidik)
        ->get(route('perkara.show', $perkara))
        ->assertOk()
        ->assertSee('Penuntut Umum (JPU)')
        ->assertSee('Kejaksaan Negeri Banjarmasin')
        ->assertSee('Bambang Trihatmojo, S.H.')
        ->assertSee('198501012010011005');
});

it('persists Pengadilan and Amar Putusan in Putusan Perkara and displays details', function () {
    Storage::fake('local');
    $penyidik = User::factory()->penyidik()->create();

    $pengadilan = InstansiHukum::create([
        'jenis' => 'pengadilan',
        'nama' => 'Pengadilan Negeri Banjarmasin',
        'wilayah' => 'Kota Banjarmasin',
    ]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PUTUSAN-01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/PUTUSAN-01/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Terdakwa Sidang',
        'nik' => '6371010101850002',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/PUTUSAN-01/2026',
        'status_formal' => 'selesai',
        'status_proses' => 'TAHAP II',
        'progress_persen' => 100,
    ]);

    // Berkas harus sudah melewati Tahap 2 untuk bisa mencatat Keputusan Pengadilan
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'nomor_surat' => 'B-T2/PUTUSAN/2026',
        'tanggal_surat' => '2026-10-15',
        'status' => 'final',
        'finalized_at' => now(),
    ]);

    // Akses form tambah Keputusan Pengadilan
    $this->actingAs($penyidik)
        ->get(route('berkas-perkara.tahapan.create', ['berkas' => $berkas, 'jenis' => 'keputusan_pengadilan']))
        ->assertOk()
        ->assertSee('Nama Pengadilan')
        ->assertSee('Pengadilan Negeri Banjarmasin')
        ->assertSee('Ketik nama pengadilan...')
        ->assertSee('Amar Putusan Pengadilan');

    // Simpan Keputusan Pengadilan
    $file = UploadedFile::fake()->create('putusan_sidang.pdf', 500, 'application/pdf');

    $response = $this->actingAs($penyidik)->post(route('berkas-perkara.tahapan.store', $berkas), [
        'action_type' => 'lanjut',
        'jenis_tahap' => 'keputusan_pengadilan',
        'nomor_surat' => '123/Pid.Sus/2026/PN.Bjm',
        'tanggal_surat' => '2026-10-20',
        'pengadilan_instansi_id' => $pengadilan->id,
        'amar_putusan' => 'Menjatuhkan pidana penjara selama 2 (dua) tahun dan denda Rp 50.000.000.',
        'dokumen' => $file,
    ]);

    $putusanTahap = TahapanPerkara::where('berkas_perkara_id', $berkas->id)
        ->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN)
        ->first();

    expect($putusanTahap)->not->toBeNull()
        ->and($putusanTahap->pengadilan_instansi_id)->toBe($pengadilan->id)
        ->and($putusanTahap->amar_putusan)->toContain('Menjatuhkan pidana penjara');

    // Detail Putusan menampilkan Pengadilan dan Amar Putusan
    $this->actingAs($penyidik)
        ->get(route('berkas-perkara.tahapan.show', [$berkas, $putusanTahap]))
        ->assertOk()
        ->assertSee('Pengadilan Negeri Banjarmasin')
        ->assertSee('Menjatuhkan pidana penjara selama 2 (dua) tahun');
});

it('exports Penuntut Umum and Pengadilan correctly in 44-column Excel export', function () {
    $kejaksaan = InstansiHukum::create([
        'jenis' => 'kejaksaan',
        'nama' => 'Kejaksaan Negeri Banjarbaru',
        'wilayah' => 'Kota Banjarbaru',
    ]);

    $pengadilan = InstansiHukum::create([
        'jenis' => 'pengadilan',
        'nama' => 'Pengadilan Negeri Banjarbaru',
        'wilayah' => 'Kota Banjarbaru',
    ]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/EXP-01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/EXP-01/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
        'penuntut_umum_instansi_id' => $kejaksaan->id,
        'penuntut_umum_nama' => 'Jaksa Ekspor, S.H.',
        'penuntut_umum_nip' => '198711112015011003',
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Ekspor',
        'nik' => '6371010101850003',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/EXP-01/2026',
        'status_formal' => 'selesai',
        'status_proses' => 'Keputusan Pengadilan',
        'progress_persen' => 100,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN,
        'nomor_surat' => '55/Pid.Sus/2026/PN.Bjb',
        'tanggal_surat' => '2026-10-21',
        'pengadilan_instansi_id' => $pengadilan->id,
        'status' => 'final',
        'finalized_at' => now(),
    ]);

    $export = new PerkaraRekapExport('semua');
    $spreadsheet = $export->generateSpreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Baris data pertama pada row 7 (setelah header judul, periode, header bertingkat, dan nomor kolom)
    expect($sheet->getCell('AO7')->getValue())->toBe('Kejaksaan Negeri Banjarbaru')
        ->and($sheet->getCell('AP7')->getValue())->toContain('Jaksa Ekspor, S.H.')
        ->and($sheet->getCell('AQ7')->getValue())->toBe('Pengadilan Negeri Banjarbaru');
});
