<?php

namespace App\Helpers;

class MenuHelper
{
    /**
     * Menu utama aplikasi.
     */
    public static function getMainNavItems(): array
    {
        return [
            [
                'icon' => 'dashboard',
                'name' => 'Dashboard',
                'path' => '/',
            ],
            [
                'icon' => 'all-cases',
                'name' => 'Seluruh Perkara',
                'path' => '/seluruh-perkara',
            ],
            [
                'icon' => 'search-cases',
                'name' => 'Pencarian Perkara',
                'path' => '/pencarian-perkara',
            ],
            [
                'icon' => 'report',
                'name' => 'Laporan Kejadian',
                'path' => '/laporan-kejadian',
            ],
        ];
    }

    /**
     * Menu yang berkaitan dengan perkara.
     */
    public static function getPerkaraItems(): array
    {
        $dataPerkaraSubItems = [
            [
                'name' => 'Daftar Perkara',
                'path' => '/data-perkara',
            ],
        ];

        // Poin 20: Halaman Progres Perkara hanya tampil untuk Pemimpin dan Umum. Untuk Penyidik disembunyikan.
        $user = auth()->user();
        if ($user && ! $user->isPenyidik()) {
            $dataPerkaraSubItems[] = [
                'name' => 'Progress Perkara',
                'path' => '/progress-perkara',
            ];
        }

        $dataPerkaraSubItems[] = [
            'name' => 'Potensi Terlimpah',
            'path' => '/potensi-terlimpah',
        ];

        return [
            [
                'icon' => 'sprindik',
                'name' => 'Sprindik',
                'path' => '/sprindik',
            ],
            [
                'icon' => 'data-perkara',
                'name' => 'Data Perkara',
                'subItems' => $dataPerkaraSubItems,
            ],
            [
                'icon' => 'spdp',
                'name' => 'SPDP',
                'path' => '/spdp',
            ],
            [
                'icon' => 'stage-1',
                'name' => 'Tahap 1',
                'path' => '/tahap-1',
            ],
            [
                'icon' => 'p21',
                'name' => 'P21',
                'path' => '/p21',
            ],
            [
                'icon' => 'stage-2',
                'name' => 'Tahap 2',
                'path' => '/tahap-2',
            ],
            [
                'icon' => 'court',
                'name' => 'Putusan Perkara',
                'path' => '/putusan-perkara',
            ],
        ];
    }

    /**
     * Menu sistem dan administrasi.
     */
    public static function getSystemItems(): array
    {
        $user = auth()->user();
        $items = [];

        // Poin 7:
        // Role Pemimpin & Umum: Master Data dihapus total (menu disembunyikan)
        // Role Penyidik: Master Data hanya Pasal dan Instansi Hukum
        // Role Admin: Mengelola seluruh Master Data
        if ($user && ($user->isAdmin() || $user->isPenyidik())) {
            $masterSubItems = [];

            if ($user->isAdmin()) {
                $masterSubItems[] = [
                    'name' => 'Jabatan',
                    'path' => '/master-data/jabatan',
                ];
                $masterSubItems[] = [
                    'name' => 'Bidang',
                    'path' => '/master-data/bidang',
                ];
                $masterSubItems[] = [
                    'name' => 'Pangkat & Golongan',
                    'path' => '/master-data/panggol',
                ];
            }

            $masterSubItems[] = [
                'name' => 'Pasal / UU',
                'path' => '/master-data/pasal',
            ];
            $masterSubItems[] = [
                'name' => 'Instansi Hukum',
                'path' => '/master-data/instansi-hukum',
            ];

            if ($user->isAdmin()) {
                $masterSubItems[] = [
                    'name' => 'Jaksa Penuntut',
                    'path' => '/master-data/jaksa-penuntut',
                ];
            }

            $items[] = [
                'icon' => 'master-data',
                'name' => 'Master Data',
                'subItems' => $masterSubItems,
            ];
        }

        if (auth()->user()?->isAdmin()) {
            $items[] = [
                'icon' => 'settings',
                'name' => 'Pengaturan',
                'path' => '/pengaturan',
            ];
        }

        $items[] = [
            'icon' => 'trash',
            'name' => 'Sampah',
            'path' => '/sampah',
        ];

        return $items;
    }

    /**
     * Menyatukan semua menu menjadi beberapa grup.
     */
    public static function getMenuGroups(): array
    {
        return [
            [
                'title' => 'Menu Utama',
                'items' => self::getMainNavItems(),
            ],
            [
                'title' => 'Perkara',
                'items' => self::getPerkaraItems(),
            ],
            [
                'title' => 'Sistem',
                'items' => self::getSystemItems(),
            ],
        ];
    }

    /**
     * Mengecek apakah path sedang aktif.
     */
    public static function isActive(string $path): bool
    {
        $path = trim($path, '/');

        // Dashboard/root
        if ($path === '') {
            return request()->path() === '/';
        }

        return request()->is($path);
    }

    /**
     * Mengembalikan Flaticon UIcons HTML berdasarkan nama icon (ref: D-010).
     */
    public static function getIconSvg(string $iconName): string
    {
        $icons = [
            'dashboard' => '<i class="fi fi-rr-apps text-lg leading-none" aria-hidden="true"></i>',
            'all-cases' => '<i class="fi fi-rr-layers text-lg leading-none" aria-hidden="true"></i>',
            'search-cases' => '<i class="fi fi-rr-search text-lg leading-none" aria-hidden="true"></i>',
            'report' => '<i class="fi fi-rr-document-signed text-lg leading-none" aria-hidden="true"></i>',
            'data-perkara' => '<i class="fi fi-rr-folders text-lg leading-none" aria-hidden="true"></i>',
            'sprindik' => '<i class="fi fi-rr-file-edit text-lg leading-none" aria-hidden="true"></i>',
            'progress' => '<i class="fi fi-rr-chart-histogram text-lg leading-none" aria-hidden="true"></i>',
            'spdp' => '<i class="fi fi-rr-paper-plane text-lg leading-none" aria-hidden="true"></i>',
            'stage-1' => '<i class="fi fi-rr-folder-open text-lg leading-none" aria-hidden="true"></i>',
            'p21' => '<i class="fi fi-rr-file text-lg leading-none" aria-hidden="true"></i>',
            'stage-2' => '<i class="fi fi-rr-briefcase text-lg leading-none" aria-hidden="true"></i>',
            'court' => '<i class="fi fi-rr-gavel text-lg leading-none" aria-hidden="true"></i>',
            'master-data' => '<i class="fi fi-rr-database text-lg leading-none" aria-hidden="true"></i>',
            'settings' => '<i class="fi fi-rr-settings text-lg leading-none" aria-hidden="true"></i>',
            'trash' => '<i class="fi fi-rr-trash text-lg leading-none" aria-hidden="true"></i>',
        ];

        return $icons[$iconName] ?? '<i class="fi fi-rr-circle text-lg leading-none" aria-hidden="true"></i>';
    }
}
