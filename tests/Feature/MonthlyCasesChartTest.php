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
    Cache::flush();
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $alamat = Alamat::create(['alamat_detail' => 'Jl. Pengujian No. 12']);
    $this->tkp = Tkp::create(['alamat_id' => $alamat->id, 'klasifikasi_lokasi' => 'Toko']);
});

test('DashboardService getMonthlyCasesTrends mengembalikan struktur data 12 bulan yang lengkap dan akurat', function () {
    // 1. Perkara Masuk di Januari 2026 (2 perkara) dan Maret 2026 (1 perkara)
    $perkaraJan1 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/I/2026',
        'tanggal_laporan_kejadian' => '2026-01-10',
        'tahap_laporan_status' => 'final',
        'tkp_id' => $this->tkp->id,
        'created_by' => $this->penyidik->id,
    ]);

    $perkaraJan2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/002/I/2026',
        'tanggal_laporan_kejadian' => '2026-01-20',
        'tahap_laporan_status' => 'final',
        'tkp_id' => $this->tkp->id,
        'created_by' => $this->penyidik->id,
    ]);

    $perkaraMar = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/003/III/2026',
        'tanggal_laporan_kejadian' => '2026-03-05',
        'tahap_laporan_status' => 'final',
        'tkp_id' => $this->tkp->id,
        'created_by' => $this->penyidik->id,
    ]);

    // Perkara tahun 2025 (tidak boleh terhitung di 2026)
    Perkara::create([
        'nomor_laporan_kejadian' => 'LK/099/XII/2025',
        'tanggal_laporan_kejadian' => '2025-12-25',
        'tahap_laporan_status' => 'final',
        'tkp_id' => $this->tkp->id,
        'created_by' => $this->penyidik->id,
    ]);

    // 2. Berkas Perkara Selesai dengan Tahap II di Maret 2026 (1 berkas) dan April 2026 (1 berkas)
    $tersangka1 = Tersangka::create([
        'perkara_id' => $perkaraJan1->id,
        'nama' => 'Tersangka Selesai Maret',
        'nik' => '6371012345670001',
        'created_by' => $this->penyidik->id,
    ]);

    $berkasSelesaiMar = BerkasPerkara::create([
        'perkara_id' => $perkaraJan1->id,
        'tersangka_id' => $tersangka1->id,
        'nomor_berkas' => 'BP/001/I/2026',
        'tanggal_spdp' => '2026-01-15',
        'progress_persen' => 100,
        'status_proses' => 'TAHAP II',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkasSelesaiMar->id,
        'jenis_tahap' => JenisTahap::TAHAP_2->value,
        'nomor_surat' => 'T2/001/III/2026',
        'tanggal_surat' => '2026-03-20',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $tersangka2 = Tersangka::create([
        'perkara_id' => $perkaraJan2->id,
        'nama' => 'Tersangka Selesai April',
        'nik' => '6371012345670002',
        'created_by' => $this->penyidik->id,
    ]);

    $berkasSelesaiApr = BerkasPerkara::create([
        'perkara_id' => $perkaraJan2->id,
        'tersangka_id' => $tersangka2->id,
        'nomor_berkas' => 'BP/002/I/2026',
        'tanggal_spdp' => '2026-01-25',
        'progress_persen' => 100,
        'status_proses' => 'TAHAP II',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkasSelesaiApr->id,
        'jenis_tahap' => JenisTahap::TAHAP_2->value,
        'nomor_surat' => 'T2/002/IV/2026',
        'tanggal_surat' => '2026-04-10',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // Kasus anomali/non-selesai yang tidak boleh dihitung:
    // a. Tahap II masih status 'draft'
    $tersangkaDraft = Tersangka::create([
        'perkara_id' => $perkaraMar->id,
        'nama' => 'Tersangka Draft',
        'nik' => '6371012345670003',
        'created_by' => $this->penyidik->id,
    ]);

    $berkasDraft = BerkasPerkara::create([
        'perkara_id' => $perkaraMar->id,
        'tersangka_id' => $tersangkaDraft->id,
        'nomor_berkas' => 'BP/003/III/2026',
        'tanggal_spdp' => '2026-03-10',
        'progress_persen' => 85,
        'status_proses' => 'TAHAP II',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkasDraft->id,
        'jenis_tahap' => JenisTahap::TAHAP_2->value,
        'nomor_surat' => 'T2/DRAFT/III/2026',
        'tanggal_surat' => '2026-03-25',
        'status' => 'draft',
        'created_by' => $this->penyidik->id,
    ]);

    // b. Berkas berstatus bukan selesai (misal dalam proses atau sp3)
    $tersangkaDalamProses = Tersangka::create([
        'perkara_id' => $perkaraMar->id,
        'nama' => 'Tersangka Dalam Proses',
        'nik' => '6371012345670004',
        'created_by' => $this->penyidik->id,
    ]);

    $berkasDalamProses = BerkasPerkara::create([
        'perkara_id' => $perkaraMar->id,
        'tersangka_id' => $tersangkaDalamProses->id,
        'nomor_berkas' => 'BP/004/III/2026',
        'tanggal_spdp' => '2026-03-12',
        'progress_persen' => 55,
        'status_proses' => 'TAHAP I',
        'status_formal' => null,
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkasDalamProses->id,
        'jenis_tahap' => JenisTahap::TAHAP_2->value,
        'nomor_surat' => 'T2/NONSELESAI/III/2026',
        'tanggal_surat' => '2026-03-28',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    /** @var DashboardService $service */
    $service = app(DashboardService::class);
    $trends = $service->getMonthlyCasesTrends(2026);

    // Assert struktur
    expect($trends)->toHaveKeys([
        'labels',
        'incoming',
        'completed',
        'totalIncoming',
        'totalCompleted',
        'clearanceRate',
    ]);

    expect($trends['labels'])->toBe([
        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
    ]);

    // 12 bulan array
    expect(count($trends['incoming']))->toBe(12);
    expect(count($trends['completed']))->toBe(12);

    // Incoming: Jan (index 0) = 2, Mar (index 2) = 1, sisanya 0
    expect($trends['incoming'][0])->toBe(2);
    expect($trends['incoming'][1])->toBe(0);
    expect($trends['incoming'][2])->toBe(1);
    expect($trends['incoming'][3])->toBe(0);
    expect($trends['totalIncoming'])->toBe(3);

    // Completed: Mar (index 2) = 1, Apr (index 3) = 1, sisanya 0
    expect($trends['completed'][0])->toBe(0);
    expect($trends['completed'][1])->toBe(0);
    expect($trends['completed'][2])->toBe(1);
    expect($trends['completed'][3])->toBe(1);
    expect($trends['completed'][4])->toBe(0);
    expect($trends['totalCompleted'])->toBe(2);

    // Clearance Rate: (2 / 3) * 100 = 66.7%
    expect($trends['clearanceRate'])->toBe(66.7);
});

test('DashboardController meneruskan variabel monthlyCases ke halaman dashboard', function () {
    $this->actingAs($this->penyidik);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertViewHas('monthlyCases');

    $monthlyCases = $response->viewData('monthlyCases');
    expect($monthlyCases)->toBeArray();
    expect($monthlyCases)->toHaveKey('labels');
    expect($monthlyCases)->toHaveKey('incoming');
    expect($monthlyCases)->toHaveKey('completed');
});

test('komponen monthly-cases-chart ter-render dengan judul, legenda, dan kontainer chart', function () {
    $this->actingAs($this->penyidik);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Tren Perkara Masuk & Selesai', false);
    $response->assertSee('Perkara Masuk', false);
    $response->assertSee('Perkara Selesai', false);
    $response->assertSee('x-ref="chartContainer"', false);
});
