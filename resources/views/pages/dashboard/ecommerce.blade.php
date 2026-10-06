@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Dashboard Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Dashboard BAP TWO
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Berbasis Akselerasi Penyidikan Melalui Transformasi Workflow Optimal
                </p>
            </div>

            <div class="flex items-center gap-3">
                <label
                    for="tahun"
                    class="text-sm font-medium text-gray-600 dark:text-gray-400"
                >
                    Tahun
                </label>

                <select
                    id="tahun"
                    class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="2026">
                        Tahun Berjalan (2026)
                    </option>

                    <option value="2025">
                        2025
                    </option>

                    <option value="2024">
                        2024
                    </option>
                </select>
            </div>

        </div>

        {{-- Statistics --}}
        <x-dashboard.statistics />

        {{-- Progress Perkara --}}
        <x-dashboard.progress-perkara />

        {{-- Bottom Statistics --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:gap-6">

            <div class="lg:col-span-7">
                <x-dashboard.average-stage />
            </div>

            <div class="lg:col-span-5">
                <x-dashboard.case-status />
            </div>

        </div>

    </div>
@endsection