@props(['data' => null, 'tahun' => null])

@php
    $tahun = (int) ($tahun ?: date('Y'));
    $data ??= app(\App\Services\DashboardService::class)->getMonthlyCasesTrends($tahun);
@endphp

<div class="@container flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- Card Header --}}
    <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800">
        <div>
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Tren Perkara Masuk & Selesai
            </h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Tahun Anggaran {{ $tahun }}
            </p>
        </div>

        {{-- Legenda & Ringkasan Tahunan --}}
        <div class="flex flex-wrap items-center gap-3 sm:gap-4">
            {{-- Perkara Masuk --}}
            <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-brand-500"></span>
                <span>Perkara Masuk:</span>
                <span class="font-semibold tabular-nums text-gray-800 dark:text-white/90">{{ $data['totalIncoming'] }}</span>
            </div>

            {{-- Perkara Selesai --}}
            <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-success-500"></span>
                <span>Perkara Selesai:</span>
                <span class="font-semibold tabular-nums text-gray-800 dark:text-white/90">{{ $data['totalCompleted'] }}</span>
            </div>

            {{-- Rasio Penyelesaian --}}
            <div class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                <span>Rasio:</span>
                <span class="font-semibold tabular-nums">{{ $data['clearanceRate'] }}%</span>
            </div>
        </div>
    </div>

    {{-- Card Body --}}
    <div
        x-data="monthlyCasesChart(@js($data))"
        class="flex flex-1 flex-col justify-center p-5 sm:p-6"
    >
        @if ($data['totalIncoming'] === 0 && $data['totalCompleted'] === 0)
            <div class="mb-3 flex items-center gap-2 rounded-xl border border-gray-100 bg-gray-50/70 p-3 text-xs text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                <i class="fi fi-rr-info shrink-0 text-sm text-gray-400 dark:text-gray-500"></i>
                <span>Belum ada catatan perkara masuk atau selesai yang tercatat pada tahun anggaran {{ $tahun }}.</span>
            </div>
        @endif

        <div x-ref="chartContainer" class="min-h-[200px] w-full"></div>
    </div>

</div>
