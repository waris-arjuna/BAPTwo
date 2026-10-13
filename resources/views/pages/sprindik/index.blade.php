@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
                <i class="fi fi-rr-check text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                <i class="fi fi-rr-cross text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <a
                        href="{{ url('/') }}"
                        class="transition hover:text-brand-500"
                    >
                        Home
                    </a>

                    <span>/</span>

                    <span class="text-gray-700 dark:text-gray-300">
                        Sprindik
                    </span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Sprindik
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola data Surat Perintah Penyidikan sebagai dasar pelaksanaan penyidikan.
                </p>
            </div>

            @can('create-cases')
                <a
                    href="{{ route('sprindik.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <i class="fi fi-rr-plus-small text-lg"></i>

                    Tambah Sprindik
                </a>
            @endcan
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Toolbar --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor Sprindik..."
                :statusOptions="[
                    'Belum Menjadi Perkara' => 'Belum Menjadi Perkara',
                ]"
            />

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.sprindik._table-partial')
            </div>

        </div>
    </div>
@endsection