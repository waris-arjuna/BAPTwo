@props(['data', 'tahun' => null, 'bulan' => null])

@php
    $totalPerkara = (int) ($data['totalPerkara'] ?? 0);
    $selesai = (int) ($data['selesai'] ?? 0);
    $dalamProses = (int) ($data['dalamProses'] ?? 0);
    $tidakSelesai = (int) ($data['tidakSelesai'] ?? 0);

    $selesaiPercent = $data['selesaiPercent'] ?? 0;
    $dalamProsesPercent = $data['dalamProsesPercent'] ?? 0;
    $tidakSelesaiPercent = $data['tidakSelesaiPercent'] ?? 0;

    $sla = $data['sla'] ?? [
        'belumSpdp' => 0,
        'dalamSla' => 0,
        'mendekatiSla' => 0,
        'lewatSla' => 0,
    ];

    $comparison = $data['comparison'] ?? [
        'direction' => 'none',
        'text' => '-',
        'diff' => 0,
    ];

    $drillUrl = function ($status = null, $slaKey = null) use ($data) {
        $params = [];
        if (!empty($data['tahun'])) $params['tahun'] = $data['tahun'];
        if (!empty($data['bulan'])) $params['bulan'] = $data['bulan'];
        if ($status) $params['status'] = $status;
        if ($slaKey) $params['sla'] = $slaKey;
        return route('seluruh-perkara.index', $params);
    };

    // Format perbandingan tahun lalu netral di bawah donut
    $diff = $comparison['diff'] ?? 0;
    $prevYear = ($tahun ?: (int) date('Y')) - 1;
    $totalPrev = $totalPerkara - $diff;
    $pctDiff = ($totalPrev > 0 && $diff !== 0) ? round((abs($diff) / $totalPrev) * 100) : null;

    if ($diff > 0) {
        $comparisonText = '▲ ' . $diff . ($pctDiff !== null ? " (+{$pctDiff}%)" : '') . " vs {$prevYear}";
    } elseif ($diff < 0) {
        $comparisonText = '▼ ' . abs($diff) . ($pctDiff !== null ? " (-{$pctDiff}%)" : '') . " vs {$prevYear}";
    } else {
        $comparisonText = "0 vs {$prevYear}";
    }

    // Geometri Donut SVG 6 Segmen
    $r = 54;
    $C = 2 * pi() * $r; // ≈ 339.292

    $categories = [
        [
            'key' => 'selesai',
            'label' => 'Selesai',
            'count' => $selesai,
            'strokeClass' => 'stroke-success-500',
            'drill' => $drillUrl('Selesai'),
        ],
        [
            'key' => 'belum_spdp',
            'label' => 'Belum Ada SPDP',
            'count' => (int) ($sla['belumSpdp'] ?? 0),
            'strokeClass' => 'stroke-brand-400',
            'drill' => $drillUrl('Dalam Proses', 'belum_spdp'),
        ],
        [
            'key' => 'dalam_sla',
            'label' => 'Dalam SLA',
            'count' => (int) ($sla['dalamSla'] ?? 0),
            'strokeClass' => 'stroke-teal-500',
            'drill' => $drillUrl('Dalam Proses', 'dalam_sla'),
        ],
        [
            'key' => 'mendekati_sla',
            'label' => 'Mendekati Batas',
            'count' => (int) ($sla['mendekatiSla'] ?? 0),
            'strokeClass' => 'stroke-amber-500',
            'drill' => $drillUrl('Dalam Proses', 'mendekati_sla'),
        ],
        [
            'key' => 'lewat_sla',
            'label' => 'Melewati SLA',
            'count' => (int) ($sla['lewatSla'] ?? 0),
            'strokeClass' => 'stroke-error-500',
            'drill' => $drillUrl('Dalam Proses', 'lewat_sla'),
        ],
        [
            'key' => 'tidak_selesai',
            'label' => 'Tidak Selesai (SP3)',
            'count' => $tidakSelesai,
            'strokeClass' => 'stroke-gray-400 dark:stroke-gray-600',
            'drill' => $drillUrl('Tidak Selesai'),
        ],
    ];

    $activeCategories = array_values(array_filter($categories, fn ($cat) => $cat['count'] > 0));
    $activeCount = count($activeCategories);

    $segments = [];
    $accumulatedOffset = 0;

    foreach ($activeCategories as $cat) {
        $percent = $totalPerkara > 0 ? round(($cat['count'] / $totalPerkara) * 100) : 0;
        $rawArc = $totalPerkara > 0 ? ($cat['count'] / $totalPerkara) * $C : 0;

        // Gap 2px jika lebih dari 1 segmen aktif, panjang busur minimum 4px
        $arcLength = $activeCount > 1 ? max(4, $rawArc - 2) : $rawArc;
        $dashArray = sprintf('%.3f %.3f', $arcLength, $C - $arcLength);
        $dashOffset = sprintf('%.3f', -$accumulatedOffset);

        $segments[] = [
            'label' => $cat['label'],
            'count' => $cat['count'],
            'percent' => $percent,
            'strokeClass' => $cat['strokeClass'],
            'dashArray' => $dashArray,
            'dashOffset' => $dashOffset,
            'drill' => $cat['drill'],
        ];

        $accumulatedOffset += $rawArc;
    }
