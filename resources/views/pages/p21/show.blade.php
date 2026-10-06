@extends('layouts.app')

@section('content')
    @php
        $p21 = [
            'nomor' => 'P-21/001/IX/2026',
            'tanggal' => '25-09-2026',
            'tahap1' => 'TH1/001/IX/2026',
            'dokumen' => 'p21-001.pdf',
            'p18' => 'p18-001.pdf',
            'p19' => null,
            'catatan' => 'Berkas perkara dinyatakan lengkap dan dapat dilanjutkan ke Tahap 2.',
            'status' => 'P-21 Terbit',
        ];
    @endphp

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
                    href="{{ url('/p21') }}"
                    class="transition hover:text-brand-500"
                >
                    P-21
                </a>

                <span>/</span>

                <span class="text-gray-700 dark:text-gray-300">
                    Detail
                </span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Detail P-21
            </h1>
        </div>

        {{-- Informasi --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Informasi P-21
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 sm:p-6">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Nomor P-21
                    </p>

                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $p21['nomor'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Tanggal
                    </p>

                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $p21['tanggal'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Tahap 1
                    </p>

                    <a
                        href="{{ url('/tahap-1/1') }}"
                        class="mt-1 inline-block text-sm font-medium text-brand-500 hover:text-brand-600"
                    >
                        {{ $p21['tahap1'] }}
                    </a>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Status Proses
                    </p>

                    <span class="mt-2 inline-flex rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                        {{ $p21['status'] }}
                    </span>
                </div>

                {{-- Dokumen P21 --}}
                <div class="sm:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Dokumen P-21
                    </p>

                    <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">

                        <div class="flex items-center gap-2">
                            <svg
                                class="h-5 w-5 text-red-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2V8z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14 2v6h6"
                                />
                            </svg>

                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $p21['dokumen'] }}
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
                        {{ $p21['catatan'] }}
                    </p>
                </div>

            </div>
        </div>

        {{-- Dokumen Sebelum P-21 --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Dokumen Pendukung Sebelum P-21
                </h2>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Dokumen P-18P dan P-19 bersifat opsional.
                </p>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-800">

                {{-- P18 --}}
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">
                        <svg
                            class="h-5 w-5 text-red-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                            />
                        </svg>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-18P
                            </p>

                            @if ($p21['p18'])
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $p21['p18'] }}
                                </p>
                            @else
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    Belum tersedia
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($p21['p18'])
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300"
                        >
                            Lihat Dokumen
                        </button>
                    @else
                        <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/10 dark:text-gray-400">
                            Opsional
                        </span>
                    @endif
                </div>

                {{-- P19 --}}
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">
                        <svg
                            class="h-5 w-5 text-red-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2h12a2 2 0 0 0 2-2V8z"
                            />
                        </svg>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-19
                            </p>

                            @if ($p21['p19'])
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $p21['p19'] }}
                                </p>
                            @else
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    Belum tersedia
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($p21['p19'])
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300"
                        >
                            Lihat Dokumen
                        </button>
                    @else
                        <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/10 dark:text-gray-400">
                            Opsional
                        </span>
                    @endif
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
                            ✓
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
                            ✓
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
                            ✓
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
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            ✓
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                Tahap 1
                            </p>

                            <p class="text-xs text-success-600 dark:text-success-400">
                                Selesai
                            </p>
                        </div>
                    </div>

                    {{-- P21 --}}
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-brand-500 bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                            5
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-21
                            </p>

                            <p class="text-xs text-brand-500">
                                Tahap aktif
                            </p>
                        </div>
                    </div>

                </div>

                <div class="mt-6 border-t border-gray-200 pt-5 dark:border-gray-800">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-21 telah terbit.
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Proses berikutnya dapat dilanjutkan ke Tahap 2.
                            </p>
                        </div>

                        <a
                            href="{{ url('/tahap-2/tambah') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
                        >
                            Lanjut Tahap 2
                        </a>

                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection