<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class RestoreSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baptwo:restore
                            {archive? : Nama file atau path arsip ZIP backup (default: backup terbaru)}
                            {--dry-run : Simulasi validasi isi arsip tanpa mengubah database atau file}
                            {--force : Lewati konfirmasi interaktif sebelum menimpa data}
                            {--skip-safety-backup : Lewati pembuatan safety backup otomatis sebelum restore}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memulihkan database dan dokumen PDF privat dari arsip ZIP hasil command baptwo:backup.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! class_exists(ZipArchive::class)) {
            $this->error('Ekstensi PHP zip (ZipArchive) belum aktif.');

            return self::FAILURE;
        }

        $archivePath = $this->resolveArchivePath($this->argument('archive'));
        if ($archivePath === null || ! File::isFile($archivePath)) {
            $this->error('File arsip backup tidak ditemukan.');

            return self::FAILURE;
        }

        $zip = new ZipArchive;
        if ($zip->open($archivePath) !== true) {
            $this->error("Gagal membuka arsip backup: {$archivePath}");

            return self::FAILURE;
        }

        try {
            $rawDump = $zip->getFromName('database-dump.json');
            if ($rawDump === false) {
                $this->error('Arsip backup tidak valid: database-dump.json tidak ditemukan.');

                return self::FAILURE;
            }

            $dump = json_decode($rawDump, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($dump) || ! is_array($dump['tables'] ?? null)) {
                $this->error('Format database-dump.json tidak valid.');

                return self::FAILURE;
            }

            $privateEntries = $this->collectPrivateEntries($zip);
        } catch (Throwable $e) {
            $this->error('Gagal membaca isi arsip backup: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            $zip->close();
        }

        $tables = $dump['tables'];
        $totalRows = array_sum(array_map(fn ($rows) => is_array($rows) ? count($rows) : 0, $tables));

        $this->info('Ringkasan arsip backup:');
        $this->line('- Arsip          : '.basename($archivePath));
        $this->line('- Dibuat pada    : '.($dump['generated_at'] ?? '-'));
        $this->line('- Jumlah tabel   : '.count($tables));
        $this->line('- Total baris    : '.$totalRows);
        $this->line('- Dokumen privat : '.count($privateEntries));

        if ((bool) $this->option('dry-run')) {
            $this->info('Validasi arsip selesai (--dry-run). Tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        if (! (bool) $this->option('force') && ! $this->confirm('Lanjutkan proses restore dan timpa seluruh data saat ini?', false)) {
            $this->warn('Proses restore dibatalkan.');

            return self::FAILURE;
        }

        if (! (bool) $this->option('skip-safety-backup')) {
            $this->info('Membuat safety backup sebelum restore...');
            if ($this->call('baptwo:backup') !== self::SUCCESS) {
                $this->error('Safety backup gagal dibuat. Proses restore dibatalkan demi keamanan data.');

                return self::FAILURE;
            }
        }

        try {
            $this->restoreDatabaseTables($tables);
            $this->restorePrivateFiles($archivePath, $privateEntries);
        } catch (Throwable $e) {
            $this->error('Proses restore gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Restore database dan dokumen privat selesai.');

        return self::SUCCESS;
    }

    /**
     * Tentukan path absolut arsip ZIP dari argumen atau backup terbaru.
     */
    private function resolveArchivePath(mixed $argument): ?string
    {
        if (is_string($argument) && trim($argument) !== '') {
            $candidate = trim($argument);
            if (File::isFile($candidate)) {
                return $candidate;
            }

            $inBackupDir = storage_path('app/backups'.DIRECTORY_SEPARATOR.$candidate);

            return File::isFile($inBackupDir) ? $inBackupDir : null;
        }

        $archives = glob(storage_path('app/backups').DIRECTORY_SEPARATOR.'baptwo-backup-*.zip') ?: [];
        if ($archives === []) {
            return null;
        }

        rsort($archives);

        return $archives[0];
    }

    /**
     * Kumpulkan daftar path file di bawah direktori private/ dalam arsip ZIP secara aman.
     *
     * @return list<array{entry: string, target: string}>
     */
    private function collectPrivateEntries(ZipArchive $zip): array
    {
        $entries = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = (string) $zip->getNameIndex($i);
            if (! str_starts_with($entryName, 'private/') || str_ends_with($entryName, '/')) {
                continue;
            }

            $relativeTarget = substr($entryName, strlen('private/'));
            if ($relativeTarget === '' || str_starts_with($relativeTarget, '/') || str_contains($relativeTarget, '..')) {
                continue;
            }

            $entries[] = [
                'entry' => $entryName,
                'target' => $relativeTarget,
            ];
        }

        return $entries;
    }

    /**
     * Pulihkan isi seluruh tabel database yang ada di skema target.
     *
     * @param  array<string, mixed>  $tables
     */
    private function restoreDatabaseTables(array $tables): void
    {
        Schema::withoutForeignKeyConstraints(function () use ($tables): void {
            DB::transaction(function () use ($tables): void {
                foreach ($tables as $tableName => $rows) {
                    if (! is_string($tableName) || ! is_array($rows) || ! Schema::hasTable($tableName)) {
                        continue;
                    }

                    DB::table($tableName)->delete();

                    if ($rows === []) {
                        continue;
                    }

                    foreach (array_chunk($rows, 20) as $chunk) {
                        DB::table($tableName)->insert($chunk);
                    }
                }
            });
        });
    }

    /**
     * Pulihkan file dokumen privat dari arsip ZIP ke disk local (storage/app/private).
     *
     * @param  list<array{entry: string, target: string}>  $privateEntries
     */
    private function restorePrivateFiles(string $archivePath, array $privateEntries): void
    {
        $zip = new ZipArchive;
        if ($zip->open($archivePath) !== true) {
            throw new \RuntimeException("Gagal membuka ulang arsip backup: {$archivePath}");
        }

        try {
            $privateDisk = Storage::disk('local');

            foreach ($privateDisk->allFiles() as $existingFile) {
                if (basename($existingFile) === '.gitignore') {
                    continue;
                }
                $privateDisk->delete($existingFile);
            }

            foreach ($privateEntries as $item) {
                $content = $zip->getFromName($item['entry']);
                if ($content !== false) {
                    $privateDisk->put($item['target'], $content);
                }
            }
        } finally {
            $zip->close();
        }
    }
}