@endphp

@if ($totalPerkara === 0)
    {{-- State Total = 0: Ring Abu Utuh 136px & Pesan Informatif --}}
    <div class="grid grid-cols-1 @[600px]:grid-cols-[150px_1fr] gap-6 items-center py-2">
        {{-- Ring Abu Utuh 136px --}}
        <div class="flex flex-col items-center justify-center">
            <div class="relative flex h-[136px] w-[136px] items-center justify-center">
                <svg class="h-full w-full" viewBox="0 0 140 140" role="img" aria-label="Belum ada perkara">
                    <circle cx="70" cy="70" r="54" fill="none" class="stroke-gray-100 dark:stroke-gray-800" stroke-width="16" />
                </svg>
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center p-2">
                    <span class="text-2xl font-semibold tracking-tight tabular-nums text-gray-400 dark:text-gray-500">0</span>
                    <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500">Total Perkara</span>
                </div>
            </div>
            <p class="mt-2 text-center text-xs text-gray-400 dark:text-gray-500 tabular-nums">
                0 vs {{ $prevYear }}
            </p>
        </div>

        {{-- Pesan Informatif --}}
        <div class="flex flex-col items-start justify-center text-start">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500 mb-2">
                <i class="fi fi-rr-folder-open text-lg"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                Belum ada perkara pada periode ini
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Silakan pilih tahun atau bulan lain pada filter di atas untuk melihat data penanganan perkara.
            </p>
        </div>
    </div>
