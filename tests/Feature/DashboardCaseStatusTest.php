<?php

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $this->pimpinan = User::factory()->create(['role' => 'pimpinan']);
    $this->admin = User::factory()->create(['role' => 'administrator']);
});

test('perhitungan persentase largest remainder method selalu menghasilkan total tepat 100 persen', function () {
    $service = app(DashboardService::class);

    // Kasus 1: Nilai kosong
    $zeros = $service->calculatePercentages(['a' => 0, 'b' => 0, 'c' => 0]);
    expect($zeros)->toBe(['a' => 0, 'b' => 0, 'c' => 0]);

    // Kasus 2: 1/3 tiap bagian (33.33%) -> pembagian sisa menjamin total 100
    $oneThird = $service->calculatePercentages(['a' => 1, 'b' => 1, 'c' => 1]);
    expect(array_sum($oneThird))->toBe(100);

    // Kasus 3: Proporsi acak dengan sisa pecahan desimal
    $random1 = $service->calculatePercentages(['selesai' => 14, 'dalamProses' => 25, 'tidakSelesai' => 61]);
    expect(array_sum($random1))->toBe(100);

    $random2 = $service->calculatePercentages(['selesai' => 7, 'dalamProses' => 13, 'tidakSelesai' => 2]);
    expect(array_sum($random2))->toBe(100);

    $single = $service->calculatePercentages(['selesai' => 5, 'dalamProses' => 0, 'tidakSelesai' => 0]);
    expect($single)->toBe(['selesai' => 100, 'dalamProses' => 0, 'tidakSelesai' => 0]);
});

test('agregasi kategori status perkara dan donut chart menghitung dengan akurat', function () {
    $tahun = (int) date('Y');

    // Buat 3 Perkara & Berkas di tahun berjalan
    // 1. Berkas Selesai
    $perkara1 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/2026',
        'tanggal_laporan_kejadian' => "{$tahun}-02-10",
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka1 = Tersangka::create([
        'perkara_id' => $perkara1->id,
        'nama' => 'Tersangka 1',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara1->id,
        'tersangka_id' => $tersangka1->id,
        'nomor_berkas' => 'BP/001/2026',
        'status_formal' => 'selesai',
        'progress_persen' => 100,
        'created_by' => $this->penyidik->id,
    ]);

    // 2. Berkas Tidak Selesai (SP3)
    $perkara2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/002/2026',
        'tanggal_laporan_kejadian' => "{$tahun}-03-15",
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka2 = Tersangka::create([
        'perkara_id' => $perkara2->id,
        'nama' => 'Tersangka 2',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara2->id,
        'tersangka_id' => $tersangka2->id,
        'nomor_berkas' => 'BP/002/2026',
        'status_formal' => 'tidak_selesai',
        'progress_persen' => 55,
        'created_by' => $this->penyidik->id,
    ]);

    // 3. Berkas Dalam Proses
    $perkara3 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/003/2026',
        'tanggal_laporan_kejadian' => "{$tahun}-04-20",
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka3 = Tersangka::create([
        'perkara_id' => $perkara3->id,
        'nama' => 'Tersangka 3',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara3->id,
        'tersangka_id' => $tersangka3->id,
        'nomor_berkas' => 'BP/003/2026',
        'status_formal' => null,
        'progress_persen' => 15,
        'created_by' => $this->penyidik->id,
    ]);

    $service = app(DashboardService::class);
    $data = $service->getCaseStatusData($tahun);

    expect($data['totalPerkara'])->toBe(3)
        ->and($data['selesai'])->toBe(1)
        ->and($data['tidakSelesai'])->toBe(1)
        ->and($data['dalamProses'])->toBe(1)
        ->and($data['selesaiPercent'] + $data['tidakSelesaiPercent'] + $data['dalamProsesPercent'])->toBe(100);
});

