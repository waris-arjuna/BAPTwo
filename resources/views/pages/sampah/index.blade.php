@php
    $title = 'Recovery Data';
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb & Header --}}
        <x-page-header
            title="Recovery Data"
            :breadcrumbs="[
                ['label' => 'Menu Utama', 'url' => route('dashboard')],
                ['label' => 'Sistem', 'url' => '#'],
                ['label' => 'Recovery Data'],
            ]"
        />

        {{-- Banner Edukasi Soft Delete (PRD F-44) --}}
        <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4 text-xs text-blue-800 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-blue-300">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">
                    <i class="fi fi-rr-info text-base"></i>
                </div>
                <div class="space-y-1">
                    <p class="font-semibold text-sm">Informasi Pemulihan Data (Soft Delete)</p>
                    <p class="leading-relaxed">
                        Seluruh penghapusan di sistem BAPTwo menggunakan mekanisme <strong>Soft Delete</strong>. Data yang dihapus tidak dimusnahkan secara permanen melainkan dipindahkan ke halaman Recovery Data ini dan dapat dipulihkan kembali sewaktu-waktu oleh pengguna yang berwenang (Penyidik &amp; Administrator). Fitur <em>Hard Delete</em> (hapus permanen) ditiadakan demi menjaga integritas dan akuntabilitas penanganan perkara.
                    </p>
                </div>
            </div>
        </div>

        {{-- Card Konten Utama --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            {{-- Tab Navigasi Kategori Sampah --}}
            <div class="border-b border-gray-200 px-5 pt-4 dark:border-gray-800 sm:px-6">
                <nav class="-mb-px flex space-x-6 overflow-x-auto no-scrollbar" aria-label="Tabs">
                    <a
                        href="{{ route('sampah.index', ['tab' => 'perkara']) }}"
                        class="inline-flex items-center gap-2 border-b-2 pb-4 text-sm font-medium transition {{ $activeTab === 'perkara' ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-300' }}"
                    >
                        <i class="fi fi-rr-document-signed text-base"></i>
                        <span>Laporan Kejadian &amp; Perkara</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums {{ $activeTab === 'perkara' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                            {{ $countPerkara }}
                        </span>
                    </a>

                    <a
                        href="{{ route('sampah.index', ['tab' => 'berkas']) }}"
                        class="inline-flex items-center gap-2 border-b-2 pb-4 text-sm font-medium transition {{ $activeTab === 'berkas' ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-300' }}"
                    >
                        <i class="fi fi-rr-folder text-base"></i>
                        <span>Berkas Perkara &amp; Tersangka</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums {{ $activeTab === 'berkas' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                            {{ $countBerkas }}
                        </span>
                    </a>

                    <a
                        href="{{ route('sampah.index', ['tab' => 'user']) }}"
                        class="inline-flex items-center gap-2 border-b-2 pb-4 text-sm font-medium transition {{ $activeTab === 'user' ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-300' }}"
                    >
                        <i class="fi fi-rr-users text-base"></i>
                        <span>Pengguna</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums {{ $activeTab === 'user' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                            {{ $countUser }}
                        </span>
                    </a>
                </nav>
            </div>

            {{-- Toolbar Filter Terpadu --}}
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800">
                <x-common.table-filter
                    action="{{ route('sampah.index') }}"
                    method="GET"
                    targetContainerId="sampah-table-partial"
                    :searchPlaceholder="match ($activeTab) {
                        'berkas' => 'Cari nomor berkas, nama tersangka, NIK...',
                        'user' => 'Cari nama, NIP, email pengguna...',
                        default => 'Cari nomor LK, nomor sprindik, deskripsi...',
                    }"
                    searchName="search"
                    dateName="tanggal"
                    dateLabel="Tanggal Dihapus"
                    resetUrl="{{ route('sampah.index', ['tab' => $activeTab]) }}"
                >
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                </x-common.table-filter>
            </div>

            {{-- Partial Table Container --}}
            <div id="sampah-table-partial">
                @include('pages.sampah._table-partial')
            </div>
        </div>
    </div>
@endsection
