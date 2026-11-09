<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportWilayahCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wilayah:import {--source= : Direktori sumber file mentah} {--target= : Direktori target hasil partisi} {--force : Timpa file hasil partisi yang sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dan partisi dataset wilayah Indonesia dari resources/data/wilayah/raw menjadi file JSON lokal per kabupaten';

    public function handle(): int
    {
        $rawDir = $this->option('source') ? (string) $this->option('source') : resource_path('data/wilayah/raw');
        $targetDir = $this->option('target') ? (string) $this->option('target') : resource_path('data/wilayah');
        $districtsDir = $targetDir.'/districts';
        $villagesDir = $targetDir.'/villages';

        if (! File::exists($rawDir)) {
            $this->error("Direktori sumber mentah [{$rawDir}] tidak ditemukan.");

            return self::FAILURE;
        }

        $files = [
            'provinces' => $rawDir.'/provinces.json',
            'regencies' => $rawDir.'/regencies.json',
            'districts' => $rawDir.'/districts.json',
            'villages' => $rawDir.'/villages.json',
        ];

        foreach ($files as $name => $path) {
            if (! File::exists($path)) {
                $this->error("File mentah [{$name}.json] tidak ditemukan di {$rawDir}.");

                return self::FAILURE;
            }
        }

        File::ensureDirectoryExists($districtsDir);
        File::ensureDirectoryExists($villagesDir);

        $this->info('Memulai pemrosesan dataset wilayah...');

        // 1. Proses Provinsi
        $this->line('1/4 Memproses Provinsi...');
        $provincesRaw = json_decode((string) File::get($files['provinces']), true) ?? [];
        $provinces = [];
        foreach ($provincesRaw as $item) {
            $code = $this->cleanCode($item['code'] ?? $item['kode'] ?? $item['id'] ?? '');
            $name = trim((string) ($item['name'] ?? $item['nama'] ?? ''));
            if ($code !== '' && $name !== '') {
                $provinces[] = ['code' => $code, 'name' => $name];
            }
        }
        usort($provinces, fn ($a, $b) => strcmp($a['code'], $b['code']));
        File::put($targetDir.'/provinces.json', json_encode($provinces, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('  ✓ '.count($provinces).' provinsi tersimpan di provinces.json');

        // 2. Proses Kabupaten/Kota
        $this->line('2/4 Memproses Kabupaten/Kota...');
        $regenciesRaw = json_decode((string) File::get($files['regencies']), true) ?? [];
        $regencies = [];
        foreach ($regenciesRaw as $item) {
            $code = $this->cleanCode($item['code'] ?? $item['kode'] ?? $item['id'] ?? '');
            $provinceCode = $this->cleanCode($item['province_code'] ?? $item['provinsi_kode'] ?? $item['province_id'] ?? substr($code, 0, 2));
            $name = trim((string) ($item['name'] ?? $item['nama'] ?? ''));
            if ($code !== '' && $name !== '') {
                $regencies[] = [
                    'code' => $code,
                    'province_code' => $provinceCode,
                    'name' => $name,
                ];
            }
        }
        usort($regencies, fn ($a, $b) => strcmp($a['code'], $b['code']));
        File::put($targetDir.'/regencies.json', json_encode($regencies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('  ✓ '.count($regencies).' kabupaten/kota tersimpan di regencies.json');

        // 3. Proses Kecamatan (Dipartisi per Kabupaten)
        $this->line('3/4 Memproses Kecamatan...');
        $districtsRaw = json_decode((string) File::get($files['districts']), true) ?? [];
        $districtsByRegency = [];
        $totalDistricts = 0;
        foreach ($districtsRaw as $item) {
            $code = $this->cleanCode($item['code'] ?? $item['kode'] ?? $item['id'] ?? '');
            $regencyCode = $this->cleanCode($item['regency_code'] ?? $item['kabupaten_code'] ?? $item['kota_code'] ?? $item['regency_id'] ?? substr($code, 0, 4));
            $name = trim((string) ($item['name'] ?? $item['nama'] ?? ''));

            if ($code !== '' && $regencyCode !== '' && $name !== '') {
                $districtsByRegency[$regencyCode][] = [
                    'code' => $code,
                    'name' => $name,
                ];
                $totalDistricts++;
            }
        }

        foreach ($districtsByRegency as $regencyCode => $districtList) {
            usort($districtList, fn ($a, $b) => strcmp($a['code'], $b['code']));
            File::put($districtsDir."/{$regencyCode}.json", json_encode($districtList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $this->info("  ✓ {$totalDistricts} kecamatan terpartisi ke dalam ".count($districtsByRegency).' file kabupaten di districts/');

        // 4. Proses Desa/Kelurahan (Dipartisi per Kabupaten: district_code => [villages])
        $this->line('4/4 Memproses Desa/Kelurahan...');
        $villagesRaw = json_decode((string) File::get($files['villages']), true) ?? [];
        $villagesByRegency = [];
        $totalVillages = 0;

        foreach ($villagesRaw as $item) {
            $code = $this->cleanCode($item['code'] ?? $item['kode'] ?? $item['id'] ?? '');
            $districtCode = $this->cleanCode($item['district_code'] ?? $item['kecamatan_code'] ?? $item['district_id'] ?? substr($code, 0, 6));
            $regencyCode = substr($districtCode, 0, 4);
            $name = trim((string) ($item['name'] ?? $item['nama'] ?? ''));

            if ($code !== '' && $districtCode !== '' && $regencyCode !== '' && $name !== '') {
                $villagesByRegency[$regencyCode][$districtCode][] = [
                    'code' => $code,
                    'name' => $name,
                ];
                $totalVillages++;
            }
        }
        unset($villagesRaw); // Bebaskan memori

        foreach ($villagesByRegency as $regencyCode => $districtVillages) {
            foreach ($districtVillages as $distCode => &$vList) {
                usort($vList, fn ($a, $b) => strcmp($a['code'], $b['code']));
            }
            unset($vList);
            File::put($villagesDir."/{$regencyCode}.json", json_encode($districtVillages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $this->info("  ✓ {$totalVillages} desa/kelurahan terpartisi ke dalam ".count($villagesByRegency).' file kabupaten di villages/');

        $this->newLine();
        $this->info('Import dan partisi wilayah berhasil!');

        return self::SUCCESS;
    }

    private function cleanCode(mixed $code): string
    {
        $code = (string) $code;

        return trim(str_replace(['.', ' '], '', $code));
    }
}
