<?php

use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('getAnnualClosureNoticeInfo mengembalikan null saat bukan bulan Januari', function () {
    Carbon::setTestNow('2026-11-05 08:00:00');
    $penyidik = User::factory()->penyidik()->create();
    $this->actingAs($penyidik);

    $service = app(DashboardService::class);
    expect($service->getAnnualClosureNoticeInfo())->toBeNull();
});

test('getAnnualClosureNoticeInfo mengembalikan null untuk role umum', function () {
    Carbon::setTestNow('2027-01-10 08:00:00');
    $pimpinan = User::factory()->umum()->create();
    $this->actingAs($pimpinan);

    $service = app(DashboardService::class);
    expect($service->getAnnualClosureNoticeInfo())->toBeNull();
});

test('getAnnualClosureNoticeInfo mengembalikan null saat bulan Januari jika tidak ada berkas ditutup sistem', function () {
    Carbon::setTestNow('2027-01-10 08:00:00');
    $penyidik = User::factory()->penyidik()->create();
    $this->actingAs($penyidik);

    $service = app(DashboardService::class);
    expect($service->getAnnualClosureNoticeInfo())->toBeNull();
});

test('getAnnualClosureNoticeInfo mengembalikan array notifikasi lengkap untuk penyidik saat ada berkas ditutup sistem', function () {
    Carbon::setTestNow('2027-01-10 08:00:00');
    $penyidik = User::factory()->penyidik()->create();
    $this->actingAs($penyidik);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/CLS/01',
        'nomor_surat_perintah' => 'SP/CLS/01',
        'tanggal_surat_perintah' => '2026-05-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Tutup', 'created_by' => $penyidik->id]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/CLS/01',
        'status_formal' => 'tidak_selesai',
        'created_by' => $penyidik->id,
    ]);

    // Simulasi audit log penutupan otomatis pada 1 Januari 2027
    AuditLog::create([
        'user_id' => null,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'sistem_tidak_selesai',
        'tabel' => 'berkas_perkara',
        'record_id' => $berkas->id,
        'data_lama' => ['status_formal' => null],
        'data_baru' => ['status_formal' => 'tidak_selesai'],
        'created_at' => Carbon::parse('2027-01-01 00:00:01'),
    ]);

    $service = app(DashboardService::class);
    $notice = $service->getAnnualClosureNoticeInfo();

    expect($notice)->not->toBeNull()
        ->and($notice['tahun_lalu'])->toBe(2026)
        ->and($notice['tahun_ini'])->toBe(2027)
        ->and($notice['jumlah_berkas'])->toBe(1)
        ->and($notice['pesan'])->toContain('Sebanyak 1 berkas perkara tahun 2026 telah ditutup otomatis menjadi Tidak Selesai')
        ->and($notice['url_daftar'])->toBe(route('data-perkara.index', ['status' => 'tidak_selesai', 'tahun' => 2026]));
});

test('getAnnualClosureNoticeInfo juga dapat diakses oleh administrator', function () {
    Carbon::setTestNow('2027-01-10 08:00:00');
    $admin = User::factory()->administrator()->create();
    $this->actingAs($admin);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/CLS/02',
        'nomor_surat_perintah' => 'SP/CLS/02',
        'tanggal_surat_perintah' => '2026-05-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $admin->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Admin', 'created_by' => $admin->id]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/CLS/02',
        'status_formal' => 'tidak_selesai',
        'created_by' => $admin->id,
    ]);

    AuditLog::create([
        'user_id' => null,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'sistem_tidak_selesai',
        'tabel' => 'berkas_perkara',
        'record_id' => $berkas->id,
        'data_lama' => ['status_formal' => null],
        'data_baru' => ['status_formal' => 'tidak_selesai'],
        'created_at' => Carbon::parse('2027-01-01 00:00:01'),
    ]);

    $service = app(DashboardService::class);
    $notice = $service->getAnnualClosureNoticeInfo();

    expect($notice)->not->toBeNull()
        ->and($notice['jumlah_berkas'])->toBe(1);
});
