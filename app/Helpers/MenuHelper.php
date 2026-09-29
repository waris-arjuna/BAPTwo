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
        ];
    }

    /**
     * Menu yang berkaitan dengan perkara.
     */
    public static function getPerkaraItems(): array
    {
        return [
            [
                'icon' => 'progress',
                'name' => 'Progress Perkara',
                'path' => '/progress-perkara',
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
                'name' => 'P-21',
                'path' => '/p-21',
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
        return [
            [
                'icon' => 'master-data',
                'name' => 'Master Data',
                'path' => '/master-data',
            ],
            [
                'icon' => 'settings',
                'name' => 'Pengaturan',
                'path' => '/pengaturan',
            ],
        ];
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
     * Mengembalikan SVG icon berdasarkan nama icon.
     */
    public static function getIconSvg(string $iconName): string
    {
        $icons = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */
            'dashboard' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M4.75 4.75C4.75 4.33579 5.08579 4 5.5 4H10C10.4142 4 10.75 4.33579 10.75 4.75V9.25C10.75 9.66421 10.4142 10 10 10H5.5C5.08579 10 4.75 9.66421 4.75 9.25V4.75ZM13.25 4.75C13.25 4.33579 13.5858 4 14 4H18.5C18.9142 4 19.25 4.33579 19.25 4.75V9.25C19.25 9.66421 18.9142 10 18.5 10H14C13.5858 10 13.25 9.66421 13.25 9.25V4.75ZM4.75 14.75C4.75 14.3358 5.08579 14 5.5 14H10C10.4142 14 10.75 14.3358 10.75 14.75V19.25C10.75 19.6642 10.4142 20 10 20H5.5C5.08579 20 4.75 19.6642 4.75 19.25V14.75ZM13.25 14.75C13.25 14.3358 13.5858 14 14 14H18.5C18.9142 14 19.25 14.3358 19.25 14.75V19.25C19.25 19.6642 18.9142 20 18.5 20H14C13.5858 20 13.25 19.6642 13.25 19.25V14.75Z"
                        fill="currentColor"
                    />
                </svg>
            ',

            /*
            |--------------------------------------------------------------------------
            | Progress Perkara
            |--------------------------------------------------------------------------
            */
            'progress' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M5 19V10"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M12 19V5"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M19 19V8"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M3.5 19.5H20.5"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>
            ',

            /*
            |--------------------------------------------------------------------------
            | SPDP
            |--------------------------------------------------------------------------
            */
            'spdp' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M5 12H19"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M14 7L19 12L14 17"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            ',
            
            /*
            |--------------------------------------------------------------------------
            | Tahap 1
            |--------------------------------------------------------------------------
            */
            'stage-1' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="8.5"
                        stroke="currentColor"
                        stroke-width="1.7"
                    />
                    <path
                        d="M12 8V16"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                    <path
                        d="M9 11H12"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                </svg>
            ',

            /*
            |--------------------------------------------------------------------------
            | P-21
            |--------------------------------------------------------------------------
            */
            'p21' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M7 3.75H14.5L19 8.25V20.25H7C5.89543 20.25 5 19.3546 5 18.25V5.75C5 4.64543 5.89543 3.75 7 3.75Z"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M14 4V9H19"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M9 13H15"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                    <path
                        d="M9 16H15"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                </svg>
            ',

            /*
            |--------------------------------------------------------------------------
            | Tahap 2
            |--------------------------------------------------------------------------
            */
            'stage-2' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <rect
                        x="4"
                        y="4"
                        width="16"
                        height="16"
                        rx="3"
                        stroke="currentColor"
                        stroke-width="1.7"
                    />
                    <path
                        d="M9 8H15"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                    <path
                        d="M9 12H15"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                    <path
                        d="M9 16H13"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                    <path
                        d="M17 3V7"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                </svg>
            ',

            /*
            |--------------------------------------------------------------------------
            | Putusan Pengadilan
            |--------------------------------------------------------------------------
            */
            'court' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M12 3V21"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                    <path
                        d="M5 7H19"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                    <path
                        d="M7 7L4 13H10L7 7Z"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M17 7L14 13H20L17 7Z"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M8 21H16"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                </svg>
            ',

            /*
            |--------------------------------------------------------------------------
            | Master Data
            |--------------------------------------------------------------------------
            */
            'master-data' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M4 6.5C4 5.39543 4.89543 4.5 6 4.5H18C19.1046 4.5 20 5.39543 20 6.5V17.5C20 18.6046 19.1046 19.5 18 19.5H6C4.89543 19.5 4 18.6046 4 17.5V6.5Z"
                        stroke="currentColor"
                        stroke-width="1.7"
                    />
                    <path
                        d="M4 9H20"
                        stroke="currentColor"
                        stroke-width="1.7"
                    />
                    <path
                        d="M8 4.5V19.5"
                        stroke="currentColor"
                        stroke-width="1.7"
                    />
                </svg>
            ',

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */
            'settings' => '
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M12 15.25C13.7949 15.25 15.25 13.7949 15.25 12C15.25 10.2051 13.7949 8.75 12 8.75C10.2051 8.75 8.75 10.2051 8.75 12C8.75 13.7949 10.2051 15.25 12 15.25Z"
                        stroke="currentColor"
                        stroke-width="1.7"
                    />
                    <path
                        d="M19.4 15.1L18.25 16.25L17.9 18.1L16.1 18.85L14.5 18.1L13.15 19.05L12.8 21H11.2L10.85 19.05L9.5 18.1L7.9 18.85L6.1 18.1L5.75 16.25L4.6 15.1L3 14.75V13.15L4.6 12.8L5.75 11.65L5.4 10L6.1 8.1L7.9 7.4L9.5 7.85L10.85 6.95L11.2 5H12.8L13.15 6.95L14.5 7.85L16.1 7.4L17.9 8.1L18.6 10L18.25 11.65L19.4 12.8L21 13.15V14.75L19.4 15.1Z"
                        stroke="currentColor"
                        stroke-width="1.4"
                        stroke-linejoin="round"
                    />
                </svg>
            ',
        ];

        return $icons[$iconName] ?? '
            <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >
                <circle
                    cx="12"
                    cy="12"
                    r="8.5"
                    stroke="currentColor"
                    stroke-width="1.7"
                />
            </svg>
        ';
    }
}