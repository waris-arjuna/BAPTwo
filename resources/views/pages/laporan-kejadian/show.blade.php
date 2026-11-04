@php
    $title = 'Detail Laporan Kejadian';
    $status = $perkara->status_laporan;
@endphp

@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-screen-xl">
        {{-- Header --}}
        <div class="mb-6">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Home</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('laporan-kejadian.index') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Laporan Kejadian</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Detail</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Laporan Kejadian</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detail data dan progres lanjutan laporan kejadian.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('laporan-kejadian.index') }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                    >
                        Kembali
                    </a>

                    @if ($perkara->isLaporanLocked())
                        <span class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3.5 text-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                            <i class="fi fi-rr-lock text-sm"></i>
                            <span>Tahap Terkunci Permanen</span>
                        </span>
                    @endif

                    @can('edit-draft', $perkara->isLaporanLocked())
                        <a
                            href="{{ route('laporan-kejadian.edit', $perkara) }}"
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
            {{-- Information Card --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Informasi Laporan</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data dasar laporan kejadian pra-SPDP.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                            @if ($status === 'Draft') bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400
                            @elseif ($status === 'Belum Dilanjutkan') bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300
                            @elseif ($status === 'Sprindik Tersedia') bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300
                            @else bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-300 @endif
                        ">
                            @if ($perkara->isLaporanLocked())
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
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Nomor Laporan</dt>
                        <dd class="font-mono text-sm font-medium text-gray-800 dark:text-white/90">{{ $perkara->nomor_laporan_kejadian ?: '(Draft Belum Bernomor)' }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Laporan</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">{{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Status Penguncian Tahap</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">
                            @if ($perkara->isLaporanLocked())
                                <span class="inline-flex items-center gap-1.5 font-medium text-success-700 dark:text-success-400">
                                    <i class="fi fi-rr-lock text-xs"></i>
                                    Final &amp; Terkunci Permanen
                                </span>
                                @if ($perkara->laporan_locked_at)
                                    <span class="ms-2 text-xs text-gray-500 dark:text-gray-400">
                                        (Dikunci pada {{ $perkara->laporan_locked_at->format('d-m-Y H:i') }}{{ $perkara->laporanFinalizer ? ' oleh ' . $perkara->laporanFinalizer->nama : '' }})
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
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Dokumen Laporan</dt>
                        <dd class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            @if ($perkara->dokumenLaporan)
                                <span class="inline-flex min-w-0 items-center gap-2 text-sm text-gray-800 dark:text-gray-300">
                                    <i class="fi fi-rr-document shrink-0 text-base text-red-500"></i>
                                    <span class="truncate">{{ $perkara->dokumenLaporan->nama_file_asli }}</span>
                                    <span class="text-xs text-gray-400">({{ $perkara->dokumenLaporan->formatted_file_size }})</span>
                                </span>
                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}"
                                        data-pdf-preview="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}"
                                        data-pdf-title="Laporan Kejadian ({{ $perkara->dokumenLaporan->nama_file_asli }})"
                                        data-pdf-download="{{ route('dokumen.download', $perkara->dokumenLaporan) }}"
                                        target="_blank"
                                        class="inline-flex h-9 w-fit items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                                    >
                                        Lihat Dokumen
                                    </a>
                                    <a
                                        href="{{ route('dokumen.download', $perkara->dokumenLaporan) }}"
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
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Deskripsi / Kronologi</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">{{ $perkara->deskripsi ?: '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[200px_1fr] sm:px-6 sm:gap-6">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Petugas Pembuat</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-300">{{ $perkara->creator?->nama ?? '-' }}</dd>
                    </div>
                </div>
            </div>

            {{-- Sprindik Status Card --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Lanjutan: Surat Perintah Penyidikan (Sprindik)</h2>
                </div>

                <div class="p-4 sm:p-6">
                    @if (! $perkara->isLaporanLocked())
                        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Laporan Kejadian ini masih berstatus <strong>Draft</strong>. Silakan lengkapi dan pilih <strong>Simpan &amp; Lanjut Tahap Berikutnya</strong> untuk membuka tahap Sprindik.
                            </p>
                            @can('edit-draft', $perkara->isLaporanLocked())
                                <a
                                    href="{{ route('laporan-kejadian.edit', $perkara) }}"
                                    class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700"
                                >
                                    <i class="fi fi-rr-edit text-sm"></i>
                                    Lengkapi &amp; Finalkan
                                </a>
                            @endcan
                        </div>
                    @elseif ($perkara->nomor_surat_perintah || $perkara->tahap_sprindik_status)
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">Nomor Sprindik: {{ $perkara->nomor_surat_perintah ?: '(Draft Belum Bernomor)' }}</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tanggal: {{ $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}</p>
                            </div>
                            <a
                                href="{{ route('sprindik.show', $perkara) }}"
                                class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600"
                            >
                                <span>Lihat Detail Sprindik</span>
                                <i class="fi fi-rr-arrow-right text-sm"></i>
                            </a>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Laporan kejadian ini telah difinalkan dan siap diterbitkan Surat Perintah Penyidikan.</p>
                            @can('create-cases')
                                <a
                                    href="{{ route('sprindik.create', ['perkara_id' => $perkara->id]) }}"
                                    class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600"
                                >
                                    <i class="fi fi-rr-plus-small text-base"></i>
                                    Input Sprindik
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            {{-- Jejak Aktivitas / Case History / Audit Trail (PRD F-31) --}}
            <x-perkara.case-history
                :perkara="$perkara"
                stage-type="laporan_kejadian"
                stage-title="Laporan Kejadian"
            />
        </div>
    </div>
@endsection