test('pecahan 4 sub-kategori SLA pada Dalam Proses tepat dan jumlahnya sama dengan total Dalam Proses', function () {
    $tahun = (int) date('Y');
    $today = Carbon::today();

    $createCaseWithSla = function ($no, $slaDeadline) use ($tahun) {
        $p = Perkara::create([
            'nomor_laporan_kejadian' => "LK/SLA/{$no}/{$tahun}",
            'tanggal_laporan_kejadian' => "{$tahun}-01-10",
            'created_by' => $this->penyidik->id,
        ]);
        $t = Tersangka::create([
            'perkara_id' => $p->id,
            'nama' => "Tersangka SLA {$no}",
            'created_by' => $this->penyidik->id,
        ]);

        return BerkasPerkara::create([
            'perkara_id' => $p->id,
            'tersangka_id' => $t->id,
            'nomor_berkas' => "BP/SLA/{$no}/{$tahun}",
            'status_formal' => null,
            'sla_deadline' => $slaDeadline,
            'created_by' => $this->penyidik->id,
        ]);
    };

    // 1. Belum ada SPDP (sla_deadline is null)
    $createCaseWithSla('1', null);

    // 2. Dalam SLA (> 7 hari lagi, misal 20 hari dari sekarang)
    $createCaseWithSla('2', $today->copy()->addDays(20)->toDateString());

    // 3. Mendekati batas (<= 7 hari lagi, misal 4 hari dari sekarang)
    $createCaseWithSla('3', $today->copy()->addDays(4)->toDateString());

    // 4. Melewati SLA (lewat 2 hari lalu)
    $createCaseWithSla('4', $today->copy()->subDays(2)->toDateString());

    $service = app(DashboardService::class);
    $data = $service->getCaseStatusData($tahun);

    expect($data['dalamProses'])->toBe(4)
        ->and($data['sla']['belumSpdp'])->toBe(1)
        ->and($data['sla']['dalamSla'])->toBe(1)
        ->and($data['sla']['mendekatiSla'])->toBe(1)
        ->and($data['sla']['lewatSla'])->toBe(1);

    // Total sub-kategori harus tepat sama dengan jumlah Dalam Proses
    $totalSla = $data['sla']['belumSpdp'] + $data['sla']['dalamSla'] + $data['sla']['mendekatiSla'] + $data['sla']['lewatSla'];
    expect($totalSla)->toBe($data['dalamProses']);
});

test('filter tahun dan bulan menyaring data sesuai tanggal LK serta menghitung komparasi tahun lalu', function () {
    // Buat data di tahun 2025 (tahun lalu)
    $p2025 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PREV/2025',
        'tanggal_laporan_kejadian' => '2025-05-10',
        'created_by' => $this->penyidik->id,
    ]);
    $t2025 = Tersangka::create([
        'perkara_id' => $p2025->id,
        'nama' => 'Tersangka 2025',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $p2025->id,
        'tersangka_id' => $t2025->id,
        'nomor_berkas' => 'BP/PREV/2025',
        'created_by' => $this->penyidik->id,
    ]);

    // Buat 2 data di tahun 2026 (1 di Mei, 1 di Juni)
    $p2026_mei = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/MEI/2026',
        'tanggal_laporan_kejadian' => '2026-05-15',
        'created_by' => $this->penyidik->id,
    ]);
    $t2026_mei = Tersangka::create([
        'perkara_id' => $p2026_mei->id,
        'nama' => 'Tersangka Mei 2026',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $p2026_mei->id,
        'tersangka_id' => $t2026_mei->id,
        'nomor_berkas' => 'BP/MEI/2026',
        'created_by' => $this->penyidik->id,
    ]);

    $p2026_jun = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/JUN/2026',
        'tanggal_laporan_kejadian' => '2026-06-20',
        'created_by' => $this->penyidik->id,
    ]);
    $t2026_jun = Tersangka::create([
        'perkara_id' => $p2026_jun->id,
        'nama' => 'Tersangka Juni 2026',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $p2026_jun->id,
        'tersangka_id' => $t2026_jun->id,
        'nomor_berkas' => 'BP/JUN/2026',
        'created_by' => $this->penyidik->id,
    ]);

    $service = app(DashboardService::class);

    // Filter seluruh 2026 (2 perkara vs 1 di 2025 -> naik 1 perkara)
    $data2026 = $service->getCaseStatusData(2026);
    expect($data2026['totalPerkara'])->toBe(2)
        ->and($data2026['comparison']['direction'])->toBe('up')
        ->and($data2026['comparison']['diff'])->toBe(1)
        ->and($data2026['comparison']['text'])->toContain('▲ 1 perkara dibanding 2025');

    // Filter Mei 2026 (1 perkara vs 1 di Mei 2025 -> sama dengan 2025)
    $dataMei = $service->getCaseStatusData(2026, 5);
    expect($dataMei['totalPerkara'])->toBe(1)
        ->and($dataMei['periodeText'])->toBe('Mei 2026')
        ->and($dataMei['comparison']['direction'])->toBe('same');

    // Filter Desember 2026 (0 perkara -> empty state data)
    $dataDes = $service->getCaseStatusData(2026, 12);
    expect($dataDes['totalPerkara'])->toBe(0);
});

