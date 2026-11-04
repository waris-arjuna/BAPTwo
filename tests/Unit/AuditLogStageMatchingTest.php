<?php

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('matchesStageContext memfilter log sesuai konteks tahapan dengan benar', function () {
    $lkLog = new AuditLog(['aksi' => 'finalisasi_laporan', 'tabel' => 'perkara']);
    expect($lkLog->matchesStageContext('laporan_kejadian'))->toBeTrue();
    expect($lkLog->matchesStageContext('sprindik'))->toBeFalse();

    $spLog = new AuditLog(['aksi' => 'finalisasi_sprindik', 'tabel' => 'perkara']);
    expect($spLog->matchesStageContext('sprindik'))->toBeTrue();
    expect($spLog->matchesStageContext('laporan_kejadian'))->toBeFalse();

    $tahapLog = new AuditLog([
        'aksi' => 'finalisasi_tahap',
        'tabel' => 'tahapan_perkara',
        'record_id' => 10,
        'berkas_perkara_id' => 5,
        'data_baru' => ['tahap' => 'SPDP', 'nomor_surat' => 'SPDP/01/2026'],
    ]);
    expect($tahapLog->matchesStageContext('tahapan', 10, 5, 'spdp'))->toBeTrue();
    expect($tahapLog->matchesStageContext('tahapan', 11, 5, 'tahap1'))->toBeFalse();
    expect($tahapLog->matchesStageContext('tahapan', 10, 99, 'spdp'))->toBeFalse();
});

test('formatted_details mengekstrak dokumen dan badge draft vs final', function () {
    $log = new AuditLog([
        'aksi' => 'finalisasi_tahap',
        'data_baru' => [
            'status' => 'final',
            'dokumen_id' => 42,
            'nama_dokumen' => 'SPDP_Resmi.pdf',
        ],
    ]);

    $details = $log->formatted_details;
    expect($details['document_chip'])->not->toBeNull();
    expect($details['document_chip']['nama'])->toBe('SPDP_Resmi.pdf');
    expect($details['document_chip']['id'])->toBe(42);
    expect($details['status_badge'])->not->toBeNull();
    expect($details['status_badge']['is_final'])->toBeTrue();
});
