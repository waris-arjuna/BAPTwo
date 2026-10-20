<?php

use App\Enums\JenisTahap;
use App\Models\Alamat;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\Tkp;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);

    $alamat = Alamat::create(['alamat_detail' => 'Jl. Pengujian No. 12']);
    $this->tkp = Tkp::create(['alamat_id' => $alamat->id, 'klasifikasi_lokasi' => 'Kantor']);
});

test('perhitungan rata-rata hari kerja mengecualikan akhir pekan secara akurat', function () {
    // Kasus 1:
    // Sprindik: Jumat 2026-10-02
    // SPDP: Senin 2026-10-05 (1 hari kerja, Sabtu & Minggu dilewati)
    // TAHAP 1: Jumat 2026-10-09 (4 hari kerja)
    // P-21: Rabu 2026-10-14 (3 hari kerja)
    // TAHAP 2: Senin 2026-10-19 (3 hari kerja)
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'tkp_id' => $this->tkp->id,
        'created_by' => $this->penyidik->id,
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Uji 1',
        'nik' => '6371012345670001',
        'created_by' => $this->penyidik->id,
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/BPOM',
        'tanggal_spdp' => '2026-10-05',
        'progress_persen' => 100,
        'status_proses' => 'TAHAP II',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/001/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'nomor_surat' => 'T1/001/X/2026',
        'tanggal_surat' => '2026-10-09',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::P21,
        'nomor_surat' => 'P21/001/X/2026',
        'tanggal_surat' => '2026-10-14',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'nomor_surat' => 'T2/001/X/2026',
        'tanggal_surat' => '2026-10-19',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $service = app(DashboardService::class);
    $averages = $service->getStageAverageDays(2026);

    expect($averages)->toHaveCount(4);
    expect($averages[0]['name'])->toBe('SPDP');
    expect($averages[0]['value'])->toBe('1 Hari');
    expect($averages[0]['sample'])->toBe(1);

    expect($averages[1]['name'])->toBe('TAHAP I');
    expect($averages[1]['value'])->toBe('4 Hari');
    expect($averages[1]['sample'])->toBe(1);

    expect($averages[2]['name'])->toBe('P-21');
    expect($averages[2]['value'])->toBe('3 Hari');
    expect($averages[2]['sample'])->toBe(1);

    expect($averages[3]['name'])->toBe('TAHAP II');
    expect($averages[3]['value'])->toBe('3 Hari');
    expect($averages[3]['sample'])->toBe(1);
});

