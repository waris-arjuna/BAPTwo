<?php

use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('isMilestone mengidentifikasi aksi milestone dan non-milestone dengan benar', function () {
    $milestoneLog = new AuditLog(['aksi' => 'finalisasi_tahap']);
    expect($milestoneLog->isMilestone())->toBeTrue();

    $sp3Log = new AuditLog(['aksi' => 'terbitkan_sp3']);
    expect($sp3Log->isMilestone())->toBeTrue();

    $dpoOnLog = new AuditLog(['aksi' => 'aktifkan_dpo']);
    expect($dpoOnLog->isMilestone())->toBeTrue();

    $dpoOffLog = new AuditLog(['aksi' => 'nonaktifkan_dpo']);
    expect($dpoOffLog->isMilestone())->toBeTrue();

    $finalizeBerkasLog = new AuditLog(['aksi' => 'finalize_perkara_berkas']);
    expect($finalizeBerkasLog->isMilestone())->toBeTrue();

    $createLkLog = new AuditLog(['aksi' => 'create_laporan_kejadian']);
    expect($createLkLog->isMilestone())->toBeTrue();

    $attachSprindikLog = new AuditLog(['aksi' => 'attach_sprindik']);
    expect($attachSprindikLog->isMilestone())->toBeTrue();

    // Non-milestone (drafts or updates)
    $draftLog = new AuditLog(['aksi' => 'simpan_draft_tahap']);
    expect($draftLog->isMilestone())->toBeFalse();

    $updateLkLog = new AuditLog(['aksi' => 'update_laporan_kejadian']);
    expect($updateLkLog->isMilestone())->toBeFalse();

    $draftBerkasLog = new AuditLog(['aksi' => 'save_draft_perkara_berkas']);
    expect($draftBerkasLog->isMilestone())->toBeFalse();
});

test('action_meta accessor mengembalikan metadata terstruktur lengkap dengan icon flaticon', function () {
    $log = new AuditLog(['aksi' => 'finalisasi_tahap']);
    $meta = $log->action_meta;

    expect($meta)->toBeArray()
        ->toHaveKeys(['label', 'icon', 'badge', 'icon_bg'])
        ->and($meta['label'])->toBe('Finalisasi Tahapan Perkara')
        ->and($meta['icon'])->toBe('fi-rr-badge-check')
        ->and($meta['badge'])->toContain('border')
        ->and($meta['icon_bg'])->toContain('bg-');

    // Aksi yang tidak terdaftar tetap mengembalikan fallback yang aman dan rapi
    $unknownLog = new AuditLog(['aksi' => 'aksi_kustom_lain']);
    $unknownMeta = $unknownLog->action_meta;

    expect($unknownMeta)->toBeArray()
        ->toHaveKeys(['label', 'icon', 'badge', 'icon_bg'])
        ->and($unknownMeta['label'])->toBe('Aksi Kustom Lain')
        ->and($unknownMeta['icon'])->toBe('fi-rr-time-past');
});

test('scopeMilestone dan scopeTerkini memfilter dan mengurutkan audit log dengan benar', function () {
    $user = User::factory()->create(['role' => 'penyidik']);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $user->id,
    ]);

    // Buat beberapa log
    AuditLog::create([
        'user_id' => $user->id,
        'perkara_id' => $perkara->id,
        'aksi' => 'create_laporan_kejadian',
        'tabel' => 'perkara',
        'record_id' => $perkara->id,
        'created_at' => now()->subMinutes(30),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'perkara_id' => $perkara->id,
        'aksi' => 'simpan_draft_tahap',
        'tabel' => 'tahapan_perkara',
        'record_id' => 1,
        'created_at' => now()->subMinutes(20),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'perkara_id' => $perkara->id,
        'aksi' => 'finalisasi_tahap',
        'tabel' => 'tahapan_perkara',
        'record_id' => 1,
        'created_at' => now()->subMinutes(10),
    ]);

    $milestones = AuditLog::milestone()->get();
    expect($milestones)->toHaveCount(2)
        ->and($milestones->pluck('aksi')->all())->toContain('create_laporan_kejadian', 'finalisasi_tahap')
        ->and($milestones->pluck('aksi')->all())->not->toContain('simpan_draft_tahap');

    $terkini = AuditLog::terkini(2)->get();
    expect($terkini)->toHaveCount(2)
        ->and($terkini->first()->aksi)->toBe('finalisasi_tahap');
});

