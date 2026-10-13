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
        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4 backdrop-blur-sm"
            @keydown.escape.window="modalOpen = false"
        >
            <div
                @click.outside="modalOpen = false"
                class="relative w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            <i class="fi fi-rr-box-alt text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Pilih Berkas Perkara untuk Tahap 2
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Berkas yang telah menyelesaikan tahapan P-21 dan siap dilimpahkan ke Kejaksaan.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="modalOpen = false"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    >
                        <i class="fi fi-rr-cross-small text-lg"></i>
                    </button>
                </div>

                @if ($availableBerkas->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-200 p-8 text-center dark:border-gray-800">
                        <i class="fi fi-rr-info text-2xl text-gray-400"></i>
                        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tidak ada Berkas Perkara yang siap diproses ke Tahap 2.
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            Pastikan berkas telah difinalkan pada tahapan P-21 dan belum memiliki tahapan Tahap 2.
                        </p>
                    </div>
                @else
                    <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                        @foreach ($availableBerkas as $b)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 p-3.5 transition hover:border-brand-300 hover:bg-brand-50/20 dark:border-gray-800 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/5">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-mono text-sm font-semibold text-gray-800 dark:text-white/90">
                                            {{ $b->nomor_berkas ?: '(Draft Belum Bernomor)' }}
                                        </p>
                                        @if ($b->is_dpo)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                                <i class="fi fi-rr-ban text-[9px]"></i>
                                                DPO Aktif
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300">
                                        Tersangka: <strong>{{ $b->tersangka?->nama ?: '-' }}</strong>
                                    </p>
                                    <p class="text-[11px] font-mono text-gray-400">
                                        Surat Perintah: {{ $b->perkara?->nomor_surat_perintah ?: '-' }}
                                    </p>
                                </div>
                                <a
                                    href="{{ route('berkas-perkara.tahapan.create', ['berkas' => $b, 'jenis' => 'tahap_2']) }}"
                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-brand-600"
                                >
                                    <span>Pilih Berkas</span>
                                    <i class="fi fi-rr-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        @click="modalOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
