@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 bg-gray-50/50 p-6 sm:p-0 dark:bg-gray-900">
        {{-- Pop-up Modal Validasi Kesalahan Input (Email / Password / Captcha) --}}
        @if ($errors->any())
            <div
                x-data="{ showErrorModal: true }"
                x-show="showErrorModal"
                x-cloak
                @keydown.escape.window="showErrorModal = false"
                class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-xs"
            >
                <div
                    @click.outside="showErrorModal = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="w-full max-w-md rounded-2xl border border-error-200 bg-white p-6 shadow-theme-xl dark:border-error-500/30 dark:bg-gray-900"
                >
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                            <i class="fi fi-rr-triangle-warning text-xl" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-title-xs font-semibold text-gray-900 dark:text-white">
                                {{ __('Gagal Masuk') }}
                            </h3>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                                {{ __('Mohon periksa kembali data yang Anda masukkan:') }}
                            </p>
                            <ul class="mt-3 space-y-1.5 rounded-xl bg-error-50/70 p-3 text-theme-sm font-medium text-error-700 dark:bg-error-500/10 dark:text-error-300">
                                @if ($errors->has('email'))
                                    <li class="flex items-center gap-2">
                                        <span>•</span>
                                        <span><strong>{{ __('Email') }}:</strong> {{ $errors->first('email') }}</span>
                                    </li>
                                @endif
                                @if ($errors->has('password'))
                                    <li class="flex items-center gap-2">
                                        <span>•</span>
                                        <span><strong>{{ __('Password') }}:</strong> {{ $errors->first('password') }}</span>
                                    </li>
                                @endif
                                @if ($errors->has('captcha'))
                                    <li class="flex items-center gap-2">
                                        <span>•</span>
                                        <span><strong>{{ __('Captcha') }}:</strong> {{ $errors->first('captcha') }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            type="button"
                            @click="showErrorModal = false"
                            class="bg-error-600 hover:bg-error-700 inline-flex items-center justify-center rounded-lg px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs transition"
                        >
                            {{ __('Coba Lagi') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
            <!-- Form -->
            <div class="flex w-full flex-1 flex-col justify-center px-4 py-8 sm:px-6 lg:w-1/2">
                <div class="mx-auto w-full max-w-lg">
                    <x-common.flash-alert />
                    <!-- Card Hover Wrapper -->
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs transition-all duration-300 hover:-translate-y-1 hover:border-brand-500/30 hover:shadow-theme-xl sm:p-8 dark:border-gray-800 dark:bg-gray-900/90 dark:hover:border-brand-500/30 dark:hover:shadow-theme-xl">
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
                                {{ __('Enter your email and password to sign in!') }}
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
                                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password" placeholder="{{ __('Enter your password') }}"
                                                class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border bg-transparent py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('password') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800' }}" />
                                            <span @click="showPassword = !showPassword" class="absolute top-1/2 right-4 z-30 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                                <i x-show="!showPassword" class="fi fi-rr-eye text-base" aria-hidden="true"></i>
                                                <i x-show="showPassword" class="fi fi-rr-eye-crossed text-base" aria-hidden="true"></i>
                                            </span>
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
                                    {{ __("Don't have an account?") }}
                                    <a href="{{ route('daftar') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ __('Daftar') }} / {{ __('Contact IT Admin') }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toggler tema -->
            <div class="fixed right-6 bottom-6 z-50">
                <button class="bg-brand-500 hover:bg-brand-600 inline-flex size-14 items-center justify-center rounded-full text-white transition-colors"
                    @click.prevent="$store.theme.toggle()">
                    <i class="fi fi-rr-sun hidden text-lg dark:inline-flex" aria-hidden="true"></i>
                    <i class="fi fi-rr-moon text-lg dark:hidden" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
@endsection