test('DashboardService getRecentActivities mengembalikan struktur milestones dan all dengan eager loading', function () {
    $user = User::factory()->create(['role' => 'penyidik']);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/002/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/002/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $user->id,
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Budi Santoso',
        'created_by' => $user->id,
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/002/X/2026',
        'status_formal' => null,
        'status_proses' => 'SPDP',
        'created_by' => $user->id,
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'finalisasi_tahap',
        'tabel' => 'tahapan_perkara',
        'record_id' => 1,
        'created_at' => now()->subMinutes(5),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'simpan_draft_tahap',
        'tabel' => 'tahapan_perkara',
        'record_id' => 1,
        'created_at' => now()->subMinutes(1),
    ]);

    $service = app(DashboardService::class);
    $result = $service->getRecentActivities(6);

    expect($result)->toBeArray()
        ->toHaveKeys(['milestones', 'all'])
        ->and($result['milestones'])->toHaveCount(1)
        ->and($result['all'])->toHaveCount(2);

    $milestoneItem = $result['milestones']->first();
    expect($milestoneItem->relationLoaded('user'))->toBeTrue()
        ->and($milestoneItem->relationLoaded('perkara'))->toBeTrue()
        ->and($milestoneItem->relationLoaded('berkasPerkara'))->toBeTrue()
        ->and($milestoneItem->berkasPerkara->relationLoaded('tersangka'))->toBeTrue()
        ->and($milestoneItem->berkasPerkara->tersangka->nama)->toBe('Budi Santoso');
});

test('dashboard route menyertakan data recentActivities dalam view response dan dapat diakses role penyidik dan umum', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $umum = User::factory()->create(['role' => 'umum']);

    $responsePenyidik = $this->actingAs($penyidik)->get(route('dashboard'));
    $responsePenyidik->assertOk()
        ->assertViewHas('recentActivities');

    $responseUmum = $this->actingAs($umum)->get(route('dashboard'));
    $responseUmum->assertOk()
        ->assertViewHas('recentActivities');
});

test('komponen recent activities menampilkan judul, toggle tab, item aktivitas dan nomor perkara', function () {
    $user = User::factory()->create(['nama' => 'Penyidik Arif', 'role' => 'penyidik']);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/003/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/003/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $user->id,
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Hendra Wijaya',
        'created_by' => $user->id,
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/003/X/2026',
        'status_formal' => null,
        'status_proses' => 'SPDP',
        'created_by' => $user->id,
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'perkara_id' => $perkara->id,
        'berkas_perkara_id' => $berkas->id,
        'aksi' => 'finalisasi_tahap',
        'tabel' => 'tahapan_perkara',
        'record_id' => 1,
        'created_at' => now()->subMinutes(5),
    ]);

    $service = app(DashboardService::class);
    $activities = $service->getRecentActivities(6);

    $view = $this->blade('<x-dashboard.recent-activities :activities="$activities" />', ['activities' => $activities]);

    $view->assertSee('Aktivitas Penyidikan Terkini')
        ->assertSee('Milestone Penting')
        ->assertSee('Semua Aktivitas')
        ->assertSee('Finalisasi Tahapan Perkara')
        ->assertSee('LK/003/X/2026')
        ->assertSee('Hendra Wijaya')
        ->assertSee('Penyidik Arif')
        ->assertSee('fi-rr-badge-check');
});

test('komponen recent activities menampilkan empty state yang informatif ketika data kosong', function () {
    $emptyActivities = [
        'milestones' => collect(),
        'all' => collect(),
    ];

    $view = $this->blade('<x-dashboard.recent-activities :activities="$activities" />', ['activities' => $emptyActivities]);

    $view->assertSee('Aktivitas Penyidikan Terkini')
        ->assertSee('Belum ada aktivitas penyidikan')
        ->assertSee('fi-rr-clipboard-list-check');
});

test('halaman dashboard merender tata letak baru dengan Status Perkara, Aktivitas Terkini, dan Rata-rata Tahap', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);

    $response = $this->actingAs($penyidik)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Aktivitas Penyidikan Terkini')
        ->assertSee('Rata-rata Penyelesaian per Tahap')
        ->assertSee('Perkara Tahun Berjalan');
});
