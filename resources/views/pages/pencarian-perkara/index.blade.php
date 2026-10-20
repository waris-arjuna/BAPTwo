@php
    $title = 'Pencarian Perkara';
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb & Header --}}
        <x-page-header
            title="Pencarian Perkara"
            :breadcrumbs="[
                ['label' => 'Menu Utama', 'url' => route('dashboard')],
                ['label' => 'Pencarian Perkara'],
            ]"
        />

        {{-- Card Hub Pencarian Cepat & Multi-Kriteria (PRD F-39) --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-6">
            <form
                method="GET"
                action="{{ route('pencarian-perkara.index') }}"
                x-data="tableFilter('#pencarian-table-partial')"
                @change="if ($event.target.name !== 'search') doFilter($el)"
                @submit.prevent="doFilter($el)"
                class="space-y-4"
            >
                {{-- Input Teks Pencarian Utama --}}
                <div class="relative w-full">
                    <span class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2">
                        <i class="fi fi-rr-search text-lg text-gray-400"></i>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Ketik nomor berkas, nama tersangka, NIK, sprindik, pasal, atau kata kunci kasus..."
                        @input.debounce.500ms="doFilter($el.form)"
                        class="h-12 w-full rounded-xl border border-gray-300 bg-gray-50/50 ps-12 pe-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800/40 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:bg-gray-900"
                    />
                </div>

                {{-- Preset Filter Chips --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Pilihan Cepat:</span>
                    <a
                        href="{{ route('pencarian-perkara.index') }}"
                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium transition {{ empty($preset) && empty($statusFormal) && empty($isDpo) ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}"
                    >
                        Semua
                    </a>
                    <a
                        href="{{ route('pencarian-perkara.index', ['preset' => 'berjalan']) }}"
                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium transition {{ $preset === 'berjalan' ? 'bg-warning-500 text-white' : 'bg-warning-50 text-warning-700 hover:bg-warning-100 dark:bg-warning-500/10 dark:text-warning-400 dark:hover:bg-warning-500/20' }}"
                    >
                        <i class="fi fi-rr-hourglass-end text-[10px]"></i>
                        Sedang Berjalan
                    </a>
                    <a
                        href="{{ route('pencarian-perkara.index', ['preset' => 'selesai']) }}"
                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium transition {{ $preset === 'selesai' ? 'bg-success-600 text-white' : 'bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-500/10 dark:text-success-400 dark:hover:bg-success-500/20' }}"
                    >
                        <i class="fi fi-rr-check text-[10px]"></i>
                        Sudah Selesai
                    </a>
                    <a
                        href="{{ route('pencarian-perkara.index', ['preset' => 'dpo']) }}"
                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium transition {{ $preset === 'dpo' ? 'bg-error-600 text-white' : 'bg-error-50 text-error-700 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20' }}"
                    >
                        <i class="fi fi-rr-cross-circle text-[10px]"></i>
                        DPO Aktif
                    </a>
                </div>

                {{-- Filter Lanjutan Multi-Kriteria --}}
                <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2 lg:grid-cols-5">
                    {{-- Status Tahapan / Proses --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Tahapan Proses</label>
                        <select
                            name="status_proses"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-700 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <option value="">Semua Tahapan</option>
                            @foreach ($tahapOptions as $tahap)
                                <option value="{{ $tahap }}" @selected($statusProses === $tahap)>{{ $tahap }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Formal --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Status Formal</label>
                        <select
                            name="status_formal"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-700 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <option value="">Semua Status Formal</option>
                            <option value="dalam_proses" @selected($statusFormal === 'dalam_proses')>Dalam Proses</option>
                            <option value="selesai" @selected($statusFormal === 'selesai')>Selesai</option>
                            <option value="tidak_selesai" @selected($statusFormal === 'tidak_selesai')>Tidak Selesai</option>
                        </select>
                    </div>

                    {{-- Status DPO --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Status DPO</label>
                        <select
                            name="is_dpo"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-700 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <option value="">Semua Kondisi</option>
                            <option value="1" @selected($isDpo === '1')>DPO Aktif Saja</option>
                            <option value="0" @selected($isDpo === '0')>Bukan DPO</option>
                        </select>
                    </div>

                    {{-- Penyidik PPNS --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Penyidik PPNS</label>
                        <select
                            name="ppns_id"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-700 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <option value="">Semua Penyidik</option>
                            @foreach ($ppnsOptions as $ppns)
                                <option value="{{ $ppns->id }}" @selected($ppnsId == $ppns->id)>{{ $ppns->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal SPDP / LK --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Dari Tanggal</label>
                        <x-form.date-picker
                            name="tanggal_dari"
                            id="pencarian-tanggal-dari"
                            placeholder="Pilih Tanggal"
                            defaultDate="{{ $tanggalDari ?: null }}"
                            class="h-10 w-full text-xs"
                        />
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <a
                        href="{{ route('pencarian-perkara.index') }}"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <i class="fi fi-rr-refresh text-xs"></i>
                        <span>Reset Filter</span>
                    </a>
                    <button
                        type="submit"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-brand-500 px-4 text-xs font-medium text-white transition hover:bg-brand-600"
                    >
                        <i class="fi fi-rr-search text-xs"></i>
                        <span>Cari Perkara</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Card Hasil Pencarian --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div id="pencarian-table-partial">
                @include('pages.pencarian-perkara._table-partial')
            </div>
        </div>
    </div>
@endsection