@else
    {{-- Container Grid 2 Kolom (< 600px 1 Kolom, >= 600px 2 Kolom) --}}
    <div class="grid grid-cols-1 @[600px]:grid-cols-[150px_1fr] gap-6 items-center">

        {{-- Kolom Kiri: Donut SVG 136px & Teks Perbandingan Tahun Lalu --}}
        <div x-data="{ hovered: null }" class="flex flex-col items-center justify-center shrink-0">
            <div class="relative flex h-[136px] w-[136px] items-center justify-center">

                {{-- Floating Tooltip Alpine.js --}}
                <div
                    x-show="hovered"
                    x-cloak
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="pointer-events-none absolute -top-8 z-20 whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1 text-xs text-white shadow-md dark:bg-gray-100 dark:text-gray-900"
                >
                    <span class="font-medium" x-text="hovered ? hovered.label : ''"></span>:
                    <span class="font-bold tabular-nums" x-text="hovered ? hovered.count : ''"></span>
                    <span class="text-[10px] opacity-80" x-text="hovered ? '(' + hovered.percent + '%)' : ''"></span>
                </div>

                {{-- Pure SVG Donut --}}
                <svg class="h-full w-full -rotate-90" viewBox="0 0 140 140" role="img" aria-label="Grafik proporsi status perkara tahun berjalan">
                    {{-- Background Ring --}}
                    <circle
                        cx="70" cy="70" r="54" fill="none"
                        class="stroke-gray-100 dark:stroke-gray-800"
                        stroke-width="16"
                    />

                    {{-- 6 Segmen SVG Dinamis --}}
                    @foreach ($segments as $seg)
                        <circle
                            cx="70" cy="70" r="54" fill="none"
                            class="cursor-pointer {{ $seg['strokeClass'] }} transition-opacity duration-150 hover:opacity-85 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            stroke-width="16"
                            stroke-dasharray="{{ $seg['dashArray'] }}"
                            stroke-dashoffset="{{ $seg['dashOffset'] }}"
                            role="button"
                            tabindex="0"
                            aria-label="{{ $seg['label'] }}: {{ $seg['count'] }} perkara ({{ $seg['percent'] }}%)"
                            @click="window.location.href = '{{ $seg['drill'] }}'"
                            @keydown.enter="window.location.href = '{{ $seg['drill'] }}'"
                            @mouseenter="hovered = { label: '{{ $seg['label'] }}', count: {{ $seg['count'] }}, percent: {{ $seg['percent'] }} }"
                            @mouseleave="hovered = null"
                            @focus="hovered = { label: '{{ $seg['label'] }}', count: {{ $seg['count'] }}, percent: {{ $seg['percent'] }} }"
                            @blur="hovered = null"
                        >
                            <title>{{ $seg['label'] }}: {{ $seg['count'] }} perkara ({{ $seg['percent'] }}%)</title>
                        </circle>
                    @endforeach
                </svg>

                {{-- Tengah Donut: Total Perkara (26px) & Label (11px) --}}
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center p-2">
                    <span class="text-2xl font-semibold tracking-tight tabular-nums text-gray-800 dark:text-white/90">
                        {{ $totalPerkara }}
                    </span>
                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">
                        Total Perkara
                    </span>
                </div>
            </div>

            {{-- Teks Perbandingan Tahun Lalu Netral di Bawah Donut --}}
            <p class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400 tabular-nums">
                {{ $comparisonText }}
            </p>

            {{-- Ringkasan Aksesibilitas Screen Reader --}}
            <div class="sr-only">
                Ringkasan status perkara: Selesai {{ $selesai }} perkara ({{ $selesaiPercent }}%), Dalam Proses {{ $dalamProses }} perkara ({{ $dalamProsesPercent }}%), Tidak Selesai {{ $tidakSelesai }} perkara ({{ $tidakSelesaiPercent }}%).
            </div>
        </div>

        {{-- Kolom Kanan: Daftar Status dengan Legenda Dot 10px --}}
        <div class="space-y-2.5 min-w-0 flex-1">

            {{-- 1. Baris Selesai --}}
            <a
                href="{{ $drillUrl('Selesai') }}"
                class="group flex items-center justify-between rounded-lg p-1.5 transition hover:bg-gray-50 dark:hover:bg-white/[0.03] focus:outline-none focus:ring-1 focus:ring-brand-500"
                title="Lihat perkara Selesai"
            >
                <div class="flex items-center gap-2.5">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-success-500"></span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white">
                        Selesai
                    </span>
                </div>
                <span class="text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90">
                    {{ $selesai }} <span class="text-xs font-normal text-gray-400 dark:text-gray-500">({{ $selesaiPercent }}%)</span>
                </span>
            </a>

            {{-- 2. Panel Surface Level 1: Dalam Proses --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-white/[0.02]">
                <a
                    href="{{ $drillUrl('Dalam Proses') }}"
                    class="group flex items-center justify-between rounded-lg p-0.5 transition hover:opacity-85 focus:outline-none focus:ring-1 focus:ring-brand-500"
                    title="Lihat perkara Dalam Proses"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-brand-500"></span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white/90">
                            Dalam Proses
                        </span>
                    </div>
                    <span class="text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90">
                        {{ $dalamProses }} <span class="text-xs font-normal text-gray-400 dark:text-gray-500">({{ $dalamProsesPercent }}%)</span>
                    </span>
                </a>

                {{-- Hairline separator --}}
                <div class="my-2 border-t border-gray-200/60 dark:border-gray-800/80"></div>

                {{-- 4 Sub-baris SLA --}}
                <div class="space-y-1.5 ps-3">
                    {{-- Sub-baris 1: Belum Ada SPDP (Pra-SPDP) --}}
                    @php $cBelum = $sla['belumSpdp'] ?? 0; @endphp
                    <a
                        href="{{ $drillUrl('Dalam Proses', 'belum_spdp') }}"
                        class="group flex items-center justify-between rounded-md px-1.5 py-0.5 text-xs transition hover:bg-white/80 dark:hover:bg-white/[0.04] focus:outline-none focus:ring-1 focus:ring-brand-500"
                    >
                        <div class="flex items-center gap-2">
                            @if ($cBelum > 0)
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-brand-400"></span>
                                <span class="font-medium text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white">
                                    Belum Ada SPDP (Pra-SPDP)
                                </span>
                            @else
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full border border-gray-300 bg-transparent dark:border-gray-600"></span>
                                <span class="font-normal text-gray-400 dark:text-gray-500">
                                    Belum Ada SPDP (Pra-SPDP)
                                </span>
                            @endif
                        </div>
                        <span class="tabular-nums {{ $cBelum > 0 ? 'font-medium text-gray-700 dark:text-gray-300' : 'font-normal text-gray-400 dark:text-gray-500' }}">
                            {{ $cBelum }}
                        </span>
                    </a>

                    {{-- Sub-baris 2: Dalam SLA (> 7 hari) --}}
                    @php $cDalam = $sla['dalamSla'] ?? 0; @endphp
                    <a
                        href="{{ $drillUrl('Dalam Proses', 'dalam_sla') }}"
                        class="group flex items-center justify-between rounded-md px-1.5 py-0.5 text-xs transition hover:bg-white/80 dark:hover:bg-white/[0.04] focus:outline-none focus:ring-1 focus:ring-brand-500"
                    >
                        <div class="flex items-center gap-2">
                            @if ($cDalam > 0)
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-teal-500"></span>
                                <span class="font-medium text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white">
                                    Dalam SLA (> 7 hari)
                                </span>
                            @else
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full border border-gray-300 bg-transparent dark:border-gray-600"></span>
                                <span class="font-normal text-gray-400 dark:text-gray-500">
                                    Dalam SLA (> 7 hari)
                                </span>
                            @endif
                        </div>
                        <span class="tabular-nums {{ $cDalam > 0 ? 'font-medium text-gray-700 dark:text-gray-300' : 'font-normal text-gray-400 dark:text-gray-500' }}">
                            {{ $cDalam }}
                        </span>
                    </a>

                    {{-- Sub-baris 3: Mendekati Batas (≤ 7 hari) --}}
                    @php $cMendekati = $sla['mendekatiSla'] ?? 0; @endphp
                    <a
                        href="{{ $drillUrl('Dalam Proses', 'mendekati_sla') }}"
                        class="group flex items-center justify-between rounded-md px-1.5 py-0.5 text-xs transition hover:bg-white/80 dark:hover:bg-white/[0.04] focus:outline-none focus:ring-1 focus:ring-brand-500"
                    >
                        <div class="flex items-center gap-2">
                            @if ($cMendekati > 0)
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
                                <span class="font-medium text-amber-600 dark:text-amber-400">
                                    Mendekati Batas (≤ 7 hari)
                                </span>
                            @else
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full border border-gray-300 bg-transparent dark:border-gray-600"></span>
                                <span class="font-normal text-gray-400 dark:text-gray-500">
                                    Mendekati Batas (≤ 7 hari)
                                </span>
                            @endif
                        </div>
                        <span class="tabular-nums {{ $cMendekati > 0 ? 'font-semibold text-amber-600 dark:text-amber-400' : 'font-normal text-gray-400 dark:text-gray-500' }}">
                            {{ $cMendekati }}
                        </span>
                    </a>

                    {{-- Sub-baris 4: Melewati SLA --}}
                    @php $cLewat = $sla['lewatSla'] ?? 0; @endphp
                    <a
                        href="{{ $drillUrl('Dalam Proses', 'lewat_sla') }}"
                        class="group flex items-center justify-between rounded-md px-1.5 py-0.5 text-xs transition hover:bg-white/80 dark:hover:bg-white/[0.04] focus:outline-none focus:ring-1 focus:ring-brand-500"
                    >
                        <div class="flex items-center gap-2">
                            @if ($cLewat > 0)
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-error-500"></span>
                                <span class="font-medium text-error-600 dark:text-error-400">
                                    Melewati SLA
                                </span>
                            @else
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full border border-gray-300 bg-transparent dark:border-gray-600"></span>
                                <span class="font-normal text-gray-400 dark:text-gray-500">
                                    Melewati SLA
                                </span>
                            @endif
                        </div>
                        @if ($cLewat > 0)
                            <span class="inline-flex items-center justify-center rounded-full bg-error-50 px-2 py-0.5 text-[11px] font-bold tabular-nums text-error-600 dark:bg-error-900/30 dark:text-error-400">
                                {{ $cLewat }}
                            </span>
                        @else
                            <span class="font-normal tabular-nums text-gray-400 dark:text-gray-500">
                                0
                            </span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- 3. Baris Tidak Selesai (SP3) --}}
            <a
                href="{{ $drillUrl('Tidak Selesai') }}"
                class="group flex items-center justify-between rounded-lg p-1.5 transition hover:bg-gray-50 dark:hover:bg-white/[0.03] focus:outline-none focus:ring-1 focus:ring-brand-500"
                title="Lihat perkara Tidak Selesai (SP3 / Kedaluwarsa)"
            >
                <div class="flex items-center gap-2.5">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white">
                        Tidak Selesai (SP3)
                    </span>
                </div>
                <span class="text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90">
                    {{ $tidakSelesai }} <span class="text-xs font-normal text-gray-400 dark:text-gray-500">({{ $tidakSelesaiPercent }}%)</span>
                </span>
            </a>
        </div>
    </div>
@endif
