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
                                <a href="/signin" class="inline-flex items-center gap-2 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    {{ __('Back to login') }}
                                </a>
                            </div>
                            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                                <div>
                                    <div class="mb-5 sm:mb-8">
                                        <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                                            {{ __("Don't have an account?") }}
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
                                                <svg class="fill-current text-brand-500" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M2.5 5.5A2 2 0 0 1 4.5 3.5h11a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-9Zm2 .4 5.5 4 5.5-4v-.4h-11v.4Zm11 1.7-5.3 3.85a.75.75 0 0 1-.9 0L4.5 7.6V14.5h11V7.6Z" />
                                                </svg>
                                                <span>{{ __('Email') }}: admin-it@baptwo.go.id</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <svg class="fill-current text-brand-500" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M6.5 2.5A2.5 2.5 0 0 0 4 5v10a2.5 2.5 0 0 0 5 0V5a2.5 2.5 0 0 0-2.5-2.5ZM5.5 5a1 1 0 0 1 2 0v10a1 1 0 1 1-2 0V5Zm6.5-.5A2.5 2.5 0 0 0 9.5 7v6a2.5 2.5 0 0 0 5 0V7A2.5 2.5 0 0 0 12 4.5ZM11 7a1 1 0 1 1 2 0v6a1 1 0 1 1-2 0V7Z" />
                                                </svg>
                                                <span>{{ __('Internal extension') }}: IT Support</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="mt-5">
                                        <a href="/signin"
                                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                            {{ __('Back to login') }}
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
