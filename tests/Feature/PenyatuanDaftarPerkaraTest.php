<?php

use App\Helpers\MenuHelper;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->penyidik()->create();
    $this->umum = User::factory()->umum()->create();
    $this->admin = User::factory()->administrator()->create();
});

test('role umum hanya memiliki 2 menu pada navigasi: dashboard dan daftar perkara', function () {
    $this->actingAs($this->umum);

    $groups = MenuHelper::getMenuGroups();

    // Hanya 1 grup dengan 2 menu
    expect($groups)->toHaveCount(1)
        ->and($groups[0]['title'])->toBe('Menu Utama')
        ->and($groups[0]['items'])->toHaveCount(2);

    $paths = array_column($groups[0]['items'], 'path');
    expect($paths)->toEqual(['/', '/data-perkara']);

    $response = $this->get(route('dashboard'));
    $response->assertOk();

    $content = $response->getContent();
    preg_match('/<nav[^>]*>(.*?)<\/nav>/s', $content, $matches);
    $navHtml = $matches[1] ?? '';

    expect($navHtml)->toContain('Dashboard')
        ->and($navHtml)->toContain('Daftar Perkara')
        ->and($navHtml)->not->toContain('Potensi Terlimpah')
        ->and($navHtml)->not->toContain('Laporan Kejadian');
});

test('halaman daftar perkara memuat 4 kartu metrik kesiapan pelimpahan', function () {
    // 1. Buat perkara Siap Tahap I (SPDP final, progress 15%)
    $perkara1 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka1 = Tersangka::create([
        'perkara_id' => $perkara1->id,
        'nama' => 'Tersangka Satu',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara1->id,
        'tersangka_id' => $tersangka1->id,
        'nomor_berkas' => 'BP/001/2026',
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
        'status_formal' => null,
        'is_dpo' => false,
        'created_by' => $this->penyidik->id,
    ]);

    // 2. Buat perkara Siap Tahap II (Tahap I difinalkan, progress 55%)
    $perkara2 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/002/X/2026',
        'nomor_surat_perintah' => 'SP/002/X/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka2 = Tersangka::create([
        'perkara_id' => $perkara2->id,
        'nama' => 'Tersangka Dua',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara2->id,
        'tersangka_id' => $tersangka2->id,
        'nomor_berkas' => 'BP/002/2026',
        'status_proses' => 'TAHAP I',
        'progress_persen' => 55,
        'status_formal' => null,
        'is_dpo' => false,
        'created_by' => $this->penyidik->id,
    ]);

    // 3. Buat perkara Terkendala DPO
    $perkara3 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/003/X/2026',
        'nomor_surat_perintah' => 'SP/003/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka3 = Tersangka::create([
        'perkara_id' => $perkara3->id,
        'nama' => 'Tersangka Tiga Buron',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara3->id,
        'tersangka_id' => $tersangka3->id,
        'nomor_berkas' => 'BP/003/2026',
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
        'status_formal' => null,
        'is_dpo' => true,
        'created_by' => $this->penyidik->id,
    ]);

    // 4. Buat perkara Melebihi SLA
    $perkara4 = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/004/X/2026',
        'nomor_surat_perintah' => 'SP/004/X/2026',
        'tanggal_surat_perintah' => '2026-10-04',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka4 = Tersangka::create([
        'perkara_id' => $perkara4->id,
        'nama' => 'Tersangka Empat Terlambat',
        'created_by' => $this->penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara4->id,
        'tersangka_id' => $tersangka4->id,
        'nomor_berkas' => 'BP/004/2026',
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
        'status_formal' => null,
        'sla_deadline' => Carbon::now()->subDays(5)->toDateString(),
        'is_dpo' => false,
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)->get(route('data-perkara.index'));
    $response->assertOk();
    $response->assertSee('Kesiapan Pelimpahan &amp; Prioritas', false);
    $response->assertSee('Siap Tahap I');
    $response->assertSee('Siap Tahap II');
    $response->assertSee('Terkendala DPO');
    $response->assertSee('Melebihi SLA');
});

