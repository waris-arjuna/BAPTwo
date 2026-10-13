@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-screen-lg px-4 pb-8 pt-6 lg:px-6 space-y-6">
        {{-- Breadcrumb & Header --}}
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-500">Home</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('tahap-2.index') }}" class="transition hover:text-brand-500">Tahap 2</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Tambah Tahap 2</span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Pilih Berkas Perkara untuk Tahap 2
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Pilih Berkas Perkara yang telah menyelesaikan P-21 untuk melakukan Pelimpahan Tersangka &amp; Barang Bukti (Tahap II).
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @if ($availableBerkas->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 p-10 text-center dark:border-gray-800">
                    <i class="fi fi-rr-info text-3xl text-gray-400"></i>
                    <p class="mt-3 text-base font-semibold text-gray-700 dark:text-gray-300">
                        Tidak ada Berkas Perkara yang siap diproses ke Tahap 2.
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Tahapan Tahap 2 hanya dapat dicatat setelah tahapan P-21 selesai difinalkan.
                    </p>
                    <div class="mt-5">
                        <a
                            href="{{ route('p21.index') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600"
                        >
                            <i class="fi fi-rr-arrow-left text-xs"></i>
                            <span>Buka Daftar P-21</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($availableBerkas as $b)
                        <div class="flex flex-col justify-between rounded-xl border border-gray-200 p-4 transition hover:border-brand-400 hover:shadow-theme-xs dark:border-gray-800 dark:hover:border-brand-500/40">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-sm font-semibold text-brand-600 dark:text-brand-400">
                                        {{ $b->nomor_berkas ?: '(Draft Belum Bernomor)' }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        @if ($b->is_dpo)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                                <i class="fi fi-rr-ban text-[9px]"></i>
                                                DPO Aktif
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-[11px] font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                            P-21 Selesai
                                        </span>
                                    </div>
                                </div>
                                <h3 class="mt-2 text-sm font-medium text-gray-800 dark:text-white/90">
                                    Tersangka: {{ $b->tersangka?->nama ?: '-' }}
                                </h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Surat Perintah: {{ $b->perkara?->nomor_surat_perintah ?: '-' }}
                                </p>
                            </div>
                            <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <a
                                    href="{{ route('berkas-perkara.tahapan.create', ['berkas' => $b, 'jenis' => 'tahap_2']) }}"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600"
                                >
                                    <span>Lanjut ke Formulir Tahap 2</span>
                                    <i class="fi fi-rr-arrow-right text-xs"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
