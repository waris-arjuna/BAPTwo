<?php

namespace App\Console\Commands;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncSlaDeadlinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perkara:sync-sla
                            {--dry-run : Simulasi tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi dan perbaikan nilai sla_deadline pada seluruh berkas perkara aktif berdasarkan kaidah 1 bulan kalender tanpa overflow dan batas tahun mutlak.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('Memulai sinkronisasi tenggat waktu (SLA)...');
        if ($isDryRun) {
            $this->warn('Mode simulasi (--dry-run) aktif. Tidak ada perubahan yang disimpan ke database. [DRY-RUN]');
        }

        $berkases = BerkasPerkara::query()
            ->with(['tahapan' => fn ($q) => $q->where('status', 'final')])
            ->get();

        $totalChecked = 0;
        $totalUpdated = 0;

        foreach ($berkases as $berkas) {
            $totalChecked++;

            // Cari basis tanggal tahap aktif (P-21, Tahap 1, atau SPDP)
            $stage = $berkas->tahapan
                ->where('status', 'final')
                ->whereIn('jenis_tahap', [JenisTahap::P21, JenisTahap::TAHAP_1, JenisTahap::SPDP, JenisTahap::P21->value, JenisTahap::TAHAP_1->value, JenisTahap::SPDP->value])
                ->sortByDesc('tanggal_surat')
                ->first();

            $basisDate = $stage?->tanggal_surat ?: $berkas->tanggal_spdp;

            if (! $basisDate) {
                continue;
            }

            $tgl = Carbon::parse($basisDate);
            $sla1Bulan = $tgl->copy()->addMonthNoOverflow();
            $akhirTahun = $tgl->copy()->endOfYear();
            $targetDeadline = $sla1Bulan->gt($akhirTahun) ? $akhirTahun->toDateString() : $sla1Bulan->toDateString();

            $currentDeadline = $berkas->sla_deadline?->toDateString();

            if ($currentDeadline !== $targetDeadline) {
                $totalUpdated++;
                $this->line(sprintf(
                    'Berkas #%d (%s): %s -> %s',
                    $berkas->id,
                    $berkas->nomor_berkas ?: 'Tanpa Nomor',
                    $currentDeadline ?: 'NULL',
                    $targetDeadline
                ));

                if (! $isDryRun) {
                    $berkas->update(['sla_deadline' => $targetDeadline]);
                }
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Sinkronisasi Tenggang Waktu (SLA) Selesai. Total diperiksa: %d, Total disesuaikan: %d.%s',
            $totalChecked,
            $totalUpdated,
            $isDryRun ? ' (Simulasi [DRY-RUN])' : ''
        ));

        return Command::SUCCESS;
    }
}
