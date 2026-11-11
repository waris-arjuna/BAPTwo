@php
    $statusCode = $code ?? ($exception?->getStatusCode() ?? 400);
@endphp

@extends('errors.layout', [
    'title' => $statusCode . ' ' . __('Permintaan Tidak Dapat Diproses'),
    'code' => (string) $statusCode,
    'icon' => 'fi fi-rr-exclamation',
    'iconContainerClass' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
    'headline' => __('Permintaan Tidak Dapat Diproses'),
    'message' => __('Permintaan Anda tidak dapat diproses oleh sistem. Pastikan data yang dikirimkan sudah benar atau kembali ke halaman utama.'),
    'primaryActionLabel' => __('Kembali ke Dashboard'),
    'primaryActionIcon' => 'fi fi-rr-arrow-left',
    'primaryActionUrl' => auth()->check() ? route('dashboard') : route('masuk'),
])
