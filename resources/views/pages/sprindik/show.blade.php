@php
    $title = 'Detail Sprindik';
    $status = $perkara->status_sprindik;
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
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('sprindik.index') }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                    >
                        Kembali
                    </a>
                    @can('create-cases')
                        <a
                            href="#"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600"
                        >
                            <i class="fi fi-rr-plus-small text-base"></i>
                            Buat Perkara
                        </a>
                    @endcan
                    @can('edit-draft')
                        <a
                            href="{{ route('sprindik.edit', $perkara) }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700"
                        >
                            <i class="fi fi-rr-edit text-base"></i>
                            Edit Sprindik
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Information Card --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Informasi Sprindik</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Dasar kewenangan penyidikan tindak pidana.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $status }}
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800/70">
                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Nomor Sprindik</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $perkara->nomor_surat_perintah }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Sprindik</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">{{ $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Laporan Kejadian</dt>
                        <dd class="text-sm">
                            <a
                                href="{{ route('laporan-kejadian.show', $perkara) }}"
                                class="inline-flex items-center gap-1.5 font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                            >
                                <span>{{ $perkara->nomor_laporan_kejadian }}</span>
                                <i class="fi fi-rr-arrow-up-right-from-square text-xs"></i>
                            </a>
                            <span class="text-xs text-gray-400"> (Tanggal: {{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') }})</span>
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

            {{-- Jejak Aktivitas / Audit Trail --}}
            @if ($perkara->auditLogs->isNotEmpty())
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Jejak Aktivitas (Audit Trail)</h3>
                    <div class="space-y-3">
                        @foreach ($perkara->auditLogs as $log)
                            <div class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50/50 p-3 text-sm dark:border-gray-800 dark:bg-white/[0.02]">
                                <i class="fi fi-rr-time-past mt-0.5 text-brand-500"></i>
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-medium text-gray-800 dark:text-white/90">{{ ucwords(str_replace('_', ' ', $log->aksi)) }}</span>
                                        <span class="text-xs text-gray-400">{{ $log->created_at?->format('d-m-Y H:i:s') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Oleh: {{ $log->user?->nama ?? 'Sistem' }} (IP: {{ $log->ip_address ?? '-' }})
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection