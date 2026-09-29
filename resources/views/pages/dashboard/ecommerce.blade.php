@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Dashboard Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Dashboard BAP TWO
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Berbasis Akselerasi Penyidikan melalui Transformasi Workflow Optimal
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                <button
                    type="button"
                    class="inline-flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                >
                    <span class="flex items-center gap-2">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect x="3" y="4" width="18" height="17" rx="2" />
                            <path d="M16 2v4M8 2v4M3 9h18" />
                        </svg>

                        Tahun Berjalan (2026)
                    </span>

                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Statistics --}}
        <x-dashboard.statistics />

        {{-- Progress Perkara --}}
        <x-dashboard.progress-perkara />

        {{-- ============================================================
            BOTTOM DASHBOARD SECTION
            Desktop : side-by-side
            Mobile  : stacked
            ============================================================ --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:gap-6">

            {{-- ========================================================
                DIV A — Rata-rata Penyelesaian per Tahap
                Desktop : 7/12
                Mobile  : 1 kolom
                ======================================================== --}}
            <div class="col-span-1 md:col-span-7">
                <x-dashboard.average-stage />
            </div>

            {{-- ========================================================
                DIV B — Statistik Perkara Tahun Berjalan
                Desktop : 5/12
                Mobile  : 1 kolom
                ======================================================== --}}
            <div class="col-span-1 md:col-span-5">
                <x-dashboard.case-status />
            </div>
        </div>
    </div>
@endsection