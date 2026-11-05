<?php

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('perkara dengan status formal dpo_aktif tetap menghasilkan badge SLA yang valid', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DPO/SLA',
        'nomor_surat_perintah' => 'SP/DPO/SLA',
        'tanggal_surat_perintah' => '2026-04-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Buron SLA', 'created_by' => $penyidik->id]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/DPO/SLA',
        'tanggal_spdp' => '2026-04-01',
        'sla_deadline' => Carbon::now()->addDays(5)->toDateString(),
        'is_dpo' => true,
        'created_by' => $penyidik->id,
    ]);

    expect($perkara->status_formal)->toBe('dpo_aktif');
    $slaBadge = $perkara->getSlaBadgeInfo();
    expect($slaBadge)->not->toBeNull();
    expect($slaBadge['label'])->toContain('Sisa');

    // Tampilkan di tabel daftar perkara
    $response = $this->actingAs($penyidik)->get(route('data-perkara.index'));
    $response->assertOk();
    $response->assertSee('DPO Aktif');
    $response->assertSee($slaBadge['label']);
});

test('tabel prioritas dashboard menggunakan effective_deadline sebagai fallback saat sla_deadline kosong', function () {
    $penyidik = User::factory()->penyidik()->create();
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DASH/01',
        'nomor_surat_perintah' => 'SP/DASH/01',
        'tanggal_surat_perintah' => '2026-05-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Dash', 'created_by' => $penyidik->id]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/DASH/01',
        'tanggal_spdp' => '2026-05-10',
        'sla_deadline' => null, // sengaja null untuk menguji fallback
        'created_by' => $penyidik->id,
    ]);

    $dashService = app(DashboardService::class);
    $priority = $dashService->getPriorityProgressPerkara(new Request);
    $item = collect($priority)->firstWhere('id', $berkas->id);

    expect($item)->not->toBeNull();
    expect($item['estimasi'])->toBe('10-06-2026');
});
