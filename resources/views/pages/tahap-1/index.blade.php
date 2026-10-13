@php
    $tahap1 ??= app(\App\Services\MockStageService::class)->getTahap1(
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
                    <a href="{{ url('/') }}" class="transition hover:text-brand-500">
                        Home
                    </a>

                    <span>/</span>

                    <span class="text-gray-700 dark:text-gray-300">
                        Tahap 1
                    </span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Tahap 1
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola data dan dokumen penyerahan berkas pada Tahap 1.
                </p>
            </div>

            @can('create-cases')
                <a
                    href="{{ url('/tahap-1/tambah') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <i class="fi fi-rr-plus-small text-lg"></i>

                    Tambah Tahap 1
                </a>
            @endcan
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Toolbar --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor Tahap 1..."
                :statusOptions="[
                    'Tahap 1 Berjalan' => 'Tahap 1 Berjalan',
                    'Menunggu P-21' => 'Menunggu P-21',
                    'P-21' => 'P-21',
                ]"
            />

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.tahap-1._table-partial')
            </div>

        </div>
    </div>
@endsection