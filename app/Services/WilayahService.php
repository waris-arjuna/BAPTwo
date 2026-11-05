<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WilayahService
{
    private const BASE_URL = 'https://api.kodewilayah.web.id';

    private const CACHE_TTL_DAYS = 30;

    /**
     * @return list<array{code: string, name: string}>
     */
    public function getProvinsi(): array
    {
        return Cache::remember('wilayah.provinsi', now()->addDays(self::CACHE_TTL_DAYS), function (): array {
            $data = $this->fetchFromApi('/provinces');

            return $data !== [] ? $data : $this->fallbackProvinsi();
        });
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public function getKabupaten(string $provinsiKode): array
    {
        return Cache::remember("wilayah.kabupaten.{$provinsiKode}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($provinsiKode): array {
            $data = $this->fetchFromApi("/regencies/{$provinsiKode}");

            return $data !== [] ? $data : $this->fallbackKabupaten($provinsiKode);
        });
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public function getKecamatan(string $kabupatenKode): array
    {
        return Cache::remember("wilayah.kecamatan.{$kabupatenKode}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($kabupatenKode): array {
            $data = $this->fetchFromApi("/districts/{$kabupatenKode}");

            return $data !== [] ? $data : $this->fallbackKecamatan($kabupatenKode);
        });
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public function getDesa(string $kecamatanKode): array
    {
        return Cache::remember("wilayah.desa.{$kecamatanKode}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($kecamatanKode): array {
            $data = $this->fetchFromApi("/villages/{$kecamatanKode}");

            return $data !== [] ? $data : $this->fallbackDesa($kecamatanKode);
        });
    }

    public function clearCache(): void
    {
        Cache::forget('wilayah.provinsi');
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function fetchFromApi(string $endpoint): array
    {
        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->withoutVerifying()
                ->get(self::BASE_URL.$endpoint);

            if (! $response->successful()) {
                Log::warning('WilayahService API call unsuccessful', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                ]);

                return [];
            }

            $items = $response->json('data', []);
            if (! is_array($items)) {
                return [];
            }

            return array_values(array_map(fn (array $item): array => [
                'code' => (string) ($item['code'] ?? ''),
                'name' => (string) ($item['name'] ?? ''),
            ], $items));
        } catch (\Throwable $e) {
            Log::warning('WilayahService API connection error, falling back to local data', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function fallbackProvinsi(): array
    {
        $path = resource_path('data/wilayah/provinces.json');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $decoded = json_decode((string) $content, true);
            if (is_array($decoded) && $decoded !== []) {
                return array_values(array_map(fn (array $item): array => [
                    'code' => (string) ($item['code'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                ], $decoded));
            }
        }

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
        $path = resource_path('data/wilayah/regencies.json');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $decoded = json_decode((string) $content, true);
            if (is_array($decoded)) {
                $filtered = array_values(array_filter(
                    $decoded,
                    fn (array $item): bool => (string) ($item['province_code'] ?? '') === (string) $provinsiKode
                ));

                if ($filtered !== []) {
                    return array_values(array_map(fn (array $item): array => [
                        'code' => (string) ($item['code'] ?? ''),
                        'name' => (string) ($item['name'] ?? ''),
                    ], $filtered));
                }
            }
        }

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
