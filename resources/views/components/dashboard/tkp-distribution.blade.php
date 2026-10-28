@props(['data' => null, 'tahun' => null])

@php
    $tahun = (int) ($tahun ?: date('Y'));
    $data ??= app(\App\Services\DashboardService::class)->getTkpDistribution($tahun);
    $regions = $data['regions'] ?? [];
    $offlineCount = (int) ($data['offlineCount'] ?? 0);
    $onlineCount = (int) ($data['onlineCount'] ?? 0);
    $offlinePercent = (int) ($data['offlinePercent'] ?? 0);
    $onlinePercent = (int) ($data['onlinePercent'] ?? 0);
@endphp

<div class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    {{-- Card Header --}}
    <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800">
        <div>
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Sebaran Wilayah TKP & Modus Peredaran
            </h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                5 Kabupaten/Kota tertinggi pada Tahun Anggaran {{ $tahun }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                <i class="fi fi-rr-shop text-xs"></i>
                <span>Offline:</span>
                <span class="font-semibold tabular-nums">{{ $offlinePercent }}% ({{ $offlineCount }})</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-50 px-2.5 py-1 font-medium text-purple-700 dark:bg-purple-500/15 dark:text-purple-300">
                <i class="fi fi-rr-globe text-xs"></i>
                <span>Online:</span>
                <span class="font-semibold tabular-nums">{{ $onlinePercent }}% ({{ $onlineCount }})</span>
            </span>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="flex-1 p-5 sm:p-6">
        @if (count($regions) > 0)
            <div class="space-y-3.5">
                @foreach ($regions as $idx => $region)
                    @php
                        $scale = max(0, min(1, ($region['percent'] ?? 0) / 100));
                    @endphp
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-2 text-xs">
                            <span class="font-medium text-gray-700 dark:text-gray-200">
                                <span class="tabular-nums text-gray-400 dark:text-gray-500">{{ $idx + 1 }}.</span>
                                {{ $region['name'] }}
                            </span>
                            <span class="font-semibold tabular-nums text-gray-800 dark:text-white/90">
                                {{ $region['count'] }} Perkara
                                <span class="font-normal text-gray-400 dark:text-gray-500">({{ $region['percent'] }}%)</span>
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div
                                class="h-full w-full origin-left rounded-full bg-brand-500 transition-transform duration-300"
                                style="transform: scaleX({{ $scale }});"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-center px-4">
                <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                    <i class="fi fi-rr-marker text-lg"></i>
                </div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Belum ada data wilayah TKP
                </p>
                <p class="mt-1 max-w-xs text-xs text-gray-400 dark:text-gray-500">
                    Sebaran Kabupaten/Kota dan modus peredaran akan tampil setelah lokasi TKP perkara tercatat pada tahun {{ $tahun }}.
                </p>
            </div>
        @endif
    </div>

    <div class="border-t border-gray-200 px-5 py-3 sm:px-6 dark:border-gray-800">
        <p class="text-xs text-gray-400 dark:text-gray-500">
            Catatan: Berdasarkan lokasi TKP dan modus operandi pada Laporan Kejadian.
        </p>
    </div>
</div>
