<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Dpo;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\Sp3Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('komponen modal pilih berkas tahap sebelumnya merender input pencarian dan pesan data tidak ditemukan', function () {
    $penyidik = User::factory()->penyidik()->create();

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/B27/2026/001',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/B27/2026/001',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Ahmad Kasus 27 Okt',
        'nik' => '6371010101900002',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/B27/2026/001',
        'status_formal' => null,
        'status_proses' => 'Baru',
        'progress_persen' => 0,
    ]);

    // 1. Uji pada halaman SPDP
    $responseSpdp = $this->actingAs($penyidik)->get(route('spdp.index'));
    $responseSpdp->assertOk();
    $responseSpdp->assertSee('x-model="searchQuery"', false);
    $responseSpdp->assertSee('Data tidak ditemukan');
    $responseSpdp->assertSee('BP/B27/2026/001');
    $responseSpdp->assertSee('Ahmad Kasus 27 Okt');
    $responseSpdp->assertSee(route('berkas-perkara.tahapan.create', ['berkas' => $berkas, 'jenis' => 'spdp']));

    // Simulasikan berkas menyelesaikan SPDP untuk Tahap 1
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'status' => 'final',
        'nomor_surat' => 'SPDP/B27/01',
        'tanggal_surat' => '2026-10-12',
        'finalized_at' => now(),
    ]);

    // 2. Uji pada halaman Tahap 1
    $responseTahap1 = $this->actingAs($penyidik)->get(route('tahap-1.index'));
    $responseTahap1->assertOk();
    $responseTahap1->assertSee('x-model="searchQuery"', false);
    $responseTahap1->assertSee('Data tidak ditemukan');
    $responseTahap1->assertSee('Pilih Berkas Perkara untuk Tahap 1');
    $responseTahap1->assertSee(route('berkas-perkara.tahapan.create', ['berkas' => $berkas, 'jenis' => 'tahap_1']));

    // Simulasikan berkas menyelesaikan Tahap 1 untuk P-21
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1->value,
        'status' => 'final',
        'nomor_surat' => 'T1/B27/01',
        'tanggal_surat' => '2026-10-14',
        'finalized_at' => now(),
    ]);

    // 3. Uji pada halaman P-21
    $responseP21 = $this->actingAs($penyidik)->get(route('p21.index'));
    $responseP21->assertOk();
    $responseP21->assertSee('x-model="searchQuery"', false);
    $responseP21->assertSee('Data tidak ditemukan');
    $responseP21->assertSee('Pilih Berkas Perkara untuk P-21');
    $responseP21->assertSee(route('berkas-perkara.tahapan.create', ['berkas' => $berkas, 'jenis' => 'p21']));

    // Simulasikan berkas menyelesaikan P-21 untuk Tahap 2
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::P21->value,
        'status' => 'final',
        'nomor_surat' => 'P21/B27/01',
        'tanggal_surat' => '2026-10-16',
        'finalized_at' => now(),
    ]);

    // 4. Uji pada halaman Tahap 2
    $responseTahap2 = $this->actingAs($penyidik)->get(route('tahap-2.index'));
    $responseTahap2->assertOk();
    $responseTahap2->assertSee('x-model="searchQuery"', false);
    $responseTahap2->assertSee('Data tidak ditemukan');
    $responseTahap2->assertSee('Pilih Berkas Perkara untuk Tahap 2');
    $responseTahap2->assertSee(route('berkas-perkara.tahapan.create', ['berkas' => $berkas, 'jenis' => 'tahap_2']));

    // Simulasikan berkas menyelesaikan Tahap 2 untuk Putusan Perkara
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_2->value,
        'status' => 'final',
        'nomor_surat' => 'T2/B27/01',
        'tanggal_surat' => '2026-10-18',
        'finalized_at' => now(),
    ]);

    // 5. Uji pada halaman Putusan Perkara
    $responsePutusan = $this->actingAs($penyidik)->get(route('putusan-perkara.index'));
    $responsePutusan->assertOk();
    $responsePutusan->assertSee('x-model="searchQuery"', false);
    $responsePutusan->assertSee('Data tidak ditemukan');
    $responsePutusan->assertSee('Pilih Berkas Perkara untuk Pencatatan Putusan');
    $responsePutusan->assertSee(route('berkas-perkara.tahapan.create', ['berkas' => $berkas, 'jenis' => 'keputusan_pengadilan']));
});

