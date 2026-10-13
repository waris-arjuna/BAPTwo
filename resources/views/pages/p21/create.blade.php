@extends('layouts.app')

@section('content')
    @php
        $perkaraOptions = [
            [
                'id' => 1,
                'nomor' => 'PRK/001/2026',
                'tersangka' => 'Nama Tersangka 1',
                'penyidik' => 'Nama Penyidik 1',
                'tahap1_tanggal' => '15-09-2026',
                'p18' => true,
                'p19' => false,
            ],
            [
                'id' => 2,
                'nomor' => 'PRK/002/2026',
                'tersangka' => 'Nama Tersangka 2',
                'penyidik' => 'Nama Penyidik 2',
                'tahap1_tanggal' => '18-09-2026',
                'p18' => false,
                'p19' => false,
            ],
        ];
    @endphp

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
                    href="{{ url('/p21') }}"
                    class="transition hover:text-brand-500"
                >
                    P-21 / P21a
                </a>

                <span>/</span>

                <span class="text-gray-700 dark:text-gray-300">
                    Tambah
                </span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Tambah P-21 / P21a
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Masukkan data P-21/P21a untuk perkara yang sedang dalam proses penyidikan.
            </p>
        </div>

        {{-- Form --}}
        <div
            x-data="{
                selectedPerkara: '',
                perkara: @js($perkaraOptions)
            }"
            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
        >

            <form>

                <div class="space-y-6 p-5 sm:p-6">

                    {{-- Perkara --}}
                    <div>
                        <label
                            for="perkara"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Perkara <span class="text-error-500">*</span>
                        </label>

                        <select
                            id="perkara"
                            name="perkara"
                            x-model="selectedPerkara"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="">
                                Pilih Perkara
                            </option>

                            @foreach ($perkaraOptions as $perkara)
                                <option value="{{ $perkara['id'] }}">
                                    {{ $perkara['nomor'] }} — {{ $perkara['tersangka'] }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Hanya perkara yang sudah memiliki SPDP dan TAHAP 1 serta belum memiliki P-21/P21a yang dapat dipilih.
                        </p>
                    </div>

                    {{-- Informasi Perkara --}}
                    <template x-if="selectedPerkara">
                        <div class="rounded-xl border border-gray-200 dark:border-gray-800">

                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                    Informasi Perkara
                                </h2>

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Informasi berikut diambil dari data perkara.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-5 p-4 sm:grid-cols-2">

                                {{-- Nomor Perkara --}}
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Nomor Perkara
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90"
                                        x-text="perkara.find(item => item.id == selectedPerkara)?.nomor"
                                    ></p>
                                </div>

                                {{-- Tersangka --}}
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Tersangka
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90"
                                        x-text="perkara.find(item => item.id == selectedPerkara)?.tersangka"
                                    ></p>
                                </div>

                                {{-- Penyidik --}}
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Penyidik
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90"
                                        x-text="perkara.find(item => item.id == selectedPerkara)?.penyidik"
                                    ></p>
                                </div>

                                {{-- Tahap 1 --}}
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        TAHAP 1
                                    </p>

                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-success-50 text-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                            <i class="fi fi-rr-check text-[10px]"></i>
                                        </span>

                                        <span
                                            class="text-sm font-medium text-success-600 dark:text-success-400"
                                            x-text="'Sudah tersedia — ' + perkara.find(item => item.id == selectedPerkara)?.tahap1_tanggal"
                                        ></span>
                                    </div>
                                </div>

                                {{-- P18/P19 --}}
                                <div class="sm:col-span-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        P-18P / P-19
                                    </p>

                                    <template
                                        x-if="perkara.find(item => item.id == selectedPerkara)?.p18 || perkara.find(item => item.id == selectedPerkara)?.p19"
                                    >
                                        <div class="mt-1 flex flex-wrap gap-2">

                                            <template x-if="perkara.find(item => item.id == selectedPerkara)?.p18">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                                    <i class="fi fi-rr-check text-[10px]"></i> P-18P tersedia
                                                </span>
                                            </template>

                                            <template x-if="perkara.find(item => item.id == selectedPerkara)?.p19">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                                    <i class="fi fi-rr-check text-[10px]"></i> P-19 tersedia
                                                </span>
                                            </template>

                                        </div>
                                    </template>

                                    <template
                                        x-if="!perkara.find(item => item.id == selectedPerkara)?.p18 && !perkara.find(item => item.id == selectedPerkara)?.p19"
                                    >
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Tidak terdapat P-18P/P-19
                                        </p>
                                    </template>
                                </div>

                            </div>
                        </div>
                    </template>

                    {{-- Nomor --}}
                    <div>
                        <label
                            for="nomor"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Nomor P-21/P21a <span class="text-error-500">*</span>
                        </label>

                        <input
                            type="text"
                            id="nomor"
                            name="nomor"
                            placeholder="Masukkan nomor P-21/P21a"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500"
                        >
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label
                            for="tanggal"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Tanggal P-21/P21a <span class="text-error-500">*</span>
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
                            Dokumen P-21/P21a
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

                                    <i class="fi fi-rr-upload mx-auto text-2xl text-gray-400"></i>

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

                                    <i class="fi fi-rr-document text-base text-red-500"></i>

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
                        href="{{ url('/p21') }}"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                    >
                        Batal
                    </a>

                    <button
                        type="button"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600"
                    >
                        Simpan P-21/P21a
                    </button>

                </div>

            </form>
        </div>
    </div>
@endsection