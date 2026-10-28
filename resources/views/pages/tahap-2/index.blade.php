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
                        Tahap 2
                    </span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Tahap 2 (Pelimpahan Tersangka &amp; Barang Bukti)
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pelimpahan tanggung jawab tersangka dan barang bukti kepada Kejaksaan. Finalisasi tahap ini menaikkan progress ke 100% dan menetapkan Status Formal Berkas menjadi Selesai.
                </p>
            </div>

            @can('create-cases')
                <button
                    type="button"
                    @click="modalOpen = true"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <i class="fi fi-rr-plus-small text-lg"></i>
                    <span>Tambah Tahap 2</span>
                </button>
            @endcan
        </div>

        {{-- Table Container --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Toolbar --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor Tahap 2, nomor berkas, tersangka, sprindik..."
                :statusOptions="[
                    'final' => 'Final (Selesai)',
                    'draft' => 'Draft',
                ]"
            />

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.tahap-2._table-partial')
            </div>
        </div>

        {{-- Modal Pemilihan Berkas Perkara Siap Tahap 2 --}}
        <x-tahapan.select-berkas-modal
            title="Pilih Berkas Perkara untuk Tahap 2"
            description="Berkas yang telah menyelesaikan tahapan P-21 dan siap dilimpahkan ke Kejaksaan."
            icon="fi-rr-box-alt"
            :availableBerkas="$availableBerkas"
            jenis="tahap_2"
            actionLabel="Pilih Berkas"
            emptyTitle="Tidak ada Berkas Perkara yang siap diproses ke Tahap 2"
            emptyDescription="Pastikan berkas telah difinalkan pada tahapan P-21 dan belum memiliki tahapan Tahap 2."
            openExpr="modalOpen"
        />
    </div>
@endsection
