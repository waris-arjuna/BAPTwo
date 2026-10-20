@props(['data', 'tahun' => null, 'bulan' => null])

@php
    $totalPerkara = $data['totalPerkara'] ?? 0;
    $selesai = $data['selesai'] ?? 0;
    $dalamProses = $data['dalamProses'] ?? 0;
    $tidakSelesai = $data['tidakSelesai'] ?? 0;

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

    $circumference = 2 * pi() * 48;
    $dalamProsesLength = ($dalamProsesPercent / 100) * $circumference;
    $selesaiLength = ($selesaiPercent / 100) * $circumference;
    $tidakSelesaiLength = ($tidakSelesaiPercent / 100) * $circumference;

    $selesaiOffset = $dalamProsesLength;
    $tidakSelesaiOffset = $dalamProsesLength + $selesaiLength;

    $drillUrl = function ($status = null, $slaKey = null) use ($data) {
        $params = [];
        if (!empty($data['tahun'])) $params['tahun'] = $data['tahun'];
        if (!empty($data['bulan'])) $params['bulan'] = $data['bulan'];
        if ($status) $params['status'] = $status;
        if ($slaKey) $params['sla'] = $slaKey;
        return route('seluruh-perkara.index', $params);
    };
@endphp

@if ($totalPerkara === 0)
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
            <i class="fi fi-rr-folder-open text-2xl"></i>
        </div>
        <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">
            Belum ada perkara pada periode ini
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Silakan pilih tahun atau bulan lain untuk melihat data perkara.
        </p>
    </div>
