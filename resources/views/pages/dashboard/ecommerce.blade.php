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
                    onchange="window.location.href = '{{ route('dashboard', [], false) }}?tahun=' + this.value;"
                    class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    @foreach ($availableYears ?? [$tahun] as $y)
                        <option value="{{ $y }}" {{ ($tahun ?? date('Y')) == $y ? 'selected' : '' }}>
                            {{ $y == (int) date('Y') ? "Tahun Berjalan ({$y})" : $y }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- Statistics --}}
        <x-dashboard.statistics :statistics="$statistics ?? null" :tahun="$tahun ?? date('Y')" />

        {{-- Baris 2: Proporsi Status (5 Kolom) & Tren Bulanan ApexCharts (7 Kolom) --}}
        <div class="grid grid-cols-1 items-stretch gap-4 lg:grid-cols-12 lg:gap-6">
            <div class="flex flex-col lg:col-span-5">
                <x-dashboard.case-status :data="$caseStatus ?? null" :tahun="$tahun ?? date('Y')" />
            </div>

            <div class="flex flex-col lg:col-span-7">
                <x-dashboard.monthly-cases-chart :data="$monthlyCases ?? null" :tahun="$tahun ?? date('Y')" />
            </div>
        </div>

        {{-- Baris 3: Tabel Operasional Prioritas (Progress Perkara 5 Prioritas Utama) --}}
        <x-dashboard.progress-perkara :perkaraList="$perkaraList ?? null" />

        {{-- Baris 4: Aktivitas Penyidikan Terkini (7 Kolom) & Rata-rata Penyelesaian per Tahap (5 Kolom) --}}
        <div class="grid grid-cols-1 items-stretch gap-4 lg:grid-cols-12 lg:gap-6">
            <div class="flex flex-col lg:col-span-7">
                <x-dashboard.recent-activities :activities="$recentActivities ?? null" />
            </div>

            <div class="flex flex-col lg:col-span-5">
                <x-dashboard.average-stage :stageAverages="$stageAverages ?? null" :tahun="$tahun ?? date('Y')" />
            </div>
        </div>

    </div>
@endsection