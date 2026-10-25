<?php

use App\Enums\JenisTahap;
use App\Models\Alamat;
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
    $response = $this->actingAs($this->penyidik)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('BP/001/X/2026/BPOM')
        ->assertSee('Budi Santoso')
        ->assertSee('Lihat Semua')
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
