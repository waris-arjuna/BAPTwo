@extends('errors.layout', [
    'title' => '403 Akses Ditolak',
    'code' => '403',
    'icon' => 'fi fi-rr-shield-exclamation',
    'iconContainerClass' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400',
    'headline' => __('Akses Ditolak'),
    'message' => __('Anda tidak memiliki hak akses atau izin yang memadai untuk membuka halaman atau menjalankan tindakan ini.'),
    'primaryActionLabel' => __('Kembali ke Dashboard'),
    'primaryActionIcon' => 'fi fi-rr-arrow-left',
    'primaryActionUrl' => auth()->check() ? route('dashboard') : route('masuk'),
])
