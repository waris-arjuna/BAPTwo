@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <x-page-header
            title="Sprindik"
            subtitle="Kelola data Surat Perintah Penyidikan sebagai dasar pelaksanaan penyidikan."
            :breadcrumbs="[
                ['label' => 'Home', 'url' => url('/')],
                ['label' => 'Sprindik'],
            ]"
        >
            @can('create-cases')
                <x-slot:actions>
                    <a
                        href="{{ route('sprindik.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                    >
                        <i class="fi fi-rr-plus-small text-lg"></i>
                        <span>Tambah Sprindik</span>
                    </a>
                </x-slot:actions>
            @endcan
        </x-page-header>

        {{-- Table --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Toolbar --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor Sprindik..."
                :statusOptions="[
                    'Draft' => 'Draft',
                    'Belum Menjadi Perkara' => 'Belum Menjadi Perkara',
                    'Draft Perkara' => 'Draft Perkara',
                    'Perkara Terbentuk' => 'Perkara Terbentuk',
                ]"
            />

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.sprindik._table-partial')
            </div>

        </div>
    </div>
@endsection