{{-- Riwayat Penanganan Perkara (Case History / Audit Trail - PRD F-31) --}}
@props(['perkara'])

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
    {{-- Header Card --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2">
                <i class="fi fi-rr-time-past text-base text-brand-500"></i>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Riwayat Penanganan Perkara (Case History)</h3>
            </div>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Catatan audit trail kronologis seluruh aktivitas penetapan tahap, DPO, dan dokumen perkara.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">
                Total Aktivitas: {{ $perkara->auditLogs->count() }}
            </span>
        </div>
    </div>

    @if ($perkara->auditLogs->isNotEmpty())
        {{-- Scrollable Container --}}
        <div class="custom-scrollbar max-h-[620px] overflow-y-auto pr-3">
            <div class="relative space-y-6 before:absolute before:bottom-3 before:left-[19px] before:top-3 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-800">
                @foreach ($perkara->auditLogs as $log)
                    @php
                        $meta = $log->action_meta;
                        $details = $log->formatted_details;
                        $isSystem = $log->isSystemAction();
                    @endphp

                    <div class="relative flex items-start gap-4">
                        {{-- Node Icon --}}
                        <div class="relative z-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-white {{ $meta['icon_bg'] }} shadow-xs dark:border-gray-900">
                            <i class="fi {{ $meta['icon'] }} text-base"></i>
                        </div>

                        {{-- Card Isi Log --}}
                        <div class="flex-1 rounded-xl border border-gray-100 bg-gray-50/60 p-4 text-sm transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]">
                            {{-- Header Item: Badge Aksi, Aktor, & Waktu --}}
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Badge Aksi --}}
                                    <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $meta['badge'] }}">
                                        {{ $meta['label'] }}
                                    </span>

                                    {{-- Pembeda Aktor --}}
                                    @if ($isSystem)
                                        <span class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            <i class="fi fi-rr-microchip text-[11px]"></i>
                                            <span>Sistem Otomatis</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md border border-brand-200/60 bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700 dark:border-brand-800/60 dark:bg-brand-900/30 dark:text-brand-300">
                                            <i class="fi fi-rr-user text-[11px]"></i>
                                            <span>{{ $log->actor_label }}</span>
                                            <span class="text-[11px] opacity-75">({{ $log->actor_role_label }})</span>
                                        </span>
                                    @endif

                                    {{-- Chip Konteks Tersangka --}}
                                    @if ($log->berkasPerkara?->tersangka)
                                        <span class="inline-flex items-center gap-1 rounded-md border border-gray-200/60 bg-white px-2 py-0.5 text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-300">
                                            <i class="fi fi-rr-portrait text-[11px] text-gray-400"></i>
                                            <span>Tersangka: <strong>{{ $log->berkasPerkara->tersangka->nama }}</strong></span>
                                        </span>
                                    @endif
                                </div>

                                {{-- Waktu --}}
                                <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                    <i class="fi fi-rr-calendar text-[11px]"></i>
                                    <span>{{ $log->created_at?->translatedFormat('d F Y, H:i') }} WITA</span>
                                    <span class="text-gray-400 dark:text-gray-500">({{ $log->created_at?->diffForHumans() }})</span>
                                </div>
                            </div>

                            {{-- Panel Perubahan Data (Payload) --}}
                            @if (! empty($details['attributes']) || ! empty($details['transitions']) || ! empty($details['notes']))
                                <div class="mt-3 space-y-2.5 rounded-lg border border-gray-200/70 bg-white/80 p-3 text-xs dark:border-gray-800 dark:bg-gray-900/50">
                                    {{-- Transisi Status --}}
                                    @if (! empty($details['transitions']))
                                        <div class="space-y-1.5">
                                            @foreach ($details['transitions'] as $trans)
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-medium text-gray-500 dark:text-gray-400">{{ $trans['label'] }}:</span>
                                                    <div class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-gray-50 px-2 py-0.5 dark:border-gray-700 dark:bg-gray-800">
                                                        <span class="text-gray-600 line-through dark:text-gray-400">{{ $trans['from'] }}</span>
                                                        <i class="fi fi-rr-arrow-right text-[10px] text-gray-400"></i>
                                                        <span class="font-semibold @if(in_array($trans['to'], ['Selesai', 'Aktif'], true)) text-success-600 dark:text-success-400 @elseif(in_array($trans['to'], ['Tidak Selesai', 'SP3', 'Nonaktif'], true)) text-error-600 dark:text-error-400 @else text-brand-600 dark:text-brand-400 @endif">
                                                            {{ $trans['to'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Grid Atribut Key-Value --}}
                                    @if (! empty($details['attributes']))
                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3">
                                            @foreach ($details['attributes'] as $attrLabel => $attrValue)
                                                <div class="rounded-md border border-gray-100 bg-gray-50/50 p-2 dark:border-gray-800 dark:bg-white/[0.02]">
                                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400">{{ $attrLabel }}</span>
                                                    <strong class="mt-0.5 block truncate font-medium text-gray-800 dark:text-white/90" title="{{ $attrValue }}">
                                                        {{ $attrValue }}
                                                    </strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Catatan / Keterangan --}}
                                    @if (! empty($details['notes']))
                                        <div class="space-y-1.5 pt-1">
                                            @foreach ($details['notes'] as $noteLabel => $noteValue)
                                                <div class="rounded-md border border-gray-100 bg-gray-50/50 p-2 dark:border-gray-800 dark:bg-white/[0.02]">
                                                    <span class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ $noteLabel }}:</span>
                                                    <p class="mt-0.5 text-xs text-gray-700 dark:text-gray-300">{{ $noteValue }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Footer Metadata (IP Address) --}}
                            @if ($log->ip_address)
                                <div class="mt-2.5 flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500">
                                    <i class="fi fi-rr-network text-[10px]"></i>
                                    <span>IP: {{ $log->ip_address }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 py-12 text-center dark:border-gray-800">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <i class="fi fi-rr-time-past text-xl"></i>
            </div>
            <h4 class="mt-3 text-sm font-semibold text-gray-800 dark:text-white/90">Belum Ada Riwayat Aktivitas</h4>
            <p class="mt-1 max-w-sm text-xs text-gray-500 dark:text-gray-400">
                Aktivitas penanganan perkara, penetapan tahap, dan status akan tercatat secara otomatis di sini.
            </p>
        </div>
    @endif
</div>
