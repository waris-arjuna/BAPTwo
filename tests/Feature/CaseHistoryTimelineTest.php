<?php

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman detail perkara merender timeline riwayat penanganan perkara dengan komponen baru', function () {
    $user = User::factory()->create([
        'nama' => 'Iptu Ahmad Yani',
        'role' => UserRole::PENYIDIK,
    ]);

    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/001/X/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_sprindik' => 'SP/001/X/2026',
        'tanggal_sprindik' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $user->id,
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Rahmat Hidayat',
        'nik' => '6371012345670001',
        'created_by' => $user->id,
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/TEST',
        'created_by' => $user->id,
    ]);

    // 1. Log Aksi Pengguna (Penyidik)
    AuditLog::create([
        'user_id' => $user->id,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'finalisasi_tahap',
        'tabel' => 'tahapan_perkara',
        'record_id' => 1,
        'data_lama' => null,
        'data_baru' => [
            'nomor_surat' => 'B-777/SPDP/BPOM/2026',
            'jenis_tahap' => 'SPDP',
            'keterangan' => 'Tahapan SPDP telah dikirimkan ke Kejaksaan.',
        ],
        'ip_address' => '192.168.1.10',
        'created_at' => now()->subHours(2),
    ]);

    // 2. Log Aksi Sistem Otomatis (Scheduler)
    AuditLog::create([
        'user_id' => null,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'sistem_tidak_selesai',
        'tabel' => 'berkas_perkara',
        'record_id' => $berkas->id,
        'data_lama' => [
            'status_formal' => null,
        ],
        'data_baru' => [
            'status_formal' => 'tidak_selesai',
            'alasan' => 'Batas waktu tahun perkara berakhir per 1 Januari 2027.',
        ],
        'ip_address' => '127.0.0.1',
        'created_at' => now()->subHour(),
    ]);

    $response = $this->actingAs($user)->get(route('perkara.show', $perkara));

    $response->assertOk();

    // Pastikan judul timeline dan container scrollable diterapkan
    $response->assertSee('Riwayat Penanganan Perkara (Case History)');
    $response->assertSee('max-h-[620px]');
    $response->assertSee('custom-scrollbar');

    // Pastikan pembeda visual aktor: Pengguna vs Sistem Otomatis
    $response->assertSee('Iptu Ahmad Yani');
    $response->assertSee('Penyidik');
    $response->assertSee('Sistem Otomatis');

    // Pastikan konteks tersangka muncul
    $response->assertSee('Rahmat Hidayat');

    // Pastikan data payload ter-render
    $response->assertSee('B-777/SPDP/BPOM/2026');
    $response->assertSee('Nomor Surat');
    $response->assertSee('Tahapan SPDP telah dikirimkan ke Kejaksaan.');
    $response->assertSee('Status Formal');
    $response->assertSee('Tidak Selesai');
    $response->assertSee('Batas waktu tahun perkara berakhir per 1 Januari 2027.');
    $response->assertSee('192.168.1.10');
});

test('halaman detail perkara menampilkan empty state informatif saat belum ada log audit', function () {
    $user = User::factory()->create([
        'role' => UserRole::PENYIDIK,
    ]);

    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/002/X/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'draft',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('perkara.show', $perkara));

    $response->assertOk();
    $response->assertSee('Belum Ada Riwayat Aktivitas');
    $response->assertSee('Total Aktivitas: 0');
});
