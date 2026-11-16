<?php

use App\Models\AuditLog;
use App\Models\Perkara;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tamu diarahkan ke halaman masuk saat mengakses audit log global', function () {
    $this->get(route('audit-log.index'))
        ->assertRedirect(route('masuk'));
});

test('non-administrator diblokir mengakses audit log global dengan 403', function () {
    $penyidik = User::factory()->penyidik()->create();
    $umum = User::factory()->umum()->create();

    $this->actingAs($penyidik)
        ->get(route('audit-log.index'))
        ->assertForbidden();

    $this->actingAs($umum)
        ->get(route('audit-log.index'))
        ->assertForbidden();
});

test('administrator dapat melihat seluruh audit log global termasuk aksi sistem dan keamanan', function () {
    $admin = User::factory()->administrator()->create(['nama' => 'Admin Audit']);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/099/XI/2026',
        'tanggal_laporan_kejadian' => '2026-11-16',
        'tahap_laporan_status' => 'final',
        'created_by' => $admin->id,
    ]);

    AuditLog::create([
        'user_id' => null,
        'aksi' => 'login_failed',
        'tabel' => 'user',
        'record_id' => 0,
        'data_baru' => ['email' => 'penyusup@bpom.go.id'],
        'ip_address' => '10.20.30.40',
        'created_at' => now(),
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'aksi' => 'export_excel',
        'tabel' => 'perkara',
        'record_id' => 0,
        'data_baru' => ['filename' => 'LAPJU_2026_11.xlsx', 'total_perkara' => 12],
        'ip_address' => '10.20.30.41',
        'created_at' => now()->subMinute(),
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'aksi' => 'sync_sibob_users',
        'tabel' => 'user',
        'record_id' => $admin->id,
        'data_baru' => ['created' => 2, 'updated' => 10, 'deactivated' => 1, 'total' => 12],
        'ip_address' => '10.20.30.42',
        'created_at' => now()->subMinutes(2),
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'perkara_id' => $perkara->id,
        'aksi' => 'create_laporan_kejadian',
        'tabel' => 'perkara',
        'record_id' => $perkara->id,
        'data_baru' => ['nomor_laporan_kejadian' => 'LK/099/XI/2026'],
        'ip_address' => '10.20.30.43',
        'created_at' => now()->subMinutes(3),
    ]);

    $this->actingAs($admin)
        ->get(route('audit-log.index'))
        ->assertOk()
        ->assertSee('Audit Log Global Sistem')
        ->assertSee('Percobaan Masuk Gagal')
        ->assertSee('penyusup@bpom.go.id')
        ->assertSee('Ekspor Rekap Excel (LAPJU)')
        ->assertSee('LAPJU_2026_11.xlsx')
        ->assertSee('Sinkronisasi Pegawai SIBOB')
        ->assertSee('LK/099/XI/2026');
});

test('filter kategori, aksi, pencarian, tanggal, dan partial ajax audit log berfungsi akurat', function () {
    $admin = User::factory()->administrator()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/111/XI/2026',
        'tanggal_laporan_kejadian' => '2026-11-10',
        'tahap_laporan_status' => 'final',
        'created_by' => $admin->id,
    ]);

    AuditLog::create([
        'user_id' => null,
        'aksi' => 'login_failed',
        'tabel' => 'user',
        'record_id' => 0,
        'data_baru' => ['email' => 'gagal@bpom.go.id'],
        'ip_address' => '192.168.1.99',
        'created_at' => '2026-11-16 04:00:00',
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'perkara_id' => $perkara->id,
        'aksi' => 'create_laporan_kejadian',
        'tabel' => 'perkara',
        'record_id' => $perkara->id,
        'data_baru' => ['nomor_laporan_kejadian' => 'LK/111/XI/2026'],
        'ip_address' => '192.168.1.100',
        'created_at' => '2026-11-10 04:00:00',
    ]);

    // Filter kategori sistem
    $this->actingAs($admin)
        ->get(route('audit-log.index', ['kategori' => 'sistem']))
        ->assertOk()
        ->assertSee('gagal@bpom.go.id')
        ->assertDontSee('LK/111/XI/2026');

    // Filter kategori perkara
    $this->actingAs($admin)
        ->get(route('audit-log.index', ['kategori' => 'perkara']))
        ->assertOk()
        ->assertSee('LK/111/XI/2026')
        ->assertDontSee('gagal@bpom.go.id');

    // Filter rentang tanggal WITA
    $this->actingAs($admin)
        ->get(route('audit-log.index', ['tanggal_dari' => '2026-11-15', 'tanggal_sampai' => '2026-11-16']))
        ->assertOk()
        ->assertSee('gagal@bpom.go.id')
        ->assertDontSee('LK/111/XI/2026');

    // Filter aksi + partial AJAX
    $this->actingAs($admin)
        ->withHeaders(['X-Filter-Partial' => '1'])
        ->get(route('audit-log.index', ['aksi' => 'login_failed', 'search' => '192.168.1.99']))
        ->assertOk()
        ->assertSee('gagal@bpom.go.id')
        ->assertDontSee('Audit Log Global Sistem')
        ->assertDontSee('LK/111/XI/2026');
});

test('actionMeta memiliki metadata berbahasa Indonesia untuk aksi keamanan dan sistem baru', function () {
    expect(AuditLog::actionMeta('login_failed')['label'])->toBe('Percobaan Masuk Gagal')
        ->and(AuditLog::actionMeta('login_inactive')['label'])->toBe('Masuk dengan Akun Nonaktif')
        ->and(AuditLog::actionMeta('export_excel')['label'])->toBe('Ekspor Rekap Excel (LAPJU)')
        ->and(AuditLog::actionMeta('atur_petugas')['label'])->toBe('Pengaturan Peran Petugas')
        ->and(AuditLog::actionMeta('penangkapan_dpo')['label'])->toBe('Penangkapan / Penahanan DPO');
});
