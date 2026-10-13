@php
    $pageTitle = $mode === 'create'
        ? 'Input Tahapan: ' . $selectedJenis->label()
        : 'Edit Draft Tahapan: ' . $selectedJenis->label();
@endphp

@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-screen-xl px-4 pb-8 pt-6 lg:px-6">
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                <div class="flex items-center gap-2 font-medium">
                    <i class="fi fi-rr-exclamation text-base"></i>
                    <span>Terdapat beberapa kesalahan input:</span>
                </div>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Home</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('data-perkara.index') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Data Perkara</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('perkara.show', $berkas->perkara_id) }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Detail Perkara</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">{{ $selectedJenis->label() }}</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $pageTitle }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Tersangka: <strong class="text-gray-700 dark:text-gray-200">{{ $berkas->tersangka?->nama ?? '-' }}</strong> &bull;
                        Nomor Berkas: <strong class="text-gray-700 dark:text-gray-200">{{ $berkas->nomor_berkas ?: '(Draft)' }}</strong>
                    </p>
                </div>

                <div>
                    @if ($tahap?->isFinal())
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-success-200 bg-success-50 px-3.5 py-1.5 text-xs font-semibold text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-lock text-sm"></i>
                            Tahap Selesai / Terkunci Permanen
                        </span>
                    @elseif ($mode === 'edit')
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-yellow-200 bg-yellow-50 px-3.5 py-1.5 text-xs font-semibold text-yellow-800 dark:border-yellow-500/20 dark:bg-yellow-500/10 dark:text-yellow-400">
                            <i class="fi fi-rr-edit text-sm"></i>
                            Status: Draft (Belum Difinalkan)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 px-3.5 py-1.5 text-xs font-semibold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-400">
                            <i class="fi fi-rr-arrow-right text-sm"></i>
                            Tahapan Baru
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Card Referensi Tahapan Sebelumnya (PRD F-14 s.d. F-19) --}}
            @if (!empty($references))
                <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02] sm:p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-folders text-base text-brand-500"></i>
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Referensi Tahapan Sebelumnya</h2>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Tahap yang telah difinalkan &amp; terkunci</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($references as $ref)
                            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/40">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-semibold text-gray-800 text-xs dark:text-white/90">{{ $ref['nama'] }}</span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-success-600 dark:text-success-400">
                                        <i class="fi fi-rr-lock text-[10px]"></i>
                                        Final
                                    </span>
                                </div>
                                <p class="mt-1 font-mono text-xs text-brand-600 dark:text-brand-400">{{ $ref['nomor'] }}</p>
                                <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">Tanggal: {{ $ref['tanggal'] }}</p>
                                @if ($ref['dokumen'])
                                    <p class="mt-1 truncate text-[11px] text-gray-400">
                                        <i class="fi fi-rr-document text-[10px]"></i> {{ $ref['dokumen'] }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Form Tahapan --}}
            <form
                action="{{ $mode === 'create' ? route('berkas-perkara.tahapan.store', $berkas) : route('berkas-perkara.tahapan.update', [$berkas, $tahap]) }}"
                method="POST"
                enctype="multipart/form-data"
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
            >
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="space-y-5">
                    {{-- Pilihan Jenis Tahap (jika create dan ada beberapa opsi yang valid) --}}
                    @if ($mode === 'create')
                        @if (count($availableStages) > 1)
                            <div>
                                <label for="jenis_tahap" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Pilih Tahapan yang Ingin Dicatat <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="jenis_tahap"
                                    name="jenis_tahap"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    onchange="window.location.href = '{{ route('berkas-perkara.tahapan.create', $berkas) }}?jenis=' + this.value"
                                >
                                    @foreach ($availableStages as $stg)
                                        <option value="{{ $stg->value }}" {{ $selectedJenis === $stg ? 'selected' : '' }}>
                                            {{ $stg->label() }} {{ $stg->isOptional() ? '(Opsional)' : '(Wajib)' }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Tahapan bertanda (Opsional) dapat dicatat jika ada atau langsung dilewati ke tahap wajib berikutnya.
                                </p>
                            </div>
                        @else
                            <input type="hidden" name="jenis_tahap" value="{{ $selectedJenis->value }}">
                        @endif
                    @endif

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="nomor_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nomor Surat / Dokumen <span class="text-xs text-gray-400">(Wajib untuk Simpan &amp; Lanjut)</span>
                            </label>
                            <input
                                type="text"
                                id="nomor_surat"
                                name="nomor_surat"
                                value="{{ old('nomor_surat', $tahap?->nomor_surat) }}"
                                placeholder="Contoh: B-01/X/2026/PPNS-BPOM"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            >
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                <strong class="font-medium text-gray-700 dark:text-gray-300">Note:</strong> Format nomor surat resmi (contoh: B-01/X/2026/PPNS-BPOM).
                            </p>
                        </div>

                        <div>
                            <label for="tanggal_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tanggal Surat / Dokumen <span class="text-xs text-gray-400">(Wajib untuk Simpan &amp; Lanjut)</span>
                            </label>
                            <x-form.date-picker
                                id="tanggal_surat"
                                name="tanggal_surat"
                                placeholder="Pilih Tanggal Surat"
                                defaultDate="{{ old('tanggal_surat', $tahap?->tanggal_surat?->format('Y-m-d')) }}"
                            />
                        </div>
                    </div>

                    {{-- Upload Dokumen PDF --}}
                    <div>
                        <x-form.pdf-upload
                            name="dokumen"
                            label="Unggah Dokumen PDF Pendukung"
                            :existingFile="$tahap?->dokumen?->first()?->nama_file_asli"
                            :existingUrl="$tahap?->dokumen?->first() ? route('dokumen.preview', $tahap->dokumen->first()) : null"
                            helperText="Format file: PDF murni (Maksimal 20 MB)."
                        />
                    </div>

                    {{-- Catatan / Keterangan (PRD F-34) --}}
                    <div>
                        <label for="catatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Catatan / Deskripsi Tambahan <span class="text-xs text-gray-400">(Opsional)</span>
                        </label>
                        <textarea
                            id="catatan"
                            name="catatan"
                            rows="3"
                            placeholder="Catatan hasil koordinasi, pertimbangan hukum, atau detail tambahan..."
                            class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >{{ old('catatan', $tahap?->catatan) }}</textarea>
                    </div>
                </div>

                {{-- Tiga Aksi: Batal, Draft, Simpan & Lanjut (PRD F-14 s.d. F-19) --}}
                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-end">
                    {{-- 1. Tombol Batal --}}
                    <a
                        href="{{ route('perkara.show', $berkas->perkara_id) }}"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                    >
                        Batal
                    </a>

                    {{-- 2. Tombol Simpan Draft --}}
                    <button
                        type="submit"
                        name="action_type"
                        value="draft"
                        formnovalidate
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-yellow-300 bg-yellow-50 px-5 text-sm font-medium text-yellow-800 transition hover:bg-yellow-100 dark:border-yellow-500/30 dark:bg-yellow-500/10 dark:text-yellow-300 dark:hover:bg-yellow-500/20"
                    >
                        <i class="fi fi-rr-disk text-base"></i>
                        <span>Simpan sebagai Draft</span>
                    </button>

                    {{-- 3. Tombol Simpan & Lanjut Tahap Berikutnya --}}
                    <button
                        type="submit"
                        name="action_type"
                        value="lanjut"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 text-sm font-medium text-white transition hover:bg-brand-700"
                    >
                        <i class="fi fi-rr-check text-base"></i>
                        <span>Simpan &amp; Lanjut Tahap Berikutnya</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
