<?php

use App\Enums\JenisTahap;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman laporan kejadian merender komponen jejak aktivitas dengan tab aktivitas tahap ini', function () {
    $penyidik = User::factory()->create([
        'nama' => 'Penyidik LK',
        'role' => UserRole::PENYIDIK,
    ]);

    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/01/TEST/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'created_by' => $penyidik->id,
    ]);

    // Log pembuatan LK
    AuditLog::create([
        'user_id' => $penyidik->id,
        'perkara_id' => $perkara->id,
        'aksi' => 'create_laporan_kejadian',
        'tabel' => 'perkara',
        'record_id' => $perkara->id,
        'data_baru' => [
            'nomor_laporan_kejadian' => 'LK/01/TEST/2026',
            'status' => 'final',
        ],
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($penyidik)->get(route('laporan-kejadian.show', $perkara));

    $response->assertOk();
    $response->assertSee('Riwayat Penanganan Perkara (Case History)');
    $response->assertSee('Aktivitas Tahap Ini');
    $response->assertSee('Semua Riwayat Perkara');
    $response->assertSee('Penyidik LK');
    $response->assertSee('LK/01/TEST/2026');
    $response->assertSee('max-h-[620px]');
    $response->assertSee('custom-scrollbar');
});

test('halaman sprindik merender komponen jejak aktivitas dengan tab aktivitas tahap ini', function () {
    $penyidik = User::factory()->create([
        'nama' => 'Penyidik Sprindik',
        'role' => UserRole::PENYIDIK,
    ]);

    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/02/TEST/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/02/TEST/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'created_by' => $penyidik->id,
    ]);

    // Log penerbitan Sprindik
    AuditLog::create([
        'user_id' => $penyidik->id,
        'perkara_id' => $perkara->id,
        'aksi' => 'attach_sprindik',
        'tabel' => 'perkara',
        'record_id' => $perkara->id,
        'data_baru' => [
            'nomor_surat_perintah' => 'SP/02/TEST/2026',
            'status' => 'final',
        ],
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($penyidik)->get(route('sprindik.show', $perkara));

    $response->assertOk();
    $response->assertSee('Riwayat Penanganan Perkara (Case History)');
    $response->assertSee('Aktivitas Tahap Ini');
    $response->assertSee('Semua Riwayat Perkara');
    $response->assertSee('Penyidik Sprindik');
    $response->assertSee('SP/02/TEST/2026');
    $response->assertSee('max-h-[620px]');
});

test('halaman detail tahapan merender komponen jejak aktivitas dengan konteks tahapan', function () {
    $penyidik = User::factory()->create([
        'nama' => 'Penyidik Tahapan',
        'role' => UserRole::PENYIDIK,
    ]);

    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/03/TEST/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/03/TEST/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Budi Sudarsono',
        'created_by' => $penyidik->id,
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/03/2026',
        'created_by' => $penyidik->id,
    ]);

    $spdp = TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'B-333/SPDP/2026',
        'tanggal_surat' => '2026-10-05',
        'status' => 'final',
        'created_by' => $penyidik->id,
        'finalized_by' => $penyidik->id,
    ]);

    // 1. Log Finalisasi SPDP
    AuditLog::create([
        'user_id' => $penyidik->id,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'finalisasi_tahap',
        'tabel' => 'tahapan_perkara',
        'record_id' => $spdp->id,
        'data_baru' => [
            'jenis_tahap' => 'spdp',
            'nomor_surat' => 'B-333/SPDP/2026',
            'status' => 'final',
        ],
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    // 2. Log Sistem Otomatis
    AuditLog::create([
        'user_id' => null,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'sistem_tidak_selesai',
        'tabel' => 'berkas_perkara',
        'record_id' => $berkas->id,
        'data_lama' => ['status_formal' => null],
        'data_baru' => ['status_formal' => 'tidak_selesai'],
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($penyidik)->get(route('berkas-perkara.tahapan.show', [$berkas, $spdp]));

    $response->assertOk();
    $response->assertSee('Riwayat Penanganan Perkara (Case History)');
    $response->assertSee('Aktivitas Tahap Ini');
    $response->assertSee('Semua Riwayat Perkara');
    $response->assertSee('B-333/SPDP/2026');
    $response->assertSee('Penyidik Tahapan');
    $response->assertSee('Sistem Otomatis');
    $response->assertSee('max-h-[620px]');
});
