<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('command baptwo:backup membuat arsip zip berisi dump database dan dokumen pdf privat serta menghapus backup kadaluarsa', function () {
    Storage::fake('local');
    Storage::disk('local')->put('dokumen/spdp-uji.pdf', '%PDF-1.4 dummy content');

    $backupDir = storage_path('app/backups');
    File::ensureDirectoryExists($backupDir);
    File::cleanDirectory($backupDir);

    $oldBackup = $backupDir.DIRECTORY_SEPARATOR.'baptwo-backup-2025-01-01-020000.zip';
    $unrelatedFile = $backupDir.DIRECTORY_SEPARATOR.'catatan.txt';
    File::put($oldBackup, 'old-zip');
    File::put($unrelatedFile, 'keep-me');
    touch($oldBackup, now()->subDays(40)->timestamp);

    $this->artisan('baptwo:backup', ['--keep-days' => 30])
        ->assertExitCode(0);

    expect(File::exists($oldBackup))->toBeFalse();
    expect(File::exists($unrelatedFile))->toBeTrue();

    $archives = glob($backupDir.DIRECTORY_SEPARATOR.'baptwo-backup-*.zip');
    expect($archives)->toHaveCount(1);

    $zip = new ZipArchive;
    expect($zip->open($archives[0]))->toBeTrue();
    expect($zip->locateName('database-dump.json'))->not->toBeFalse();
    expect($zip->locateName('private/dokumen/spdp-uji.pdf'))->not->toBeFalse();

    $dump = json_decode((string) $zip->getFromName('database-dump.json'), true);
    expect($dump)->toHaveKeys(['generated_at', 'connection', 'tables']);
    expect($dump['tables'])->toHaveKey('user');

    $zip->close();
    File::cleanDirectory($backupDir);
});

test('command baptwo:backup tetap sukses membuat arsip saat storage privat belum memiliki file pdf', function () {
    Storage::fake('local');

    $backupDir = storage_path('app/backups');
    File::ensureDirectoryExists($backupDir);
    File::cleanDirectory($backupDir);

    $this->artisan('baptwo:backup')
        ->assertExitCode(0);

    $archives = glob($backupDir.DIRECTORY_SEPARATOR.'baptwo-backup-*.zip');
    expect($archives)->toHaveCount(1);

    $zip = new ZipArchive;
    expect($zip->open($archives[0]))->toBeTrue();
    expect($zip->locateName('database-dump.json'))->not->toBeFalse();
    $zip->close();

    File::cleanDirectory($backupDir);
});

test('command baptwo:backup terdaftar pada scheduler harian pukul 02:00 WITA', function () {
    Artisan::call('schedule:list');
    $output = Artisan::output();

    expect($output)->toContain('baptwo:backup');
});
