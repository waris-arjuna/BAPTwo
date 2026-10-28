<?php

use App\Enums\JenisTahap;
use App\Models\Alamat;
use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\Tkp;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $this->pimpinan = User::factory()->create(['role' => 'pimpinan']);
    $this->admin = User::factory()->create(['role' => 'administrator']);

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $alamat = Alamat::create(['alamat_detail' => 'Jl. Test No. 1']);
    $tkp = Tkp::create(['alamat_id' => $alamat->id, 'klasifikasi_lokasi' => 'Gudang']);
    $this->perkara->update(['tkp_id' => $tkp->id]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Budi Santoso',
        'nik' => '6371012345670001',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/BPOM',
        'progress_persen' => 15,
        'status_proses' => 'SPDP',
        'status_formal' => null,
        'created_by' => $this->penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/001/X/2026',
        'tanggal_surat' => '2026-10-03',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $pasal = Pasal::create([
        'nama_pasal' => 'Pasal 196 UU Kesehatan',
        'pasal' => '196',
        'deskripsi' => 'Ketentuan pidana sediaan farmasi',
        'status' => 'aktif',
    ]);
    $this->berkas->pasals()->attach($pasal->id);
});

test('halaman dashboard monitoring dapat diakses oleh penyidik, pimpinan, dan admin', function () {
    foreach ([$this->penyidik, $this->pimpinan, $this->admin] as $user) {
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('Dashboard BAP TWO')
            ->assertSee('Total Perkara')
            ->assertSee('Perkara Berjalan')
            ->assertSee('Progress Perkara')
            ->assertSee('Lihat Semua');
    }
});

test('dashboard menampilkan 5 card statistik dan donut chart dengan data riil database', function () {
    $service = app(DashboardService::class);
    $stats = $service->getStatistics(2026);

    expect($stats)->toHaveCount(5)
        ->and($stats[0]['title'])->toBe('Total Perkara')
        ->and((int) $stats[0]['value'])->toBeGreaterThanOrEqual(1)
        ->and($stats[1]['title'])->toBe('Perkara Berjalan')
        ->and((int) $stats[1]['value'])->toBeGreaterThanOrEqual(1);

    $caseStatus = $service->getCaseStatusData(2026);
    expect($caseStatus)->toHaveKeys(['totalPerkara', 'selesai', 'dalamProses', 'tidakSelesai']);

    $response = $this->actingAs($this->penyidik)->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Perkara Tahun Berjalan')
        ->assertSee('Rata-rata Penyelesaian per Tahap');
});

test('tabel progress perkara pada dashboard memprioritaskan progress terendah dan menyertakan tombol lihat semua', function () {
    // Untuk Penyidik: tombol Lihat Semua langsung mengarah ke data-perkara (Poin 3A)
    $responsePenyidik = $this->actingAs($this->penyidik)->get(route('dashboard'));

    $responsePenyidik->assertOk()
        ->assertSee('BP/001/X/2026/BPOM')
        ->assertSee('Budi Santoso')
        ->assertSee('Lihat Semua')
        ->assertSee(route('data-perkara.index'));

    // Untuk Pimpinan: tombol Lihat Semua mengarah ke progress-perkara
    $responsePimpinan = $this->actingAs($this->pimpinan)->get(route('dashboard'));
    $responsePimpinan->assertOk()
        ->assertSee(route('progress-perkara.index'));
});

test('request ajax dengan header X-Filter-Partial mengembalikan partial tabel progress perkara', function () {
    $response = $this->actingAs($this->penyidik)->get(route('dashboard'), [
        'X-Filter-Partial' => '1',
    ]);

    $response->assertOk()
        ->assertViewIs('components.dashboard._progress-partial')
        ->assertSee('BP/001/X/2026/BPOM')
        ->assertSee('Budi Santoso');
});

test('filter pencarian pada dashboard menyaring berkas perkara dengan tepat', function () {
    $response = $this->actingAs($this->penyidik)->get(route('dashboard', [
        'search' => 'Budi Santoso',
    ]), [
        'X-Filter-Partial' => '1',
    ]);

    $response->assertOk()
        ->assertSee('Budi Santoso');

    $responseNotFound = $this->actingAs($this->penyidik)->get(route('dashboard', [
        'search' => 'NamaTidakAda12345',
    ]), [
        'X-Filter-Partial' => '1',
    ]);

    $responseNotFound->assertOk()
        ->assertDontSee('BP/001/X/2026/BPOM');
});

