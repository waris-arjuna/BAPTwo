@props(['activities' => null])

@php
    $activities ??= app(\App\Services\DashboardService::class)->getRecentActivities(4);
    $milestones = $activities['milestones'] ?? collect();
    $all = $activities['all'] ?? collect();
@endphp

<div
    x-data="{ tab: 'milestones' }"
    class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
>
    {{-- Card Header --}}
    <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Aktivitas Penyidikan Terkini
                </h2>
                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-medium text-brand-700 dark:bg-brand-900/20 dark:text-brand-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                    Live
                </span>
            </div>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Pembaruan riwayat dan perkembangan berkas perkara
            </p>
        </div>

        {{-- Tab Toggle --}}
        <div class="inline-flex items-center rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
            <button
                type="button"
                @click="tab = 'milestones'"
                :class="tab === 'milestones'
                    ? 'bg-white text-gray-800 shadow-theme-xs font-semibold dark:bg-gray-900 dark:text-white'
                    : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white'"
                class="rounded-lg px-3 py-1.5 text-xs transition focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                aria-label="Tampilkan milestone penting"
            >
                Milestone Penting
            </button>
            <button
                type="button"
                @click="tab = 'all'"
                :class="tab === 'all'
                    ? 'bg-white text-gray-800 shadow-theme-xs font-semibold dark:bg-gray-900 dark:text-white'
                    : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white'"
                class="rounded-lg px-3 py-1.5 text-xs transition focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                aria-label="Tampilkan semua aktivitas"
            >
                Semua Aktivitas
            </button>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="flex-1 p-4 sm:p-5">
        {{-- Tab: Milestone Penting --}}
        <div x-show="tab === 'milestones'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if ($milestones->isNotEmpty())
                <div class="space-y-2.5">
                    @foreach ($milestones as $log)
                        @php
                            $meta = $log->action_meta;
                        @endphp
                        <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]">
                            {{-- Circle Icon --}}
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/60 {{ $meta['icon_bg'] }} shadow-xs dark:border-gray-800">
                                <i class="fi {{ $meta['icon'] }} text-base"></i>
                            </div>

                            {{-- Item Content --}}
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-semibold {{ $meta['badge'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $log->created_at?->diffForHumans() ?? 'Baru saja' }}
                                    </span>
                                </div>

                                {{-- Perkara Link & Tersangka --}}
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                    @if ($log->perkara_id)
                                        <a
                                            href="{{ route('perkara.show', $log->perkara_id) }}"
                                            class="inline-flex items-center gap-1 font-semibold text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400 dark:hover:text-brand-300"
                                        >
                                            <i class="fi fi-rr-document text-[11px]"></i>
                                            <span>{{ $log->perkara?->nomor_laporan_kejadian ?: 'Perkara #' . $log->perkara_id }}</span>
                                        </a>
                                    @endif

                                    @if ($log->berkasPerkara?->tersangka?->nama)
                                        <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-300">
                                            <i class="fi fi-rr-user text-[11px] text-gray-400"></i>
                                            <span>{{ $log->berkasPerkara->tersangka->nama }}</span>
                                        </span>
                                    @endif

                                    @if ($log->user?->nama)
                                        <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                            <i class="fi fi-rr-id-badge text-[11px] text-gray-400"></i>
                                            <span>{{ $log->user->nama }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center px-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500 mb-3">
                        <i class="fi fi-rr-clipboard-list-check text-xl"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Belum ada aktivitas penyidikan
                    </p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 max-w-xs">
                        Setiap pencapaian milestone dan tahapan baru penyidikan perkara akan tercatat secara otomatis di sini.
                    </p>
                </div>
            @endif
        </div>

        {{-- Tab: Semua Aktivitas --}}
        <div x-show="tab === 'all'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if ($all->isNotEmpty())
                <div class="space-y-2.5">
                    @foreach ($all as $log)
                        @php
                            $meta = $log->action_meta;
                        @endphp
                        <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]">
                            {{-- Circle Icon --}}
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/60 {{ $meta['icon_bg'] }} shadow-xs dark:border-gray-800">
                                <i class="fi {{ $meta['icon'] }} text-base"></i>
                            </div>

                            {{-- Item Content --}}
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-semibold {{ $meta['badge'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $log->created_at?->diffForHumans() ?? 'Baru saja' }}
                                    </span>
                                </div>

                                {{-- Perkara Link & Tersangka --}}
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                    @if ($log->perkara_id)
                                        <a
                                            href="{{ route('perkara.show', $log->perkara_id) }}"
                                            class="inline-flex items-center gap-1 font-semibold text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400 dark:hover:text-brand-300"
                                        >
                                            <i class="fi fi-rr-document text-[11px]"></i>
                                            <span>{{ $log->perkara?->nomor_laporan_kejadian ?: 'Perkara #' . $log->perkara_id }}</span>
                                        </a>
                                    @endif

                                    @if ($log->berkasPerkara?->tersangka?->nama)
                                        <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-300">
                                            <i class="fi fi-rr-user text-[11px] text-gray-400"></i>
                                            <span>{{ $log->berkasPerkara->tersangka->nama }}</span>
                                        </span>
                                    @endif

                                    @if ($log->user?->nama)
                                        <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                            <i class="fi fi-rr-id-badge text-[11px] text-gray-400"></i>
                                            <span>{{ $log->user->nama }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center px-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500 mb-3">
                        <i class="fi fi-rr-clipboard-list-check text-xl"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Belum ada aktivitas penyidikan
                    </p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 max-w-xs">
                        Setiap pembaruan draft maupun tahap penyidikan perkara akan tercatat secara otomatis di sini.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
