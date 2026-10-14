@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a
                    href="{{ url('/') }}"
                    class="transition hover:text-brand-500"
                >
                    Home
                </a>

                <span>/</span>

                <a
                    href="{{ url('/spdp') }}"
                    class="transition hover:text-brand-500"
                >
                    SPDP
                </a>

                <span>/</span>

                <span class="text-gray-700 dark:text-gray-300">
                    Tambah
                </span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Tambah SPDP
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Masukkan data Surat Pemberitahuan Dimulainya Penyidikan.
            </p>
        </div>

        {{-- Form --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <form>

                <div class="space-y-6 p-5 sm:p-6">

                    {{-- Sprindik --}}
                    <x-form.searchable-select
                        name="sprindik"
                        id="sprindik"
                        label="Sprindik"
                        placeholder="Pilih Sprindik"
                        search-placeholder="Ketik nomor sprindik atau tanggal..."
                        hint="Pilih Sprindik yang tersedia untuk dibuatkan SPDP."
                        required
                    >
                        <option value="">Pilih Sprindik</option>
                        <option value="1">SP/001/IX/2026 — 03-09-2026</option>
                        <option value="3">SP/003/IX/2026 — 08-09-2026</option>
                    </x-form.searchable-select>

                    {{-- Nomor SPDP --}}
                    <div>
                        <label
                            for="nomor"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Nomor SPDP <span class="text-error-500">*</span>
                        </label>

                        <input
                            type="text"
                            id="nomor"
                            name="nomor"
                            placeholder="Contoh: B-01/X/2026/PPNS-BPOM"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500"
                        >
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                            <strong class="font-medium text-gray-700 dark:text-gray-300">Note:</strong> Format penomoran SPDP resmi (contoh: B-01/X/2026/PPNS-BPOM).
                        </p>
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label
                            for="tanggal"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Tanggal SPDP <span class="text-error-500">*</span>
                        </label>

                        <input
                            type="date"
                            id="tanggal"
                            name="tanggal"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
                        >
                    </div>

                    {{-- Dokumen --}}
                    <div>
                        <x-form.pdf-upload
                            name="dokumen"
                            label="Dokumen SPDP"
                        />
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:justify-end dark:border-gray-800">

                    <a
                        href="{{ url('/spdp') }}"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                    >
                        Batal
                    </a>

                    <button
                        type="button"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600"
                    >
                        Simpan SPDP
                    </button>

                </div>

            </form>
        </div>
    </div>
@endsection