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

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Sprindik</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui data Surat Perintah Penyidikan.</p>
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

        <form action="{{ route('sprindik.update', $perkara) }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @method('PUT')

            <div class="space-y-6 p-4 sm:p-6">
                {{-- Laporan Kejadian Terkait (Read Only) --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Laporan Kejadian Terkait
                    </label>
                    <input
                        type="text"
                        disabled
                        value="{{ $perkara->nomor_laporan_kejadian }} ({{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') }})"
                        class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                </div>

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
                        <label for="dokumen_sprindik" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Dokumen Sprindik (PDF)
                        </label>
                        <label
                            for="dokumen_sprindik"
                            class="flex h-11 cursor-pointer items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 bg-white px-3 text-sm text-gray-600 transition hover:border-brand-400 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300"
                        >
                            <span class="truncate" x-text="fileName || '{{ $perkara->dokumenSprindik ? $perkara->dokumenSprindik->nama_file_asli : 'Pilih File PDF Sprindik' }}'"></span>
                            <span class="shrink-0 rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                Browse
                            </span>
                        </label>
                        <input
                            id="dokumen_sprindik"
                            name="dokumen_sprindik"
                            type="file"
                            accept="application/pdf,.pdf"
                            class="sr-only"
                            @change="fileName = $event.target.files[0]?.name || ''"
                        >
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Biarkan kosong jika tidak ingin mengubah dokumen (PDF Maksimal 20 MB).</p>
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

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-4 py-4 dark:border-gray-800 sm:flex-row sm:justify-end sm:px-6">
                <a
                    href="{{ route('sprindik.show', $perkara) }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                >
                    Perbarui Sprindik
                </button>
            </div>
        </form>
    </div>
@endsection
