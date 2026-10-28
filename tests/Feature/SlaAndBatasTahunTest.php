<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\TahapanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-03-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-03-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Budi Santoso',
        'nik' => '6371012345670001',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/001/III/2026/BPOM',
        'created_by' => $this->penyidik->id,
    ]);
});

test('perhitungan SLA adalah 1 bulan kalender secara total dari tanggal terbit SPDP', function () {
    $tahapanService = app(TahapanService::class);

    // Finalkan SPDP dengan tanggal 10 Maret 2026
    $tahap = $tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::SPDP->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'SPDP/001/III/2026',
            'tanggal_surat' => '2026-03-10',
        ],
        null,
        $this->penyidik
    );

    $this->berkas->refresh();

    expect($this->berkas->tanggal_spdp->toDateString())->toBe('2026-03-10');
    // 1 bulan kalender dari 10 Maret 2026 adalah 10 April 2026
    expect($this->berkas->sla_deadline->toDateString())->toBe('2026-04-10');
    expect($this->berkas->effective_deadline->toDateString())->toBe('2026-04-10');
});

test('batas tahun mutlak selalu didahulukan jika SLA 1 bulan kalender jatuh di tahun berikutnya', function () {
    $tahapanService = app(TahapanService::class);

    // Finalkan SPDP dekat akhir tahun: 15 Desember 2026
    $tahap = $tahapanService->saveStage(
        $this->berkas,
        [
            'jenis_tahap' => JenisTahap::SPDP->value,
            'action_type' => 'lanjut',
            'nomor_surat' => 'SPDP/999/XII/2026',
            'tanggal_surat' => '2026-12-15',
        ],
        null,
        $this->penyidik
    );

    $this->berkas->refresh();

    expect($this->berkas->tanggal_spdp->toDateString())->toBe('2026-12-15');
    // Karena batas tahun mutlak (PRD bagian Batas Waktu), batas akhir adalah 31 Desember 2026, bukan 15 Januari 2027
    expect($this->berkas->effective_deadline->toDateString())->toBe('2026-12-31');
    expect($this->berkas->sla_deadline->toDateString())->toBe('2026-12-31');
});

test('evaluasi status SLA memberikan status dan badge yang sesuai dengan kondisi berkas', function () {
    // 1. Pra-SPDP: belum ada SPDP
    expect($this->berkas->sla_status)->toBe('belum_spdp');
    expect($this->berkas->sla_badge['label'])->toBe('Pra-SPDP');

    // 2. SPDP aktif masih jauh dari batas waktu
    $this->berkas->update([
        'tanggal_spdp' => Carbon::now()->toDateString(),
        'sla_deadline' => Carbon::now()->addDays(20)->toDateString(),
    ]);
    expect($this->berkas->sla_status)->toBe('sesuai_sla');
    expect($this->berkas->sla_badge['label'])->toContain('Tenggang Waktu Berjalan');

    // 3. SPDP aktif mendekati batas waktu (<= 7 hari)
    $this->berkas->update([
        'sla_deadline' => Carbon::now()->addDays(3)->toDateString(),
    ]);
    expect($this->berkas->sla_status)->toBe('mendekati_sla');
    expect($this->berkas->sla_badge['label'])->toContain('Mendekati Batas Tenggang Waktu');

    // 4. SPDP aktif melebihi batas waktu (overdue)
    $this->berkas->update([
        'sla_deadline' => Carbon::now()->subDays(5)->toDateString(),
    ]);
    expect($this->berkas->sla_status)->toBe('lewat_sla');
    expect($this->berkas->sla_badge['label'])->toContain('Melebihi Tenggang Waktu');

    // 5. Berkas selesai tepat waktu
    $this->berkas->update([
        'status_formal' => 'selesai',
        'sla_deadline' => Carbon::now()->addDays(10)->toDateString(),
    ]);
    expect($this->berkas->sla_status)->toBe('selesai_tepat_waktu');
    expect($this->berkas->sla_badge['label'])->toBe('Selesai Tepat Waktu');

    // 6. Berkas tidak selesai
    $this->berkas->update([
        'status_formal' => 'tidak_selesai',
    ]);
    expect($this->berkas->sla_status)->toBe('tidak_selesai');
    expect($this->berkas->sla_badge['label'])->toBe('Tidak Selesai');
});

test('status DPO aktif tidak menghentikan atau menjeda evaluasi SLA', function () {
    $this->berkas->update([
        'tanggal_spdp' => Carbon::now()->toDateString(),
        'sla_deadline' => Carbon::now()->addDays(5)->toDateString(),
        'is_dpo' => true,
    ]);

    // DPO aktif tetap mengevaluasi SLA secara normal (mendekati_sla karena sisa 5 hari)
    expect($this->berkas->sla_status)->toBe('mendekati_sla');
    expect($this->berkas->is_dpo)->toBeTrue();
});
