@php
    $title = 'Detail Sprindik';
    $status = $perkara->status_sprindik;
@endphp

@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-screen-xl">
        {{-- Header --}}
        <div class="mb-6">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Home</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('sprindik.index') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Sprindik</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Detail</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detail Sprindik</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detail data Surat Perintah Penyidikan pra-SPDP.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('sprindik.index') }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                    >
                        Kembali
                    </a>

                    @if ($perkara->isSprindikLocked())
                        <span class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3.5 text-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                            <i class="fi fi-rr-lock text-sm"></i>
                            <span>Tahap Terkunci Permanen</span>
                        </span>

                        @if ($perkara->isPerkaraLocked() || $perkara->tahap_perkara_status === 'draft')
                            <a
                                href="{{ route('perkara.show', $perkara) }}"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-4 text-sm font-medium text-brand-700 transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300"
                            >
                                <i class="fi fi-rr-folder-open text-base"></i>
                                Lihat Perkara
                            </a>
                        @endif

                        @can('edit-draft', $perkara->isPerkaraLocked())
                            <a
                                href="{{ route('perkara.edit', $perkara) }}"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600"
                            >
                                <i class="fi {{ $perkara->tahap_perkara_status === 'draft' ? 'fi-rr-edit' : 'fi-rr-plus-small' }} text-base"></i>
                                {{ $perkara->tahap_perkara_status === 'draft' ? 'Edit Draft Perkara' : 'Buat Perkara' }}
                            </a>
                        @endcan
                    @endif

                    @can('edit-draft', $perkara->isSprindikLocked())
                        <a
                            href="{{ route('sprindik.edit', $perkara) }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700"
                        >
                            <i class="fi fi-rr-edit text-base"></i>
                            Edit Draft
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Card Referensi Tahapan Sebelumnya (Laporan Kejadian) --}}
            <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02] sm:p-5">
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
                        <a href="{{ route('laporan-kejadian.show', $perkara) }}" class="inline-flex items-center gap-1.5 font-medium text-brand-600 hover:underline dark:text-brand-400">
                            <span class="font-mono">{{ $perkara->nomor_laporan_kejadian }}</span>
                            <i class="fi fi-rr-arrow-up-right-from-square text-xs"></i>
                        </a>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Laporan</p>
                        <p class="text-gray-700 dark:text-gray-300">{{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen Laporan</p>
                        @if ($perkara->dokumenLaporan)
                            <a href="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}"
                               data-pdf-preview="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}"
                               data-pdf-title="Laporan Kejadian ({{ $perkara->dokumenLaporan->nama_file_asli }})"
                               data-pdf-download="{{ route('dokumen.download', $perkara->dokumenLaporan) }}"
                               target="_blank" class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400">
                                <i class="fi fi-rr-document text-xs"></i>
                                <span>{{ $perkara->dokumenLaporan->nama_file_asli }}</span>
                            </a>
                        @else
                            <span class="text-xs italic text-gray-400">Tidak ada dokumen</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Information Card --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Informasi Sprindik</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Dasar kewenangan penyidikan tindak pidana.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                            @if ($status === 'Draft') bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400
                            @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 @endif
                        ">
                            @if ($perkara->isSprindikLocked())
                                <i class="fi fi-rr-lock text-[10px]"></i>
                            @else
                                <i class="fi fi-rr-edit text-[10px]"></i>
                            @endif
                            {{ $status }}
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800/70">
                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Nomor Sprindik</dt>
                        <dd class="font-mono text-sm font-medium text-gray-800 dark:text-white/90">{{ $perkara->nomor_surat_perintah ?: '(Draft Belum Bernomor)' }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Sprindik</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">{{ $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Status Penguncian Tahap</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">
                            @if ($perkara->isSprindikLocked())
                                <span class="inline-flex items-center gap-1.5 font-medium text-success-700 dark:text-success-400">
                                    <i class="fi fi-rr-lock text-xs"></i>
                                    Final &amp; Terkunci Permanen
                                </span>
                                @if ($perkara->sprindik_locked_at)
                                    <span class="ms-2 text-xs text-gray-500 dark:text-gray-400">
                                        (Dikunci pada {{ $perkara->sprindik_locked_at->format('d-m-Y H:i') }}{{ $perkara->sprindikFinalizer ? ' oleh ' . $perkara->sprindikFinalizer->nama : '' }})
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1.5 font-medium text-blue-700 dark:text-blue-400">
                                    <i class="fi fi-rr-edit text-xs"></i>
                                    Draft (Masih Dapat Diedit)
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Dokumen Sprindik</dt>
                        <dd class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            @if ($perkara->dokumenSprindik)
                                <span class="inline-flex min-w-0 items-center gap-2 text-sm text-gray-800 dark:text-gray-300">
                                    <i class="fi fi-rr-document shrink-0 text-base text-red-500"></i>
                                    <span class="truncate">{{ $perkara->dokumenSprindik->nama_file_asli }}</span>
                                    <span class="text-xs text-gray-400">({{ $perkara->dokumenSprindik->formatted_file_size }})</span>
                                </span>
                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('dokumen.preview', $perkara->dokumenSprindik) }}"
                                        data-pdf-preview="{{ route('dokumen.preview', $perkara->dokumenSprindik) }}"
                                        data-pdf-title="Sprindik ({{ $perkara->dokumenSprindik->nama_file_asli }})"
                                        data-pdf-download="{{ route('dokumen.download', $perkara->dokumenSprindik) }}"
                                        target="_blank"
                                        class="inline-flex h-9 w-fit items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                                    >
                                        Lihat Dokumen
                                    </a>
                                    <a
                                        href="{{ route('dokumen.download', $perkara->dokumenSprindik) }}"
                                        class="inline-flex h-9 w-fit items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                                    >
                                        Download
                                    </a>
                                </div>
                            @else
                                <span class="text-sm italic text-gray-400 dark:text-gray-500">Belum ada file dokumen PDF diunggah.</span>
                            @endif
                        </dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Keterangan / Deskripsi</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">{{ $perkara->deskripsi ?: '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Petugas Pembuat</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">{{ $perkara->creator?->nama ?? '-' }}</dd>
                    </div>
                </div>
            </div>

            {{-- Jejak Aktivitas / Case History / Audit Trail (PRD F-31) --}}
            <x-perkara.case-history
                :perkara="$perkara"
                stage-type="sprindik"
                stage-title="Sprindik"
            />
        </div>
    </div>
@endsection