<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

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

test('query scope BerkasPerkara memfilter status formal secara konsisten', function () {
    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SCOPE/2026',
        'tanggal_laporan_kejadian' => '2026-05-01',
        'created_by' => $this->penyidik->id,
    ]);

    $t1 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T1', 'created_by' => $this->penyidik->id]);
    $t2 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T2', 'created_by' => $this->penyidik->id]);
    $t3 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T3', 'created_by' => $this->penyidik->id]);

    $bNull = BerkasPerkara::create(['perkara_id' => $p->id, 'tersangka_id' => $t1->id, 'status_formal' => null]);
    $bSelesai = BerkasPerkara::create(['perkara_id' => $p->id, 'tersangka_id' => $t2->id, 'status_formal' => 'selesai']);
    $bTidak = BerkasPerkara::create(['perkara_id' => $p->id, 'tersangka_id' => $t3->id, 'status_formal' => 'tidak_selesai']);

    expect(BerkasPerkara::dalamProses()->pluck('id')->all())->toContain($bNull->id)
        ->and(BerkasPerkara::dalamProses()->pluck('id')->all())->not->toContain($bSelesai->id, $bTidak->id);

    expect(BerkasPerkara::selesai()->pluck('id')->all())->toBe([$bSelesai->id]);

    expect(BerkasPerkara::tidakSelesai()->pluck('id')->all())->toBe([$bTidak->id]);
});

test('kartu statistik selesai dan perkara berjalan sinkron dengan donut chart serta mengabaikan progress 100 tanpa status selesai', function () {
    Carbon::setTestNow('2026-06-15 10:00:00');
    DashboardService::clearCache(2026);

    $service = app(DashboardService::class);

    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SYNC/2026',
        'tanggal_laporan_kejadian' => '2026-01-10',
        'created_by' => $this->penyidik->id,
    ]);

    $t1 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T1', 'created_by' => $this->penyidik->id]);
    $t2 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T2', 'created_by' => $this->penyidik->id]);
    $t3 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T3', 'created_by' => $this->penyidik->id]);
    $t4 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T4', 'created_by' => $this->penyidik->id]);

    // 1. Berkas selesai resmi
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t1->id,
        'nomor_berkas' => 'BP/SYNC/1',
        'status_formal' => 'selesai',
        'progress_persen' => 100,
        'created_by' => $this->penyidik->id,
    ]);

    // 2. Berkas dalam proses biasa
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t2->id,
        'nomor_berkas' => 'BP/SYNC/2',
        'status_formal' => null,
        'progress_persen' => 55,
        'created_by' => $this->penyidik->id,
    ]);

    // 3. Berkas progress 100% tapi belum berstatus formal 'selesai' (misal draft Tahap II belum difinalkan)
    // Wajib masuk "Dalam Proses", BUKAN "Selesai"
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t3->id,
        'nomor_berkas' => 'BP/SYNC/3',
        'status_formal' => null,
        'progress_persen' => 100,
        'created_by' => $this->penyidik->id,
    ]);

    // 4. Berkas tidak selesai (SP3)
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t4->id,
        'nomor_berkas' => 'BP/SYNC/4',
        'status_formal' => 'tidak_selesai',
        'progress_persen' => 55,
        'created_by' => $this->penyidik->id,
    ]);

    $stats = $service->getStatistics(2026);
    $donut = $service->getCaseStatusData(2026);

    $cardBerjalan = (int) collect($stats)->firstWhere('title', 'Perkara Berjalan')['value'];
    $cardSelesai = (int) collect($stats)->firstWhere('title', 'Selesai')['value'];

    $donutDalamProses = $donut['dalamProses'];
    $donutSelesai = $donut['selesai'];
    $donutTidakSelesai = $donut['tidakSelesai'];

    // Selesai kartu harus sama persis dengan Selesai donut
    expect($cardSelesai)->toBe(1)
        ->and($donutSelesai)->toBe(1)
        ->and($cardSelesai)->toBe($donutSelesai);

    // Berjalan kartu harus sama persis dengan Dalam Proses donut (2 berkas: satu progress 55%, satu progress 100% status_formal null)
    expect($cardBerjalan)->toBe(2)
        ->and($donutDalamProses)->toBe(2)
        ->and($cardBerjalan)->toBe($donutDalamProses);

    expect($donutTidakSelesai)->toBe(1);

    Carbon::setTestNow();
});