test('tabel daftar perkara menampilkan kolom rekomendasi tindakan dengan badge status cerdas', function () {
    // Perkara Selesai
    $perkaraSelesai = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SEL/01',
        'nomor_surat_perintah' => 'SP/SEL/01',
        'tanggal_surat_perintah' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'status_formal' => 'selesai',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkaraSelesai->id, 'nama' => 'Tersangka Selesai', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $perkaraSelesai->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/SEL/01',
        'status_proses' => 'TAHAP II',
        'progress_persen' => 100,
        'status_formal' => 'selesai',
        'is_dpo' => false,
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)->get(route('data-perkara.index'));
    $response->assertOk();
    $response->assertSee('Rekomendasi Tindakan');
    $response->assertSee('Catat Keputusan Pengadilan');
});

test('pil rekomendasi memuat perintah, sisa hari, dan tooltip title', function () {
    Carbon::setTestNow('2026-11-05 08:00:00');
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PIL/01',
        'nomor_surat_perintah' => 'SP/PIL/01',
        'tanggal_surat_perintah' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);
    $tersangka = Tersangka::create(['perkara_id' => $perkara->id, 'nama' => 'Tersangka Pil', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/PIL/01',
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
        'sla_deadline' => '2026-11-15',
        'status_formal' => null,
        'is_dpo' => false,
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)->get(route('data-perkara.index'));
    $response->assertSee('Serahkan berkas TAHAP I')
        ->assertSee('10 hari lagi')
        ->assertSee('title="SPDP sudah final. Serahkan berkas perkara TAHAP I ke Kejaksaan paling lambat 15 Nov 2026."', false);
});

test('filter kesiapan pelimpahan menyaring data perkara secara akurat', function () {
    // Perkara A: Siap Tahap I
    $perkaraA = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/FLT/01',
        'nomor_surat_perintah' => 'SP/FLT/01',
        'tanggal_surat_perintah' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangkaA = Tersangka::create(['perkara_id' => $perkaraA->id, 'nama' => 'Tersangka A', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $perkaraA->id,
        'tersangka_id' => $tersangkaA->id,
        'nomor_berkas' => 'BP/FLT/01',
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
        'status_formal' => null,
        'is_dpo' => false,
        'created_by' => $this->penyidik->id,
    ]);

    // Perkara B: DPO
    $perkaraB = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/FLT/02',
        'nomor_surat_perintah' => 'SP/FLT/02',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    $tersangkaB = Tersangka::create(['perkara_id' => $perkaraB->id, 'nama' => 'Tersangka B', 'created_by' => $this->penyidik->id]);
    BerkasPerkara::create([
        'perkara_id' => $perkaraB->id,
        'tersangka_id' => $tersangkaB->id,
        'nomor_berkas' => 'BP/FLT/02',
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
        'status_formal' => null,
        'is_dpo' => true,
        'created_by' => $this->penyidik->id,
    ]);

    // Filter siap_tahap_1
    $res1 = $this->actingAs($this->penyidik)->get(route('data-perkara.index', ['kesiapan' => 'siap_tahap_1']));
    $res1->assertOk();
    $res1->assertSee('LK/FLT/01');
    $res1->assertDontSee('LK/FLT/02');

    // Filter terkendala_dpo
    $res2 = $this->actingAs($this->penyidik)->get(route('data-perkara.index', ['kesiapan' => 'terkendala_dpo']));
    $res2->assertOk();
    $res2->assertSee('LK/FLT/02');
    $res2->assertDontSee('LK/FLT/01');
});

test('request ajax dengan header X-Filter-Partial merender table-partial data perkara', function () {
    $response = $this->actingAs($this->penyidik)
        ->withHeaders(['X-Filter-Partial' => '1'])
        ->get(route('data-perkara.index'));

    $response->assertOk();
    $response->assertViewIs('pages.data-perkara._table-partial');
    $response->assertSee('Rekomendasi Tindakan');
});
