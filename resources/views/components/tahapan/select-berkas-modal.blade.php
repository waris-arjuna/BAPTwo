@props([
    'title' => 'Pilih Berkas Perkara',
    'description' => 'Pilih berkas perkara yang siap diproses ke tahapan ini.',
    'icon' => 'fi-rr-document-signed',
    'availableBerkas' => collect(),
    'jenis' => 'spdp',
    'actionLabel' => 'Pilih Berkas',
    'emptyTitle' => 'Tidak Ada Berkas yang Siap',
    'emptyDescription' => 'Seluruh berkas perkara yang ada saat ini belum memenuhi syarat atau sudah menyelesaikan tahapan ini.',
    'openExpr' => 'modalOpen',
])

@php
    $berkasCount = count($availableBerkas);
    $containerClass = $berkasCount > 7 ? 'max-h-[34rem] overflow-y-auto' : 'h-auto';
@endphp

<div
    x-show="{{ $openExpr }}"
    x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="{{ $openExpr }} = false"
    x-data="{
        searchQuery: '',
        items: [
            @foreach($availableBerkas as $b)
                {
                    id: {{ $b->id }},
                    search: '{{ strtolower(addslashes(($b->nomor_berkas ?? '') . ' ' . ($b->tersangka?->nama ?? '') . ' ' . ($b->perkara?->nomor_surat_perintah ?? '') . ' ' . ($b->perkara?->nomor_laporan_kejadian ?? ''))) }}'
                },
            @endforeach
        ],
        get hasMatches() {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase().trim();
            return this.items.some(item => item.search.includes(q));
        }
    }"
>
    {{-- Backdrop --}}
    <div
        x-show="{{ $openExpr }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="{{ $openExpr }} = false"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
    ></div>

    {{-- Dialog Box --}}
    <div
        x-show="{{ $openExpr }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
    >
        {{-- Modal Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    <i class="fi {{ $icon }} text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        {{ $title }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $description }}
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="{{ $openExpr }} = false"
                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
            >
                <i class="fi fi-rr-cross-small text-xl"></i>
            </button>
        </div>

        {{-- Search Input (Hanya tampil jika ada berkas yang siap) --}}
        @if ($availableBerkas->isNotEmpty())
            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/30">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <i class="fi fi-rr-search text-sm"></i>
                    </span>
                    <input
                        type="text"
                        x-model="searchQuery"
                        placeholder="Cari berkas, tersangka, sprindik..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-9 text-xs text-gray-800 placeholder-gray-400 transition focus:border-brand-500 focus:outline-hidden focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder-gray-500"
                    />
                    <button
                        type="button"
                        x-show="searchQuery"
                        x-cloak
                        @click="searchQuery = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <i class="fi fi-rr-cross-circle text-xs"></i>
                    </button>
                </div>
            </div>
        @endif

        {{-- Modal Body --}}
        <div class="flex-1 overflow-hidden p-6">
            @if ($availableBerkas->isEmpty())
                <div class="py-8 text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <i class="fi fi-rr-folder-open text-2xl"></i>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $emptyTitle }}
                    </h4>
                    <p class="mx-auto mt-1 max-w-md text-xs text-gray-500 dark:text-gray-400">
                        {{ $emptyDescription }}
                    </p>
                    <div class="mt-4">
                        <a
                            href="{{ route('data-perkara.index') }}"
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 transition hover:text-brand-700 dark:text-brand-400"
                        >
                            <i class="fi fi-rr-arrow-right text-xs"></i>
                            <span>Buka Data Perkara</span>
                        </a>
                    </div>
                </div>
            @else
                {{-- Container list dengan scroll vertikal otomatis jika > 7 baris --}}
                <div class="{{ $containerClass }} space-y-3 pr-1">
                    @foreach ($availableBerkas as $b)
                        @php
                            $searchText = strtolower(($b->nomor_berkas ?? '') . ' ' . ($b->tersangka?->nama ?? '') . ' ' . ($b->perkara?->nomor_surat_perintah ?? '') . ' ' . ($b->perkara?->nomor_laporan_kejadian ?? ''));
                        @endphp
                        <div
                            x-show="!searchQuery || '{{ addslashes($searchText) }}'.includes(searchQuery.toLowerCase().trim())"
                            class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4 transition hover:border-brand-500 hover:bg-white sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-800/40 dark:hover:border-brand-500 dark:hover:bg-gray-800/80"
                        >
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold uppercase text-brand-600 dark:text-brand-400">
                                        Berkas:
                                    </span>
                                    <span class="font-mono text-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ $b->nomor_berkas ?: '(Draft Belum Bernomor)' }}
                                    </span>
                                    @if ($b->is_dpo)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                            <i class="fi fi-rr-ban text-[9px]"></i>
                                            DPO Aktif
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                    <i class="fi fi-rr-user text-[11px] text-gray-400"></i>
                                    <span>Tersangka: <strong>{{ $b->tersangka?->nama ?: '-' }}</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <i class="fi fi-rr-memo text-[11px] text-gray-400"></i>
                                    <span>Sprindik / LK: {{ $b->perkara?->nomor_surat_perintah ?: ($b->perkara?->nomor_laporan_kejadian ?: '-') }}</span>
                                </div>
                            </div>

                            <a
                                href="{{ route('berkas-perkara.tahapan.create', ['berkas' => $b, 'jenis' => $jenis]) }}"
                                class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                            >
                                <span>{{ $actionLabel }}</span>
                                <i class="fi fi-rr-arrow-small-right text-base"></i>
                            </a>
                        </div>
                    @endforeach

                    {{-- Pesan saat pencarian tidak ditemukan --}}
                    <div
                        x-show="searchQuery && !hasMatches"
                        x-cloak
                        class="py-8 text-center"
                    >
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                            <i class="fi fi-rr-search text-lg"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Data tidak ditemukan
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Tidak ada berkas yang cocok dengan kata kunci "<span class="font-semibold text-gray-700 dark:text-gray-200" x-text="searchQuery"></span>".
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-end border-t border-gray-200 bg-gray-50 px-6 py-3.5 dark:border-gray-800 dark:bg-gray-900/50">
            <button
                type="button"
                @click="{{ $openExpr }} = false"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