test('perkara dengan LK 2025 dan sprindik 2026 hanya dihitung pada tahun 2025 melalui scopeTahunLk', function () {
    DashboardService::clearCache(2025);
    DashboardService::clearCache(2026);

    $service = app(DashboardService::class);

    // Perkara A: LK 2025, Sprindik 2026
    $perkara2025 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/CROSS/2025',
        'tanggal_laporan_kejadian' => '2025-11-20',
        'nomor_surat_perintah' => 'SP/CROSS/2026',
        'tanggal_surat_perintah' => '2026-01-10',
        'created_by' => $this->penyidik->id,
    ]);

    $tA = Tersangka::create(['perkara_id' => $perkara2025->id, 'nama' => 'Tersangka Cross', 'created_by' => $this->penyidik->id]);
    $bA = BerkasPerkara::create([
        'perkara_id' => $perkara2025->id,
        'tersangka_id' => $tA->id,
        'nomor_berkas' => 'BP/CROSS/2025',
        'tanggal_spdp' => '2026-01-15', // SPDP 2026 tapi LK 2025
        'status_formal' => null,
        'created_by' => $this->penyidik->id,
    ]);

    // Verifikasi scopeTahunLk
    expect(BerkasPerkara::tahunLk(2025)->pluck('id')->all())->toContain($bA->id);
    expect(BerkasPerkara::tahunLk(2026)->pluck('id')->all())->not->toContain($bA->id);

    // Verifikasi filter bulan
    expect(BerkasPerkara::tahunLk(2025, 11)->pluck('id')->all())->toContain($bA->id);
    expect(BerkasPerkara::tahunLk(2025, 12)->pluck('id')->all())->not->toContain($bA->id);

    // Verifikasi getStatistics untuk 2025 dan 2026
    $stats2025 = $service->getStatistics(2025);
    $cardTotal2025 = (int) collect($stats2025)->firstWhere('title', 'Total Perkara')['value'];
    $cardBerjalan2025 = (int) collect($stats2025)->firstWhere('title', 'Perkara Berjalan')['value'];

    $stats2026 = $service->getStatistics(2026);
    $cardTotal2026 = (int) collect($stats2026)->firstWhere('title', 'Total Perkara')['value'];
    $cardBerjalan2026 = (int) collect($stats2026)->firstWhere('title', 'Perkara Berjalan')['value'];

    expect($cardTotal2025)->toBe(1)
        ->and($cardBerjalan2025)->toBe(1)
        ->and($cardTotal2026)->toBe(0)
        ->and($cardBerjalan2026)->toBe(0);

    // Verifikasi donut case status
    $donut2025 = $service->getCaseStatusData(2025);
    $donut2026 = $service->getCaseStatusData(2026);

    expect($donut2025['totalPerkara'])->toBe(1)
        ->and($donut2026['totalPerkara'])->toBe(0);
});

