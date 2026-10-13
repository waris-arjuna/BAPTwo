@php
    $title = 'Detail Tahapan: ' . $tahap->jenis_tahap->label();
@endphp

@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-screen-xl px-4 pb-8 pt-6 lg:px-6">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
                <i class="fi fi-rr-check text-base"></i>
                <span>{{ session('success') }}</span>
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
                <span class="text-gray-700 dark:text-gray-300">{{ $tahap->jenis_tahap->label() }}</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Tersangka: <strong class="text-gray-700 dark:text-gray-200">{{ $berkas->tersangka?->nama ?? '-' }}</strong> &bull;
                        Nomor Berkas: <strong class="text-gray-700 dark:text-gray-200">{{ $berkas->nomor_berkas ?: '(Draft)' }}</strong>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('perkara.show', $berkas->perkara_id) }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                    >
                        Kembali ke Detail Perkara
                    </a>

                    @if ($tahap->isFinal())
                        <span class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-success-200 bg-success-50 px-3.5 text-xs font-semibold text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-lock text-sm"></i>
                            Tahap Selesai / Terkunci Permanen
                        </span>

                        @can('create-cases')
                            <a
                                href="{{ route('berkas-perkara.tahapan.create', $berkas) }}"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700"
                            >
                                <i class="fi fi-rr-arrow-right text-sm"></i>
                                <span>Input Tahap Selanjutnya</span>
                            </a>
                        @endcan
                    @else
                        @can('edit-draft')
                            <a
                                href="{{ route('berkas-perkara.tahapan.edit', [$berkas, $tahap]) }}"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700"
                            >
                                <i class="fi fi-rr-edit text-base"></i>
                                <span>Lanjutkan / Edit Draft</span>
                            </a>
                        @endcan
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

            {{-- Card Detail Informasi Tahapan (Mode Read-Only Terkunci) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-document-signed text-xl text-brand-500"></i>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            Data Administrasi Tahapan: {{ $tahap->jenis_tahap->label() }}
                        </h2>
                    </div>

                    @if ($tahap->isFinal())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-3 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-lock text-xs"></i>
                            Terkunci Permanen pada {{ $tahap->finalized_at?->format('d-m-Y H:i') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-50 px-3 py-1 text-xs font-semibold text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400">
                            <i class="fi fi-rr-edit text-xs"></i>
                            Draft Sementara
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nomor Surat / Dokumen</p>
                        <p class="mt-1 font-mono text-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $tahap->nomor_surat ?: '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Surat / Dokumen</p>
                        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $tahap->tanggal_surat?->format('d-m-Y') ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Bobot Kontribusi Tahap</p>
                        <p class="mt-1 text-sm font-semibold text-brand-600 dark:text-brand-400">
                            {{ $tahap->jenis_tahap->bobot() }}%
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dicatat Oleh</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $tahap->createdBy?->nama ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Difinalkan Oleh</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $tahap->finalizedBy?->nama ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen Lampiran (PDF)</p>
                        <div class="mt-1">
                            @if ($tahap->dokumen->isNotEmpty())
                                @foreach ($tahap->dokumen as $doc)
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('dokumen.preview', $doc) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            <i class="fi fi-rr-document text-xs"></i>
                                            <span class="truncate">{{ $doc->nama_file_asli }}</span>
                                        </a>
                                        <a href="{{ route('dokumen.download', $doc) }}" class="text-xs text-gray-400 hover:text-gray-600" title="Unduh">
                                            <i class="fi fi-rr-download text-xs"></i>
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-xs italic text-gray-400">Tidak ada dokumen lampiran.</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                @if ($tahap->catatan)
                    <div class="mt-6 rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Catatan / Keterangan</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $tahap->catatan }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
