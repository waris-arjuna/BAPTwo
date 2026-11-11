@extends('layouts.fullscreen-layout', ['title' => $title ?? (($code ?? ($exception?->getStatusCode() ?? 'Error')) . ' ' . ($headline ?? __('Terjadi Kesalahan')))])

@section('content')
    <div class="relative z-1 flex min-h-screen w-full flex-col justify-center bg-gray-50/50 p-6 sm:p-8 dark:bg-gray-900">
        <div class="mx-auto w-full max-w-lg">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs transition-[border-color,box-shadow] duration-200 hover:border-brand-500/30 hover:shadow-theme-xl sm:p-8 dark:border-gray-800 dark:bg-gray-900/90 dark:hover:border-brand-500/30 dark:hover:shadow-theme-xl">
                <!-- Header Brand BAPTwo -->
                <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-5 dark:border-gray-800">
                    <a href="{{ auth()->check() ? route('dashboard') : route('masuk') }}" class="inline-flex items-center gap-3">
                        <img class="dark:hidden h-10 w-auto" src="/images/logo/logo.svg" alt="Logo BAPTwo" />
                        <img class="hidden dark:block h-10 w-auto" src="/images/logo/logo-dark.svg" alt="Logo BAPTwo" />
                        <span class="text-title-sm font-bold tracking-tight text-gray-900 dark:text-white">BAPTwo</span>
                    </a>
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        BPOM Kalsel
                    </span>
                </div>

                <!-- Konten Error -->
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl {{ $iconContainerClass ?? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' }}">
                        <i class="{{ $icon ?? 'fi fi-rr-exclamation' }} text-3xl leading-none" aria-hidden="true"></i>
                    </div>

                    <div class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-theme-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <span>{{ __('Kode Status') }}:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $code ?? ($exception?->getStatusCode() ?? 'Error') }}</span>
                    </div>

                    <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                        {{ $headline }}
                    </h1>

                    <p class="text-theme-sm text-gray-500 dark:text-gray-400 leading-relaxed max-w-md mx-auto">
                        {{ $message }}
                    </p>

                    <!-- Tombol Aksi -->
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        @if (! empty($secondaryActionLabel))
                            <a href="{{ $secondaryActionUrl ?? '#' }}"
                                @if (empty($secondaryActionUrl) || $secondaryActionUrl === '#') onclick="window.location.reload(); return false;" @endif
                                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                @if (! empty($secondaryActionIcon))
                                    <i class="{{ $secondaryActionIcon }} text-sm" aria-hidden="true"></i>
                                @endif
                                <span>{{ $secondaryActionLabel }}</span>
                            </a>
                        @endif

                        <a href="{{ $primaryActionUrl ?? (auth()->check() ? route('dashboard') : route('masuk')) }}"
                            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            @if (! empty($primaryActionIcon))
                                <i class="{{ $primaryActionIcon }} text-sm" aria-hidden="true"></i>
                            @endif
                            <span>{{ $primaryActionLabel ?? __('Kembali ke Dashboard') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Footer Informasi -->
                <div class="mt-8 border-t border-gray-100 pt-4 text-center dark:border-gray-800">
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">
                        Balai Besar Pengawas Obat dan Makanan di Banjarmasin
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
