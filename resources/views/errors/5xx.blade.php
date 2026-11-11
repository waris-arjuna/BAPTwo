@php
    $statusCode = $code ?? ($exception?->getStatusCode() ?? 500);
@endphp

@extends('errors.layout', [
    'title' => $statusCode . ' ' . __('Terjadi Kesalahan Sistem'),
    'code' => (string) $statusCode,
    'icon' => 'fi fi-rr-engine-warning',
    'iconContainerClass' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400',
    'headline' => __('Terjadi Kesalahan Sistem'),
    'message' => __('Server mengalami kendala saat memproses permintaan data perkara Anda. Silakan coba beberapa saat lagi atau hubungi administrator.'),
    'primaryActionLabel' => __('Kembali ke Dashboard'),
    'primaryActionIcon' => 'fi fi-rr-arrow-left',
    'primaryActionUrl' => auth()->check() ? route('dashboard') : route('masuk'),
    'secondaryActionLabel' => __('Muat Ulang Halaman'),
    'secondaryActionIcon' => 'fi fi-rr-refresh',
    'secondaryActionUrl' => '#',
])
