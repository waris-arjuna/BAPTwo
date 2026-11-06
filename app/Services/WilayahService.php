<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class WilayahService
{
    private const CACHE_TTL_DAYS = 30;

    /**
     * @return list<array{code: string, name: string}>
     */
    public function getProvinsi(): array
    {
        return Cache::remember('wilayah.provinsi', now()->addDays(self::CACHE_TTL_DAYS), function (): array {
            $path = resource_path('data/wilayah/provinces.json');
            if (File::exists($path)) {
                $decoded = File::json($path);
                if (is_array($decoded) && $decoded !== []) {
                    return array_values(array_map(fn (array $item): array => [
                        'code' => (string) ($item['code'] ?? ''),
                        'name' => (string) ($item['name'] ?? ''),
                    ], $decoded));
                }
            }

            return $this->fallbackProvinsi();
        });
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public function getKabupaten(string $provinsiKode): array
    {
        $provinsiKode = trim(str_replace(['.', ' '], '', $provinsiKode));

        return Cache::remember("wilayah.kabupaten.{$provinsiKode}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($provinsiKode): array {
            $path = resource_path('data/wilayah/regencies.json');
            if (File::exists($path)) {
                $decoded = File::json($path);
                if (is_array($decoded)) {
                    $filtered = array_values(array_filter(
                        $decoded,
                        fn (array $item): bool => (string) ($item['province_code'] ?? '') === $provinsiKode
                    ));

                    if ($filtered !== []) {
                        return array_values(array_map(fn (array $item): array => [
                            'code' => (string) ($item['code'] ?? ''),
                            'name' => (string) ($item['name'] ?? ''),
                        ], $filtered));
                    }
                }
            }

            return $this->fallbackKabupaten($provinsiKode);
        });
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public function getKecamatan(string $kabupatenKode): array
    {
        $kabupatenKode = trim(str_replace(['.', ' '], '', $kabupatenKode));

        return Cache::remember("wilayah.kecamatan.{$kabupatenKode}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($kabupatenKode): array {
            $partitionPath = resource_path("data/wilayah/districts/{$kabupatenKode}.json");
            if (File::exists($partitionPath)) {
                $decoded = File::json($partitionPath);
                if (is_array($decoded) && $decoded !== []) {
                    return array_values(array_map(fn (array $item): array => [
                        'code' => (string) ($item['code'] ?? ''),
                        'name' => (string) ($item['name'] ?? ''),
                    ], $decoded));
                }
            }

            return $this->fallbackKecamatan($kabupatenKode);
        });
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public function getDesa(string $kecamatanKode): array
    {
        $kecamatanKode = trim(str_replace(['.', ' '], '', $kecamatanKode));
        $kabupatenKode = substr($kecamatanKode, 0, 4);

        return Cache::remember("wilayah.desa.{$kecamatanKode}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($kabupatenKode, $kecamatanKode): array {
            $partitionPath = resource_path("data/wilayah/villages/{$kabupatenKode}.json");
            if (File::exists($partitionPath)) {
                $decoded = File::json($partitionPath);
                if (is_array($decoded) && isset($decoded[$kecamatanKode]) && is_array($decoded[$kecamatanKode]) && $decoded[$kecamatanKode] !== []) {
                    return array_values(array_map(fn (array $item): array => [
                        'code' => (string) ($item['code'] ?? ''),
                        'name' => (string) ($item['name'] ?? ''),
                    ], $decoded[$kecamatanKode]));
                }
            }

            return $this->fallbackDesa($kecamatanKode);
        });
    }

    public function clearCache(): void
    {
        Cache::forget('wilayah.provinsi');
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function fallbackProvinsi(): array
    {
        return [
            ['code' => '63', 'name' => 'Kalimantan Selatan'],
            ['code' => '62', 'name' => 'Kalimantan Tengah'],
            ['code' => '64', 'name' => 'Kalimantan Timur'],
            ['code' => '61', 'name' => 'Kalimantan Barat'],
            ['code' => '65', 'name' => 'Kalimantan Utara'],
            ['code' => '31', 'name' => 'DKI Jakarta'],
        ];
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function fallbackKabupaten(string $provinsiKode): array
    {
        if ($provinsiKode === '63') {
            return [
                ['code' => '6371', 'name' => 'Kota Banjarmasin'],
                ['code' => '6372', 'name' => 'Kota Banjarbaru'],
                ['code' => '6303', 'name' => 'Kabupaten Banjar'],
                ['code' => '6304', 'name' => 'Kabupaten Barito Kuala'],
                ['code' => '6301', 'name' => 'Kabupaten Tanah Laut'],
                ['code' => '6302', 'name' => 'Kabupaten Kotabaru'],
                ['code' => '6310', 'name' => 'Kabupaten Tanah Bumbu'],
            ];
        }

        return [
            ['code' => $provinsiKode.'01', 'name' => 'Kabupaten/Kota Utama'],
        ];
    }

    /**
     * @return array{districts: array<string, list<array{code: string, name: string}>>, villages: array<string, list<array{code: string, name: string}>>}
     */
    private function getKalselData(): array
    {
        $path = resource_path('data/wilayah/districts_kalsel.json');
        if (File::exists($path)) {
            $data = File::json($path);
            if (is_array($data)) {
                return $data;
            }
        }

        return ['districts' => [], 'villages' => []];
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function fallbackKecamatan(string $kabupatenKode): array
    {
        $kalselData = $this->getKalselData();
        if (isset($kalselData['districts'][$kabupatenKode])) {
            return $kalselData['districts'][$kabupatenKode];
        }

        return [
            ['code' => $kabupatenKode.'01', 'name' => 'Kecamatan Utama'],
        ];
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function fallbackDesa(string $kecamatanKode): array
    {
        $kalselData = $this->getKalselData();
        if (isset($kalselData['villages'][$kecamatanKode])) {
            return $kalselData['villages'][$kecamatanKode];
        }

        return [
            ['code' => $kecamatanKode.'1001', 'name' => 'Kelurahan/Desa Utama'],
        ];
    }
}