test('endpoint ajax dashboard case-status mengembalikan data valid baik json maupun html partial', function () {
    // 1. Uji kondisi kosong (empty state)
    $responseEmpty = $this->actingAs($this->penyidik)
        ->get(route('dashboard.case-status', ['tahun' => 2026]), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'text/html',
        ]);

    $responseEmpty->assertOk()
        ->assertHeader('X-Periode-Text')
        ->assertHeader('X-Updated-At')
        ->assertSee('Belum ada perkara pada periode ini');

    // 2. Buat data untuk pengujian populated view & JSON
    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/AJAX/2026',
        'tanggal_laporan_kejadian' => '2026-03-01',
        'created_by' => $this->penyidik->id,
    ]);
    $t = Tersangka::create([
        'perkara_id' => $p->id,
        'nama' => 'Tersangka AJAX',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t->id,
        'nomor_berkas' => 'BP/AJAX/2026',
        'status_formal' => null,
        'created_by' => $this->penyidik->id,
    ]);

    // 3. Permintaan JSON
    $responseJson = $this->actingAs($this->penyidik)
        ->getJson(route('dashboard.case-status', ['tahun' => 2026]));

    $responseJson->assertOk()
        ->assertJsonStructure([
            'totalPerkara',
            'selesai',
            'dalamProses',
            'tidakSelesai',
            'selesaiPercent',
            'dalamProsesPercent',
            'tidakSelesaiPercent',
            'sla' => ['belumSpdp', 'dalamSla', 'mendekatiSla', 'lewatSla'],
            'comparison',
            'periodeText',
            'updatedAt',
        ]);

    // 4. Permintaan HTML Partial populated via AJAX
    $responseHtml = $this->actingAs($this->penyidik)
        ->get(route('dashboard.case-status', ['tahun' => 2026]), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'text/html',
        ]);

    $responseHtml->assertOk()
        ->assertHeader('X-Periode-Text')
        ->assertHeader('X-Updated-At')
        ->assertSee('Total Perkara')
        ->assertSee('Dalam Proses')
        ->assertSee('Selesai')
        ->assertSee('Tidak Selesai');
});

test('drill-down dari kartu dashboard ke halaman seluruh-perkara terfilter dengan tepat untuk semua role', function () {
    $today = Carbon::today();

    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DRILL/2026',
        'tanggal_laporan_kejadian' => '2026-04-10',
        'created_by' => $this->penyidik->id,
    ]);
    $t = Tersangka::create([
        'perkara_id' => $p->id,
        'nama' => 'Tersangka Drilldown',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t->id,
        'nomor_berkas' => 'BP/DRILL/2026',
        'status_formal' => null,
        'sla_deadline' => $today->copy()->addDays(3)->toDateString(), // Mendekati SLA
        'created_by' => $this->penyidik->id,
    ]);

    $roles = [$this->penyidik, $this->pimpinan, $this->admin];

    foreach ($roles as $user) {
        $response = $this->actingAs($user)->get(route('seluruh-perkara.index', [
            'tahun' => 2026,
            'status' => 'Dalam Proses',
            'sla' => 'mendekati_sla',
        ]));

        $response->assertOk()
            ->assertSee('BP/DRILL/2026')
            ->assertSee('Tersangka Drilldown');
    }
});
