@extends('errors.layout', [
    'title' => '429 Terlalu Banyak Permintaan',
    'code' => '429',
    'icon' => 'fi fi-rr-time-past',
    'iconContainerClass' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
    'headline' => __('Terlalu Banyak Permintaan'),
    'message' => __('Sistem mendeteksi terlalu banyak permintaan dari perangkat Anda dalam waktu singkat. Silakan tunggu beberapa saat lalu coba kembali.'),
    'primaryActionLabel' => __('Kembali ke Dashboard'),
    'primaryActionIcon' => 'fi fi-rr-arrow-left',
    'primaryActionUrl' => auth()->check() ? route('dashboard') : route('masuk'),
    'secondaryActionLabel' => __('Muat Ulang Halaman'),
    'secondaryActionIcon' => 'fi fi-rr-refresh',
    'secondaryActionUrl' => '#',
])
