@extends('errors.layout', [
    'title' => '419 Sesi Telah Berakhir',
    'code' => '419',
    'icon' => 'fi fi-rr-time-past',
    'iconContainerClass' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
    'headline' => __('Sesi Telah Berakhir'),
    'message' => __('Sesi aktif Anda telah kedaluwarsa demi keamanan akun dan data perkara. Silakan masuk kembali untuk melanjutkan pekerjaan.'),
    'primaryActionLabel' => __('Masuk Kembali'),
    'primaryActionIcon' => 'fi fi-rr-sign-in-alt',
    'primaryActionUrl' => route('masuk'),
    'secondaryActionLabel' => __('Muat Ulang Halaman'),
    'secondaryActionIcon' => 'fi fi-rr-refresh',
    'secondaryActionUrl' => '#',
])
