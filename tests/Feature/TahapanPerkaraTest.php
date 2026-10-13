<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\TahapanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->perkara = Perkara::create([
        'nomor_laporan' => 'LK/001/X/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_sprindik' => 'SP/001/X/2026',
        'tanggal_sprindik' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->user->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Budi Santoso',
        'created_by' => $this->user->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/001/X/2026/BPOM',
        'created_by' => $this->user->id,
    ]);

    $this->service = app(TahapanService::class);
});

test('enum JenisTahap memiliki label, bobot, urutan, dan flag opsional yang tepat', function () {
    expect(JenisTahap::SPDP->label())->toBe('SPDP')
        ->and(JenisTahap::SPDP->bobot())->toBe(15)
        ->and(JenisTahap::SPDP->isOptional())->toBeFalse()
        ->and(JenisTahap::SPDP->urutan())->toBe(1)
        ->and(JenisTahap::P16->label())->toBe('P-16')
        ->and(JenisTahap::P16->isOptional())->toBeTrue()
        ->and(JenisTahap::TAHAP_1->label())->toBe('TAHAP I')
        ->and(JenisTahap::TAHAP_1->bobot())->toBe(55)
        ->and(JenisTahap::P21->label())->toBe('P-21/P21a')
        ->and(JenisTahap::P21->bobot())->toBe(85)
        ->and(JenisTahap::TAHAP_2->label())->toBe('TAHAP II')
        ->and(JenisTahap::TAHAP_2->bobot())->toBe(100)
        ->and(JenisTahap::KEPUTUSAN_PENGADILAN->bobot())->toBe(100);
});

test('berkas perkara baru hanya dapat memulai dari tahapan SPDP', function () {
    $next = $this->service->getNextAvailableStages($this->berkas);

    expect($next)->toBe([JenisTahap::SPDP]);
    expect($this->service->canProceedTo($this->berkas, JenisTahap::SPDP))->toBeTrue();
    expect($this->service->canProceedTo($this->berkas, JenisTahap::TAHAP_1))->toBeFalse();

    expect(fn () => $this->service->validateStageOrder($this->berkas, JenisTahap::TAHAP_1))
        ->toThrow(DomainException::class, 'tidak valid untuk urutan progress saat ini');
});

test('alur tahapan dengan tahapan opsional P-16 dan P-17P berjalan sesuai urutan', function () {
    // 1. Simpan dan finalkan SPDP
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/001/X/2026',
        'tanggal_surat' => '2026-10-03',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->user->id,
    ]);

    $this->berkas->refresh();
    expect($this->service->calculateProgress($this->berkas))->toBe(15);
    expect($this->service->determineStatusProses($this->berkas))->toBe('SPDP');

    // Pilihan setelah SPDP: P-16, P-17P, atau TAHAP I
    $availableAfterSpdp = $this->service->getNextAvailableStages($this->berkas);
    expect($availableAfterSpdp)->toBe([JenisTahap::P16, JenisTahap::P17P, JenisTahap::TAHAP_1]);

    // 2. Pilih P-16
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::P16,
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->user->id,
    ]);

    $this->berkas->refresh();
    expect($this->service->calculateProgress($this->berkas))->toBe(15) // P-16 tidak menambah bobot
        ->and($this->service->determineStatusProses($this->berkas))->toBe('P-16');

    // Pilihan setelah P-16: P-17P atau TAHAP I
    $availableAfterP16 = $this->service->getNextAvailableStages($this->berkas);
    expect($availableAfterP16)->toBe([JenisTahap::P17P, JenisTahap::TAHAP_1]);
});

test('tahapan opsional dapat dilewati langsung ke tahapan wajib berikutnya', function () {
    // 1. Finalkan SPDP
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'status' => 'final',
        'created_by' => $this->user->id,
    ]);

    // Loncat lewati P-16 dan P-17P langsung ke TAHAP I
    expect($this->service->canProceedTo($this->berkas, JenisTahap::TAHAP_1))->toBeTrue();
    $this->service->validateStageOrder($this->berkas, JenisTahap::TAHAP_1);

    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1,
        'status' => 'final',
        'created_by' => $this->user->id,
    ]);

    $this->berkas->refresh();
    expect($this->service->calculateProgress($this->berkas))->toBe(55);
    expect($this->service->determineStatusProses($this->berkas))->toBe('TAHAP I');

    // Loncat lewati P-18P dan P-19 langsung ke P-21/P21a
    expect($this->service->canProceedTo($this->berkas, JenisTahap::P21))->toBeTrue();
    $this->service->validateStageOrder($this->berkas, JenisTahap::P21);

    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::P21,
        'status' => 'final',
        'created_by' => $this->user->id,
    ]);

    $this->berkas->refresh();
    expect($this->service->calculateProgress($this->berkas))->toBe(85);
    expect($this->service->determineStatusProses($this->berkas))->toBe('P-21/P21a');

    // Lanjut ke TAHAP II
    expect($this->service->canProceedTo($this->berkas, JenisTahap::TAHAP_2))->toBeTrue();
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'status' => 'final',
        'created_by' => $this->user->id,
    ]);

    $this->berkas->refresh();
    expect($this->service->calculateProgress($this->berkas))->toBe(100);
    expect($this->service->determineStatusProses($this->berkas))->toBe('TAHAP II');

    // Lanjut ke Keputusan Pengadilan (tidak menaikkan di atas 100%)
    expect($this->service->canProceedTo($this->berkas, JenisTahap::KEPUTUSAN_PENGADILAN))->toBeTrue();
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN,
        'status' => 'final',
        'created_by' => $this->user->id,
    ]);

    $this->berkas->refresh();
    expect($this->service->calculateProgress($this->berkas))->toBe(100);
    expect($this->service->determineStatusProses($this->berkas))->toBe('Keputusan Pengadilan');
    expect($this->service->getNextAvailableStages($this->berkas))->toBe([]);
});

test('satu jenis tahapan tidak boleh dicatat dua kali per berkas perkara', function () {
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'status' => 'final',
        'created_by' => $this->user->id,
    ]);

    $this->berkas->refresh();

    expect(fn () => $this->service->validateStageOrder($this->berkas, JenisTahap::SPDP))
        ->toThrow(DomainException::class, 'sudah pernah dicatat pada Berkas Perkara ini');
});

test('tahapan yang telah final terkunci permanen dan melempar exception saat divalidasi', function () {
    $tahapDraft = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'status' => 'draft',
        'created_by' => $this->user->id,
    ]);

    // Draft tidak terkunci
    expect($tahapDraft->isLocked())->toBeFalse();
    $this->service->assertStageNotLocked($tahapDraft); // tidak throw exception

    // Final terkunci
    $tahapDraft->update([
        'status' => 'final',
        'finalized_at' => now(),
    ]);

    expect($tahapDraft->isLocked())->toBeTrue();
    expect(fn () => $this->service->assertStageNotLocked($tahapDraft))
        ->toThrow(DomainException::class, 'telah difinalkan dan terkunci permanen');
});