test('ekspor excel mengembalikan status 422 json saat periode kosong dengan header X-Export-Fetch dan menyertakan header filename saat sukses', function () {
    $penyidik = User::factory()->penyidik()->create();

    // 1. Saat data kosong dengan header X-Export-Fetch
    $responseEmpty = $this->actingAs($penyidik)
        ->withHeaders(['X-Export-Fetch' => '1'])
        ->get(route('perkara.export', ['mode' => 'tahun', 'tahun' => 2020]));

    $responseEmpty->assertStatus(422);
    $responseEmpty->assertJson([
        'message' => 'Tidak ada data pada periode ini',
    ]);

    // 2. Buat data perkara agar ekspor berhasil
    Perkara::create([
        'nomor_laporan_kejadian' => 'LK/EXP-01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/EXP-01/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
    ]);

    $responseSuccess = $this->actingAs($penyidik)
        ->withHeaders(['X-Export-Fetch' => '1'])
        ->get(route('perkara.export', ['mode' => 'tahun', 'tahun' => 2026]));

    $responseSuccess->assertOk();
    $responseSuccess->assertHeader('X-Export-Filename');

    // 3. Verifikasi komponen export-excel-modal memiliki teks 'Mengunduh data...' dan state 'isDownloading'
    $responseIndex = $this->actingAs($penyidik)->get(route('data-perkara.index'));
    $responseIndex->assertOk();
    $responseIndex->assertSee('Mengunduh data...');
    $responseIndex->assertSee('isDownloading');
});

test('pengalihan rute seluruh perkara dan progress perkara bagi admin dan penyidik ke data perkara dengan query string terbawa', function () {
    $admin = User::factory()->administrator()->create();
    $penyidik = User::factory()->penyidik()->create();
    $pimpinan = User::factory()->pimpinan()->create();
    $umum = User::factory()->umum()->create();

    // 1. Admin diarahkan dari seluruh-perkara dan progress-perkara ke data-perkara
    $resAdminSeluruh = $this->actingAs($admin)->get(route('seluruh-perkara.index', ['status' => 'selesai', 'tahun' => 2026]));
    $resAdminSeluruh->assertRedirect(route('data-perkara.index', ['status' => 'selesai', 'tahun' => 2026]));

    $resAdminProgress = $this->actingAs($admin)->get(route('progress-perkara.index', ['search' => 'kasus-01']));
    $resAdminProgress->assertRedirect(route('data-perkara.index', ['search' => 'kasus-01']));

    // 2. Penyidik diarahkan dari seluruh-perkara dan progress-perkara ke data-perkara
    $resPenyidikSeluruh = $this->actingAs($penyidik)->get(route('seluruh-perkara.index', ['status' => 'dalam_proses']));
    $resPenyidikSeluruh->assertRedirect(route('data-perkara.index', ['status' => 'dalam_proses']));

    $resPenyidikProgress = $this->actingAs($penyidik)->get(route('progress-perkara.index'));
    $resPenyidikProgress->assertRedirect(route('data-perkara.index'));

    // 3. Pimpinan dan Umum tetap dapat mengakses seluruh-perkara dan progress-perkara
    $resPimpinanSeluruh = $this->actingAs($pimpinan)->get(route('seluruh-perkara.index'));
    $resPimpinanSeluruh->assertOk();

    $resPimpinanProgress = $this->actingAs($pimpinan)->get(route('progress-perkara.index'));
    $resPimpinanProgress->assertOk();

    $resUmumSeluruh = $this->actingAs($umum)->get(route('seluruh-perkara.index'));
    $resUmumSeluruh->assertOk();

    $resUmumProgress = $this->actingAs($umum)->get(route('progress-perkara.index'));
    $resUmumProgress->assertOk();
});

