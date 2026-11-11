@extends('errors.layout', [
    'title' => '404 Halaman Tidak Ditemukan',
    'code' => '404',
    'icon' => 'fi fi-rr-search-alt',
    'iconContainerClass' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
    'headline' => __('Halaman Tidak Ditemukan'),
    'message' => __('Halaman atau data berkas perkara yang Anda tuju tidak ditemukan di sistem, telah dihapus, atau tautan tidak valid.'),
    'primaryActionLabel' => __('Kembali ke Dashboard'),
    'primaryActionIcon' => 'fi fi-rr-arrow-left',
    'primaryActionUrl' => auth()->check() ? route('dashboard') : route('masuk'),
])
