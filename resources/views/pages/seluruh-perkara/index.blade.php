@php
    $title = 'Seluruh Perkara';
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Header & Breadcrumb --}}
        <x-page-header
            title="Seluruh Perkara"
            :breadcrumbs="[
                ['label' => 'Menu Utama', 'url' => route('dashboard')],
                ['label' => 'Seluruh Perkara'],
            ]"
        >
            @can('export-excel')
                <x-slot:actions>
                    <button
                        type="button"
                        @click="$dispatch('open-export-modal')"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <i class="fi fi-rr-file-excel text-base text-success-600 dark:text-success-400"></i>
                        <span>Ekspor Excel</span>
                    </button>
                </x-slot:actions>
            @endcan
        </x-page-header>

        {{-- 5 Kartu Statistik Ringkasan Perkara (PRD F-38, F-40) --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 md:gap-5">
            {{-- Total Perkara --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <i class="fi fi-rr-layers text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Perkara</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $totalPerkara }}</h4>
                    </div>
                </div>
            </div>

            {{-- Berkas Berjalan / Dalam Proses --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                        <i class="fi fi-rr-hourglass-end text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Dalam Proses</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $berkasDalamProses }}</h4>
                    </div>
                </div>
            </div>

            {{-- Berkas Selesai --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                        <i class="fi fi-rr-check-circle text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Berkas Selesai</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $berkasSelesai }}</h4>
                    </div>
                </div>
            </div>

            {{-- Berkas Tidak Selesai --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <i class="fi fi-rr-cross-circle text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tidak Selesai</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $berkasTidakSelesai }}</h4>
                    </div>
                </div>
            </div>

            {{-- DPO Aktif --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
                        <i class="fi fi-rr-user-slash text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">DPO Aktif</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $tersangkaDpo }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Konten Utama --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            {{-- Toolbar Filter Terpadu --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor sprindik, nomor LK, tersangka, PPNS, TKP..."
                :statusOptions="[
                    'Dalam Proses' => 'Dalam Proses',
                    'Selesai' => 'Selesai',
                    'Tidak Selesai' => 'Tidak Selesai',
                    'DPO' => 'DPO Aktif',
                ]"
                :showDate="true"
                target="#seluruh-perkara-table-partial"
            />

            {{-- Container Tabel Parsial AJAX --}}
            <div id="seluruh-perkara-table-partial">
                @include('pages.seluruh-perkara._table-partial')
            </div>
        </div>
    </div>

    {{-- Modal Ekspor Excel --}}
    <x-common.export-excel-modal />
@endsection
