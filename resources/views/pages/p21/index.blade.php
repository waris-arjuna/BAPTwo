@extends('layouts.app')

@section('content')
    <div
        x-data="{
            modalOpen: {{ request('open_modal') ? 'true' : 'false' }}
        }"
        class="space-y-6"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ url('/') }}" class="transition hover:text-brand-500">
                        Home
                    </a>
                    <span>/</span>
                    <span class="text-gray-700 dark:text-gray-300">
                        P-21 / P-21a
                    </span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    P-21 / P-21a (Hasil Penyidikan Lengkap)
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola surat pemberitahuan bahwa hasil penyidikan perkara telah lengkap dari Kejaksaan (Bobot progress 85%).
                </p>
            </div>

            @can('create-cases')
                <button
                    type="button"
                    @click="modalOpen = true"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <i class="fi fi-rr-plus-small text-lg"></i>
                    <span>Tambah P-21 / P-21a</span>
                </button>
            @endcan
        </div>

        {{-- Table Container --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Toolbar --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor P-21, nomor berkas, tersangka, sprindik..."
                :statusOptions="[
                    'final' => 'Final (Terkunci)',
                    'draft' => 'Draft',
                ]"
            />

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.p21._table-partial')
            </div>
        </div>

        {{-- Modal Pemilihan Berkas Perkara Siap P-21 --}}
        <x-tahapan.select-berkas-modal
            title="Pilih Berkas Perkara untuk P-21"
            description="Berkas yang telah menyelesaikan Tahap 1 dan siap dicatatkan surat P-21 dari Kejaksaan."
            icon="fi-rr-diploma"
            :availableBerkas="$availableBerkas"
            jenis="p21"
            actionLabel="Pilih Berkas"
            emptyTitle="Tidak ada Berkas Perkara yang siap dicatatkan P-21"
            emptyDescription="Pastikan berkas telah difinalkan pada Tahap 1 dan belum memiliki tahapan P-21."
            openExpr="modalOpen"
        />
    </div>
@endsection