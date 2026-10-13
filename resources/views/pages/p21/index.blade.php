@php
    $p21s ??= app(\App\Services\MockStageService::class)->getP21(
        request('search'),
        request('status'),
        request('tanggal_dari', request('tanggal'))
    );
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">

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
                        P-21 / P-21a
                    </span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    P-21 / P-21a
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola data P-21/P-21a pada proses penyidikan.
                </p>
            </div>

            @can('create-cases')
                <a
                    href="{{ url('/p21/tambah') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <i class="fi fi-rr-plus-small text-lg"></i>

                    Tambah P-21 / P-21a
                </a>
            @endcan
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Toolbar --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor P-21, perkara, atau tersangka..."
                :statusOptions="[
                    'P-21 / P-21a Tersedia' => 'P-21 / P-21a Tersedia',
                    'Siap Tahap 2' => 'Siap Tahap 2',
                ]"
            />

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.p21._table-partial')
            </div>

        </div>
    </div>
@endsection