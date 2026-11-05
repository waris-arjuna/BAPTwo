<?php

use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('banner penutupan tahunan tampil di dashboard untuk penyidik saat bulan Januari jika ada berkas ditutup', function () {
    Carbon::setTestNow('2027-01-10 08:00:00');
    $penyidik = User::factory()->penyidik()->create();

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/FEAT/01',
        'nomor_surat_perintah' => 'SP/FEAT/01',
        'tanggal_surat_perintah' => '2026-05-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Feat', 'created_by' => $penyidik->id]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/FEAT/01',
        'status_formal' => 'tidak_selesai',
        'created_by' => $penyidik->id,
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

    $response = $this->actingAs($penyidik)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Pemberitahuan Sistem: Sebanyak 1 berkas perkara tahun 2026 telah ditutup otomatis');
    $response->assertSee('Lihat Daftar Berkas');
    $response->assertSee(route('data-perkara.index', ['status' => 'tidak_selesai', 'tahun' => 2026]));
});

test('banner penutupan tahunan tidak tampil untuk role umum', function () {
    Carbon::setTestNow('2027-01-10 08:00:00');
    $umum = User::factory()->umum()->create();

    $response = $this->actingAs($umum)->get(route('dashboard'));
    $response->assertOk();
    $response->assertDontSee('Pemberitahuan Sistem: Sebanyak');
});

test('banner penutupan tahunan tidak tampil saat jumlah berkas ditutup adalah nol', function () {
    Carbon::setTestNow('2027-01-10 08:00:00');
    $penyidik = User::factory()->penyidik()->create();

    $response = $this->actingAs($penyidik)->get(route('dashboard'));
    $response->assertOk();
    $response->assertDontSee('Pemberitahuan Sistem: Sebanyak');
});
