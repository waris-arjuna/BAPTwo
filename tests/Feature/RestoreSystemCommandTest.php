<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('command baptwo:restore memulihkan data database dan dokumen pdf privat serta membuat safety backup', function () {
    Storage::fake('local');
    Storage::disk('local')->put('dokumen/asli.pdf', '%PDF-1.4 dokumen asli');

    $user = User::factory()->create([
        'nama' => 'Penyidik Asli',
        'email' => 'asli@baptwo.test',
        'role' => UserRole::PENYIDIK,
    ]);

    $backupDir = storage_path('app/backups');
    File::ensureDirectoryExists($backupDir);
    File::cleanDirectory($backupDir);

    $this->artisan('baptwo:backup')->assertExitCode(0);
    $archives = glob($backupDir.DIRECTORY_SEPARATOR.'baptwo-backup-*.zip');
    expect($archives)->toHaveCount(1);
    $backupArchive = $archives[0];

    // Simulasikan kerusakan/perubahan data setelah backup
    $user->update(['nama' => 'Nama Rusak']);
    User::factory()->create(['email' => 'tambahan@baptwo.test']);
    Storage::disk('local')->delete('dokumen/asli.pdf');
    Storage::disk('local')->put('dokumen/salah.pdf', 'file salah');

    // Jeda 1 detik agar safety backup memiliki nama file berbeda dari arsip sumber
    sleep(1);

    $this->artisan('baptwo:restore', [
        'archive' => basename($backupArchive),
        '--force' => true,
    ])->assertExitCode(0);

    expect(User::where('email', 'asli@baptwo.test')->value('nama'))->toBe('Penyidik Asli');
    expect(User::where('email', 'tambahan@baptwo.test')->exists())->toBeFalse();
    expect(Storage::disk('local')->exists('dokumen/asli.pdf'))->toBeTrue();
    expect(Storage::disk('local')->get('dokumen/asli.pdf'))->toBe('%PDF-1.4 dokumen asli');
    expect(Storage::disk('local')->exists('dokumen/salah.pdf'))->toBeFalse();

    // Safety backup otomatis dibuat sebelum restore
    $allArchives = glob($backupDir.DIRECTORY_SEPARATOR.'baptwo-backup-*.zip');
    expect(count($allArchives))->toBeGreaterThanOrEqual(2);

    File::cleanDirectory($backupDir);
});

test('command baptwo:restore mode --dry-run memvalidasi arsip tanpa mengubah data maupun file', function () {
    Storage::fake('local');
    Storage::disk('local')->put('dokumen/awal.pdf', 'awal');

    $user = User::factory()->create([
        'nama' => 'Nama Awal',
        'email' => 'awal@baptwo.test',
    ]);

    $backupDir = storage_path('app/backups');
    File::ensureDirectoryExists($backupDir);
    File::cleanDirectory($backupDir);

    $this->artisan('baptwo:backup')->assertExitCode(0);
    $backupArchive = glob($backupDir.DIRECTORY_SEPARATOR.'baptwo-backup-*.zip')[0];

    $user->update(['nama' => 'Nama Terbaru']);
    Storage::disk('local')->put('dokumen/baru.pdf', 'baru');

    $this->artisan('baptwo:restore', [
        'archive' => $backupArchive,
        '--dry-run' => true,
    ])->assertExitCode(0);

    expect(User::where('email', 'awal@baptwo.test')->value('nama'))->toBe('Nama Terbaru');
    expect(Storage::disk('local')->exists('dokumen/baru.pdf'))->toBeTrue();

    File::cleanDirectory($backupDir);
});

test('command baptwo:restore dibatalkan jika pengguna menolak konfirmasi interaktif', function () {
    Storage::fake('local');
    $user = User::factory()->create(['nama' => 'Nama Sebelumnya', 'email' => 'cek@baptwo.test']);

    $backupDir = storage_path('app/backups');
    File::ensureDirectoryExists($backupDir);
    File::cleanDirectory($backupDir);

    $this->artisan('baptwo:backup')->assertExitCode(0);
    $backupArchive = glob($backupDir.DIRECTORY_SEPARATOR.'baptwo-backup-*.zip')[0];

    $user->update(['nama' => 'Nama Sekarang']);

    $this->artisan('baptwo:restore', [
        'archive' => $backupArchive,
    ])
        ->expectsConfirmation('Lanjutkan proses restore dan timpa seluruh data saat ini?', 'no')
        ->assertExitCode(1);

    expect(User::where('email', 'cek@baptwo.test')->value('nama'))->toBe('Nama Sekarang');

    File::cleanDirectory($backupDir);
});

test('command baptwo:restore menolak arsip yang tidak valid atau tidak ditemukan tanpa merusak data', function () {
    Storage::fake('local');
    User::factory()->create(['nama' => 'Tetap Aman', 'email' => 'aman@baptwo.test']);

    $this->artisan('baptwo:restore', [
        'archive' => 'tidak-ada.zip',
        '--force' => true,
    ])->assertExitCode(1);

    expect(User::where('email', 'aman@baptwo.test')->value('nama'))->toBe('Tetap Aman');
});
