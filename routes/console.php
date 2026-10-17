<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('perkara:close-unfinished')
    ->yearlyOn(1, 1, '00:00')
    ->description('Otomatis menutup berkas perkara yang belum selesai dari tahun sebelumnya menjadi Tidak Selesai (Trigger 1 Januari 00:00 per PRD F-23).');
