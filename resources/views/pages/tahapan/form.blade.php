@php
    $pageTitle = $mode === 'create'
        ? 'Input Tahapan: ' . $selectedJenis->label()
        : ($isLocked ? 'Kelola Dokumen: ' . $selectedJenis->label() : 'Edit Draft Tahapan: ' . $selectedJenis->label());

    $p16 = $subTahapan[\App\Enums\JenisTahap::P16->value] ?? null;
    $p17p = $subTahapan[\App\Enums\JenisTahap::P17P->value] ?? null;
    $p18p = $subTahapan[\App\Enums\JenisTahap::P18P->value] ?? null;
    $p19 = $subTahapan[\App\Enums\JenisTahap::P19->value] ?? null;
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
                            Tahap Final &amp; Terkunci
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
            {{-- Alert DPO Gate khusus Tahap II --}}
            @if ($selectedJenis === \App\Enums\JenisTahap::TAHAP_2 && $berkas->is_dpo)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    <div class="flex items-center gap-2 font-semibold">
                        <i class="fi fi-rr-ban text-base text-red-600"></i>
                        <span>Peringatan: Tersangka Berstatus DPO (Daftar Pencarian Orang)</span>
                    </div>
                    <p class="mt-1 text-xs text-red-700 dark:text-red-400">
                        Pelimpahan Tersangka &amp; Barang Bukti (Tahap II) tidak dapat difinalkan selama status DPO tersangka masih aktif. Anda hanya dapat menyimpan form ini sebagai Draft sementara hingga status DPO dicabut.
                    </p>
                </div>
            @endif

            {{-- Card Referensi Tahapan Sebelumnya --}}
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
                class="space-y-6"
            >
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                {{-- CARD UTAMA TAHAPAN --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-document-signed text-xl text-brand-600"></i>
                            <div>
                                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                    Data Dokumen Utama: {{ $selectedJenis->label() }}
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Kontribusi Progress: <span class="font-semibold text-brand-600 dark:text-brand-400">{{ $selectedJenis->bobot() }}%</span>
                                </p>
                            </div>
                        </div>

                        @if ($isLocked)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-3 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                <i class="fi fi-rr-lock text-xs"></i>
                                Data Utama Terkunci Permanen
                            </span>
                        @endif
                    </div>

                    <div class="space-y-5">
                        {{-- Pilihan Jenis Tahap (jika create dan ada beberapa opsi yang valid) --}}
                        @if ($mode === 'create')
                            @if (count($availableStages) > 1)
                                <div>
                                    <x-form.searchable-select
                                        id="jenis_tahap"
                                        name="jenis_tahap"
                                        label="Pilih Tahapan yang Ingin Dicatat"
                                        placeholder="-- Pilih Tahapan --"
                                        search-placeholder="Ketik nama tahapan..."
                                        :value="$selectedJenis->value"
                                        :clearable="false"
                                        hint="Tahapan bertanda (Opsional) dapat dicatat jika ada atau langsung dilewati ke tahap wajib berikutnya."
                                        @change="window.location.href = '{{ route('berkas-perkara.tahapan.create', $berkas) }}?jenis=' + $event.target.value"
                                        required
                                    >
                                        @foreach ($availableStages as $stg)
                                            <option value="{{ $stg->value }}" {{ $selectedJenis === $stg ? 'selected' : '' }}>
                                                {{ $stg->label() }} {{ $stg->isOptional() ? '(Opsional)' : '(Wajib)' }}
                                            </option>
                                        @endforeach
                                    </x-form.searchable-select>
                                </div>
                            @else
                                <input type="hidden" name="jenis_tahap" value="{{ $selectedJenis->value }}">
                            @endif
                        @endif

                        @if ($isLocked)
                            {{-- Tampilan Read-Only Data Utama Jika Sudah Final --}}
                            <div class="grid grid-cols-1 gap-5 rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02] sm:grid-cols-2">
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Nomor Surat / Dokumen</span>
                                    <p class="mt-1 font-mono text-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ $tahap->nomor_surat ?: '-' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Tanggal Surat</span>
                                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ $tahap->tanggal_surat?->format('d-m-Y') ?? '-' }}
                                    </p>
                                </div>
                                @if ($tahap->dokumen->isNotEmpty())
                                    <div class="sm:col-span-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Dokumen Lampiran</span>
                                        <div class="mt-1 flex items-center gap-2">
                                            <a
                                                href="{{ route('dokumen.preview', $tahap->dokumen->first()) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:underline dark:text-brand-400"
                                                data-pdf-preview
                                                data-pdf-title="{{ $tahap->dokumen->first()->nama_file_asli }}"
                                                data-pdf-download="{{ route('dokumen.download', $tahap->dokumen->first()) }}"
                                            >
                                                <i class="fi fi-rr-document text-xs"></i>
                                                <span>{{ $tahap->dokumen->first()->nama_file_asli }}</span>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ($tahap->catatan)
                                    <div class="sm:col-span-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Catatan</span>
                                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $tahap->catatan }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Field Input Data Utama --}}
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

                            {{-- Catatan / Keterangan --}}
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
                        @endif
                    </div>
                </div>

                {{-- SUB-TAHAP TERSEMAT PADA FORM SPDP: P-16 & P-17 --}}
                @if ($selectedJenis === \App\Enums\JenisTahap::SPDP)
                    <div class="space-y-6">
                        {{-- 1. Bagian P-16 (Opsional) --}}
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            <div class="mb-5 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pb-4 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <i class="fi fi-rr-user-police text-xl text-brand-600"></i>
                                    <div>
                                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                            P-16: Penunjukan Jaksa Penuntut Umum (Opsional)
                                        </h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Dicatat apabila telah menerima Surat Perintah Penunjukan JPU dari Kejaksaan.
                                        </p>
                                    </div>
                                </div>
                                @if ($p16)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                        <i class="fi fi-rr-check text-xs"></i>
                                        Tercatat
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-5">
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="p16_nomor_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nomor Surat P-16 <span class="text-xs text-gray-400">(Opsional)</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="p16_nomor_surat"
                                            name="p16_nomor_surat"
                                            value="{{ old('p16_nomor_surat', $p16?->nomor_surat) }}"
                                            placeholder="Contoh: PRINT-12/O.3.10/Fd.1/02/2026"
                                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        >
                                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                            <strong class="font-medium text-gray-700 dark:text-gray-300">Note:</strong> Format nomor surat penunjukan JPU dari Kejaksaan.
                                        </p>
                                    </div>

                                    <div>
                                        <label for="p16_tanggal_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tanggal Surat P-16 <span class="text-xs text-gray-400">(Opsional)</span>
                                        </label>
                                        <x-form.date-picker
                                            id="p16_tanggal_surat"
                                            name="p16_tanggal_surat"
                                            placeholder="Pilih Tanggal Surat P-16"
                                            defaultDate="{{ old('p16_tanggal_surat', $p16?->tanggal_surat?->format('Y-m-d')) }}"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <x-form.pdf-upload
                                        id="p16_dokumen"
                                        name="p16_dokumen"
                                        label="Unggah Dokumen PDF P-16"
                                        :existingFile="$p16?->dokumen?->first()?->nama_file_asli"
                                        :existingUrl="$p16?->dokumen?->first() ? route('dokumen.preview', $p16->dokumen->first()) : null"
                                        helperText="Format file: PDF murni (Maksimal 20 MB)."
                                    />
                                </div>

                                <div>
                                    <label for="p16_catatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Catatan / Nama JPU yang Ditunjuk <span class="text-xs text-gray-400">(Opsional)</span>
                                    </label>
                                    <textarea
                                        id="p16_catatan"
                                        name="p16_catatan"
                                        rows="2"
                                        placeholder="Contoh: JPU Utama: Ahmad S.H., M.H. (Kejari Banjarmasin)..."
                                        class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    >{{ old('p16_catatan', $p16?->catatan) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Bagian P-17 (Opsional) --}}
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            <div class="mb-5 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pb-4 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <i class="fi fi-rr-time-forward text-xl text-amber-600"></i>
                                    <div>
                                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                            P-17: Permintaan Perkembangan Penyidikan (Opsional)
                                        </h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Dicatat apabila Kejaksaan mengirimkan surat permintaan perkembangan penyidikan perkara.
                                        </p>
                                    </div>
                                </div>
                                @if ($p17p)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                        <i class="fi fi-rr-check text-xs"></i>
                                        Tercatat
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-5">
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="p17p_nomor_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nomor Surat P-17 <span class="text-xs text-gray-400">(Opsional)</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="p17p_nomor_surat"
                                            name="p17p_nomor_surat"
                                            value="{{ old('p17p_nomor_surat', $p17p?->nomor_surat) }}"
                                            placeholder="Contoh: B-450/O.3.10/Fd.1/03/2026"
                                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        >
                                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                            <strong class="font-medium text-gray-700 dark:text-gray-300">Note:</strong> Format nomor surat permintaan perkembangan dari Kejaksaan.
                                        </p>
                                    </div>

                                    <div>
                                        <label for="p17p_tanggal_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tanggal Surat P-17 <span class="text-xs text-gray-400">(Opsional)</span>
                                        </label>
                                        <x-form.date-picker
                                            id="p17p_tanggal_surat"
                                            name="p17p_tanggal_surat"
                                            placeholder="Pilih Tanggal Surat P-17"
                                            defaultDate="{{ old('p17p_tanggal_surat', $p17p?->tanggal_surat?->format('Y-m-d')) }}"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <x-form.pdf-upload
                                        id="p17p_dokumen"
                                        name="p17p_dokumen"
                                        label="Unggah Dokumen PDF P-17"
                                        :existingFile="$p17p?->dokumen?->first()?->nama_file_asli"
                                        :existingUrl="$p17p?->dokumen?->first() ? route('dokumen.preview', $p17p->dokumen->first()) : null"
                                        helperText="Format file: PDF murni (Maksimal 20 MB)."
                                    />
                                </div>

                                <div>
                                    <label for="p17p_catatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Catatan / Keterangan P-17 <span class="text-xs text-gray-400">(Opsional)</span>
                                    </label>
                                    <textarea
                                        id="p17p_catatan"
                                        name="p17p_catatan"
                                        rows="2"
                                        placeholder="Catatan mengenai permintaan tindak lanjut atau batas waktu jawaban..."
                                        class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    >{{ old('p17p_catatan', $p17p?->catatan) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- SUB-TAHAP TERSEMAT PADA FORM TAHAP 1: P-18 & P-19 --}}
                @if ($selectedJenis === \App\Enums\JenisTahap::TAHAP_1)
                    <div class="space-y-6">
                        {{-- 1. Bagian P-18 (Opsional) --}}
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            <div class="mb-5 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pb-4 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <i class="fi fi-rr-document-signed text-xl text-amber-600"></i>
                                    <div>
                                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                            P-18: Hasil Penyidikan Belum Lengkap (Opsional)
                                        </h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Pemberitahuan dari Kejaksaan bahwa hasil penyidikan berkas perkara belum lengkap.
                                        </p>
                                    </div>
                                </div>
                                @if ($p18p)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                        <i class="fi fi-rr-check text-xs"></i>
                                        Tercatat
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-5">
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="p18p_nomor_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nomor Surat P-18 <span class="text-xs text-gray-400">(Opsional)</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="p18p_nomor_surat"
                                            name="p18p_nomor_surat"
                                            value="{{ old('p18p_nomor_surat', $p18p?->nomor_surat) }}"
                                            placeholder="Contoh: B-512/O.3.10/Fd.1/04/2026"
                                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        >
                                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                            <strong class="font-medium text-gray-700 dark:text-gray-300">Note:</strong> Format nomor surat pemberitahuan hasil penyidikan belum lengkap.
                                        </p>
                                    </div>

                                    <div>
                                        <label for="p18p_tanggal_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tanggal Surat P-18 <span class="text-xs text-gray-400">(Opsional)</span>
                                        </label>
                                        <x-form.date-picker
                                            id="p18p_tanggal_surat"
                                            name="p18p_tanggal_surat"
                                            placeholder="Pilih Tanggal Surat P-18"
                                            defaultDate="{{ old('p18p_tanggal_surat', $p18p?->tanggal_surat?->format('Y-m-d')) }}"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <x-form.pdf-upload
                                        id="p18p_dokumen"
                                        name="p18p_dokumen"
                                        label="Unggah Dokumen PDF P-18"
                                        :existingFile="$p18p?->dokumen?->first()?->nama_file_asli"
                                        :existingUrl="$p18p?->dokumen?->first() ? route('dokumen.preview', $p18p->dokumen->first()) : null"
                                        helperText="Format file: PDF murni (Maksimal 20 MB)."
                                    />
                                </div>

                                <div>
                                    <label for="p18p_catatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Catatan / Keterangan P-18 <span class="text-xs text-gray-400">(Opsional)</span>
                                    </label>
                                    <textarea
                                        id="p18p_catatan"
                                        name="p18p_catatan"
                                        rows="2"
                                        placeholder="Catatan mengenai kekurangan berkas penyidikan..."
                                        class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    >{{ old('p18p_catatan', $p18p?->catatan) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Bagian P-19 (Opsional) --}}
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            <div class="mb-5 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pb-4 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <i class="fi fi-rr-undo text-xl text-orange-600"></i>
                                    <div>
                                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                            P-19: Pengembalian Berkas Perkara untuk Dilengkapi (Opsional)
                                        </h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Pengembalian berkas perkara dari Kejaksaan disertai petunjuk kelengkapan penyidikan.
                                        </p>
                                    </div>
                                </div>
                                @if ($p19)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">
                                        <i class="fi fi-rr-check text-xs"></i>
                                        Tercatat
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-5">
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="p19_nomor_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nomor Surat P-19 <span class="text-xs text-gray-400">(Opsional)</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="p19_nomor_surat"
                                            name="p19_nomor_surat"
                                            value="{{ old('p19_nomor_surat', $p19?->nomor_surat) }}"
                                            placeholder="Contoh: B-602/O.3.10/Fd.1/04/2026"
                                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        >
                                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                            <strong class="font-medium text-gray-700 dark:text-gray-300">Note:</strong> Format nomor surat pengembalian berkas perkara dan petunjuk.
                                        </p>
                                    </div>

                                    <div>
                                        <label for="p19_tanggal_surat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tanggal Surat P-19 <span class="text-xs text-gray-400">(Opsional)</span>
                                        </label>
                                        <x-form.date-picker
                                            id="p19_tanggal_surat"
                                            name="p19_tanggal_surat"
                                            placeholder="Pilih Tanggal Surat P-19"
                                            defaultDate="{{ old('p19_tanggal_surat', $p19?->tanggal_surat?->format('Y-m-d')) }}"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <x-form.pdf-upload
                                        id="p19_dokumen"
                                        name="p19_dokumen"
                                        label="Unggah Dokumen PDF P-19"
                                        :existingFile="$p19?->dokumen?->first()?->nama_file_asli"
                                        :existingUrl="$p19?->dokumen?->first() ? route('dokumen.preview', $p19->dokumen->first()) : null"
                                        helperText="Format file: PDF murni (Maksimal 20 MB)."
                                    />
                                </div>

                                <div>
                                    <label for="p19_catatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Catatan / Ringkasan Petunjuk P-19 <span class="text-xs text-gray-400">(Opsional)</span>
                                    </label>
                                    <textarea
                                        id="p19_catatan"
                                        name="p19_catatan"
                                        rows="2"
                                        placeholder="Catatan petunjuk dari Kejaksaan yang perlu dilengkapi penyidik..."
                                        class="w-full rounded-lg border border-gray-300 bg-transparent p-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    >{{ old('p19_catatan', $p19?->catatan) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- AKSI TOMBOL FORM --}}
                <div class="flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:flex-row sm:items-center sm:justify-end sm:p-5">
                    {{-- 1. Tombol Batal --}}
                    <a
                        href="{{ $tahap ? route('berkas-perkara.tahapan.show', [$berkas, $tahap]) : route('perkara.show', $berkas->perkara_id) }}"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                    >
                        Batal
                    </a>

                    @if ($isLocked)
                        {{-- Mode Update Sub-Tahap Saja (Tahapan Utama Sudah Final) --}}
                        <button
                            type="submit"
                            name="action_type"
                            value="draft"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 text-sm font-medium text-white transition hover:bg-brand-700"
                        >
                            <i class="fi fi-rr-disk text-base"></i>
                            <span>Simpan Perubahan Sub-Tahap</span>
                        </button>
                    @else
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
                        @if ($selectedJenis === \App\Enums\JenisTahap::TAHAP_2 && $berkas->is_dpo)
                            <button
                                type="button"
                                disabled
                                title="Tidak dapat memfinalkan Tahap II karena tersangka berstatus DPO"
                                class="inline-flex h-11 cursor-not-allowed items-center justify-center gap-2 rounded-lg bg-gray-300 px-6 text-sm font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-500"
                            >
                                <i class="fi fi-rr-ban text-base"></i>
                                <span>Terkunci (Status DPO Aktif)</span>
                            </button>
                        @else
                            <button
                                type="submit"
                                name="action_type"
                                value="lanjut"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 text-sm font-medium text-white transition hover:bg-brand-700"
                            >
                                <i class="fi fi-rr-check text-base"></i>
                                <span>{{ $selectedJenis === \App\Enums\JenisTahap::TAHAP_2 ? 'Simpan & Selesaikan Berkas' : 'Simpan & Lanjut Tahap Berikutnya' }}</span>
                            </button>
                        @endif
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
