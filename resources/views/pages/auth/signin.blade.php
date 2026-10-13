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
                            <svg class="h-6 w-6 stroke-current" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
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
                <div class="mx-auto mb-4 w-full max-w-lg">
                    <a href="/" class="inline-flex items-center gap-2 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ __('Back to dashboard') }}
                    </a>
                </div>
                <div class="mx-auto w-full max-w-lg">
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
                                {{ __('Sign In') }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Enter your email and password to sign in!') }}
                            </p>
                        </div>
                        <div>
                            <form action="{{ route('signin.attempt') }}" method="POST">
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
                                                <svg x-show="!showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" fill="#98A2B3" d="M10 13.86c-2.77 0-5.13-1.72-6.08-4.16.95-2.44 3.31-4.16 6.08-4.16 2.77 0 5.13 1.72 6.08 4.16-.95 2.44-3.31 4.16-6.08 4.16Zm0-9.82c-3.52 0-6.51 2.27-7.58 5.42-.05.16-.05.33 0 .49C3.49 13.1 6.48 15.36 10 15.36s6.51-2.27 7.58-5.42c.05-.16.05-.33 0-.49C16.51 6.31 13.52 4.04 10 4.04Zm-.01 3.8c-1.03 0-1.86.83-1.86 1.86s.83 1.86 1.86 1.86h.01c1.03 0-1.86-.83 1.86-1.86s-.83-1.86-1.86-1.86h-.01Z" />
                                                </svg>
                                                <svg x-show="showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" fill="#98A2B3" d="M4.64 3.58a.75.75 0 0 0-1.06 1.06l1.28 1.28C3.75 6.84 2.89 8.06 2.42 9.46c-.05.16-.05.33 0 .49 1.08 3.15 4.06 5.41 7.58 5.41 1.25 0 2.44-.29 3.5-.8l1.86 1.86a.75.75 0 1 0 1.06-1.06L4.64 3.58Zm7.72 9.84-1.91-1.91a1.86 1.86 0 0 1-2.51-2.51L5.92 6.98c-.88.71-1.58 1.65-1.99 2.72.95 2.44 3.31 4.16 6.07 4.16.83 0 1.63-.15 2.36-.42Z" />
                                                </svg>
                                            </span>
                                        </div>
                                        @error('password')
                                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <!-- Captcha -->
                                    <x-form.math-captcha />
                                    <!-- Checkbox -->
                                    <div class="flex items-center justify-between">
                                        <div x-data="{ checkboxToggle: false }">
                                            <label for="checkboxLabelOne" class="flex cursor-pointer items-center text-sm font-normal text-gray-700 select-none dark:text-gray-400">
                                                <div class="relative">
                                                    <input type="checkbox" id="checkboxLabelOne" name="remember" value="1" class="sr-only" @change="checkboxToggle = !checkboxToggle" />
                                                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                        class="me-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                                                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                                {{ __('Keep me logged in') }}
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Button -->
                                    <div>
                                        <button type="submit"
                                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                            {{ __('Sign In') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-400">
                                    {{ __("Don't have an account?") }}
                                    <a href="/signup" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ __('Contact IT Admin') }}</a>
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
                    <svg class="hidden fill-current dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" fill="white" d="M10 1.54c.41 0 .75.34.75.75v1.25a.75.75 0 0 1-1.5 0V2.29a.75.75 0 0 1 .75-.75Zm0 5.25A3.21 3.21 0 0 0 6.79 10 3.21 3.21 0 0 0 10 13.21 3.21 3.21 0 0 0 13.21 10 3.21 3.21 0 0 0 10 6.79ZM5.29 10A4.71 4.71 0 0 1 10 5.29 4.71 4.71 0 0 1 14.71 10 4.71 4.71 0 0 1 10 14.71 4.71 4.71 0 0 1 5.29 10Z" />
                    </svg>
                    <svg class="fill-current dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill="currentColor" d="M17.45 11.97l.72.19c.09-.32-.05-.66-.33-.83a.75.75 0 0 0-.9.13l.51.51ZM8.03 2.55l.55.51a.75.75 0 0 0-.04-1.08.74.74 0 0 0-.79-.16l.28.73ZM12.92 13a6.96 6.96 0 0 1-2.92-1.04A6.96 6.96 0 0 1 7 10C5.34 7.26 6.31 4.06 9 3.27a8.42 8.42 0 0 0-6.8 4.36A7.96 7.96 0 0 0 10 18.46c3.93 0 7.22-2.67 8.18-6.3a6.92 6.92 0 0 1-3.26 1.4c-.66.1-1.34.1-2 .04Z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endsection
