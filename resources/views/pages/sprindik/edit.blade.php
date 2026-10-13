@php
    $title = 'Edit Sprindik';
@endphp

@extends('layouts.app')

@section('content')
    <div
        x-data="{ fileName: '' }"
        class="mx-auto max-w-screen-xl px-4 pb-8 pt-6 lg:px-6"
    >
        {{-- Header --}}
        <div class="mb-6">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Home</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('sprindik.index') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Sprindik</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Edit</span>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Draft Sprindik</h1>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                    <i class="fi fi-rr-edit text-xs"></i>
                    Status: Draft
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui draft Surat Perintah Penyidikan atau finalkan untuk mengunci tahap ini.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                <div class="flex items-center gap-2 font-semibold">
                    <i class="fi fi-rr-exclamation text-base"></i>
                    <span>Terdapat kesalahan pengisian form:</span>
                </div>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Card Referensi Tahapan Sebelumnya (Laporan Kejadian) --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02] sm:p-5">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fi fi-rr-folder-open text-base text-brand-500"></i>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Referensi Tahapan Sebelumnya: Laporan Kejadian</h2>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                    <i class="fi fi-rr-lock text-[10px]"></i>
                    Tahap Final &amp; Terkunci
                </span>
            </div>
            <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Nomor Laporan Kejadian</p>
                    <a href="{{ route('laporan-kejadian.show', $perkara) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                        {{ $perkara->nomor_laporan_kejadian }}
                    </a>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Laporan</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen Laporan</p>
                    @if ($perkara->dokumenLaporan)
                        <a href="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}" target="_blank" class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400">
                            <i class="fi fi-rr-document text-xs"></i>
                            <span>{{ $perkara->dokumenLaporan->nama_file_asli }}</span>
                        </a>
                    @else
                        <span class="text-xs italic text-gray-400">Tidak ada dokumen</span>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('sprindik.update', $perkara) }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @method('PUT')

            <div class="space-y-6 p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    {{-- Nomor Sprindik --}}
                    <div>
                        <label for="nomor_sprindik" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nomor Sprindik <span class="text-error-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="nomor_sprindik"
                            name="nomor_sprindik"
                            value="{{ old('nomor_sprindik', $perkara->nomor_surat_perintah) }}"
                            required
                            placeholder="Contoh: SP/001/IX/2026"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900/40 dark:text-white/90"
                        >
                    </div>

                    {{-- Tanggal Sprindik --}}
                    <div>
                        <label for="tanggal_sprindik" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Sprindik <span class="text-error-500">*</span>
                        </label>
                        <x-form.date-picker
                            id="tanggal_sprindik"
                            name="tanggal_sprindik"
                            :value="old('tanggal_sprindik', $perkara->tanggal_surat_perintah?->format('Y-m-d'))"
                            required
                            placeholder="Pilih Tanggal Sprindik"
                        />
                    </div>

                    {{-- Dokumen Sprindik --}}
                    <div class="md:col-span-2">
                        <x-form.pdf-upload
                            name="dokumen_sprindik"
                            label="Dokumen Sprindik (PDF)"
                            :existingFile="$perkara->dokumenSprindik?->nama_file_asli"
                            :existingUrl="$perkara->dokumenSprindik ? route('dokumen.preview', $perkara->dokumenSprindik) : null"
                            helperText="Biarkan kosong jika tidak ingin mengubah dokumen (PDF Maksimal 20 MB)."
                        />
                    </div>

                    {{-- Deskripsi --}}
                    <div class="md:col-span-2">
                        <label for="deskripsi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Keterangan Tambahan
                        </label>
                        <textarea
                            id="deskripsi"
                            name="deskripsi"
                            rows="3"
                            placeholder="Catatan tambahan mengenai penerbitan surat perintah..."
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900/40 dark:text-white/90"
                        >{{ old('deskripsi', $perkara->deskripsi) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-4 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-end sm:px-6">
                {{-- 1. Tombol Batal --}}
                <a
                    href="{{ route('sprindik.show', $perkara) }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                >
                    Batal
                </a>

                {{-- 2. Tombol Simpan Draft --}}
                <button
                    type="submit"
                    name="action_type"
                    value="draft"
                    formnovalidate
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-brand-300 bg-brand-50 px-4 text-sm font-medium text-brand-700 transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20"
                >
                    <i class="fi fi-rr-disk text-sm"></i>
                    <span>Simpan Draft</span>
                </button>

                {{-- 3. Tombol Simpan & Lanjut Tahap Berikutnya --}}
                <button
                    type="submit"
                    name="action_type"
                    value="lanjut"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                >
                    <i class="fi fi-rr-check-circle text-sm"></i>
                    <span>Simpan &amp; Lanjut Tahap Berikutnya</span>
                </button>
            </div>
        </form>
    </div>
@endsection
