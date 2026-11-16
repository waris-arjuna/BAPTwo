@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            detailModalOpen: false,
            selectedLog: null,
            openDetailModal(payload) {
                this.selectedLog = payload;
                this.detailModalOpen = true;
            },
            closeDetailModal() {
                this.detailModalOpen = false;
            }
        }"
        @keydown.escape.window="closeDetailModal()"
    >
        <x-common.page-breadcrumb :pageTitle="__('Audit Log Sistem')" />

        {{-- Header Halaman --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    {{ __('Audit Log Global Sistem') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Pantau catatan aktivitas keamanan, autentikasi, ekspor data, sinkronisasi pegawai, dan riwayat perubahan perkara.') }}
                </p>
            </div>
        </div>

        {{-- Kartu Statistik Ringkas --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <i class="fi fi-rr-time-past text-lg leading-none" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Total Catatan Log') }}</p>
                        <p class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ number_format($stats['total'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400">
                        <i class="fi fi-rr-settings text-lg leading-none" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Keamanan & Sistem') }}</p>
                        <p class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ number_format($stats['keamanan_sistem'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                        <i class="fi fi-rr-shield-exclamation text-lg leading-none" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Login Gagal (7 Hari)') }}</p>
                        <p class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ number_format($stats['login_gagal_7_hari'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                        <i class="fi fi-rr-folders text-lg leading-none" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Aktivitas Perkara') }}</p>
                        <p class="mt-0.5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ number_format($stats['aktivitas_perkara'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kontainer Tabel & Filter --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            @php
                $hasActiveFilter = $filters['search'] !== ''
                    || $filters['kategori'] !== ''
                    || $filters['aksi'] !== ''
                    || $filters['tanggal_dari'] !== ''
                    || $filters['tanggal_sampai'] !== '';
            @endphp

            <x-common.table-filter
                searchPlaceholder="Cari aksi, pengguna, IP, atau nomor perkara..."
                :showDate="true"
                :hasActiveFilter="$hasActiveFilter"
            >
                <select
                    name="kategori"
                    aria-label="{{ __('Filter Kategori Log') }}"
                    class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition-colors focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">{{ __('Semua Kategori') }}</option>
                    <option value="sistem" @selected($filters['kategori'] === 'sistem')>{{ __('Keamanan & Sistem') }}</option>
                    <option value="perkara" @selected($filters['kategori'] === 'perkara')>{{ __('Perkara & Tahapan') }}</option>
                </select>

                <select
                    name="aksi"
                    aria-label="{{ __('Filter Jenis Aksi') }}"
                    class="h-11 max-w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition-colors focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 sm:max-w-[220px] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">{{ __('Semua Aksi') }}</option>
                    @foreach ($actionOptions as $aksiValue => $aksiLabel)
                        <option value="{{ $aksiValue }}" @selected($filters['aksi'] === $aksiValue)>
                            {{ $aksiLabel }}
                        </option>
                    @endforeach
                </select>

                <x-form.date-picker
                    name="tanggal_sampai"
                    id="filter-audit-tanggal-sampai"
                    placeholder="{{ __('Sampai Tanggal') }}"
                    defaultDate="{{ $filters['tanggal_sampai'] ?: null }}"
                    class="w-full sm:w-44"
                />
            </x-common.table-filter>

            <div id="table-body-partial" class="overflow-x-auto">
                @include('pages.pengaturan.audit-log._table-partial')
            </div>
        </div>

        @include('pages.pengaturan.audit-log._modal-detail')
    </div>
@endsection