test('perhitungan rata-rata mengakumulasi banyak berkas dan melakukan pembulatan terdekat', function () {
    // Kasus 1: SPDP butuh 1 hari kerja
    $perkara1 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-02', // Jumat
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka1 = Tersangka::create([
        'perkara_id' => $perkara1->id,
        'nama' => 'Tersangka 1',
        'created_by' => $this->penyidik->id,
    ]);
    $berkas1 = BerkasPerkara::create([
        'perkara_id' => $perkara1->id,
        'tersangka_id' => $tersangka1->id,
        'nomor_berkas' => 'BP/001/X/2026',
        'tanggal_spdp' => '2026-10-05', // Senin (+1 hari kerja)
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas1->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/001/X/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // Kasus 2: SPDP butuh 4 hari kerja (Senin 2026-10-05 s.d. Jumat 2026-10-09)
    $perkara2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/002/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-05',
        'nomor_surat_perintah' => 'SP/002/X/2026',
        'tanggal_surat_perintah' => '2026-10-05',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka2 = Tersangka::create([
        'perkara_id' => $perkara2->id,
        'nama' => 'Tersangka 2',
        'created_by' => $this->penyidik->id,
    ]);
    $berkas2 = BerkasPerkara::create([
        'perkara_id' => $perkara2->id,
        'tersangka_id' => $tersangka2->id,
        'nomor_berkas' => 'BP/002/X/2026',
        'tanggal_spdp' => '2026-10-09',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas2->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/002/X/2026',
        'tanggal_surat' => '2026-10-09',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // Rata-rata SPDP = (1 + 4) / 2 = 2.5 -> pembulatan round() = 3 Hari
    $service = app(DashboardService::class);
    $averages = $service->getStageAverageDays(2026);

    expect($averages[0]['name'])->toBe('SPDP');
    expect($averages[0]['value'])->toBe('3 Hari');
    expect($averages[0]['sample'])->toBe(2);
});

test('perhitungan rata-rata terisolasi per tahun anggaran dan menampilkan strip jika belum ada data', function () {
    // Berkas di tahun 2025
    $perkara2025 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2025',
        'tanggal_laporan_kejadian' => '2025-06-02',
        'nomor_surat_perintah' => 'SP/001/X/2025',
        'tanggal_surat_perintah' => '2025-06-02',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka2025 = Tersangka::create([
        'perkara_id' => $perkara2025->id,
        'nama' => 'Tersangka 2025',
        'created_by' => $this->penyidik->id,
    ]);
    $berkas2025 = BerkasPerkara::create([
        'perkara_id' => $perkara2025->id,
        'tersangka_id' => $tersangka2025->id,
        'nomor_berkas' => 'BP/001/X/2025',
        'tanggal_spdp' => '2025-06-05',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas2025->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/001/X/2025',
        'tanggal_surat' => '2025-06-05',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $service = app(DashboardService::class);

    // Ambil tahun 2025
    $avg2025 = $service->getStageAverageDays(2025);
    expect($avg2025[0]['name'])->toBe('SPDP');
    expect($avg2025[0]['value'])->toBe('3 Hari');
    expect($avg2025[0]['sample'])->toBe(1);

    // Ambil tahun 2026 yang belum memiliki data sama sekali
    $avg2026 = $service->getStageAverageDays(2026);
    expect($avg2026[0]['name'])->toBe('SPDP');
    expect($avg2026[0]['value'])->toBe('- Hari');
    expect($avg2026[0]['sample'])->toBe(0);
    expect($avg2026[1]['value'])->toBe('- Hari');
    expect($avg2026[2]['value'])->toBe('- Hari');
    expect($avg2026[3]['value'])->toBe('- Hari');
});

test('cache rata-rata tahapan otomatis di-invalidasi saat ada tahapan baru difinalkan', function () {
    $service = app(DashboardService::class);

    // Hitung pertama kali (masih kosong)
    $first = $service->getStageAverageDays(2026);
    expect($first[0]['value'])->toBe('- Hari');

    // Verifikasi cache key tersimpan
    expect(Cache::has('dashboard_stage_averages_2026'))->toBeTrue();

    // Buat data baru
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/099/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'nomor_surat_perintah' => 'SP/099/X/2026',
        'tanggal_surat_perintah' => '2026-10-01',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Cache Test',
        'created_by' => $this->penyidik->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/099/X/2026',
        'tanggal_spdp' => '2026-10-02',
        'created_by' => $this->penyidik->id,
    ]);

    // Simpan tahapan (memicu event saved -> DashboardService::clearCache())
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/099/X/2026',
        'tanggal_surat' => '2026-10-02',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // Verifikasi cache key telah terhapus otomatis oleh event observer
    expect(Cache::has('dashboard_stage_averages_2026'))->toBeFalse();

    // Panggilan berikutnya menghasilkan data terbarui
    $second = $service->getStageAverageDays(2026);
    expect($second[0]['value'])->toBe('1 Hari');
});

test('tampilan komponen average-stage di dashboard merender label dan angka tabular dengan benar', function () {
    $response = $this->actingAs($this->penyidik)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Rata-rata Penyelesaian per Tahap');
    $response->assertSee('SPDP');
    $response->assertSee('TAHAP I');
    $response->assertSee('P-21');
    $response->assertSee('TAHAP II');
    $response->assertSee('tabular-nums', false);
    $response->assertSee('Catatan: Perhitungan waktu penyelesaian tahapan menggunakan Hari Kerja.');
});
