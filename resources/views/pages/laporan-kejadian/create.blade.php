@php
    $title = 'Tambah Laporan Kejadian';
@endphp

@extends('layouts.app')

@section('content')
    <div
        x-data="laporanKejadianForm()"
        class="mx-auto max-w-screen-xl px-4 pb-8 pt-6 lg:px-6"
    >
        {{-- Header --}}
        <div class="mb-6">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Home</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ url('/laporan-kejadian') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Laporan Kejadian</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Tambah</span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Tambah Laporan Kejadian</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Masukkan data laporan kejadian.</p>
        </div>

        <form @submit.prevent="submitDemo" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="nomor-laporan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nomor Laporan <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="nomor-laporan"
                            x-model="form.number"
                            type="text"
                            placeholder="Masukkan nomor laporan"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900/40 dark:text-white/90"
                        >
                    </div>

                    <div>
                        <label for="tanggal-laporan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Laporan <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="tanggal-laporan"
                            x-model="form.date"
                            type="date"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900/40 dark:text-white/90"
                        >
                    </div>

                    <div>
                        <label for="dokumen-laporan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Dokumen Laporan
                        </label>
                        <label
                            for="dokumen-laporan"
                            class="flex h-11 cursor-pointer items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 bg-white px-3 text-sm text-gray-600 transition hover:border-brand-400 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300"
                        >
                            <span class="truncate" x-text="fileName || 'Pilih File'"></span>
                            <span class="shrink-0 rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                Browse
                            </span>
                        </label>
                        <input id="dokumen-laporan" type="file" accept="application/pdf,.pdf" class="sr-only" @change="handleFile($event)">
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Format file yang diperbolehkan: PDF.</p>
                    </div>
                </div>

                <div x-show="submitted" x-cloak class="mt-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
                    Data contoh berhasil disimpan untuk tampilan frontend. Belum terhubung ke backend.
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-4 py-4 dark:border-gray-800 sm:flex-row sm:justify-end sm:px-6">
                <a
                    href="{{ url('/laporan-kejadian') }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                >
                    Simpan Laporan
                </button>
            </div>
        </form>
    </div>

    <script>
        function laporanKejadianForm() {
            return {
                form: {
                    number: '',
                    date: '',
                },
                fileName: '',
                submitted: false,

                handleFile(event) {
                    const file = event.target.files?.[0];
                    this.fileName = file ? file.name : '';
                },

                submitDemo() {
                    this.submitted = true;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
            };
        }
    </script>
@endsection
