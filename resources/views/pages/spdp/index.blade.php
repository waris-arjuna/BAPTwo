@extends('layouts.app')

@section('content')
    <div
        x-data="{
            modalOpen: {{ request('open_modal') ? 'true' : 'false' }},
            currentSubStage: '{{ request('sub_stage', 'all') }}',
            setSubStage(val) {
                this.currentSubStage = val;
                const input = document.getElementById('filter-sub-stage');
                if (input) {
                    input.value = val;
                    const form = input.closest('form');
                    if (form) {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                        }
                    }
                }
            }
        }"
        @popstate.window="currentSubStage = new URLSearchParams(window.location.search).get('sub_stage') || 'all'"
        class="space-y-6"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ url('/') }}" class="transition hover:text-brand-500">
                        Home
                    </a>
                    <span>/</span>
                    <span class="text-gray-700 dark:text-gray-300">
                        SPDP
                    </span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    SPDP &amp; Sub-Tahap Penyidikan
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola data Surat Pemberitahuan Dimulainya Penyidikan (SPDP) dan tahapan opsional P-16 serta P-17P.
                </p>
            </div>

            @can('create-cases')
                <button
                    type="button"
                    @click="modalOpen = true"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <i class="fi fi-rr-plus-small text-lg"></i>
                    <span>Tambah SPDP</span>
                </button>
            @endcan
        </div>

        {{-- Sub-Stage Filter Tabs (Pills) --}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Tab: Semua --}}
            <button
                type="button"
                @click="setSubStage('all')"
                :class="currentSubStage === 'all'
                    ? 'bg-brand-500 text-white shadow-theme-xs'
                    : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04]'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition"
            >
                <i class="fi fi-rr-folders text-xs"></i>
                <span>Semua Tahapan</span>
                <span
                    :class="currentSubStage === 'all'
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                >
                    {{ $tabCounts['all'] }}
                </span>
            </button>

            {{-- Tab: SPDP --}}
            <button
                type="button"
                @click="setSubStage('spdp')"
                :class="currentSubStage === 'spdp'
                    ? 'bg-brand-500 text-white shadow-theme-xs'
                    : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04]'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition"
            >
                <i class="fi fi-rr-document-signed text-xs"></i>
                <span>SPDP</span>
                <span
                    :class="currentSubStage === 'spdp'
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                >
                    {{ $tabCounts['spdp'] }}
                </span>
            </button>

            {{-- Tab: P-16 (Opsional) --}}
            <button
                type="button"
                @click="setSubStage('p16')"
                :class="currentSubStage === 'p16'
                    ? 'bg-brand-500 text-white shadow-theme-xs'
                    : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04]'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition"
            >
                <i class="fi fi-rr-search-alt text-xs"></i>
                <span>P-16 <span class="text-xs opacity-75 font-normal">(Opsional)</span></span>
                <span
                    :class="currentSubStage === 'p16'
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                >
                    {{ $tabCounts['p16'] }}
                </span>
            </button>

            {{-- Tab: P-17P (Opsional) --}}
            <button
                type="button"
                @click="setSubStage('p17p')"
                :class="currentSubStage === 'p17p'
                    ? 'bg-brand-500 text-white shadow-theme-xs'
                    : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04]'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition"
            >
                <i class="fi fi-rr-interrogation text-xs"></i>
                <span>P-17P <span class="text-xs opacity-75 font-normal">(Opsional)</span></span>
                <span
                    :class="currentSubStage === 'p17p'
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                >
                    {{ $tabCounts['p17p'] }}
                </span>
            </button>
        </div>

        {{-- Table Container --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Toolbar Filter --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor surat, berkas, tersangka, sprindik..."
                :statusOptions="[
                    'final' => 'Final (Terkunci)',
                    'draft' => 'Draft',
                ]"
            >
                <input type="hidden" name="sub_stage" id="filter-sub-stage" value="{{ request('sub_stage', 'all') }}">
            </x-common.table-filter>

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.spdp._table-partial')
            </div>
        </div>

        {{-- Modal Pemilihan Berkas Perkara untuk SPDP --}}
        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="modalOpen = false"
        >
            {{-- Backdrop --}}
            <div
                x-show="modalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="modalOpen = false"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
            ></div>

            {{-- Dialog Box --}}
            <div
                x-show="modalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            <i class="fi fi-rr-document-signed text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Pilih Berkas Perkara untuk SPDP
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Berkas perkara yang telah menyelesaikan Pra-SPDP dan siap memulai tahapan SPDP.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="modalOpen = false"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <i class="fi fi-rr-cross-small text-xl"></i>
                    </button>
                </div>

                {{-- Modal Body (Scrollable List) --}}
                <div class="flex-1 space-y-3 overflow-y-auto p-6">
                    @forelse ($availableBerkas as $berkas)
                        <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4 transition hover:border-brand-500 hover:bg-white sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-800/40 dark:hover:border-brand-500 dark:hover:bg-gray-800/80">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold uppercase text-brand-600 dark:text-brand-400">
                                        Berkas:
                                    </span>
                                    <span class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ $berkas->nomor_berkas }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                    <i class="fi fi-rr-user text-[11px] text-gray-400"></i>
                                    <span>Tersangka: <strong>{{ $berkas->tersangka?->nama ?: '-' }}</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <i class="fi fi-rr-memo text-[11px] text-gray-400"></i>
                                    <span>Sprindik: {{ $berkas->perkara?->nomor_surat_perintah ?: ($berkas->perkara?->nomor_laporan_kejadian ?: '-') }}</span>
                                </div>
                            </div>

                            <a
                                href="{{ route('berkas-perkara.tahapan.create', ['berkas' => $berkas, 'jenis' => 'spdp']) }}"
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                            >
                                <span>Buat SPDP</span>
                                <i class="fi fi-rr-arrow-small-right text-base"></i>
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                <i class="fi fi-rr-folder-open text-2xl"></i>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                Tidak Ada Berkas yang Siap
                            </h4>
                            <p class="mx-auto mt-1 max-w-md text-xs text-gray-500 dark:text-gray-400">
                                Seluruh berkas perkara yang ada saat ini sudah memiliki SPDP, atau tahapan Pra-SPDP (Tersangka, TKP, PPNS, Berkas Perkara) belum difinalkan.
                            </p>
                            <div class="mt-4">
                                <a
                                    href="{{ route('data-perkara.index') }}"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 transition hover:text-brand-700 dark:text-brand-400"
                                >
                                    <i class="fi fi-rr-arrow-right text-xs"></i>
                                    <span>Buka Data Perkara Pra-SPDP</span>
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end border-t border-gray-200 bg-gray-50 px-6 py-3.5 dark:border-gray-800 dark:bg-gray-900/50">
                    <button
                        type="button"
                        @click="modalOpen = false"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection