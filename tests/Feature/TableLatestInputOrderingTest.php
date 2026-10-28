<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->penyidik()->create(['is_active' => true]);
});

test('tabel laporan kejadian, sprindik, dan daftar perkara mengurutkan data berdasarkan waktu input terbaru bukan tanggal surat', function () {
    // Data 1: Diinput lebih dulu (kemarin), tetapi tanggal suratnya lebih baru (25 Okt 2026)
    $perkaraLama = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/LAMA-INPUT/2026',
        'tanggal_laporan_kejadian' => '2026-10-25',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/LAMA-INPUT/2026',
        'tanggal_surat_perintah' => '2026-10-26',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
    Perkara::whereKey($perkaraLama->id)->update([
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);

    // Data 2: Baru saja diinput hari ini, tetapi tanggal suratnya mundur (01 Okt 2026)
    $perkaraBaru = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/BARU-INPUT/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/BARU-INPUT/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    // 1. Laporan Kejadian: LK/BARU-INPUT/2026 muncul sebelum LK/LAMA-INPUT/2026
    $this->actingAs($this->penyidik)
        ->get(route('laporan-kejadian.index'))
        ->assertOk()
        ->assertSeeInOrder(['LK/BARU-INPUT/2026', 'LK/LAMA-INPUT/2026']);

    // 2. Sprindik: SP/BARU-INPUT/2026 muncul sebelum SP/LAMA-INPUT/2026
    $this->actingAs($this->penyidik)
        ->get(route('sprindik.index'))
        ->assertOk()
        ->assertSeeInOrder(['SP/BARU-INPUT/2026', 'SP/LAMA-INPUT/2026']);

    // 3. Daftar Perkara: LK/BARU-INPUT/2026 muncul sebelum LK/LAMA-INPUT/2026
    $this->actingAs($this->penyidik)
        ->get(route('data-perkara.index'))
        ->assertOk()
        ->assertSeeInOrder(['LK/BARU-INPUT/2026', 'LK/LAMA-INPUT/2026']);
});

test('tabel tahapan (spdp, tahap 1, p21, tahap 2, dan putusan perkara) mengurutkan data berdasarkan waktu input terbaru', function () {
    $stages = [
        ['jenis' => JenisTahap::SPDP, 'route' => 'spdp.index', 'prefix' => 'SPDP'],
        ['jenis' => JenisTahap::TAHAP_1, 'route' => 'tahap-1.index', 'prefix' => 'THP1'],
        ['jenis' => JenisTahap::P21, 'route' => 'p21.index', 'prefix' => 'P21'],
        ['jenis' => JenisTahap::TAHAP_2, 'route' => 'tahap-2.index', 'prefix' => 'THP2'],
        ['jenis' => JenisTahap::KEPUTUSAN_PENGADILAN, 'route' => 'putusan-perkara.index', 'prefix' => 'PTSN'],
    ];

    foreach ($stages as $idx => $cfg) {
        $perkara1 = Perkara::create([
            'nomor_laporan_kejadian' => "LK/STG1-{$idx}/2026",
            'tanggal_laporan_kejadian' => '2026-10-01',
            'tahap_laporan_status' => 'final',
            'nomor_surat_perintah' => "SP/STG1-{$idx}/2026",
            'tanggal_surat_perintah' => '2026-10-02',
            'tahap_sprindik_status' => 'final',
            'tahap_perkara_status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);
        $tsk1 = Tersangka::create(['perkara_id' => $perkara1->id, 'nama' => "Tersangka Lama {$idx}"]);
        $berkas1 = BerkasPerkara::create([
            'perkara_id' => $perkara1->id,
            'tersangka_id' => $tsk1->id,
            'nomor_berkas' => "BP/LAMA-{$idx}/2026",
        ]);

        $perkara2 = Perkara::create([
            'nomor_laporan_kejadian' => "LK/STG2-{$idx}/2026",
            'tanggal_laporan_kejadian' => '2026-10-01',
            'tahap_laporan_status' => 'final',
            'nomor_surat_perintah' => "SP/STG2-{$idx}/2026",
            'tanggal_surat_perintah' => '2026-10-02',
            'tahap_sprindik_status' => 'final',
            'tahap_perkara_status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);
        $tsk2 = Tersangka::create(['perkara_id' => $perkara2->id, 'nama' => "Tersangka Baru {$idx}"]);
        $berkas2 = BerkasPerkara::create([
            'perkara_id' => $perkara2->id,
            'tersangka_id' => $tsk2->id,
            'nomor_berkas' => "BP/BARU-{$idx}/2026",
        ]);

        // Tahap 1: Diinput kemarin, tetapi tanggal_surat lebih baru (25 Okt 2026)
        $tahapLama = TahapanPerkara::create([
            'berkas_perkara_id' => $berkas1->id,
            'jenis_tahap' => $cfg['jenis']->value,
            'nomor_surat' => "{$cfg['prefix']}/LAMA-INPUT/2026",
            'tanggal_surat' => '2026-10-25',
            'status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);
        TahapanPerkara::whereKey($tahapLama->id)->update([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // Tahap 2: Baru saja diinput hari ini, tetapi tanggal_surat lebih tua (05 Okt 2026)
        TahapanPerkara::create([
            'berkas_perkara_id' => $berkas2->id,
            'jenis_tahap' => $cfg['jenis']->value,
            'nomor_surat' => "{$cfg['prefix']}/BARU-INPUT/2026",
            'tanggal_surat' => '2026-10-05',
            'status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);

        $this->actingAs($this->penyidik)
            ->get(route($cfg['route']))
            ->assertOk()
            ->assertSeeInOrder([
                "{$cfg['prefix']}/BARU-INPUT/2026",
                "{$cfg['prefix']}/LAMA-INPUT/2026",
            ]);
    }
});