test('kartu menunggu verifikasi hanya menghitung berkas dalam proses dan mengabaikan draft pada berkas selesai atau SP3', function () {
    Carbon::setTestNow('2026-07-20 10:00:00');
    DashboardService::clearCache(2026);

    $service = app(DashboardService::class);

    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/VERIF/2026',
        'tanggal_laporan_kejadian' => '2026-03-01',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 1: Dalam proses, memiliki draft tahapan -> HARUS terhitung
    $t1 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T1', 'created_by' => $this->penyidik->id]);
    $b1 = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t1->id,
        'nomor_berkas' => 'BP/VERIF/1',
        'status_formal' => null,
        'progress_persen' => 55,
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b1->id,
        'jenis_tahap' => JenisTahap::P21,
        'status' => 'draft',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 2: Dalam proses, progress_persen 0% (tahap awal pra-SPDP) -> HARUS terhitung
    $t2 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T2', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t2->id,
        'nomor_berkas' => 'BP/VERIF/2',
        'status_formal' => null,
        'progress_persen' => 0,
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 3: Dalam proses, progress 15% (baru SPDP) -> HARUS terhitung
    $t3 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T3', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t3->id,
        'nomor_berkas' => 'BP/VERIF/3',
        'status_formal' => null,
        'progress_persen' => 15,
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 4: Dalam proses, progress 55% (Tahap I final), tanpa draft -> TIDAK boleh terhitung
    $t4 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T4', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t4->id,
        'nomor_berkas' => 'BP/VERIF/4',
        'status_formal' => null,
        'progress_persen' => 55,
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 5: Selesai, tapi ada riwayat record berstatus draft -> TIDAK boleh terhitung
    $t5 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T5', 'created_by' => $this->penyidik->id]);
    $b5 = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t5->id,
        'nomor_berkas' => 'BP/VERIF/5',
        'status_formal' => 'selesai',
        'progress_persen' => 100,
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b5->id,
        'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN,
        'status' => 'draft',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 6: Tidak Selesai (SP3), tapi ada riwayat record berstatus draft -> TIDAK boleh terhitung
    $t6 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T6', 'created_by' => $this->penyidik->id]);
    $b6 = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t6->id,
        'nomor_berkas' => 'BP/VERIF/6',
        'status_formal' => 'tidak_selesai',
        'progress_persen' => 15,
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b6->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'status' => 'draft',
        'created_by' => $this->penyidik->id,
    ]);

    $stats = $service->getStatistics(2026);
    $cardMenunggu = (int) collect($stats)->firstWhere('title', 'Menunggu Verifikasi Penyidik')['value'];

    // Hanya Berkas 1, Berkas 2, dan Berkas 3 yang terhitung (total = 3)
    expect($cardMenunggu)->toBe(3);

    Carbon::setTestNow();
});

