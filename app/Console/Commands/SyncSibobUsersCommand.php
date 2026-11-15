<?php

namespace App\Console\Commands;

use App\Services\SibobApiService;
use Illuminate\Console\Command;
use Throwable;

class SyncSibobUsersCommand extends Command
{
    protected $signature = 'sibob:sync-users';

    protected $description = 'Sinkronisasi daftar pegawai dari API SIBOB ke tabel user BAPTwo';

    public function handle(SibobApiService $sibobApiService): int
    {
        try {
            $result = $sibobApiService->syncAllUsers();

            $this->info(
                "Sinkronisasi SIBOB berhasil: {$result['created']} ditambahkan, {$result['updated']} diperbarui, {$result['deactivated']} dinonaktifkan (Total: {$result['total']} pegawai)."
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Sinkronisasi SIBOB gagal: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
