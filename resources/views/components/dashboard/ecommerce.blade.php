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
                {{-- Filter Tahun --}}
                <button
                    type="button"
                    class="inline-flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                >
                    <span class="flex items-center gap-2">
                        <i class="fi fi-rr-calendar text-base"></i>

                        Tahun Berjalan (2026)
                    </span>

                    <i class="fi fi-rr-angle-small-down text-base"></i>
                </button>
            </div>
        </div>

        {{-- Statistics --}}
        <x-dashboard.statistics />
        
        {{-- Bottom Section --}}
        <div class="grid grid-cols-12 gap-4 items-center md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <x-dashboard.average-stage />
            </div>

            <div class="col-span-12 xl:col-span-5">
                <x-dashboard.case-status />
            </div>
        </div>

        {{-- Progress Perkara --}}
        <x-dashboard.progress-perkara />

    </div>
@endsection