@else
    {{-- Donut Section --}}
    <div x-data="{ hovered: null }" class="flex flex-col items-center justify-center">
        <div class="relative h-56 w-56">
            <svg class="h-full w-full -rotate-90" viewBox="0 0 120 120" role="img" aria-label="Grafik proporsi status perkara">
                {{-- Background Ring --}}
                <circle
                    cx="60" cy="60" r="48" fill="none"
                    class="stroke-gray-100 dark:stroke-gray-800"
                    stroke-width="14"
                />

                {{-- Segmen Dalam Proses (Biru) --}}
                @if ($dalamProsesPercent > 0)
                    <circle
                        cx="60" cy="60" r="48" fill="none"
                        class="cursor-pointer stroke-brand-500 transition-all duration-200 hover:opacity-85 focus:outline-none"
                        stroke-width="14"
                        stroke-dasharray="{{ $dalamProsesLength }} {{ $circumference }}"
                        stroke-dashoffset="0"
                        role="button"
                        tabindex="0"
                        aria-label="Dalam Proses: {{ $dalamProses }} perkara ({{ $dalamProsesPercent }}%)"
                        @click="window.location.href = '{{ $drillUrl('Dalam Proses') }}'"
                        @keydown.enter="window.location.href = '{{ $drillUrl('Dalam Proses') }}'"
                        @mouseenter="hovered = { label: 'Dalam Proses', count: {{ $dalamProses }}, percent: {{ $dalamProsesPercent }} }"
                        @mouseleave="hovered = null"
                    >
                        <title>Dalam Proses: {{ $dalamProses }} perkara ({{ $dalamProsesPercent }}%)</title>
                    </circle>
                @endif

                {{-- Segmen Selesai (Hijau) --}}
                @if ($selesaiPercent > 0)
                    <circle
                        cx="60" cy="60" r="48" fill="none"
                        class="cursor-pointer stroke-success-500 transition-all duration-200 hover:opacity-85 focus:outline-none"
                        stroke-width="14"
                        stroke-dasharray="{{ $selesaiLength }} {{ $circumference }}"
                        stroke-dashoffset="-{{ $selesaiOffset }}"
                        role="button"
                        tabindex="0"
                        aria-label="Selesai: {{ $selesai }} perkara ({{ $selesaiPercent }}%)"
                        @click="window.location.href = '{{ $drillUrl('Selesai') }}'"
                        @keydown.enter="window.location.href = '{{ $drillUrl('Selesai') }}'"
                        @mouseenter="hovered = { label: 'Selesai', count: {{ $selesai }}, percent: {{ $selesaiPercent }} }"
                        @mouseleave="hovered = null"
                    >
                        <title>Selesai: {{ $selesai }} perkara ({{ $selesaiPercent }}%)</title>
                    </circle>
                @endif

                {{-- Segmen Tidak Selesai (Abu-abu Netral) --}}
                @if ($tidakSelesaiPercent > 0)
                    <circle
                        cx="60" cy="60" r="48" fill="none"
                        class="cursor-pointer stroke-gray-400 transition-all duration-200 hover:opacity-85 focus:outline-none dark:stroke-gray-500"
                        stroke-width="14"
                        stroke-dasharray="{{ $tidakSelesaiLength }} {{ $circumference }}"
                        stroke-dashoffset="-{{ $tidakSelesaiOffset }}"
                        role="button"
                        tabindex="0"
                        aria-label="Tidak Selesai: {{ $tidakSelesai }} perkara ({{ $tidakSelesaiPercent }}%)"
                        @click="window.location.href = '{{ $drillUrl('Tidak Selesai') }}'"
                        @keydown.enter="window.location.href = '{{ $drillUrl('Tidak Selesai') }}'"
                        @mouseenter="hovered = { label: 'Tidak Selesai', count: {{ $tidakSelesai }}, percent: {{ $tidakSelesaiPercent }} }"
                        @mouseleave="hovered = null"
                    >
                        <title>Tidak Selesai: {{ $tidakSelesai }} perkara ({{ $tidakSelesaiPercent }}%)</title>
                    </circle>
                @endif
            </svg>

            {{-- Tengah Donut: Default Total atau Hover Detail --}}
            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center p-2">
                <template x-if="hovered">
                    <div class="flex flex-col items-center justify-center transition-all">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" x-text="hovered.label"></span>
                        <span class="text-2xl font-bold tracking-tight tabular-nums text-gray-800 dark:text-white/90" x-text="hovered.count + ' (' + hovered.percent + '%)'"></span>
                        <span class="mt-0.5 text-[10px] font-medium text-brand-600 dark:text-brand-400">Klik segmen untuk filter</span>
                    </div>
                </template>
                <template x-if="!hovered">
                    <div class="flex flex-col items-center justify-center transition-all">
                        <span class="text-3xl font-bold tracking-tight tabular-nums text-gray-800 dark:text-white/90">
                            {{ $totalPerkara }}
                        </span>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            Total Perkara
                        </span>
                        @if ($comparison['direction'] !== 'none')
                            <span class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-theme-xs font-medium tabular-nums {{ $comparison['direction'] === 'up' ? 'bg-success-50 text-success-700 dark:bg-success-900/20 dark:text-success-400' : ($comparison['direction'] === 'down' ? 'bg-error-50 text-error-700 dark:bg-error-900/20 dark:text-error-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                                {{ $comparison['text'] }}
                            </span>
                        @endif
                    </div>
                </template>
            </div>
        </div>

        {{-- Ringkasan Screen Reader --}}
        <div class="sr-only">
            Ringkasan status perkara: Selesai {{ $selesai }} perkara ({{ $selesaiPercent }}%), Dalam Proses {{ $dalamProses }} perkara ({{ $dalamProsesPercent }}%), Tidak Selesai {{ $tidakSelesai }} perkara ({{ $tidakSelesaiPercent }}%).
        </div>
    </div>

    {{-- Legend & Drill-down Section --}}
    <div class="mt-6 space-y-3">
        {{-- Selesai --}}
        <a
            href="{{ $drillUrl('Selesai') }}"
            class="group flex items-center justify-between rounded-xl border border-transparent p-2.5 transition hover:border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:hover:border-gray-800 dark:hover:bg-white/[0.03]"
            title="Klik untuk melihat perkara berstatus Selesai"
        >
            <div class="flex items-center gap-2.5">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-900/30 dark:text-success-400">
                    <i class="fi fi-rr-check text-xs"></i>
                </span>
                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white">
                    Selesai
                </span>
            </div>
            <span class="text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90">
                {{ $selesai }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $selesaiPercent }}%)</span>
            </span>
        </a>

        {{-- Dalam Proses --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-2.5 dark:border-gray-800/80 dark:bg-white/[0.02]">
            <a
                href="{{ $drillUrl('Dalam Proses') }}"
                class="group flex items-center justify-between rounded-lg p-1 transition hover:bg-white/80 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:hover:bg-white/[0.04]"
                title="Klik untuk melihat perkara Dalam Proses"
            >
                <div class="flex items-center gap-2.5">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                        <i class="fi fi-rr-hourglass-start text-xs"></i>
                    </span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white">
                        Dalam Proses
                    </span>
                </div>
                <span class="text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90">
                    {{ $dalamProses }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $dalamProsesPercent }}%)</span>
                </span>
            </a>

            {{-- 4 Sub-baris SLA --}}
            <div class="mt-2.5 space-y-1.5 border-t border-gray-200/60 pt-2 ps-3 dark:border-gray-800">
                {{-- Belum ada SPDP --}}
                <a
                    href="{{ $drillUrl('Dalam Proses', 'belum_spdp') }}"
                    class="group flex items-center justify-between rounded-lg px-2 py-1 text-xs text-gray-600 transition hover:bg-white hover:text-gray-900 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:text-gray-400 dark:hover:bg-white/[0.04] dark:hover:text-white"
                >
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-document text-gray-400"></i>
                        <span>Belum Ada SPDP (Pra-SPDP)</span>
                    </div>
                    <span class="font-medium tabular-nums text-gray-700 dark:text-gray-300">{{ $sla['belumSpdp'] }}</span>
                </a>

                {{-- Dalam SLA --}}
                <a
                    href="{{ $drillUrl('Dalam Proses', 'dalam_sla') }}"
                    class="group flex items-center justify-between rounded-lg px-2 py-1 text-xs text-gray-600 transition hover:bg-white hover:text-gray-900 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:text-gray-400 dark:hover:bg-white/[0.04] dark:hover:text-white"
                >
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-clock text-blue-500"></i>
                        <span>Dalam SLA (&gt; 7 hari)</span>
                    </div>
                    <span class="font-medium tabular-nums text-gray-700 dark:text-gray-300">{{ $sla['dalamSla'] }}</span>
                </a>

                {{-- Mendekati Batas --}}
                <a
                    href="{{ $drillUrl('Dalam Proses', 'mendekati_sla') }}"
                    class="group flex items-center justify-between rounded-lg px-2 py-1 text-xs text-gray-600 transition hover:bg-white hover:text-gray-900 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:text-gray-400 dark:hover:bg-white/[0.04] dark:hover:text-white"
                >
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-time-forward text-amber-500"></i>
                        <span>Mendekati Batas (≤ 7 hari)</span>
                    </div>
                    <span class="font-semibold tabular-nums text-amber-600 dark:text-amber-400">{{ $sla['mendekatiSla'] }}</span>
                </a>

                {{-- Melewati SLA --}}
                <a
                    href="{{ $drillUrl('Dalam Proses', 'lewat_sla') }}"
                    class="group flex items-center justify-between rounded-lg px-2 py-1 text-xs text-gray-600 transition hover:bg-white hover:text-gray-900 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:text-gray-400 dark:hover:bg-white/[0.04] dark:hover:text-white"
                >
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-exclamation text-error-500"></i>
                        <span class="font-medium text-error-600 dark:text-error-400">Melewati SLA</span>
                    </div>
                    <span class="inline-flex items-center justify-center rounded-full bg-error-50 px-1.5 py-0.5 text-theme-xs font-bold tabular-nums text-error-600 dark:bg-error-900/30 dark:text-error-400">
                        {{ $sla['lewatSla'] }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Tidak Selesai --}}
        <a
            href="{{ $drillUrl('Tidak Selesai') }}"
            class="group flex items-center justify-between rounded-xl border border-transparent p-2.5 transition hover:border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:hover:border-gray-800 dark:hover:bg-white/[0.03]"
            title="Klik untuk melihat perkara Tidak Selesai (SP3 / Kedaluwarsa)"
        >
            <div class="flex items-center gap-2.5">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <i class="fi fi-rr-folder-download text-xs"></i>
                </span>
                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white">
                    Tidak Selesai (SP3)
                </span>
            </div>
            <span class="text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90">
                {{ $tidakSelesai }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $tidakSelesaiPercent }}%)</span>
            </span>
        </a>
    </div>
@endif
