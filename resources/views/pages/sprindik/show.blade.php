@php
    $sprindik = [
        'nomor' => 'SP/001/IX/2026',
        'tanggal' => '03-09-2026',
        'laporan' => 'LK/001/IX/2026',
        'dokumen' => 'sprindik-001.pdf',
        'status' => 'Belum Menjadi Perkara',
    ];
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a
                href="{{ url('/') }}"
                class="transition hover:text-brand-500"
            >
                Home
            </a>

            <span>/</span>

            <a
                href="{{ url('/sprindik') }}"
                class="transition hover:text-brand-500"
            >
                Sprindik
            </a>

            <span>/</span>

            <span class="text-gray-700 dark:text-gray-300">
                Detail
            </span>
        </div>

        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
            Detail Sprindik
        </h1>
    </div>

    {{-- Informasi --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Informasi Sprindik
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 sm:p-6">

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Nomor Sprindik
                </p>

                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                    {{ $sprindik['nomor'] }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tanggal
                </p>

                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                    {{ $sprindik['tanggal'] }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Laporan Kejadian
                </p>

                <a
                    href="{{ url('/laporan-kejadian/1') }}"
                    class="mt-1 inline-block text-sm font-medium text-brand-500 hover:text-brand-600"
                >
                    {{ $sprindik['laporan'] }}
                </a>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Status
                </p>

                <span class="mt-2 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">
                    {{ $sprindik['status'] }}
                </span>
            </div>

            <div class="sm:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Dokumen
                </p>

                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">

                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-document text-base text-red-500"></i>

                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $sprindik['dokumen'] }}
                        </span>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                    >
                        Lihat Dokumen
                    </button>

                </div>
            </div>

        </div>
    </div>

    {{-- Workflow --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Workflow Proses
            </h2>
        </div>

        <div class="p-5 sm:p-6">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

                {{-- Step 1 --}}
                <div>
                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-check text-xs"></i>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                Laporan Kejadian
                            </p>

                            <p class="text-xs text-success-600 dark:text-success-400">
                                Selesai
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div>
                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-check text-xs"></i>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                Sprindik
                            </p>

                            <p class="text-xs text-success-600 dark:text-success-400">
                                Selesai
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div>
                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-300 text-gray-400 dark:border-gray-700 dark:text-gray-500">
                            3
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Perkara
                            </p>

                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Belum dibuat
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Step 4 --}}
                <div>
                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-300 text-gray-400 dark:border-gray-700 dark:text-gray-500">
                            4
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Proses Penyidikan
                            </p>

                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Belum dimulai
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Connector --}}
            <div class="my-6 hidden h-px bg-gray-200 md:block dark:bg-gray-800"></div>

            {{-- Current Action --}}
            <div class="border-t border-gray-200 pt-5 dark:border-gray-800">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            Sprindik siap dilanjutkan ke pembuatan perkara.
                        </p>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Setelah perkara dibuat, proses penyidikan dapat dimulai pada modul Perkara.
                        </p>
                    </div>

                    @can('create-cases')
                        <a
                            href="#"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
                        >
                            <i class="fi fi-rr-plus-small text-lg"></i>

                            Buat Perkara
                        </a>
                    @endcan

                </div>
            </div>
        </div>
    </div>

</div>
@endsection