@props(['stageAverages' => null])

@php
    $stageAverages ??= app(\App\Services\DashboardService::class)->getStageAverageDays();
@endphp

<div class="h-full rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

    <div class="border-b border-gray-200 px-5 py-5 sm:px-6 dark:border-gray-800">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Rata-rata Penyelesaian per Tahap
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tahun Berjalan 2026
                </p>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400">
                <i class="fi fi-rr-clock text-lg"></i>
            </div>
        </div>

    </div>

    <div class="divide-y divide-gray-200 dark:divide-gray-800">

        @foreach ($stageAverages as $stage)

            <div class="flex items-center justify-between px-5 py-4 sm:px-6">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-50 text-gray-500 dark:bg-white/[0.03] dark:text-gray-400">
                        <i class="fi fi-rr-chart-histogram text-base"></i>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $stage['name'] }}
                        </p>

                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            Rata-rata waktu penyelesaian
                        </p>
                    </div>

                </div>

                <div class="text-right">
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ $stage['value'] }}
                    </p>

                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Hari Kerja
                    </p>
                </div>

            </div>

        @endforeach

    </div>

    <div class="border-t border-gray-200 px-5 py-4 sm:px-6 dark:border-gray-800">

        <p class="text-xs leading-5 text-gray-400 dark:text-gray-500">
            Catatan: Perhitungan waktu penyelesaian tahapan menggunakan Hari Kerja.
        </p>

    </div>

</div>