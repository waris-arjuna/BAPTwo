@extends('errors.layout', [
    'title' => '401 Otentikasi Diperlukan',
    'code' => '401',
    'icon' => 'fi fi-rr-key',
    'iconContainerClass' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
    'headline' => __('Otentikasi Diperlukan'),
    'message' => __('Anda harus masuk terlebih dahulu dengan akun yang sah untuk dapat mengakses halaman atau tindakan ini.'),
    'primaryActionLabel' => __('Masuk Sekarang'),
    'primaryActionIcon' => 'fi fi-rr-sign-in-alt',
    'primaryActionUrl' => route('masuk'),
])
