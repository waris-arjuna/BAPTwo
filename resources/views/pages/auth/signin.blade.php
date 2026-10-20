@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 bg-gray-50/50 p-6 sm:p-0 dark:bg-gray-900">
        <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
            <!-- Form -->
            <div class="flex w-full flex-1 flex-col justify-center px-4 py-8 sm:px-6 lg:w-1/2">
                <div class="mx-auto w-full max-w-lg">
                    {{-- Banner Sesi Berakhir (Tidak Auto-Dismiss, Tanpa Modal) --}}
                    @if (request()->query('session_expired'))
                        <div class="mb-5 rounded-2xl border border-warning-200 bg-warning-50 p-4 text-warning-800 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300" role="alert">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-100 text-warning-600 dark:bg-warning-500/20 dark:text-warning-400">
                                    <i class="fi fi-rr-clock text-base leading-none"></i>
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <h4 class="text-sm font-semibold">Sesi Berakhir</h4>
                                    <p class="mt-0.5 text-xs leading-relaxed text-warning-700 dark:text-warning-300/90">
                                        Anda keluar otomatis setelah 30 menit tidak ada aktivitas. Silakan masuk kembali untuk melanjutkan pekerjaan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Card Hover Wrapper -->
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs transition-all duration-300 hover:border-brand-500/30 hover:shadow-theme-xl sm:p-8 dark:border-gray-800 dark:bg-gray-900/90 dark:hover:border-brand-500/30 dark:hover:shadow-theme-xl">
                        <!-- Logo BAPTwo -->
                        <div class="mb-6 flex items-center justify-between">
                            <a href="/" class="inline-flex items-center gap-3">
                                <img class="dark:hidden h-20 w-auto" src="/images/logo/logo.svg" alt="Logo BAPTwo" />
                                <img class="hidden dark:block h-21 w-auto" src="/images/logo/logo-dark.svg" alt="Logo BAPTwo" />
                                <span class="text-title-sm font-bold tracking-tight text-gray-900 dark:text-white">BAPTwo</span>
                            </a>
                            <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                BPOM Kalsel
                            </span>
                        </div>

                        <!-- Card Header -->
                        <div class="mb-5 sm:mb-8 text-center">
                            <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                                {{ __('Masuk') }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Silakan masukkan email dan kata sandi kedinasan Anda.') }}
                            </p>
                        </div>
                        <div>
                            <form action="{{ route('masuk.attempt') }}" method="POST">
                                @csrf
                                <div class="space-y-5">
                                    <!-- Email -->
                                    <div>
                                        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                             {{ __('Email') }}<span class="text-error-500">*</span>
                                        </label>
                                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@baptwo.go.id"
                                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('email') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800' }}" />
                                        @error('email')
                                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <!-- Password -->
                                    <div>
                                        <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ __('Password') }}<span class="text-error-500">*</span>
                                        </label>
                                        <div class="relative" x-data="{ showPassword: false }">
                                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password" placeholder="{{ __('Masukkan kata sandi Anda') }}"
                                                class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border bg-transparent py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('password') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800' }}" />
                                            <button type="button" @click="showPassword = !showPassword" aria-label="{{ __('Tampilkan atau sembunyikan kata sandi') }}" class="absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                <i x-show="!showPassword" class="fi fi-rr-eye text-base" aria-hidden="true"></i>
                                                <i x-show="showPassword" class="fi fi-rr-eye-crossed text-base" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <!-- Captcha -->
                                    <x-form.math-captcha />
                                    <!-- Button -->
                                    <div>
                                        <button type="submit"
                                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                            {{ __('Masuk') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-400">
                                    {{ __('Belum memiliki akun?') }}
                                    <a href="{{ route('daftar') }}" class="font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ __('Hubungi Administrator IT BPOM') }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toggler tema -->
            <div class="fixed right-6 bottom-6 z-50">
                <button type="button" aria-label="{{ __('Alihkan mode tema (terang/gelap)') }}" title="{{ __('Alihkan mode tema') }}" class="bg-brand-500 hover:bg-brand-600 shadow-theme-md inline-flex size-12 items-center justify-center rounded-full text-white transition focus:outline-hidden focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                    @click.prevent="$store.theme.toggle()">
                    <i class="fi fi-rr-sun hidden text-lg dark:inline-flex" aria-hidden="true"></i>
                    <i class="fi fi-rr-moon text-lg dark:hidden" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
@endsection
