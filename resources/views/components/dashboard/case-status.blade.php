@props(['data' => null])

@php
    $data ??= app(\App\Services\DashboardService::class)->getCaseStatusData();

    $totalPerkara = $data['totalPerkara'] ?? 0;
    $selesai = $data['selesai'] ?? 0;
    $dalamProses = $data['dalamProses'] ?? 0;
    $tidakSelesai = $data['tidakSelesai'] ?? 0;

    $selesaiPercent = $data['selesaiPercent'] ?? 0;
    $dalamProsesPercent = $data['dalamProsesPercent'] ?? 0;
    $tidakSelesaiPercent = $data['tidakSelesaiPercent'] ?? 0;

    $circumference = 2 * pi() * 48;

    $dalamProsesLength = ($dalamProsesPercent / 100) * $circumference;
    $selesaiLength = ($selesaiPercent / 100) * $circumference;
    $tidakSelesaiLength = ($tidakSelesaiPercent / 100) * $circumference;

    // offset kumulatif, bukan cuma offset segmen sebelumnya
    $selesaiOffset = $dalamProsesLength;
    $tidakSelesaiOffset = $dalamProsesLength + $selesaiLength;
@endphp

<div class="h-full rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

    <div class="border-b border-gray-200 px-5 py-5 sm:px-6 dark:border-gray-800">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Perkara Tahun Berjalan 2026
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Ringkasan monitoring perkara.
        </p>
    </div>

    <div class="p-5 sm:p-6">

        {{-- Donut --}}
        <div class="flex justify-center">
            <div class="relative h-52 w-52">
                <svg class="h-full w-full -rotate-90" viewBox="0 0 120 120">
                    {{-- Background --}}
                    <circle
                        cx="60" cy="60" r="48" fill="none"
                        class="stroke-gray-100 dark:stroke-gray-800"
                        stroke-width="14"
                    />

                    {{-- Dalam Proses --}}
                    @if ($dalamProsesPercent > 0)
                        <circle
                            cx="60" cy="60" r="48" fill="none"
                            class="stroke-brand-500"
                            stroke-width="14"
                            stroke-dasharray="{{ $dalamProsesLength }} {{ $circumference }}"
                            stroke-dashoffset="0"
                        />
                    @endif

                    {{-- Selesai --}}
                    @if ($selesaiPercent > 0)
                        <circle
                            cx="60" cy="60" r="48" fill="none"
                            class="stroke-success-500"
                            stroke-width="14"
                            stroke-dasharray="{{ $selesaiLength }} {{ $circumference }}"
                            stroke-dashoffset="-{{ $selesaiOffset }}"
                        />
                    @endif

                    {{-- Tidak Selesai --}}
                    @if ($tidakSelesaiPercent > 0)
                        <circle
                            cx="60" cy="60" r="48" fill="none"
                            class="stroke-error-500"
                            stroke-width="14"
                            stroke-dasharray="{{ $tidakSelesaiLength }} {{ $circumference }}"
                            stroke-dashoffset="-{{ $tidakSelesaiOffset }}"
                        />
                    @endif
                </svg>

                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <p class="text-3xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $totalPerkara }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Total Perkara
                    </p>
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="mt-6 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-success-500"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Selesai</span>
                </div>
                <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                    {{ $selesai }} ({{ $selesaiPercent }}%)
                </span>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-brand-500"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Dalam Proses</span>
                </div>
                <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                    {{ $dalamProses }} ({{ $dalamProsesPercent }}%)
                </span>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-error-500"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Tidak Selesai</span>
                </div>
                <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                    {{ $tidakSelesai }} ({{ $tidakSelesaiPercent }}%)
                </span>
            </div>
        </div>

        <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-800">
            <p class="text-xs leading-5 text-gray-400 dark:text-gray-500">
                Perhitungan persentase berdasarkan total perkara pada Tahun Berjalan.
                "Dalam Proses" dan "Belum Dimulai" merupakan kategori visual monitoring dashboard,
                bukan status resmi perkara.
            </p>
        </div>

    </div>

</div>