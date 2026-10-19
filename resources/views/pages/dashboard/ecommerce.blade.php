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
                    Sistem Pemantauan dan Pelacakan Penanganan Perkara BPOM Kalimantan Selatan
                </p>
            </div>

            <div class="flex items-center gap-3">
                <label for="tahun" class="sr-only">Pilih Tahun</label>
                <i class="fi fi-rr-calendar text-base text-gray-500 dark:text-gray-400" aria-hidden="true"></i>
                <select
                    id="tahun"
                    onchange="const u = new URL(window.location.href); u.searchParams.set('tahun', this.value); window.location.href = u.toString();"
                    class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="2026" {{ ($tahun ?? date('Y')) == 2026 ? 'selected' : '' }}>
                        Tahun Berjalan (2026)
                    </option>

                    <option value="2025" {{ ($tahun ?? date('Y')) == 2025 ? 'selected' : '' }}>
                        2025
                    </option>

                    <option value="2024" {{ ($tahun ?? date('Y')) == 2024 ? 'selected' : '' }}>
                        2024
                    </option>
                </select>
            </div>

        </div>

        {{-- Statistics --}}
        <x-dashboard.statistics :statistics="$statistics ?? null" :tahun="$tahun ?? date('Y')" />

        {{-- Visual Statistics (Grafik & Status) --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:gap-6">

            <div class="lg:col-span-7">
                <x-dashboard.average-stage :stageAverages="$stageAverages ?? null" :tahun="$tahun ?? date('Y')" />
            </div>

            <div class="lg:col-span-5">
                <x-dashboard.case-status :data="$caseStatus ?? null" :tahun="$tahun ?? date('Y')" />
            </div>

        </div>

        {{-- Progress Perkara Table (Paling Bawah - Poin 6) --}}
        <x-dashboard.progress-perkara :perkaraList="$perkaraList ?? null" />

    </div>
@endsection