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
                        Tahap 1
                    </span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Tahap 1 &amp; Sub-Tahap Berkas Perkara
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola data penyerahan berkas perkara tahap pertama ke Kejaksaan serta catatan pra-penuntutan (P-18 &amp; P-19).
                </p>
            </div>

            @can('create-cases')
                <button
                    type="button"
                    @click="modalOpen = true"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <i class="fi fi-rr-plus-small text-lg"></i>
                    <span>Tambah Tahap 1</span>
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

            {{-- Tab: Tahap 1 --}}
            <button
                type="button"
                @click="setSubStage('tahap_1')"
                :class="currentSubStage === 'tahap_1'
                    ? 'bg-brand-500 text-white shadow-theme-xs'
                    : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04]'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition"
            >
                <i class="fi fi-rr-document-signed text-xs"></i>
                <span>Tahap 1</span>
                <span
                    :class="currentSubStage === 'tahap_1'
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                >
                    {{ $tabCounts['tahap_1'] }}
                </span>
            </button>

            {{-- Tab: P-18 (Opsional) --}}
            <button
                type="button"
                @click="setSubStage('p18p')"
                :class="currentSubStage === 'p18p'
                    ? 'bg-brand-500 text-white shadow-theme-xs'
                    : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04]'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition"
            >
                <i class="fi fi-rr-exclamation text-xs"></i>
                <span>P-18 <span class="text-xs opacity-75 font-normal">(Opsional)</span></span>
                <span
                    :class="currentSubStage === 'p18p'
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                >
                    {{ $tabCounts['p18p'] }}
                </span>
            </button>

            {{-- Tab: P-19 (Opsional) --}}
            <button
                type="button"
                @click="setSubStage('p19')"
                :class="currentSubStage === 'p19'
                    ? 'bg-brand-500 text-white shadow-theme-xs'
                    : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04]'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition"
            >
                <i class="fi fi-rr-undo text-xs"></i>
                <span>P-19 <span class="text-xs opacity-75 font-normal">(Opsional)</span></span>
                <span
                    :class="currentSubStage === 'p19'
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                >
                    {{ $tabCounts['p19'] }}
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
                @include('pages.tahap-1._table-partial')
            </div>
        </div>

        {{-- Modal Pemilihan Berkas Perkara Siap Tahap 1 --}}
        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4 backdrop-blur-sm"
            @keydown.escape.window="modalOpen = false"
        >
            <div
                @click.outside="modalOpen = false"
                class="relative w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            <i class="fi fi-rr-document-signed text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Pilih Berkas Perkara untuk Tahap 1
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Berkas yang telah menyelesaikan SPDP dan siap dilimpahkan ke Kejaksaan (Tahap I).
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="modalOpen = false"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    >
                        <i class="fi fi-rr-cross-small text-lg"></i>
                    </button>
                </div>

                @if ($availableBerkas->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-200 p-8 text-center dark:border-gray-800">
                        <i class="fi fi-rr-info text-2xl text-gray-400"></i>
                        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tidak ada Berkas Perkara yang siap Tahap 1.
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            Pastikan berkas telah difinalkan pada tahap SPDP dan belum memiliki tahapan Tahap 1.
                        </p>
                    </div>
                @else
                    <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                        @foreach ($availableBerkas as $b)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 p-3.5 transition hover:border-brand-300 hover:bg-brand-50/20 dark:border-gray-800 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/5">
                                <div class="min-w-0">
                                    <p class="font-mono text-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ $b->nomor_berkas ?: '(Draft Belum Bernomor)' }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-300">
                                        Tersangka: <strong>{{ $b->tersangka?->nama ?: '-' }}</strong>
                                    </p>
                                    <p class="text-[11px] font-mono text-gray-400">
                                        Surat Perintah: {{ $b->perkara?->nomor_surat_perintah ?: '-' }}
                                    </p>
                                </div>
                                <a
                                    href="{{ route('berkas-perkara.tahapan.create', ['berkas' => $b, 'jenis' => 'tahap_1']) }}"
                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-brand-600"
                                >
                                    <span>Pilih Berkas</span>
                                    <i class="fi fi-rr-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        @click="modalOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection