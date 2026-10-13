@extends('layouts.app')

@section('content')
    @php
        $p21 = [
            'nomor' => 'P-21/001/IX/2026',
            'tanggal' => '20-09-2026',
            'perkara' => 'PRK/001/2026',
            'tersangka' => 'Nama Tersangka 1',
            'penyidik' => 'Nama Penyidik 1',
            'dokumen' => 'p21-001.pdf',
            'tahap1' => '15-09-2026',
            'p18' => 'p18-001.pdf',
            'p19' => null,
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
                    P-21 / P21a
                </a>

                <span>/</span>

                <span class="text-gray-700 dark:text-gray-300">
                    Detail
                </span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Detail P-21 / P21a
            </h1>
        </div>

        {{-- Informasi Utama --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Informasi P-21 / P21a
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 lg:grid-cols-3">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Nomor P-21/P21a
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
                        Perkara
                    </p>

                    <a
                        href="{{ url('/progress-perkara') }}"
                        class="mt-1 inline-block text-sm font-medium text-brand-500 hover:text-brand-600"
                    >
                        {{ $p21['perkara'] }}
                    </a>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Tersangka
                    </p>

                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $p21['tersangka'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Penyidik
                    </p>

                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $p21['penyidik'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        TAHAP 1
                    </p>

                    <div class="mt-1 flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-success-50 text-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-check text-[10px]"></i>
                        </span>

                        <span class="text-sm font-medium text-success-600 dark:text-success-400">
                            Tersedia — {{ $p21['tahap1'] }}
                        </span>
                    </div>
                </div>

                {{-- Dokumen --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Dokumen P-21/P21a
                    </p>

                    <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">

                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-document text-base text-red-500"></i>

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

            </div>
        </div>

        {{-- Optional Documents --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    P-18P / P-19
                </h2>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Dokumen ini bersifat opsional dan hanya dapat tersedia sebelum P-21/P21a.
                </p>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-800">

                {{-- P18 --}}
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">
                        <i class="fi fi-rr-document text-base text-red-500"></i>

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
                                    Tidak tersedia
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
                            Tidak ada
                        </span>
                    @endif

                </div>

                {{-- P19 --}}
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">
                        <i class="fi fi-rr-document text-base text-gray-400"></i>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-19
                            </p>

                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Tidak tersedia
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/10 dark:text-gray-400">
                        Tidak ada
                    </span>

                </div>

            </div>
        </div>

        {{-- Workflow --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Workflow Penyidikan
                </h2>
            </div>

            <div class="p-5 sm:p-6">

                <div class="space-y-5">

                    {{-- SPDP --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
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

                    {{-- P16 P17 --}}
                    <div class="ml-4 border-l border-gray-200 pl-7 dark:border-gray-800">
                        <div class="flex items-center gap-2">

                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Tahapan opsional
                            </span>

                            @if ($p21['p18'] || $p21['p19'])
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/10 dark:text-gray-400">
                                    Tersedia
                                </span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/10 dark:text-gray-400">
                                    Tidak ada
                                </span>
                            @endif

                        </div>

                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                            P-16 / P-17P
                        </p>
                    </div>

                    {{-- Tahap 1 --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-check text-xs"></i>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                TAHAP 1
                            </p>

                            <p class="text-xs text-success-600 dark:text-success-400">
                                Selesai — {{ $p21['tahap1'] }}
                            </p>
                        </div>
                    </div>

                    {{-- P18 P19 --}}
                    <div class="ml-4 border-l border-gray-200 pl-7 dark:border-gray-800">
                        <div class="flex items-center gap-2">

                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Tahapan opsional
                            </span>

                            @if ($p21['p18'] || $p21['p19'])
                                <span class="rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                    Tersedia
                                </span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/10 dark:text-gray-400">
                                    Tidak ada
                                </span>
                            @endif

                        </div>

                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                            P-18P / P-19
                        </p>
                    </div>

                    {{-- P21 --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-check text-xs"></i>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-21 / P21a
                            </p>

                            <p class="text-xs text-success-600 dark:text-success-400">
                                Selesai — tahap saat ini
                            </p>
                        </div>
                    </div>

                    {{-- Tahap 2 --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gray-300 text-xs font-medium text-gray-400 dark:border-gray-700 dark:text-gray-500">
                            6
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                TAHAP 2
                            </p>

                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Belum dimulai
                            </p>
                        </div>
                    </div>

                    {{-- Keputusan --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gray-300 text-xs font-medium text-gray-400 dark:border-gray-700 dark:text-gray-500">
                            7
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Keputusan Pengadilan
                            </p>

                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Belum dimulai
                            </p>
                        </div>
                    </div>

                </div>

                {{-- Action --}}
                <div class="mt-6 border-t border-gray-200 pt-5 dark:border-gray-800">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                P-21/P21a telah tersedia.
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                P-18P/P-19 tidak dapat ditambahkan lagi setelah tahap ini.
                            </p>
                        </div>

                        @can('create-cases')
                            <a
                                href="{{ url('/tahap-2/tambah') }}"
                                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
                            >
                                Lanjut Tahap 2
                            </a>
                        @endcan

                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection