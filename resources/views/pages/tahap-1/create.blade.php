@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-500">
                    Home
                </a>

                <span>/</span>

                <a href="{{ url('/tahap-1') }}" class="transition hover:text-brand-500">
                    Tahap 1
                </a>

                <span>/</span>

                <span class="text-gray-700 dark:text-gray-300">
                    Tambah
                </span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Tambah Tahap 1
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Masukkan data dan dokumen pelaksanaan Tahap 1.
            </p>
        </div>

        {{-- Form --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <form>

                <div class="space-y-6 p-5 sm:p-6">

                    {{-- SPDP --}}
                    <div>
                        <label
                            for="spdp"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            SPDP <span class="text-error-500">*</span>
                        </label>

                        <select
                            id="spdp"
                            name="spdp"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="">
                                Pilih SPDP
                            </option>

                            <option value="1">
                                SPDP/001/IX/2026 — 04-09-2026
                            </option>

                            <option value="2">
                                SPDP/002/IX/2026 — 06-09-2026
                            </option>
                        </select>

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Pilih SPDP yang tersedia dan belum memiliki data Tahap 1.
                        </p>
                    </div>

                    {{-- Nomor --}}
                    <div>
                        <label
                            for="nomor"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Nomor Tahap 1
                        </label>

                        <input
                            type="text"
                            id="nomor"
                            name="nomor"
                            placeholder="Masukkan nomor dokumen Tahap 1"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500"
                        >
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label
                            for="tanggal"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Tanggal Tahap 1 <span class="text-error-500">*</span>
                        </label>

                        <input
                            type="date"
                            id="tanggal"
                            name="tanggal"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
                        >
                    </div>

                    {{-- Dokumen BAP --}}
                    <div>
                        <label
                            for="dokumen_bap"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            BAP <span class="text-error-500">*</span>
                        </label>

                        <div
                            x-data="{ fileName: '' }"
                            class="space-y-3"
                        >
                            <label
                                for="dokumen_bap"
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
                                        Pilih File BAP
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Format file yang diperbolehkan: PDF.
                                    </p>
                                </div>

                                <input
                                    type="file"
                                    id="dokumen_bap"
                                    name="dokumen_bap"
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
                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2h12a2 2 0 0 0 2-2V8z"
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

                    {{-- Catatan --}}
                    <div>
                        <label
                            for="catatan"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Catatan <span class="text-error-500">*</span>
                        </label>

                        <textarea
                            id="catatan"
                            name="catatan"
                            rows="4"
                            placeholder="Masukkan catatan Tahap 1..."
                            class="w-full resize-none rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500"
                        ></textarea>
                    </div>

                    {{-- Dokumen Opsional --}}
                    <div class="border-t border-gray-200 pt-6 dark:border-gray-800">

                        <div class="mb-4">
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                Dokumen Pendukung
                            </h2>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                P-16 dan P-17P bersifat opsional dan dapat ditambahkan sebelum Tahap 1 diselesaikan.
                            </p>
                        </div>


                        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2">
                            {{-- INPUT P-16 --}}
                            <div>
                                {{-- Tanggal --}}
                                <div>
                                    <label
                                        for="tanggal"
                                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Tanggal P-16 <span class="text-error-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        id="tanggal"
                                        name="tanggal"
                                        class="h-11 min-w-md rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
                                    >
                                </div>
                                <label
                                    for="p16"
                                    class="mb-2 mt-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    P-16
                                </label>
                                <input
                                    type="file"
                                    id="p16"
                                    name="p16"
                                    accept=".pdf,application/pdf"
                                    class="block min-w-md rounded-lg border border-gray-300 bg-transparent text-sm text-gray-700 file:mr-4 file:border-0 file:border-r file:border-gray-300 file:bg-gray-50 file:px-4 file:py-2.5 file:text-sm dark:border-gray-700 dark:text-gray-300 dark:file:border-gray-700 dark:file:bg-white/[0.03]"
                                >

                            </div>

                            {{-- INPUT P-17 --}}
                            <div>
                                {{-- Tanggal --}}
                                <div>
                                    <label
                                        for="tanggal"
                                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Tanggal P-17 <span class="text-error-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        id="tanggal"
                                        name="tanggal"
                                        class="h-11 min-w-md rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
                                    >
                                </div>
                                <label
                                    for="p17"
                                    class="mb-2 mt-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    P-17
                                </label>
                                <input
                                    type="file"
                                    id="p17"
                                    name="p17"
                                    accept=".pdf,application/pdf"
                                    class="block min-w-md rounded-lg border border-gray-300 bg-transparent text-sm text-gray-700 file:mr-4 file:border-0 file:border-r file:border-gray-300 file:bg-gray-50 file:px-4 file:py-2.5 file:text-sm dark:border-gray-700 dark:text-gray-300 dark:file:border-gray-700 dark:file:bg-white/[0.03]"
                                >

                            </div>

                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:justify-end dark:border-gray-800">

                    <a
                        href="{{ url('/tahap-1') }}"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                    >
                        Batal
                    </a>

                    <button
                        type="button"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600"
                    >
                        Simpan Tahap 1
                    </button>

                </div>

            </form>
        </div>
    </div>
@endsection