@extends('layouts.app')

@section('content')
    @php
        $tahap1 = [
            'nomor' => 'TH1/001/IX/2026',
            'tanggal' => '18-09-2026',
            'spdp' => 'SPDP/001/IX/2026',
            'dokumen' => 'bap-001.pdf',
            'catatan' => 'Berkas hasil penyidikan telah disampaikan untuk proses penelitian.',
            'status' => 'Tahap 1 Berjalan',
            'p16' => 'p16-001.pdf',
            'p17' => null,
        ];
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-500">
                    Home
                </a>

                <span>/</span>

                <a href="{{ url('/tahap-1') }}" class="transition hover:text-brand-500">
                    Tahap 1
                </a>

                <span>/</span>

                <span class="text-gray-700 dark:text-gray-300">
                    Detail
                </span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Detail Tahap 1
            </h1>
        </div>

        {{-- Informasi --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Informasi Tahap 1
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 sm:p-6">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Nomor Tahap 1
                    </p>

                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $tahap1['nomor'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Tanggal
                    </p>

                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $tahap1['tanggal'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        SPDP
                    </p>

                    <a
                        href="{{ url('/spdp/1') }}"
                        class="mt-1 inline-block text-sm font-medium text-brand-500 hover:text-brand-600"
                    >
                        {{ $tahap1['spdp'] }}
                    </a>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Status Proses
                    </p>

                    <span class="mt-2 inline-flex rounded-full bg-warning-50 px-3 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                        {{ $tahap1['status'] }}
                    </span>
                </div>

                {{-- BAP --}}
                <div class="sm:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        BAP
                    </p>

                    <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">

                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-document text-base text-red-500"></i>

                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $tahap1['dokumen'] }}
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

                {{-- Catatan --}}
                <div class="sm:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Catatan
                    </p>

                    <p class="mt-2 text-sm leading-6 text-gray-700 dark:text-gray-300">
                        {{ $tahap1['catatan'] }}
                    </p>
                </div>

            </div>
        </div>

        {{-- Dokumen Pendukung --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Dokumen Pendukung
                </h2>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-800">

                {{-- P16 --}}
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">
                        <i class="fi fi-rr-document text-base text-red-500"></i>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-16
                            </p>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $tahap1['p16'] }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300"
                    >
                        Lihat Dokumen
                    </button>
                </div>

                {{-- P17P --}}
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">
                        <div class="flex h-5 w-5 items-center justify-center text-gray-400">
                            —
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-17P
                            </p>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Belum tersedia
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/10 dark:text-gray-400">
                        Opsional
                    </span>
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

                <div class="grid grid-cols-1 gap-5 md:grid-cols-5">

                    {{-- Laporan --}}
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

                    {{-- Sprindik --}}
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

                    {{-- SPDP --}}
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-check text-xs"></i>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                SPDP
                            </p>

                            <p class="text-xs text-success-600 dark:text-success-400">
                                Selesai
                            </p>
                        </div>
                    </div>

                    {{-- Tahap 1 --}}
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-brand-500 bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                            4
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                Tahap 1
                            </p>

                            <p class="text-xs text-brand-500">
                                Tahap aktif
                            </p>
                        </div>
                    </div>

                    {{-- P21 --}}
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-300 text-gray-400 dark:border-gray-700 dark:text-gray-500">
                            5
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                P-21
                            </p>

                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Menunggu
                            </p>
                        </div>
                    </div>

                </div>

                {{-- Next Action --}}
                <div class="mt-6 border-t border-gray-200 pt-5 dark:border-gray-800">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                Tahap 1 sedang berjalan.
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Setelah proses Tahap 1 selesai, perkara dapat dilanjutkan ke P-21.
                            </p>
                        </div>

                        @can('create-cases')
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
                            >
                                Lanjut P-21
                            </button>
                        @endcan

                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection