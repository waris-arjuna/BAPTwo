@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <x-page-header
            title="Laporan Kejadian"
            subtitle="Kelola data laporan kejadian sebagai dasar proses penyidikan."
            :breadcrumbs="[
                ['label' => 'Home', 'url' => url('/')],
                ['label' => 'Laporan Kejadian'],
            ]"
        >
            @can('create-cases')
                <x-slot:actions>
                    <a
                        href="{{ route('laporan-kejadian.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                    >
                        <i class="fi fi-rr-plus-small text-lg"></i>
                        <span>Tambah Laporan Kejadian</span>
                    </a>
                </x-slot:actions>
            @endcan
        </x-page-header>

        {{-- Table Area --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Toolbar --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor laporan..."
                :statusOptions="[
                    'Draft' => 'Draft',
                    'Belum Dilanjutkan' => 'Belum Dilanjutkan',
                    'Sprindik Tersedia' => 'Sprindik Tersedia',
                    'Perkara Terbentuk' => 'Perkara Terbentuk',
                ]"
            />

            {{-- Responsive Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.laporan-kejadian._table-partial')
            </div>

        </div>
    </div>
@endsection