test('penerbitan sp3 pada berkas perkara dpo aktif otomatis mengisi alasan pencabutan Ditutup karena SP3', function () {
    $penyidik = User::factory()->penyidik()->create();

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SP3-DPO/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/SP3-DPO/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Buronan SP3',
        'nik' => '6371010101900003',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/SP3-DPO/2026',
        'is_dpo' => true,
    ]);

    $dpo = Dpo::create([
        'berkas_perkara_id' => $berkas->id,
        'tanggal_penetapan' => '2026-10-12',
        'nomor_dpo' => 'DPO/001/2026',
        'alasan' => 'Melarikan diri',
        'status' => 'aktif',
        'created_by' => $penyidik->id,
    ]);

    $file = UploadedFile::fake()->create('surat_sp3.pdf', 100, 'application/pdf');

    $sp3Service = app(Sp3Service::class);
    $sp3Service->issueSp3($berkas, [
        'nomor_surat' => 'SP3/001/X/2026',
        'tanggal_sp3' => '2026-10-20',
        'alasan' => 'tidak_cukup_bukti',
        'keterangan' => 'Uji penutupan DPO',
    ], $file, $penyidik->id);

    $dpo->refresh();
    expect($dpo->status)->toBe('nonaktif');
    expect($dpo->alasan_pencabutan)->toBe('Ditutup karena SP3');
});

test('evaluasi status formal perkara mematuhi prioritas SP3 lebih tinggi dari Selesai lebih tinggi dari DPO Aktif lebih tinggi dari Dalam Proses', function () {
    // 1. Kasus Perkara dengan 2 berkas: satu Selesai, satu SP3 -> Status Perkara adalah 'tidak_selesai' (SP3)
    $perkara1 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PRIO-01/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/PRIO-01/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
    ]);
    $t1A = Tersangka::create(['perkara_id' => $perkara1->id, 'nama' => 'Tersangka 1A', 'nik' => '6371010101900011', 'jenis_kelamin' => 'laki-laki']);
    $t1B = Tersangka::create(['perkara_id' => $perkara1->id, 'nama' => 'Tersangka 1B', 'nik' => '6371010101900012', 'jenis_kelamin' => 'laki-laki']);

    $b1 = BerkasPerkara::create([
        'perkara_id' => $perkara1->id,
        'tersangka_id' => $t1A->id,
        'nomor_berkas' => 'BP/PRIO-01A/2026',
        'status_formal' => 'selesai',
    ]);
    $b2 = BerkasPerkara::create([
        'perkara_id' => $perkara1->id,
        'tersangka_id' => $t1B->id,
        'nomor_berkas' => 'BP/PRIO-01B/2026',
        'status_formal' => 'tidak_selesai',
    ]);

    $perkara1->refresh();
    expect($perkara1->status_formal)->toBe('tidak_selesai');
    expect($perkara1->status_formal_label)->toBe('Tidak Selesai / SP3');

    // 2. Kasus Perkara dengan 2 berkas: satu Selesai, satu Dalam Proses -> Status Perkara adalah 'dalam_proses'
    $perkara2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PRIO-02/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
    ]);
    $t2A = Tersangka::create(['perkara_id' => $perkara2->id, 'nama' => 'Tersangka 2A', 'nik' => '6371010101900021', 'jenis_kelamin' => 'laki-laki']);
    $t2B = Tersangka::create(['perkara_id' => $perkara2->id, 'nama' => 'Tersangka 2B', 'nik' => '6371010101900022', 'jenis_kelamin' => 'laki-laki']);

    BerkasPerkara::create([
        'perkara_id' => $perkara2->id,
        'tersangka_id' => $t2A->id,
        'nomor_berkas' => 'BP/PRIO-02A/2026',
        'status_formal' => 'selesai',
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara2->id,
        'tersangka_id' => $t2B->id,
        'nomor_berkas' => 'BP/PRIO-02B/2026',
        'status_formal' => null,
    ]);

    $perkara2->refresh();
    expect($perkara2->status_formal)->toBe('dalam_proses');

    // 3. Kasus Perkara dengan 2 berkas: satu Dalam Proses, satu DPO Aktif -> Status Perkara adalah 'dpo_aktif'
    $perkara3 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PRIO-03/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
    ]);
    $t3A = Tersangka::create(['perkara_id' => $perkara3->id, 'nama' => 'Tersangka 3A', 'nik' => '6371010101900031', 'jenis_kelamin' => 'laki-laki']);
    $t3B = Tersangka::create(['perkara_id' => $perkara3->id, 'nama' => 'Tersangka 3B', 'nik' => '6371010101900032', 'jenis_kelamin' => 'laki-laki']);

    BerkasPerkara::create([
        'perkara_id' => $perkara3->id,
        'tersangka_id' => $t3A->id,
        'nomor_berkas' => 'BP/PRIO-03A/2026',
        'status_formal' => null,
        'is_dpo' => false,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara3->id,
        'tersangka_id' => $t3B->id,
        'nomor_berkas' => 'BP/PRIO-03B/2026',
        'status_formal' => null,
        'is_dpo' => true,
    ]);

    $perkara3->refresh();
    expect($perkara3->status_formal)->toBe('dpo_aktif');
    expect($perkara3->status_formal_label)->toBe('DPO Aktif');

    // 4. Kasus Perkara dengan semua berkas Selesai -> Status Perkara adalah 'selesai'
    $perkara4 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PRIO-04/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
    ]);
    $t4A = Tersangka::create(['perkara_id' => $perkara4->id, 'nama' => 'Tersangka 4A', 'nik' => '6371010101900041', 'jenis_kelamin' => 'laki-laki']);
    $t4B = Tersangka::create(['perkara_id' => $perkara4->id, 'nama' => 'Tersangka 4B', 'nik' => '6371010101900042', 'jenis_kelamin' => 'laki-laki']);

    BerkasPerkara::create([
        'perkara_id' => $perkara4->id,
        'tersangka_id' => $t4A->id,
        'nomor_berkas' => 'BP/PRIO-04A/2026',
        'status_formal' => 'selesai',
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara4->id,
        'tersangka_id' => $t4B->id,
        'nomor_berkas' => 'BP/PRIO-04B/2026',
        'status_formal' => 'selesai',
    ]);

    $perkara4->refresh();
    expect($perkara4->status_formal)->toBe('selesai');
    expect($perkara4->status_formal_label)->toBe('Selesai');
});

