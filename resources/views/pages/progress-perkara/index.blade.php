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

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ url('/') }}" class="transition hover:text-brand-500">Home</a>
                    <span>/</span>
                    <span class="text-gray-500 dark:text-gray-400">Data Perkara</span>
                    <span>/</span>
                    <span class="text-gray-700 dark:text-gray-300">Progress Perkara</span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Progress Perkara
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Monitoring tahapan progress berkas perkara berbobot: SPDP (15%), TAHAP I (55%), P-21/P21a (85%), dan TAHAP II (100%).
                </p>
            </div>
        </div>

        {{-- Table Area --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Toolbar Filter --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor berkas, Sprindik, tersangka, pasal..."
                :statusOptions="[
                    'Dalam Proses' => 'Dalam Proses',
                    'Selesai' => 'Selesai',
                    'Tidak Selesai' => 'Tidak Selesai',
                    'DPO' => 'DPO',
                ]"
            />

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.progress-perkara._table-partial')
            </div>
        </div>
    </div>
@endsection
