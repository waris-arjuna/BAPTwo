<?php

namespace App\Console\Commands;

use App\Services\DashboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class BackupSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baptwo:backup
                            {--keep-days=30 : Jumlah hari penyimpanan arsip backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat arsip backup terpadu database dan dokumen PDF privat lintas sistem operasi dengan retensi otomatis.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! class_exists(ZipArchive::class)) {
            $this->error('Ekstensi PHP zip (ZipArchive) belum aktif.');

            return self::FAILURE;
        }

        $now = Carbon::now(DashboardService::ZONA_WAKTU);
        $keepDays = max(1, (int) $this->option('keep-days'));
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $archiveName = 'baptwo-backup-'.$now->format('Y-m-d-His').'.zip';
        $archivePath = $backupDir.DIRECTORY_SEPARATOR.$archiveName;

        $zip = new ZipArchive;
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Gagal membuat file arsip backup di: {$archivePath}");

            return self::FAILURE;
        }

        try {
            $dumpPayload = $this->buildDatabaseDump($now);
            $zip->addFromString(
                'database-dump.json',
                json_encode($dumpPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
            );

            $privateDisk = Storage::disk('local');
            $files = $privateDisk->allFiles();
            foreach ($files as $relativePath) {
                $content = $privateDisk->get($relativePath);
                if ($content !== null) {
                    $zip->addFromString('private/'.ltrim($relativePath, '/'), $content);
                }
            }
        } catch (Throwable $e) {
            $zip->close();
            File::delete($archivePath);
            $this->error('Proses backup gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $zip->close();

        $deletedCount = $this->pruneExpiredBackups($backupDir, $now->copy()->subDays($keepDays)->timestamp);

        $this->info("Backup berhasil dibuat: {$archiveName} (dokumen privat: ".count($files).", backup kadaluarsa dihapus: {$deletedCount}).");

        return self::SUCCESS;
    }

    /**
     * Ekspor seluruh tabel database ke struktur array siap-JSON.
     *
     * @return array<string, mixed>
     */
    private function buildDatabaseDump(Carbon $timestamp): array
    {
        $connection = DB::getDefaultConnection();
        $tablesData = [];

        foreach (Schema::getTables() as $tableInfo) {
            $tableName = is_array($tableInfo) ? ($tableInfo['name'] ?? '') : (string) $tableInfo;
            if ($tableName === '' || str_starts_with($tableName, 'sqlite_')) {
                continue;
            }

            $rows = [];
            foreach (DB::table($tableName)->cursor() as $row) {
                $rows[] = (array) $row;
            }
            $tablesData[$tableName] = $rows;
        }

        return [
            'generated_at' => $timestamp->toIso8601String(),
            'connection' => $connection,
            'tables' => $tablesData,
        ];
    }

    /**
     * Hapus arsip backup berpola baptwo-backup-*.zip yang melewati batas retensi.
     */
    private function pruneExpiredBackups(string $backupDir, int $cutoffTimestamp): int
    {
        $deleted = 0;
        $archives = glob($backupDir.DIRECTORY_SEPARATOR.'baptwo-backup-*.zip') ?: [];

        foreach ($archives as $filePath) {
            $mtime = @filemtime($filePath);
            if ($mtime !== false && $mtime < $cutoffTimestamp && File::delete($filePath)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