test('tabel prioritas dashboard default hanya memuat berkas dalam proses dan diurutkan berdasarkan sla_deadline asc dengan null terakhir', function () {
    $service = app(DashboardService::class);

    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PRIO/2026',
        'tanggal_laporan_kejadian' => '2026-02-01',
        'created_by' => $this->penyidik->id,
    ]);

    // 1. Berkas Selesai -> TIDAK boleh muncul di default
    $t1 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T1 Selesai', 'created_by' => $this->penyidik->id]);
    $bSelesai = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t1->id,
        'nomor_berkas' => 'BP/PRIO/SELESAI',
        'status_formal' => 'selesai',
        'progress_persen' => 100,
        'created_by' => $this->penyidik->id,
    ]);

    // 2. Berkas Tidak Selesai (SP3) -> TIDAK boleh muncul di default
    $t2 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T2 SP3', 'created_by' => $this->penyidik->id]);
    $bSp3 = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t2->id,
        'nomor_berkas' => 'BP/PRIO/SP3',
        'status_formal' => 'tidak_selesai',
        'progress_persen' => 55,
        'created_by' => $this->penyidik->id,
    ]);

    // 3. Berkas Dalam Proses dengan SLA Deadline lebih lama (2026-05-10)
    $t3 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T3 Lama', 'created_by' => $this->penyidik->id]);
    $bLama = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t3->id,
        'nomor_berkas' => 'BP/PRIO/LAMA',
        'status_formal' => null,
        'sla_deadline' => '2026-05-10',
        'progress_persen' => 55,
        'created_at' => '2026-02-01 10:00:00',
        'created_by' => $this->penyidik->id,
    ]);

    // 4. Berkas Dalam Proses dengan SLA Deadline lebih mendesak (2026-05-01) -> Harus Pertama
    $t4 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T4 Cepat', 'created_by' => $this->penyidik->id]);
    $bCepat = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t4->id,
        'nomor_berkas' => 'BP/PRIO/CEPAT',
        'status_formal' => null,
        'sla_deadline' => '2026-05-01',
        'progress_persen' => 55,
        'created_at' => '2026-02-01 10:00:00',
        'created_by' => $this->penyidik->id,
    ]);

    // 5. Berkas Dalam Proses dengan SLA Deadline NULL (belum SPDP) -> Harus Terakhir di antara dalam proses
    $t5 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T5 Null SLA', 'created_by' => $this->penyidik->id]);
    $bNullSla = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t5->id,
        'nomor_berkas' => 'BP/PRIO/NULLSLA',
        'status_formal' => null,
        'sla_deadline' => null,
        'progress_persen' => 0,
        'created_at' => '2026-02-01 10:00:00',
        'created_by' => $this->penyidik->id,
    ]);

    // Uji request tanpa filter status (default)
    $requestDefault = new Request;
    $priorityDefault = $service->getPriorityProgressPerkara($requestDefault, 10);
    $ids = collect($priorityDefault)->pluck('id')->all();

    // Pastikan berkas Selesai dan SP3 tidak muncul
    expect($ids)->not->toContain($bSelesai->id)
        ->and($ids)->not->toContain($bSp3->id);

    // Pastikan urutan: bCepat (SLA 01 Mei), bLama (SLA 10 Mei), bNullSla (SLA NULL)
    expect($ids)->toBe([$bCepat->id, $bLama->id, $bNullSla->id]);

    // Uji request manual filter status = 'selesai'
    $requestSelesai = new Request(['status' => 'Selesai']);
    $prioritySelesai = $service->getPriorityProgressPerkara($requestSelesai, 10);
    expect(collect($prioritySelesai)->pluck('id')->all())->toBe([$bSelesai->id]);

    // Uji request manual filter status = 'tidak selesai'
    $requestTidakSelesai = new Request(['status' => 'Tidak Selesai']);
    $priorityTidakSelesai = $service->getPriorityProgressPerkara($requestTidakSelesai, 10);
    expect(collect($priorityTidakSelesai)->pluck('id')->all())->toBe([$bSp3->id]);
});

test('perhitungan rata-rata waktu penyelesaian mengabaikan tahapan draft dan tanggal terbalik', function () {
    DashboardService::clearCache(2026);

    $service = app(DashboardService::class);

    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/AVG/2026',
        'tanggal_laporan_kejadian' => '2026-01-01',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 1: Valid 20 hari (01 Jan s.d. 21 Jan)
    $t1 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T1 Valid 20', 'created_by' => $this->penyidik->id]);
    $b1 = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t1->id,
        'nomor_berkas' => 'BP/AVG/1',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b1->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'tanggal_surat' => '2026-01-01',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b1->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'tanggal_surat' => '2026-01-21',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 2: Valid 10 hari (01 Feb s.d. 11 Feb)
    $t2 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T2 Valid 10', 'created_by' => $this->penyidik->id]);
    $b2 = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t2->id,
        'nomor_berkas' => 'BP/AVG/2',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b2->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'tanggal_surat' => '2026-02-01',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b2->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'tanggal_surat' => '2026-02-11',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 3: Tanggal terbalik (Tahap II 10 Jan, SPDP 20 Jan) -> Harus dilewati
    $t3 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T3 Terbalik', 'created_by' => $this->penyidik->id]);
    $b3 = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t3->id,
        'nomor_berkas' => 'BP/AVG/3',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b3->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'tanggal_surat' => '2026-01-20',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b3->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'tanggal_surat' => '2026-01-10',
        'status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // Berkas 4: Tahapan draft (bukan final) -> Harus diabaikan
    $t4 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T4 Draft', 'created_by' => $this->penyidik->id]);
    $b4 = BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t4->id,
        'nomor_berkas' => 'BP/AVG/4',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b4->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'tanggal_surat' => '2026-01-01',
        'status' => 'draft',
        'created_by' => $this->penyidik->id,
    ]);
    TahapanPerkara::create([
        'berkas_perkara_id' => $b4->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'tanggal_surat' => '2026-01-05',
        'status' => 'draft',
        'created_by' => $this->penyidik->id,
    ]);

    $stats = $service->getStatistics(2026);
    $cardAvg = collect($stats)->firstWhere('title', 'Rata-rata Waktu Penyelesaian')['value'];

    // Rata-rata dari berkas 1 (20 hari) dan berkas 2 (10 hari) = 15 Hari
    expect($cardAvg)->toBe('15 Hari');
});

