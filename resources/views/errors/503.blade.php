@extends('errors.layout', [
    'title' => '503 Sistem Dalam Pemeliharaan',
    'code' => '503',
    'icon' => 'fi fi-rr-settings',
    'iconContainerClass' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
    'headline' => __('Sistem Dalam Pemeliharaan'),
    'message' => __('Aplikasi BAPTwo saat ini sedang menjalani pemeliharaan sistem atau pembaruan berkala. Layanan akan segera aktif kembali.'),
    'primaryActionLabel' => __('Muat Ulang Halaman'),
    'primaryActionIcon' => 'fi fi-rr-refresh',
    'primaryActionUrl' => '#',
])
