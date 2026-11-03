<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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
     * @return list<array{code: string, name: string}>
     */
    private function fallbackKecamatan(string $kabupatenKode): array
    {
        return match ($kabupatenKode) {
            '6371' => [
                ['code' => '637101', 'name' => 'Banjarmasin Selatan'],
                ['code' => '637102', 'name' => 'Banjarmasin Timur'],
                ['code' => '637103', 'name' => 'Banjarmasin Barat'],
                ['code' => '637104', 'name' => 'Banjarmasin Utara'],
                ['code' => '637105', 'name' => 'Banjarmasin Tengah'],
            ],
            '6372' => [
                ['code' => '637202', 'name' => 'Landasan Ulin'],
                ['code' => '637203', 'name' => 'Cempaka'],
                ['code' => '637204', 'name' => 'Banjarbaru Utara'],
                ['code' => '637205', 'name' => 'Banjarbaru Selatan'],
                ['code' => '637206', 'name' => 'Liang Anggang'],
            ],
            '6301' => [
                ['code' => '630103', 'name' => 'Pelaihari'],
                ['code' => '630101', 'name' => 'Takisung'],
                ['code' => '630104', 'name' => 'Bati-Bati'],
            ],
            '6303' => [
                ['code' => '630302', 'name' => 'Kertak Hanyar'],
                ['code' => '630305', 'name' => 'Martapura'],
                ['code' => '630301', 'name' => 'Aluh-Aluh'],
            ],
            '6304' => [
                ['code' => '630401', 'name' => 'Alalak'],
                ['code' => '630403', 'name' => 'Marabahan'],
            ],
            '6305' => [
                ['code' => '630504', 'name' => 'Tapin Utara'],
            ],
            '6306' => [
                ['code' => '630604', 'name' => 'Kandangan'],
            ],
            '6307' => [
                ['code' => '630706', 'name' => 'Barabai'],
            ],
            '6308' => [
                ['code' => '630804', 'name' => 'Amuntai Tengah'],
            ],
            '6309' => [
                ['code' => '630903', 'name' => 'Tanjung'],
                ['code' => '630904', 'name' => 'Murung Pudak'],
            ],
            '6310' => [
                ['code' => '631001', 'name' => 'Batulicin'],
            ],
            '6311' => [
                ['code' => '631102', 'name' => 'Paringin'],
            ],
            '6302' => [
                ['code' => '630209', 'name' => 'Pulau Laut Utara'],
            ],
            default => [
                ['code' => $kabupatenKode.'01', 'name' => 'Kecamatan Utama'],
            ],
        };
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function fallbackDesa(string $kecamatanKode): array
    {
        return match ($kecamatanKode) {
            '637104' => [
                ['code' => '6371041001', 'name' => 'Alalak Selatan'],
                ['code' => '6371041002', 'name' => 'Alalak Tengah'],
                ['code' => '6371041003', 'name' => 'Alalak Utara'],
                ['code' => '6371041004', 'name' => 'Antasan Kecil Timur'],
                ['code' => '6371041005', 'name' => 'Kuin Utara'],
                ['code' => '6371041006', 'name' => 'Pangeran'],
                ['code' => '6371041007', 'name' => 'Sungai Jingah'],
                ['code' => '6371041008', 'name' => 'Sungai Miai'],
                ['code' => '6371041009', 'name' => 'Surgi Mufti'],
            ],
            '637105' => [
                ['code' => '6371051001', 'name' => 'Kertak Baru Ilir'],
                ['code' => '6371051002', 'name' => 'Kertak Baru Ulu'],
                ['code' => '6371051003', 'name' => 'Pasar Lama'],
                ['code' => '6371051004', 'name' => 'Teluk Dalam'],
                ['code' => '6371051005', 'name' => 'Mawar'],
                ['code' => '6371051006', 'name' => 'Melayu'],
                ['code' => '6371051007', 'name' => 'Gadang'],
                ['code' => '6371051008', 'name' => 'Pekapuran Laut'],
                ['code' => '6371051009', 'name' => 'Seberang Mesjid'],
                ['code' => '6371051010', 'name' => 'Antasan Besar'],
                ['code' => '6371051011', 'name' => 'Kelayan Luar'],
            ],
            '637204' => [
                ['code' => '6372041001', 'name' => 'Komet'],
                ['code' => '6372041002', 'name' => 'Loktabat Utara'],
                ['code' => '6372041003', 'name' => 'Mentaos'],
                ['code' => '6372041004', 'name' => 'Sungai Ulin'],
            ],
            '637202' => [
                ['code' => '6372021001', 'name' => 'Landasan Ulin Barat'],
                ['code' => '6372021002', 'name' => 'Landasan Ulin Tengah'],
                ['code' => '6372021003', 'name' => 'Landasan Ulin Utara'],
                ['code' => '6372021004', 'name' => 'Guntung Payung'],
                ['code' => '6372021005', 'name' => 'Guntung Manggis'],
            ],
            '630305' => [
                ['code' => '6303051001', 'name' => 'Jawa'],
                ['code' => '6303051002', 'name' => 'Keraton'],
                ['code' => '6303051003', 'name' => 'Murung Kenanga'],
                ['code' => '6303051004', 'name' => 'Pasayangan'],
                ['code' => '6303051005', 'name' => 'Sekumpul'],
                ['code' => '6303051006', 'name' => 'Sungai Sipai'],
                ['code' => '6303051007', 'name' => 'Tanjung Rema'],
            ],
            '630302' => [
                ['code' => '6303022003', 'name' => 'Manarap Baru'],
                ['code' => '6303022001', 'name' => 'Kertak Hanyar I'],
            ],
            '630403' => [
                ['code' => '6304031001', 'name' => 'Marabahan Kota'],
            ],
            '630401' => [
                ['code' => '6304011002', 'name' => 'Handil Bakti'],
            ],
            '630103' => [
                ['code' => '6301031001', 'name' => 'Pelaihari'],
                ['code' => '6301031002', 'name' => 'Sarang Halang'],
                ['code' => '6301031003', 'name' => 'Karang Taruna'],
                ['code' => '6301031004', 'name' => 'Angsau'],
            ],
            '630504' => [
                ['code' => '6305041001', 'name' => 'Rantau Kiwa'],
            ],
            '630706' => [
                ['code' => '6307061002', 'name' => 'Barabai Barat'],
            ],
            '630804' => [
                ['code' => '6308041003', 'name' => 'Kebun Sari'],
            ],
            '630604' => [
                ['code' => '6306041001', 'name' => 'Kandangan Kota'],
            ],
            '630904' => [
                ['code' => '6309041002', 'name' => 'Belimbing'],
            ],
            '630903' => [
                ['code' => '6309031002', 'name' => 'Tanjung'],
            ],
            '631001' => [
                ['code' => '6310011001', 'name' => 'Batulicin'],
            ],
            '631102' => [
                ['code' => '6311021001', 'name' => 'Batu Piring'],
            ],
            '630209' => [
                ['code' => '6302091002', 'name' => 'Kotabaru Hilir'],
            ],
            default => [
                ['code' => $kecamatanKode.'1001', 'name' => 'Kelurahan/Desa Utama'],
            ],
        };
    }
}