test('evaluasi kategori sla zona waktu makassar: hari ini dan hari ini + 7 mendekati, hari ini + 8 dalam sla, kemarin lewat, null belum spdp', function () {
    $zona = DashboardService::ZONA_WAKTU;
    $now = Carbon::parse('2026-06-10 14:00:00', $zona);
    Carbon::setTestNow($now);

    $service = app(DashboardService::class);

    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SLA-ZONE/2026',
        'tanggal_laporan_kejadian' => '2026-05-01',
        'created_by' => $this->penyidik->id,
    ]);

    // 1. Deadline hari ini (2026-06-10) -> mendekati_sla
    $t1 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T1 Hari Ini', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t1->id,
        'nomor_berkas' => 'BP/SLA/1',
        'status_formal' => null,
        'sla_deadline' => '2026-06-10',
        'created_by' => $this->penyidik->id,
    ]);

    // 2. Deadline hari ini + 7 (2026-06-17) -> mendekati_sla
    $t2 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T2 Plus 7', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t2->id,
        'nomor_berkas' => 'BP/SLA/2',
        'status_formal' => null,
        'sla_deadline' => '2026-06-17',
        'created_by' => $this->penyidik->id,
    ]);

    // 3. Deadline hari ini + 8 (2026-06-18) -> dalam_sla
    $t3 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T3 Plus 8', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t3->id,
        'nomor_berkas' => 'BP/SLA/3',
        'status_formal' => null,
        'sla_deadline' => '2026-06-18',
        'created_by' => $this->penyidik->id,
    ]);

    // 4. Deadline kemarin (2026-06-09) -> lewat_sla
    $t4 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T4 Kemarin', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t4->id,
        'nomor_berkas' => 'BP/SLA/4',
        'status_formal' => null,
        'sla_deadline' => '2026-06-09',
        'created_by' => $this->penyidik->id,
    ]);

    // 5. Deadline NULL -> belum_spdp
    $t5 = Tersangka::create(['perkara_id' => $p->id, 'nama' => 'T5 Null', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t5->id,
        'nomor_berkas' => 'BP/SLA/5',
        'status_formal' => null,
        'sla_deadline' => null,
        'created_by' => $this->penyidik->id,
    ]);

    $data = $service->getCaseStatusData(2026);

    expect($data['sla']['mendekatiSla'])->toBe(2)
        ->and($data['sla']['dalamSla'])->toBe(1)
        ->and($data['sla']['lewatSla'])->toBe(1)
        ->and($data['sla']['belumSpdp'])->toBe(1)
        ->and($data['dalamProses'])->toBe(5);

    Carbon::setTestNow();
});

test('getAvailableYears mengembalikan tahun DISTINCT dari tanggal LK ditambah tahun berjalan urut menurun', function () {
    Carbon::setTestNow('2026-08-01 10:00:00');

    $service = app(DashboardService::class);

    Perkara::create([
        'nomor_laporan_kejadian' => 'LK/YEARS/2023',
        'tanggal_laporan_kejadian' => '2023-04-12',
        'created_by' => $this->penyidik->id,
    ]);
    Perkara::create([
        'nomor_laporan_kejadian' => 'LK/YEARS/2024-1',
        'tanggal_laporan_kejadian' => '2024-01-15',
        'created_by' => $this->penyidik->id,
    ]);
    Perkara::create([
        'nomor_laporan_kejadian' => 'LK/YEARS/2024-2',
        'tanggal_laporan_kejadian' => '2024-09-20',
        'created_by' => $this->penyidik->id,
    ]);
    // Perkara dengan hanya tanggal surat perintah tanpa tanggal LK (tidak boleh masuk)
    Perkara::create([
        'nomor_laporan_kejadian' => 'LK/YEARS/SP-ONLY',
        'tanggal_laporan_kejadian' => null,
        'nomor_surat_perintah' => 'SP/YEARS/2022',
        'tanggal_surat_perintah' => '2022-05-10',
        'created_by' => $this->penyidik->id,
    ]);

    $years = $service->getAvailableYears();

    // 2026 (tahun berjalan), 2024 (dari LK), 2023 (dari LK)
    expect($years)->toBe([2026, 2024, 2023]);

    Carbon::setTestNow();
});

test('komponen case-status merender tata letak 2 kolom, SVG donut 6 segmen radius 54, dan legenda dot', function () {
    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/UI/2026',
        'tanggal_laporan_kejadian' => '2026-03-01',
        'created_by' => $this->penyidik->id,
    ]);
    $t = Tersangka::create([
        'perkara_id' => $p->id,
        'nama' => 'Tersangka UI',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t->id,
        'nomor_berkas' => 'BP/UI/2026',
        'status_formal' => null,
        'sla_deadline' => Carbon::today()->subDay(), // lewat SLA
        'created_by' => $this->penyidik->id,
    ]);

    $service = app(DashboardService::class);
    $data = $service->getCaseStatusData(2026);

    $html = view('components.dashboard._case-status-content', [
        'data' => $data,
        'tahun' => 2026,
    ])->render();

    // Radius 54 dan stroke-width 16 pada SVG donut
    expect($html)->toContain('r="54"')
        ->toContain('stroke-width="16"')
        // Angka total perkara dan label Total Perkara di tengah donut
        ->toContain('Total Perkara')
        // Teks perbandingan vs tahun sebelumnya di bawah donut
        ->toContain('vs ')
        // Panel Dalam Proses dengan 4 sub-baris SLA
        ->toContain('Belum Ada SPDP (Pra-SPDP)')
        ->toContain('Dalam SLA (> 7 hari)')
        ->toContain('Mendekati Batas (≤ 7 hari)')
        ->toContain('Melewati SLA')
        // Tautan drill-down
        ->toContain(e(route('seluruh-perkara.index', ['tahun' => 2026, 'status' => 'Dalam Proses', 'sla' => 'lewat_sla'])))
        // Pill hanya muncul jika Melewati SLA > 0
        ->toContain('bg-error-50')
        ->toContain('text-error-600');
});

test('komponen case-status merender dot outline dan teks muted ketika sub-baris SLA bernilai 0', function () {
    $p = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/ZERO/2026',
        'tanggal_laporan_kejadian' => '2026-03-01',
        'created_by' => $this->penyidik->id,
    ]);
    $t = Tersangka::create([
        'perkara_id' => $p->id,
        'nama' => 'Tersangka Zero',
        'created_by' => $this->penyidik->id,
    ]);
    // Buat berkas yang Selesai, sehingga Dalam Proses = 0 dan seluruh sub-baris SLA = 0
    BerkasPerkara::create([
        'perkara_id' => $p->id,
        'tersangka_id' => $t->id,
        'nomor_berkas' => 'BP/ZERO/2026',
        'status_formal' => 'selesai',
        'status_proses' => 'TAHAP II',
        'progress_persen' => 100,
        'created_by' => $this->penyidik->id,
    ]);

    $service = app(DashboardService::class);
    $data = $service->getCaseStatusData(2026);

    $html = view('components.dashboard._case-status-content', [
        'data' => $data,
        'tahun' => 2026,
    ])->render();

    // Selesai = 1, Melewati SLA = 0
    expect($html)->toContain('border border-gray-300')
        ->toContain('font-normal text-gray-400')
        // Tidak ada pill danger bg-error-50 karena Melewati SLA bernilai 0
        ->not->toContain('bg-error-50');
});
