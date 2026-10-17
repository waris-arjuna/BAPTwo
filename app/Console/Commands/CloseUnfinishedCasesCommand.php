<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CloseUnfinishedCasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perkara:close-unfinished
                            {--year= : Tahun penerbitan SPDP atau pembuatan perkara yang ditutup (default: tahun sebelumnya)}
                            {--dry-run : Menjalankan simulasi pencarian berkas tanpa mengubah database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menutup berkas perkara yang belum selesai dari tahun sebelumnya menjadi Tidak Selesai (Trigger 1 Januari 00:00 per PRD F-23)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $currentYear = Carbon::now()->year;
        $targetYear = $this->option('year') ? (int) $this->option('year') : ($currentYear - 1);
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('Menjalankan penutupan otomatis berkas perkara belum selesai untuk tahun: '.$targetYear);
        if ($isDryRun) {
            $this->warn('Mode simulasi (--dry-run) aktif. Tidak ada perubahan yang disimpan ke database.');
        }

        // Ambil seluruh BerkasPerkara yang belum mencapai status Selesai atau Tidak Selesai
        // dan berasal dari tahun target atau tahun-tahun sebelumnya (PRD F-23 & Batas Tahun)
        $unclosedBerkas = BerkasPerkara::query()
            ->with(['perkara', 'tersangka'])
            ->whereNull('status_formal')
            ->where(function ($query) use ($targetYear) {
                $query->where(function ($q) use ($targetYear) {
                    $q->whereNotNull('tanggal_spdp')
                        ->whereYear('tanggal_spdp', '<=', $targetYear);
                })->orWhere(function ($q) use ($targetYear) {
                    $q->whereNull('tanggal_spdp')
                        ->whereYear('created_at', '<=', $targetYear);
                });
            })
            ->get();

        $totalFound = $unclosedBerkas->count();

        if ($totalFound === 0) {
            $this->info('Tidak ditemukan berkas perkara aktif dari tahun '.$targetYear.' atau sebelumnya yang belum selesai.');

            return self::SUCCESS;
        }

        $this->info('Ditemukan '.$totalFound.' berkas perkara yang belum selesai dari tahun '.$targetYear.' atau sebelumnya.');

        $processedCount = 0;

        foreach ($unclosedBerkas as $berkas) {
            $nomorBerkas = $berkas->nomor_berkas ?: 'ID #'.$berkas->id;
            $namaTersangka = $berkas->tersangka?->nama ?: 'Tanpa Nama';
            $tahunSpdp = $berkas->tanggal_spdp ? $berkas->tanggal_spdp->year : $berkas->created_at?->year;

            $this->line('- Memproses berkas: ['.$nomorBerkas.'] Tersangka: '.$namaTersangka.' (Tahun: '.$tahunSpdp.')');

            if (! $isDryRun) {
                // Update status formal menjadi tidak_selesai
                $berkas->update([
                    'status_formal' => 'tidak_selesai',
                ]);

                // Catat audit log dengan user_id = null untuk aksi sistem scheduler
                AuditLog::create([
                    'user_id' => null,
                    'perkara_id' => $berkas->perkara_id,
                    'berkas_perkara_id' => $berkas->id,
                    'aksi' => 'sistem_tidak_selesai',
                    'tabel' => 'berkas_perkara',
                    'record_id' => $berkas->id,
                    'data_lama' => [
                        'status_formal' => null,
                    ],
                    'data_baru' => [
                        'status_formal' => 'tidak_selesai',
                        'alasan' => 'Penetapan otomatis Tidak Selesai oleh sistem karena batas tahun berakhir per 1 Januari '.($targetYear + 1).'.',
                    ],
                    'ip_address' => '127.0.0.1',
                    'created_at' => Carbon::now(),
                ]);
            }

            $processedCount++;
        }

        $statusMsg = $isDryRun ? 'Disimulasikan' : 'Berhasil ditutup';
        $this->info('Selesai. '.$processedCount.' berkas perkara '.$statusMsg.' menjadi \'Tidak Selesai\'.');

        return self::SUCCESS;
    }
}
