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
                    <div>
                        <label
                            for="sprindik"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Sprindik <span class="text-error-500">*</span>
                        </label>

                        <select
                            id="sprindik"
                            name="sprindik"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="">
                                Pilih Sprindik
                            </option>

                            <option value="1">
                                SP/001/IX/2026 — 03-09-2026
                            </option>

                            <option value="3">
                                SP/003/IX/2026 — 08-09-2026
                            </option>
                        </select>

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Pilih Sprindik yang tersedia untuk dibuatkan SPDP.
                        </p>
                    </div>

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
                            placeholder="Masukkan nomor SPDP"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500"
                        >
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
                        <label
                            for="dokumen"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Dokumen SPDP
                        </label>

                        <div
                            x-data="{ fileName: '' }"
                            class="space-y-3"
                        >
                            <label
                                for="dokumen"
                                class="flex cursor-pointer items-center justify-center rounded-lg border border-dashed border-gray-300 px-4 py-6 transition hover:border-brand-500 dark:border-gray-700"
                            >
                                <div class="text-center">

                                    <svg
                                        class="mx-auto h-8 w-8 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M12 16V4m0 0L8 8m4-4 4 4M5 20h14"
                                        />
                                    </svg>

                                    <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Pilih File
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Format file yang diperbolehkan: PDF.
                                    </p>
                                </div>

                                <input
                                    type="file"
                                    id="dokumen"
                                    name="dokumen"
                                    accept=".pdf,application/pdf"
                                    class="hidden"
                                    @change="fileName = $event.target.files[0]?.name ?? ''"
                                >
                            </label>

                            <template x-if="fileName">
                                <div class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                                    <svg
                                        class="h-5 w-5 text-red-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                        />
                                    </svg>

                                    <span
                                        class="text-sm text-gray-700 dark:text-gray-300"
                                        x-text="fileName"
                                    ></span>
                                </div>
                            </template>
                        </div>
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