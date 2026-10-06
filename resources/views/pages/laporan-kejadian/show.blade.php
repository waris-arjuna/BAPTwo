@php
    $laporanId = (int) request()->route('id');

    $laporan = match ($laporanId) {
        1 => [
            'number' => 'LK/001/IX/2026',
            'date' => '02-09-2026',
            'document' => 'laporan-kejadian-001.pdf',
            'status' => 'Belum Dilanjutkan',
            'step' => 1,
        ],
        2 => [
            'number' => 'LK/002/IX/2026',
            'date' => '04-09-2026',
            'document' => 'laporan-kejadian-002.pdf',
            'status' => 'Sprindik Tersedia',
            'step' => 2,
        ],
        default => [
            'number' => 'LK/003/IX/2026',
            'date' => '05-09-2026',
            'document' => 'laporan-kejadian-003.pdf',
            'status' => 'Perkara Terbentuk',
            'step' => 3,
        ],
    };

    $title = 'Detail Laporan Kejadian';
@endphp

@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-screen-xl px-4 pb-8 pt-6 lg:px-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Home</a>
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 1 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd" />
                </svg>
                <a href="{{ url('/laporan-kejadian') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Laporan Kejadian</a>
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24-4.24a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                </svg>
                <span class="text-gray-700 dark:text-gray-300">Detail</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Laporan Kejadian</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detail data dan progres lanjutan laporan kejadian.</p>
                </div>
                <a
                    href="{{ url('/laporan-kejadian') }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                >
                    Kembali
                </a>
            </div>
        </div>

        {{-- Information --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Informasi Laporan</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data dasar laporan kejadian.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                        @if ($laporan['status'] === 'Belum Dilanjutkan') bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300
                        @elseif ($laporan['status'] === 'Sprindik Tersedia') bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300
                        @else bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-300 @endif
                    ">
                        {{ $laporan['status'] }}
                    </span>
                </div>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800/70">
                <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[180px_1fr] sm:px-6 sm:gap-6">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Nomor Laporan</dt>
                    <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $laporan['number'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[180px_1fr] sm:px-6 sm:gap-6">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal</dt>
                    <dd class="text-sm text-gray-800 dark:text-gray-300">{{ $laporan['date'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[180px_1fr] sm:px-6 sm:gap-6">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Dokumen</dt>
                    <dd class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <span class="inline-flex min-w-0 items-center gap-2 text-sm text-gray-800 dark:text-gray-300">
                            <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 3.5h7.2L19 8.3v12.2H7v-17Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                <path d="M14 3.5v5h5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                            </svg>
                            <span class="truncate">{{ $laporan['document'] }}</span>
                        </span>
                        <button
                            type="button"
                            class="inline-flex h-9 w-fit items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                        >
                            Lihat Dokumen
                        </button>
                    </dd>
                </div>
            </div>
        </div>

        {{-- Workflow --}}
        <div class="mt-5 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Alur Proses</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Posisi laporan kejadian pada proses lanjutan.</p>
            </div>

            <div class="px-4 py-6 sm:px-6 sm:py-8">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-4 md:gap-0">
                    @php
                        $steps = [
                            ['label' => 'Laporan Kejadian', 'number' => 1],
                            ['label' => 'Sprindik', 'number' => 2],
                            ['label' => 'Perkara', 'number' => 3],
                            ['label' => 'Proses Penyidikan', 'number' => 4],
                        ];
                    @endphp

                    @foreach ($steps as $index => $step)
                        <div class="relative flex items-start gap-3 md:flex-col md:items-center md:text-center">
                            @if ($index < count($steps) - 1)
                                <div class="absolute left-[15px] top-8 h-[calc(100%+24px)] w-px bg-gray-200 md:left-1/2 md:top-[15px] md:h-px md:w-full dark:bg-gray-700"></div>
                            @endif

                            <div class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-xs font-semibold
                                @if ($step['number'] < $laporan['step']) border-green-600 bg-green-600 text-white
                                @elseif ($step['number'] === $laporan['step']) border-brand-600 bg-brand-600 text-white
                                @else border-gray-300 bg-white text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-500 @endif
                            ">
                                @if ($step['number'] < $laporan['step'])
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.01 7.07a1 1 0 0 1-1.423.002L3.29 8.53A1 1 0 0 1 4.71 7.12l4.276 4.3 6.304-6.36a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    {{ $step['number'] }}
                                @endif
                            </div>

                            <div class="md:mt-3">
                                <p class="text-sm font-medium
                                    @if ($step['number'] <= $laporan['step']) text-gray-800 dark:text-white/90
                                    @else text-gray-400 dark:text-gray-500 @endif
                                ">{{ $step['label'] }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-500">
                                    @if ($step['number'] < $laporan['step']) Selesai
                                    @elseif ($step['number'] === $laporan['step']) Posisi saat ini
                                    @else Belum dimulai @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Next action --}}
        <div id="sprindik" class="mt-5 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-4 py-5 sm:px-6">
                @if ($laporan['status'] === 'Belum Dilanjutkan')
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Laporan siap dilanjutkan</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Input Sprindik untuk melanjutkan proses dari laporan kejadian ini.</p>
                        </div>
                        <a href="#" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700 sm:w-auto">+ Input Sprindik</a>
                    </div>
                @elseif ($laporan['status'] === 'Sprindik Tersedia')
                    <div id="perkara" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Sprindik tersedia</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Laporan dapat dilanjutkan ke pembentukan perkara.</p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]">Lihat Sprindik</button>
                            <a href="#" class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700">Buat Perkara</a>
                        </div>
                    </div>
                @else
                    <div id="perkara" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Perkara sudah terbentuk</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Laporan ini sudah terhubung dengan data perkara.</p>
                        </div>
                        <a href="#" class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700">Lihat Perkara</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
