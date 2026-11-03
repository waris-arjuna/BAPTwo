<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\TahapanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);

    $this->perkara = Perkara::create([
        'nomor_laporan' => 'LK/001/X/2026',
        'tanggal_laporan' => '2026-02-01',
        'tahap_laporan_status' => 'final',
        'nomor_sprindik' => 'SP/001/X/2026',
        'tanggal_sprindik' => '2026-02-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Budi SLA',
        'nik' => '6371012345670008',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/008/X/2026/SLA',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tahapanService = app(TahapanService::class);
});

test('batas waktu SLA 1 bulan kalender dihitung dan direset di setiap tahap aktif yang difinalkan', function () {
    // 1. Finalkan SPDP tanggal 01 Maret 2026 -> deadline awal 01 April 2026
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::SPDP->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'SPDP/001/2026',
            'tanggal_surat' => '2026-03-01',
        ],
        null,
        $this->penyidik
    );

    $this->berkas->refresh();
    expect($this->berkas->tanggal_spdp?->toDateString())->toBe('2026-03-01');
    expect($this->berkas->sla_deadline?->toDateString())->toBe('2026-04-01');

    // 2. Finalkan Tahap 1 tanggal 20 April 2026 -> SLA direset 1 bulan dari Tahap 1 menjadi 20 Mei 2026
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::TAHAP_1->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'THP1/001/2026',
            'tanggal_surat' => '2026-04-20',
        ],
        null,
        $this->penyidik
    );

    $this->berkas->refresh();
    expect($this->berkas->sla_deadline?->toDateString())->toBe('2026-05-20');
    expect($this->berkas->effective_deadline?->toDateString())->toBe('2026-05-20');

    // 3. Finalkan P-21 tanggal 10 Juni 2026 -> SLA direset 1 bulan dari P-21 menjadi 10 Juli 2026
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::P21->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'P21/001/2026',
            'tanggal_surat' => '2026-06-10',
        ],
        null,
        $this->penyidik
    );

    $this->berkas->refresh();
    expect($this->berkas->sla_deadline?->toDateString())->toBe('2026-07-10');
    expect($this->berkas->effective_deadline?->toDateString())->toBe('2026-07-10');
});

test('evaluasi status SLA saat Tahap 2 difinalkan dievaluasi terhadap deadline tahap 2 dari P-21', function () {
    // 1. SPDP tanggal 01 Maret 2026
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::SPDP->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'SPDP/001/2026',
            'tanggal_surat' => '2026-03-01',
        ],
        null,
        $this->penyidik
    );

    // 2. Tahap 1 tanggal 20 April 2026
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::TAHAP_1->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'THP1/001/2026',
            'tanggal_surat' => '2026-04-20',
        ],
        null,
        $this->penyidik
    );

    // 3. P-21 tanggal 10 Juni 2026 -> deadline Tahap 2 adalah 10 Juli 2026
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::P21->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'P21/001/2026',
            'tanggal_surat' => '2026-06-10',
        ],
        null,
        $this->penyidik
    );

    // 4. Skenario Selesai Tepat Waktu: Tahap 2 tanggal 05 Juli 2026 (sebelum 10 Juli 2026)
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::TAHAP_2->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'THP2/001/2026',
            'tanggal_surat' => '2026-07-05',
        ],
        null,
        $this->penyidik
    );

    $this->berkas->refresh();
    expect($this->berkas->status_formal)->toBe('selesai');
    expect($this->berkas->sla_status)->toBe('selesai_tepat_waktu');
    expect($this->berkas->sla_badge['label'])->toBe('Selesai Tepat Waktu');
});

test('evaluasi status SLA menghasilkan selesai melebihi tenggang waktu jika Tahap 2 melewati deadline P-21', function () {
    // 1. SPDP, Tahap 1, P-21 (tanggal 10 Juni 2026 -> deadline 10 Juli 2026)
    $this->tahapanService->saveStage($this->berkas, ['jenis_tahap' => JenisTahap::SPDP->value, 'action_type' => 'lanjut', 'nomor_surat' => 'S1', 'tanggal_surat' => '2026-03-01'], null, $this->penyidik);
    $this->tahapanService->saveStage($this->berkas, ['jenis_tahap' => JenisTahap::TAHAP_1->value, 'action_type' => 'lanjut', 'nomor_surat' => 'S2', 'tanggal_surat' => '2026-04-20'], null, $this->penyidik);
    $this->tahapanService->saveStage($this->berkas, ['jenis_tahap' => JenisTahap::P21->value, 'action_type' => 'lanjut', 'nomor_surat' => 'S3', 'tanggal_surat' => '2026-06-10'], null, $this->penyidik);

    // 2. Tahap 2 tanggal 20 Juli 2026 (melewati deadline 10 Juli 2026)
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::TAHAP_2->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'THP2/001/2026',
            'tanggal_surat' => '2026-07-20',
        ],
        null,
        $this->penyidik
    );

    $this->berkas->refresh();
    expect($this->berkas->status_formal)->toBe('selesai');
    expect($this->berkas->sla_status)->toBe('selesai_lewat_sla');
    expect($this->berkas->sla_badge['label'])->toBe('Selesai Melebihi Tenggang Waktu');
});

test('batas tahun mutlak tetap berlaku saat tahap aktif difinalkan di bulan Desember', function () {
    // SPDP bulan Oktober
    $this->tahapanService->saveStage($this->berkas, ['jenis_tahap' => JenisTahap::SPDP->value, 'action_type' => 'lanjut', 'nomor_surat' => 'S1', 'tanggal_surat' => '2026-10-01'], null, $this->penyidik);

    // Tahap 1 difinalkan 15 Desember 2026 -> batas 1 bulan seharusnya 15 Januari 2027, tetapi dipotong batas tahun 31 Desember 2026
    $this->tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::TAHAP_1->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'THP1/001/2026',
            'tanggal_surat' => '2026-12-15',
        ],
        null,
        $this->penyidik
    );

    $this->berkas->refresh();
    expect($this->berkas->sla_deadline?->toDateString())->toBe('2026-12-31');
    expect($this->berkas->effective_deadline?->toDateString())->toBe('2026-12-31');
});

test('antarmuka pengguna menampilkan keterangan tenggang waktu 1 bulan per tahapan aktif', function () {
    $this->actingAs($this->penyidik);

    // 1. Cek tabel prioritas dashboard
    $resDashboard = $this->get(route('dashboard'));
    $resDashboard->assertOk()
        ->assertDontSee('1 bulan kalender dari SPDP')
        ->assertSee('1 bulan kalender tahap aktif');

    // 2. Cek kartu statistik rata-rata waktu total
    $resDashboard->assertSee('Rata-rata Waktu Total (SPDP s.d. Tahap II)')
        ->assertDontSee('(Maks. 30 Hari Kalender)');

    // 3. Cek halaman pengaturan umum (sebagai administrator)
    $admin = User::factory()->create(['role' => 'administrator']);
    $resPengaturan = $this->actingAs($admin)->get(route('pengaturan.umum'));
    $resPengaturan->assertOk()
        ->assertSee('1 Bulan Kalender per Tahapan Aktif (SPDP, Tahap I, P-21)')
        ->assertDontSee('1 Bulan Kalender Total (Sejak SPDP diterbitkan)');
});