test('halaman data perkara memuat 5 card ringkasan, filter ppns utama untuk admin, dan kolom status dan sla', function () {
    $admin = User::factory()->administrator()->create();
    $penyidik = User::factory()->penyidik()->create();

    // Buat data perkara
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DP-01/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/DP-01/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
    ]);
    $perkara->ppnsUsers()->attach($penyidik->id, ['peran' => 'utama']);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka DP',
        'nik' => '6371010101900099',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/DP-01/2026',
        'status_formal' => null,
    ]);

    // 1. Uji akses Admin: melihat 5 card info, filter PPNS Utama, dan kolom Status & SLA
    $resAdmin = $this->actingAs($admin)->get(route('data-perkara.index'));
    $resAdmin->assertOk();
    $resAdmin->assertSee('Total Perkara');
    $resAdmin->assertSee('Dalam Proses');
    $resAdmin->assertSee('DPO Aktif');
    $resAdmin->assertSee('Selesai');
    $resAdmin->assertSee('Tidak Selesai / SP3');
    $resAdmin->assertSee('perkara</span>', false);
    $resAdmin->assertSee('name="ppns_id"', false);
    $resAdmin->assertSee('name="tahap"', false);
    $resAdmin->assertSee('Status & Tenggang Waktu', false);
    $resAdmin->assertSee('Tahap:');

    // 2. Uji akses Penyidik: filter PPNS Utama disembunyikan
    $resPenyidik = $this->actingAs($penyidik)->get(route('data-perkara.index'));
    $resPenyidik->assertOk();
    $resPenyidik->assertDontSee('name="ppns_id"', false);

    // 3. Uji filter status formal dan tahap
    $resFilter = $this->actingAs($admin)->get(route('data-perkara.index', ['status' => 'Dalam Proses', 'tahap' => 'Pra-SPDP']));
    $resFilter->assertOk();
    $resFilter->assertSee('LK/DP-01/2026');
    $resFilter->assertSee('Tersangka DP');
});
