@php
    $title = 'Monitoring Potensi Terlimpah';
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb & Header --}}
        <x-page-header
            title="Monitoring Potensi Terlimpah"
            :breadcrumbs="[
                ['label' => 'Menu Utama', 'url' => route('dashboard')],
                ['label' => 'Data Perkara', 'url' => route('data-perkara.index')],
                ['label' => 'Potensi Terlimpah'],
            ]"
        />

        {{-- Banner Edukasi & Peringatan Dini Batas Akhir Tahun (PRD F-41) --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-xs text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/20 dark:text-amber-300">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                    <i class="fi fi-rr-alarm-exclamation text-base"></i>
                </div>
                <div class="space-y-1">
                    <p class="font-semibold text-sm">Peringatan Dini Batas Waktu &amp; Pelimpahan Akhir Tahun</p>
                    <p class="leading-relaxed">
                        Sistem memantau seluruh Berkas Perkara yang masih berjalan. Berkas perkara yang belum mencapai status formal <strong>Selesai</strong> pada tanggal 31 Desember pukul 23:59 akan otomatis ditetapkan sebagai <strong>Tidak Selesai</strong> oleh sistem pada 1 Januari pukul 00:00. Prioritaskan berkas yang sudah mencapai <strong>Tahap I</strong> atau <strong>P-21</strong> untuk segera dilakukan pelimpahan ke <strong>Tahap II</strong>.
                    </p>
                </div>
            </div>
        </div>

        {{-- 5 Kartu Metrik Ringkasan Potensi Terlimpah --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 md:gap-5">
            {{-- Berkas Berjalan Aktif --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <i class="fi fi-rr-folder-open text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Berkas Berjalan</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $totalPotensi }}</h4>
                    </div>
                </div>
            </div>

            {{-- Siap Pelimpahan Tahap II (>= 55%) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                        <i class="fi fi-rr-badge-check text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Siap Tahap II (&ge;55%)</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $siapTahap2Count }}</h4>
                    </div>
                </div>
            </div>

            {{-- Mendekati Batas SLA (<= 7 Hari) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                        <i class="fi fi-rr-hourglass-end text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Mendekati Batas (&le;7 Hari)</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $mendekatiBatasCount }}</h4>
                    </div>
                </div>
            </div>

            {{-- Kritis / Melebihi SLA --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <i class="fi fi-rr-cross-circle text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Melebihi SLA</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $kritisCount }}</h4>
                    </div>
                </div>
            </div>

            {{-- Terkendala DPO --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
                        <i class="fi fi-rr-user-slash text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Terkendala DPO</span>
                        <h4 class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ $terkendalaDpoCount }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Konten Utama --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            {{-- Toolbar Filter Terpadu --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor berkas, nama tersangka, NIK, sprindik, PPNS..."
                :statusOptions="[
                    'siap_tahap2' => 'Siap Pelimpahan (Progress >= 55%)',
                    'mendekati_batas' => 'Mendekati Batas SLA (<= 7 Hari)',
                    'kritis' => 'Melebihi Batas Waktu SLA',
                    'dpo' => 'Terkendala DPO Aktif',
                ]"
                :showDate="true"
                target="#potensi-terlimpah-table-partial"
            >
                @if ($ppnsOptions->isNotEmpty())
                    <select
                        name="ppns_id"
                        class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">Semua Penyidik PPNS</option>
                        @foreach ($ppnsOptions as $ppns)
                            <option value="{{ $ppns->id }}" @selected($ppnsId == $ppns->id)>{{ $ppns->nama }}</option>
                        @endforeach
                    </select>
                @endif
            </x-common.table-filter>

            {{-- Container Tabel Parsial AJAX --}}
            <div id="potensi-terlimpah-table-partial">
                @include('pages.potensi-terlimpah._table-partial')
            </div>
        </div>
    </div>
@endsection
