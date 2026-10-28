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
        <x-tahapan.select-berkas-modal
            title="Pilih Berkas Perkara untuk Tahap 1"
            description="Berkas yang telah menyelesaikan SPDP dan siap dilimpahkan ke Kejaksaan (Tahap I)."
            icon="fi-rr-document-signed"
            :availableBerkas="$availableBerkas"
            jenis="tahap_1"
            actionLabel="Pilih Berkas"
            emptyTitle="Tidak ada Berkas Perkara yang siap Tahap 1"
            emptyDescription="Pastikan berkas telah difinalkan pada tahap SPDP dan belum memiliki tahapan Tahap 1."
            openExpr="modalOpen"
        />
    </div>
@endsection