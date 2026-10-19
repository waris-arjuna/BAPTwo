@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
        <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
            <!-- Form -->
            <div class="flex w-full flex-1 flex-col justify-center px-4 py-8 sm:px-6 lg:w-1/2">
                <div class="mx-auto w-full max-w-lg">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs transition-all duration-300 hover:-translate-y-1 hover:border-brand-500/30 hover:shadow-theme-xl sm:p-8 dark:border-gray-800 dark:bg-gray-900/90 dark:hover:border-brand-500/30 dark:hover:shadow-theme-xl">
                        <div class="flex w-full flex-1 flex-col">
                            <div class="mx-auto w-full max-w-md pt-10">
                                <a href="{{ route('masuk') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    <i class="fi fi-rr-angle-small-left text-base" aria-hidden="true"></i>
                                    {{ __('Kembali ke Halaman Masuk') }}
                                </a>
                            </div>
                            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                                <div>
                                    <div class="mb-5 sm:mb-8">
                                        <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                                            {{ __('Daftar Akun') }}
                                        </h1>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('Pendaftaran akun BAPTwo tidak tersedia untuk umum.') }}
                                        </p>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/5">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ __('Untuk mendapatkan akun, silakan hubungi Admin Tim IT BPOM Kalimantan Selatan.') }}
                                        </p>
                                        <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                            <li class="flex items-center gap-2">
                                                <i class="fi fi-rr-envelope text-sm text-brand-500" aria-hidden="true"></i>
                                                <span>{{ __('Email') }}: admin-it@baptwo.go.id</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <i class="fi fi-rr-headset text-sm text-brand-500" aria-hidden="true"></i>
                                                <span>{{ __('Internal extension') }}: IT Support</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="mt-5">
                                        <a href="{{ route('masuk') }}"
                                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                            {{ __('Kembali ke Halaman Masuk') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
