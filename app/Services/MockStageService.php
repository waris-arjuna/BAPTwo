<?php

namespace App\Services;

use Illuminate\Http\Request;

class MockStageService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLaporanKejadian(mixed $search = '', ?string $status = '', ?string $tanggal = ''): array
    {
        [$search, $status, $tanggal] = $this->resolveParams($search, $status, $tanggal);

        $items = [
            [
                'id' => 1,
                'nomor' => 'LK/001/IX/2026',
                'tanggal' => '02-09-2026',
                'dokumen' => 'laporan-001.pdf',
                'status' => 'Belum Dilanjutkan',
            ],
            [
                'id' => 2,
                'nomor' => 'LK/002/IX/2026',
                'tanggal' => '04-09-2026',
                'dokumen' => 'laporan-002.pdf',
                'status' => 'Sprindik Tersedia',
            ],
            [
                'id' => 3,
                'nomor' => 'LK/003/IX/2026',
                'tanggal' => '05-09-2026',
                'dokumen' => 'laporan-003.pdf',
                'status' => 'Perkara Terbentuk',
            ],
            [
                'id' => 4,
                'nomor' => 'LK/004/IX/2026',
                'tanggal' => '08-09-2026',
                'dokumen' => 'laporan-004.pdf',
                'status' => 'Belum Dilanjutkan',
            ],
            [
                'id' => 5,
                'nomor' => 'LK/005/IX/2026',
                'tanggal' => '10-09-2026',
                'dokumen' => 'laporan-005.pdf',
                'status' => 'Sprindik Tersedia',
            ],
        ];

        return $this->filterList($items, $search, $status, $tanggal, ['nomor', 'dokumen', 'status']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSprindik(mixed $search = '', ?string $status = '', ?string $tanggal = ''): array
    {
        [$search, $status, $tanggal] = $this->resolveParams($search, $status, $tanggal);

        $items = [
            [
                'id' => 1,
                'nomor' => 'SP/001/IX/2026',
                'tanggal' => '03-09-2026',
                'laporan' => 'LK/001/IX/2026',
                'dokumen' => 'sprindik-001.pdf',
                'status' => 'Belum Menjadi Perkara',
            ],
            [
                'id' => 2,
                'nomor' => 'SP/002/IX/2026',
                'tanggal' => '05-09-2026',
                'laporan' => 'LK/002/IX/2026',
                'dokumen' => 'sprindik-002.pdf',
                'status' => 'Perkara Terbentuk',
            ],
            [
                'id' => 3,
                'nomor' => 'SP/003/IX/2026',
                'tanggal' => '08-09-2026',
                'laporan' => 'LK/004/IX/2026',
                'dokumen' => 'sprindik-003.pdf',
                'status' => 'Belum Menjadi Perkara',
            ],
        ];

        return $this->filterList($items, $search, $status, $tanggal, ['nomor', 'laporan', 'dokumen', 'status']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSpdp(mixed $search = '', ?string $status = '', ?string $tanggal = ''): array
    {
        [$search, $status, $tanggal] = $this->resolveParams($search, $status, $tanggal);

        $items = [
            [
                'id' => 1,
                'nomor' => 'SPDP/001/IX/2026',
                'tanggal' => '04-09-2026',
                'sprindik' => 'SP/001/IX/2026',
                'dokumen' => 'spdp-001.pdf',
                'status' => 'Tahap 1 Belum Dimulai',
            ],
            [
                'id' => 2,
                'nomor' => 'SPDP/002/IX/2026',
                'tanggal' => '06-09-2026',
                'sprindik' => 'SP/002/IX/2026',
                'dokumen' => 'spdp-002.pdf',
                'status' => 'Tahap 1 Berjalan',
            ],
            [
                'id' => 3,
                'nomor' => 'SPDP/003/IX/2026',
                'tanggal' => '09-09-2026',
                'sprindik' => 'SP/003/IX/2026',
                'dokumen' => 'spdp-003.pdf',
                'status' => 'P-21',
            ],
        ];

        return $this->filterList($items, $search, $status, $tanggal, ['nomor', 'sprindik', 'dokumen', 'status']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTahap1(mixed $search = '', ?string $status = '', ?string $tanggal = ''): array
    {
        [$search, $status, $tanggal] = $this->resolveParams($search, $status, $tanggal);

        $items = [
            [
                'id' => 1,
                'nomor' => 'TH1/001/IX/2026',
                'tanggal' => '18-09-2026',
                'spdp' => 'SPDP/001/IX/2026',
                'dokumen' => 'bap-001.pdf',
                'p16' => 'p16-001.pdf',
                'p17' => 'p17-001.pdf',
                'status' => 'Tahap 1 Berjalan',
            ],
            [
                'id' => 2,
                'nomor' => 'TH1/002/IX/2026',
                'tanggal' => '22-09-2026',
                'spdp' => 'SPDP/002/IX/2026',
                'dokumen' => 'bap-002.pdf',
                'p16' => 'p16-002.pdf',
                'p17' => null,
                'status' => 'Menunggu P-21',
            ],
            [
                'id' => 3,
                'nomor' => 'TH1/003/IX/2026',
                'tanggal' => '25-09-2026',
                'spdp' => 'SPDP/003/IX/2026',
                'dokumen' => 'bap-003.pdf',
                'p16' => null,
                'p17' => null,
                'status' => 'P-21',
            ],
        ];

        return $this->filterList($items, $search, $status, $tanggal, ['nomor', 'spdp', 'dokumen', 'status']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getP21(mixed $search = '', ?string $status = '', ?string $tanggal = ''): array
    {
        [$search, $status, $tanggal] = $this->resolveParams($search, $status, $tanggal);

        $items = [
            [
                'id' => 1,
                'nomor' => 'P-21/001/IX/2026',
                'tanggal' => '20-09-2026',
                'perkara' => 'PRK/001/2026',
                'tersangka' => 'Nama Tersangka 1',
                'penyidik' => 'Nama Penyidik 1',
                'dokumen' => 'p21-001.pdf',
                'status' => 'P-21 / P-21a Tersedia',
            ],
            [
                'id' => 2,
                'nomor' => 'P-21a/002/IX/2026',
                'tanggal' => '25-09-2026',
                'perkara' => 'PRK/002/2026',
                'tersangka' => 'Nama Tersangka 2',
                'penyidik' => 'Nama Penyidik 2',
                'dokumen' => 'p21-002.pdf',
                'status' => 'P-21 / P-21a Tersedia',
            ],
        ];

        return $this->filterList($items, $search, $status, $tanggal, ['nomor', 'perkara', 'tersangka', 'penyidik', 'status']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProgressPerkara(mixed $search = '', ?string $status = '', ?string $tanggal = ''): array
    {
        [$search, $status, $tanggal] = $this->resolveParams($search, $status, $tanggal);

        $items = [
            [
                'nomor' => 'BPOM.BJB-001/IV/2026/PPNS',
                'tersangka' => 'Andi Saputra',
                'pasal' => 'Pasal 196 UU 36/2009',
                'tahap' => 'TAHAP I',
                'progress' => 55,
                'status' => 'Dalam Proses',
                'spdp' => '15-09-2026',
                'tahap1' => '22-09-2026',
                'p21' => null,
                'tahap2' => null,
                'estimasi' => '15-10-2026',
            ],
            [
                'nomor' => 'BPOM.BJB-002/IV/2026/PPNS',
                'tersangka' => 'Budi Hartono',
                'pasal' => 'Pasal 197 UU 36/2009',
                'tahap' => 'P-21',
                'progress' => 85,
                'status' => 'Dalam Proses',
                'spdp' => '20-09-2026',
                'tahap1' => '27-09-2026',
                'p21' => '02-10-2026',
                'tahap2' => null,
                'estimasi' => '20-10-2026',
            ],
        ];

        $search = trim($search);
        $status = trim($status);
        $tanggal = trim($tanggal);
        $tanggalFormatted = $tanggal !== '' ? date('d-m-Y', strtotime($tanggal)) : '';

        return array_values(array_filter($items, function (array $item) use ($search, $status, $tanggalFormatted): bool {
            if ($search !== '' && ! str_contains(strtolower($item['nomor'].' '.$item['tersangka'].' '.$item['pasal'].' '.$item['status'].' '.$item['tahap']), strtolower($search))) {
                return false;
            }
            if ($status !== '' && $item['status'] !== $status) {
                return false;
            }
            if ($tanggalFormatted !== '' && ! in_array($tanggalFormatted, [$item['spdp'], $item['tahap1'], $item['p21'], $item['tahap2'], $item['estimasi']], true)) {
                return false;
            }

            return true;
        }));
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolveParams(mixed $search, ?string $status, ?string $tanggal): array
    {
        if ($search instanceof Request) {
            return [
                (string) $search->input('search', ''),
                (string) $search->input('status', ''),
                (string) $search->input('tanggal_dari', $search->input('tanggal', '')),
            ];
        }

        return [
            (string) ($search ?? ''),
            (string) ($status ?? ''),
            (string) ($tanggal ?? ''),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, string>  $searchFields
     * @return array<int, array<string, mixed>>
     */
    private function filterList(array $items, string $search, string $status, string $tanggal, array $searchFields): array
    {
        $search = trim($search);
        $status = trim($status);
        $tanggal = trim($tanggal);
        $tanggalFormatted = $tanggal !== '' ? date('d-m-Y', strtotime($tanggal)) : '';

        return array_values(array_filter($items, function (array $item) use ($search, $status, $tanggalFormatted, $searchFields): bool {
            if ($search !== '') {
                $searchHaystack = implode(' ', array_map(fn ($f) => (string) ($item[$f] ?? ''), $searchFields));
                if (! str_contains(strtolower($searchHaystack), strtolower($search))) {
                    return false;
                }
            }
            if ($status !== '' && ($item['status'] ?? '') !== $status) {
                return false;
            }
            if ($tanggalFormatted !== '' && ($item['tanggal'] ?? '') !== $tanggalFormatted) {
                return false;
            }

            return true;
        }));
    }
}
