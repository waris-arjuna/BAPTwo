@php
    $title = 'Detail Perkara & Berkas Perkara';
    $isLocked = $perkara->isPerkaraLocked();
    $tkpAlamat = $perkara->tkp?->alamat;
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
                <a href="{{ route('sprindik.show', $perkara) }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">{{ $perkara->nomor_surat_perintah }}</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Detail Perkara</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detail Perkara &amp; Berkas Perkara</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Data TKP, Tim PPNS, Tersangka (1 Tersangka = 1 Berkas Perkara), dan Pasal Berlapis.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('sprindik.show', $perkara) }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                    >
                        Kembali ke Sprindik
                    </a>

                    @if ($isLocked)
                        <span class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3.5 text-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                            <i class="fi fi-rr-lock text-sm"></i>
                            <span>Tahap Terkunci Permanen</span>
                        </span>

                        @can('create-cases')
                            <a
                                href="{{ route('spdp.create') }}"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600"
                            >
                                <i class="fi fi-rr-arrow-right text-sm"></i>
                                <span>Lanjut ke Tahap SPDP</span>
                            </a>
                        @endcan
                    @endif

                    @can('edit-draft', $isLocked)
                        <a
                            href="{{ route('perkara.edit', $perkara) }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700"
                        >
                            <i class="fi fi-rr-edit text-base"></i>
                            <span>Edit Draft Perkara</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Card Referensi Tahapan Sebelumnya (Laporan Kejadian & Sprindik) --}}
            <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02] sm:p-5">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-folder-open text-base text-brand-500"></i>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Referensi Tahapan Sebelumnya: Laporan Kejadian &amp; Sprindik</h2>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                        <i class="fi fi-rr-lock text-[10px]"></i>
                        Tahap Final &amp; Terkunci
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nomor Laporan Kejadian</p>
                        <a href="{{ route('laporan-kejadian.show', $perkara) }}" class="inline-flex items-center gap-1 font-medium text-brand-600 hover:underline dark:text-brand-400">
                            <span>{{ $perkara->nomor_laporan_kejadian }}</span>
                            <i class="fi fi-rr-arrow-up-right-from-square text-xs"></i>
                        </a>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tanggal: {{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen Laporan Kejadian</p>
                        @if ($perkara->dokumenLaporan)
                            <a href="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}" target="_blank" class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400">
                                <i class="fi fi-rr-document text-xs"></i>
                                <span class="truncate">{{ $perkara->dokumenLaporan->nama_file_asli }}</span>
                            </a>
                        @else
                            <span class="text-xs italic text-gray-400">Tidak ada dokumen</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nomor Sprindik</p>
                        <a href="{{ route('sprindik.show', $perkara) }}" class="inline-flex items-center gap-1 font-medium text-brand-600 hover:underline dark:text-brand-400">
                            <span>{{ $perkara->nomor_surat_perintah }}</span>
                            <i class="fi fi-rr-arrow-up-right-from-square text-xs"></i>
                        </a>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tanggal: {{ $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen Sprindik</p>
                        @if ($perkara->dokumenSprindik)
                            <a href="{{ route('dokumen.preview', $perkara->dokumenSprindik) }}" target="_blank" class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400">
                                <i class="fi fi-rr-document text-xs"></i>
                                <span class="truncate">{{ $perkara->dokumenSprindik->nama_file_asli }}</span>
                            </a>
                        @else
                            <span class="text-xs italic text-gray-400">Tidak ada dokumen</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card Informasi TKP & Tim PPNS --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Card TKP --}}
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-marker text-base text-brand-500"></i>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Informasi TKP</h2>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                            {{ $isLocked ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' }}">
                            <i class="fi {{ $isLocked ? 'fi-rr-lock' : 'fi-rr-edit' }} text-[10px]"></i>
                            {{ $isLocked ? 'Perkara Terbentuk' : 'Draft Perkara' }}
                        </span>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800/70">
                        <div class="grid grid-cols-1 gap-1 px-4 py-3.5 sm:grid-cols-[160px_1fr] sm:px-6">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Wilayah TKP</dt>
                            <dd class="text-sm text-gray-800 dark:text-white/90">
                                @if ($tkpAlamat)
                                    {{ collect([$tkpAlamat->desa_nama, $tkpAlamat->kecamatan_nama, $tkpAlamat->kabupaten_nama, $tkpAlamat->provinsi_nama])->filter()->implode(', ') ?: '-' }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 px-4 py-3.5 sm:grid-cols-[160px_1fr] sm:px-6">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Detail Alamat</dt>
                            <dd class="text-sm text-gray-800 dark:text-white/90">{{ $tkpAlamat?->alamat_detail ?: '-' }}</dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 px-4 py-3.5 sm:grid-cols-[160px_1fr] sm:px-6">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Klasifikasi Sarana</dt>
                            <dd class="text-sm capitalize text-gray-800 dark:text-white/90">{{ $perkara->tkp?->klasifikasi ?: '-' }}</dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 px-4 py-3.5 sm:grid-cols-[160px_1fr] sm:px-6">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Legalitas Sarana</dt>
                            <dd class="text-sm capitalize text-gray-800 dark:text-white/90">{{ $perkara->tkp?->legalitas ?: '-' }}</dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 px-4 py-3.5 sm:grid-cols-[160px_1fr] sm:px-6">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Modus Operandi</dt>
                            <dd class="text-sm capitalize text-gray-800 dark:text-white/90">{{ $perkara->tkp?->modus ?: '-' }}</dd>
                        </div>
                    </div>
                </div>

                {{-- Card Tim PPNS --}}
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-users-alt text-base text-brand-500"></i>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Tim Penyidik PPNS</h2>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800/70">
                        <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[160px_1fr] sm:px-6">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">PPNS Utama</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-white/90">
                                @if ($perkara->ppnsUtama->isNotEmpty())
                                    @foreach ($perkara->ppnsUtama as $utama)
                                        <div class="inline-flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                            <i class="fi fi-rr-badge-check text-xs"></i>
                                            <span>{{ $utama->nama }} ({{ $utama->nip ?: $utama->role }})</span>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[160px_1fr] sm:px-6">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">PPNS Anggota</dt>
                            <dd class="flex flex-wrap gap-2 text-sm text-gray-800 dark:text-white/90">
                                @forelse ($perkara->ppnsAnggota as $anggota)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1 text-xs text-gray-700 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300">
                                        <i class="fi fi-rr-user text-xs text-gray-400"></i>
                                        <span>{{ $anggota->nama }}</span>
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-400">-</span>
                                @endforelse
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 px-4 py-4 sm:grid-cols-[160px_1fr] sm:px-6">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Status Penguncian</dt>
                            <dd class="text-sm text-gray-800 dark:text-gray-300">
                                @if ($isLocked)
                                    <span class="inline-flex items-center gap-1.5 font-medium text-success-700 dark:text-success-400">
                                        <i class="fi fi-rr-lock text-xs"></i>
                                        Final &amp; Terkunci Permanen
                                    </span>
                                    @if ($perkara->perkara_locked_at)
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            Dikunci pada {{ $perkara->perkara_locked_at->format('d-m-Y H:i') }}{{ $perkara->perkaraFinalizer ? ' oleh ' . $perkara->perkaraFinalizer->nama : '' }}
                                        </p>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 font-medium text-blue-700 dark:text-blue-400">
                                        <i class="fi fi-rr-edit text-xs"></i>
                                        Draft (Masih Dapat Diedit)
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daftar Tersangka & Berkas Perkara (1 Tersangka = 1 Berkas Perkara) --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Daftar Tersangka &amp; Berkas Perkara ({{ $perkara->tersangkas->count() }} Berkas)
                            </h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Setiap tersangka memiliki 1 Berkas Perkara terpisah beserta Pasal Berlapis yang disangkakan.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($perkara->tersangkas as $index => $tersangka)
                        @php
                            $berkas = $tersangka->berkasPerkara;
                        @endphp
                        <div class="p-4 sm:p-6">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                            {{ $tersangka->nama }}
                                            @if ($tersangka->alias)
                                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $tersangka->alias }})</span>
                                            @endif
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Nomor Berkas Perkara:
                                            <strong class="text-gray-800 dark:text-white/90">{{ $berkas?->nomor_berkas ?: '(Belum Bernomor / Draft)' }}</strong>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">
                                        Status Proses: {{ $berkas?->status_proses ?? 'Baru' }} ({{ $berkas?->progress_persen ?? 0 }}%)
                                    </span>

                                    @if ($berkas)
                                        @if ($berkas->currentDraftTahap())
                                            @can('edit-draft')
                                                <a
                                                    href="{{ route('berkas-perkara.tahapan.edit', [$berkas, $berkas->currentDraftTahap()]) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-full border border-yellow-300 bg-yellow-50 px-3 py-1 text-xs font-medium text-yellow-800 transition hover:bg-yellow-100 dark:border-yellow-500/30 dark:bg-yellow-500/10 dark:text-yellow-300"
                                                >
                                                    <i class="fi fi-rr-edit text-xs"></i>
                                                    <span>Lanjut Draft: {{ $berkas->currentDraftTahap()->jenis_tahap->label() }}</span>
                                                </a>
                                            @endcan
                                        @elseif ($isLocked)
                                            @can('create-cases')
                                                <a
                                                    href="{{ route('berkas-perkara.tahapan.create', $berkas) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-brand-600 px-3 py-1 text-xs font-medium text-white transition hover:bg-brand-700"
                                                >
                                                    <i class="fi fi-rr-plus text-xs"></i>
                                                    <span>Input Tahapan</span>
                                                </a>
                                            @endcan
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 rounded-xl border border-gray-100 bg-gray-50/60 p-4 text-sm sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-800 dark:bg-white/[0.02]">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">NIK / Jenis Kelamin</p>
                                    <p class="capitalize font-medium text-gray-800 dark:text-white/90">
                                        {{ $tersangka->nik ?: '-' }} / {{ $tersangka->jenis_kelamin ?: '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir</p>
                                    <p class="font-medium text-gray-800 dark:text-white/90">
                                        {{ $tersangka->tempat_lahir ?: '-' }}, {{ $tersangka->tanggal_lahir?->format('d-m-Y') ?? '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Bin/Binti &amp; Agama</p>
                                    <p class="capitalize font-medium text-gray-800 dark:text-white/90">
                                        {{ $tersangka->bin_binti ?: '-' }} / {{ $tersangka->agama ?: '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Pekerjaan &amp; Kewarganegaraan</p>
                                    <p class="font-medium text-gray-800 dark:text-white/90">
                                        {{ $tersangka->pekerjaan ?: '-' }} ({{ $tersangka->kewarganegaraan ?: 'WNI' }})
                                    </p>
                                </div>
                            </div>

                            {{-- Pasal Berlapis --}}
                            <div class="mt-4">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Pasal Berlapis yang Disangkakan ({{ $berkas?->pasals->count() ?? 0 }} Pasal)
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($berkas?->pasals ?? [] as $pasal)
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
                                            <i class="fi fi-rr-scale text-xs"></i>
                                            <span>{{ $pasal->label() }}</span>
                                        </span>
                                    @empty
                                        <span class="text-xs italic text-gray-400">Belum ada pasal yang dipilih.</span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Riwayat Tahapan Progress (PRD F-10 s.d. F-19) --}}
                            <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Riwayat Tahapan Progress ({{ $berkas?->tahapan->count() ?? 0 }} Tahap)
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($berkas?->tahapan ?? [] as $tahap)
                                        <a
                                            href="{{ route('berkas-perkara.tahapan.show', [$berkas, $tahap]) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border {{ $tahap->isFinal() ? 'border-success-200 bg-success-50 text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300' : 'border-yellow-200 bg-yellow-50 text-yellow-800 dark:border-yellow-500/30 dark:bg-yellow-500/10 dark:text-yellow-300' }} px-3 py-1.5 text-xs font-medium hover:underline"
                                        >
                                            <i class="fi {{ $tahap->isFinal() ? 'fi-rr-lock' : 'fi-rr-edit' }} text-xs"></i>
                                            <span>{{ $tahap->jenis_tahap->label() }} ({{ $tahap->status === 'final' ? 'Final' : 'Draft' }})</span>
                                        </a>
                                    @empty
                                        <span class="text-xs italic text-gray-400">Belum ada tahapan progress yang dicatat.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Belum ada data tersangka dan berkas perkara.
                        </div>
                    @endforelse
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
