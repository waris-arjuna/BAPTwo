<?php

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('isSystemAction mengembalikan true jika user_id kosong dan false jika ada user', function () {
    $systemLog = new AuditLog(['user_id' => null, 'aksi' => 'sistem_tidak_selesai']);
    expect($systemLog->isSystemAction())->toBeTrue();
    expect($systemLog->actor_label)->toBe('Sistem Otomatis');

    $user = User::factory()->create(['nama' => 'Budi Santoso', 'role' => UserRole::PENYIDIK]);
    $userLog = new AuditLog(['user_id' => $user->id, 'aksi' => 'finalisasi_tahap']);
    $userLog->setRelation('user', $user);

    expect($userLog->isSystemAction())->toBeFalse();
    expect($userLog->actor_label)->toBe('Budi Santoso');
    expect($userLog->actor_role_label)->toBe($user->role->label());
});

test('formatted_details mengekstrak atribut dokumen dan transisi status dengan rapi', function () {
    $log = new AuditLog([
        'aksi' => 'terbitkan_sp3',
        'data_lama' => ['status_formal' => null],
        'data_baru' => [
            'nomor_sp3' => 'S.Tap/SP3/001/XI/2026/BPOM',
            'status_formal' => 'tidak_selesai',
            'status_proses' => 'SP3',
            'keterangan' => 'Tersangka meninggal dunia',
        ],
    ]);

    $details = $log->formatted_details;

    expect($details['attributes'])->toHaveKey('Nomor SP3');
    expect($details['attributes']['Nomor SP3'])->toBe('S.Tap/SP3/001/XI/2026/BPOM');
    expect($details['transitions'])->toHaveCount(1);
    expect($details['transitions'][0]['label'])->toBe('Status Formal');
    expect($details['transitions'][0]['to'])->toBe('Tidak Selesai');
    expect($details['notes'])->toHaveKey('Keterangan');
    expect($details['notes']['Keterangan'])->toBe('Tersangka meninggal dunia');
});