test('dashboard merender 5 baris Opsi A lengkap dengan Progress Perkara di Baris 3 serta Wilayah TKP dan Top Pasal di Baris 5', function () {
    $this->perkara->tkp->alamat->update(['kabupaten_nama' => 'Kota Banjarmasin']);
    $this->perkara->tkp->update(['modus' => 'offline']);
    $this->berkas->update(['is_dpo' => true]);
    DashboardService::clearCache(2026);

    $response = $this->actingAs($this->penyidik)->get(route('dashboard'));
    $response->assertOk();

    $html = $response->getContent();

    expect($html)->toContain('lg:col-span-5')
        ->and($html)->toContain('lg:col-span-7')
        ->and($html)->not->toContain('Status Khusus DPO Aktif')
        ->and($html)->toContain('Sebaran Wilayah TKP & Modus Peredaran')
        ->and($html)->toContain('Kota Banjarmasin')
        ->and($html)->toContain('Pasal yang Paling Banyak Disangkakan')
        ->and($html)->toContain('Pasal 196 UU Kesehatan');

    $posCaseStatus = strpos($html, 'Perkara Tahun Berjalan');
    $posProgressTable = strpos($html, 'Monitoring perkembangan perkara tahun berjalan (5 prioritas utama).');
    $posRecentActivities = strpos($html, 'Aktivitas Penyidikan Terkini');
    $posAverageStage = strpos($html, 'Rata-rata Penyelesaian per Tahap');
    $posTkpDist = strpos($html, 'Sebaran Wilayah TKP & Modus Peredaran');
    $posTopPasal = strpos($html, 'Pasal yang Paling Banyak Disangkakan');

    expect($posCaseStatus)->toBeLessThan($posProgressTable)
        ->and($posProgressTable)->toBeLessThan($posRecentActivities)
        ->and($posRecentActivities)->toBeLessThan($posAverageStage)
        ->and($posAverageStage)->toBeLessThan($posTkpDist)
        ->and($posTkpDist)->toBeLessThan($posTopPasal);
});

test('DashboardController membatasi recentActivities maksimal 4 item pada halaman dashboard', function () {
    for ($i = 1; $i <= 6; $i++) {
        AuditLog::create([
            'user_id' => $this->penyidik->id,
            'perkara_id' => $this->perkara->id,
            'berkas_perkara_id' => $this->berkas->id,
            'aksi' => 'finalisasi_tahap',
            'tabel' => 'tahapan_perkara',
            'record_id' => $i,
            'created_at' => now()->subMinutes($i),
        ]);
    }

    $response = $this->actingAs($this->penyidik)->get(route('dashboard'));
    $response->assertOk();

    $recentActivities = $response->viewData('recentActivities');
    expect($recentActivities['milestones'])->toHaveCount(4)
        ->and($recentActivities['all'])->toHaveCount(4);
});

test('DashboardService mengagregasi sebaran wilayah TKP, modus peredaran, top pasal, dpoAktif, dan jumlah berkas per tahap dengan akurat', function () {
    // Update alamat & TKP perkara pertama (Kota Banjarmasin, modus offline)
    $this->perkara->tkp->alamat->update(['kabupaten_nama' => 'Kota Banjarmasin']);
    $this->perkara->tkp->update(['modus' => 'offline']);
    $this->berkas->update(['is_dpo' => true]);

    // Buat perkara kedua di Kab. Banjar dengan modus online
    $alamat2 = Alamat::create([
        'kabupaten_nama' => 'Kabupaten Banjar',
        'alamat_detail' => 'Jl. Ahmad Yani KM 40',
    ]);
    $tkp2 = Tkp::create([
        'alamat_id' => $alamat2->id,
        'klasifikasi' => 'distributor',
        'modus' => 'online',
    ]);
    $perkara2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/002/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-05',
        'tahap_laporan_status' => 'final',
        'tkp_id' => $tkp2->id,
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka2 = Tersangka::create([
        'perkara_id' => $perkara2->id,
        'nama' => 'Siti Aminah',
        'created_by' => $this->penyidik->id,
    ]);
    $berkas2 = BerkasPerkara::create([
        'perkara_id' => $perkara2->id,
        'tersangka_id' => $tersangka2->id,
        'nomor_berkas' => 'BP/002/X/2026/BPOM',
        'progress_persen' => 55,
        'status_proses' => 'TAHAP I',
        'status_formal' => null,
        'created_by' => $this->penyidik->id,
    ]);

    $pasalKesehatan = Pasal::first();
    $berkas2->pasals()->attach($pasalKesehatan->id);

    DashboardService::clearCache(2026);
    $service = app(DashboardService::class);

    // 1. Verifikasi dpoAktif pada getCaseStatusData
    $caseStatus = $service->getCaseStatusData(2026);
    expect($caseStatus)->toHaveKey('dpoAktif')
        ->and($caseStatus['dpoAktif'])->toBe(1);

    // 2. Verifikasi active_count & active_label pada getStageAverageDays
    $stageAverages = $service->getStageAverageDays(2026);
    expect($stageAverages[0]['name'])->toBe('SPDP')
        ->and($stageAverages[0]['active_count'])->toBe(1)
        ->and($stageAverages[0]['active_label'])->toBe('1 berkas aktif')
        ->and($stageAverages[1]['name'])->toBe('TAHAP I')
        ->and($stageAverages[1]['active_count'])->toBe(1)
        ->and($stageAverages[1]['active_label'])->toBe('1 berkas aktif');

    // 3. Verifikasi getTkpDistribution
    $tkpDist = $service->getTkpDistribution(2026);
    expect($tkpDist['totalWithTkp'])->toBe(2)
        ->and($tkpDist['offlineCount'])->toBe(1)
        ->and($tkpDist['onlineCount'])->toBe(1)
        ->and($tkpDist['offlinePercent'])->toBe(50)
        ->and($tkpDist['onlinePercent'])->toBe(50)
        ->and($tkpDist['regions'])->toHaveCount(2)
        ->and(collect($tkpDist['regions'])->pluck('name')->all())->toContain('Kota Banjarmasin', 'Kabupaten Banjar');

    // 4. Verifikasi getTopPasals
    $topPasals = $service->getTopPasals(2026);
    expect($topPasals)->toHaveCount(1)
        ->and($topPasals[0]['count'])->toBe(2)
        ->and($topPasals[0]['title'])->toContain('196');
});
