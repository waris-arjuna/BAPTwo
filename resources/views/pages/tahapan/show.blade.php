@php
    $title = 'Detail Tahapan: ' . $tahap->jenis_tahap->label();
    $p16 = $subTahapan[\App\Enums\JenisTahap::P16->value] ?? null;
    $p17p = $subTahapan[\App\Enums\JenisTahap::P17P->value] ?? null;
    $p18p = $subTahapan[\App\Enums\JenisTahap::P18P->value] ?? null;
    $p19 = $subTahapan[\App\Enums\JenisTahap::P19->value] ?? null;

    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $imageDocs = $tahap->dokumen->filter(function ($doc) use ($imageExtensions) {
        return in_array(strtolower(pathinfo($doc->nama_file_asli, PATHINFO_EXTENSION)), $imageExtensions, true);
    })->values();
    $pdfDocs = $tahap->dokumen->reject(function ($doc) use ($imageExtensions) {
        return in_array(strtolower(pathinfo($doc->nama_file_asli, PATHINFO_EXTENSION)), $imageExtensions, true);
    })->values();
    $isPutusan = $tahap->jenis_tahap === \App\Enums\JenisTahap::KEPUTUSAN_PENGADILAN;
@endphp

@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-screen-xl">
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
                            Tahap Final &amp; Terkunci
                        </span>



                        @can('create-cases')
                            @if (!empty($nextStage) && ! $berkas->is_dpo)
                                <a
                                    href="{{ route('berkas-perkara.tahapan.create', ['berkas' => $berkas->id, 'jenis' => $nextStage->value]) }}"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700"
                                >
                                    <i class="fi fi-rr-arrow-right text-sm"></i>
                                    <span>Input Tahap Selanjutnya ({{ $nextStage->label() }})</span>
                                </a>
                            @endif
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

        <div
            class="space-y-6"
            x-data="{
                isLightboxOpen: false,
                lightboxIndex: 0,
                lightboxImages: {{ Js::from($imageDocs->map(fn($d) => ['url' => route('dokumen.preview', $d), 'title' => $d->nama_file_asli, 'download' => route('dokumen.download', $d)])->toArray()) }},
                openLightbox(idx) {
                    this.lightboxIndex = idx;
                    this.isLightboxOpen = true;
                },
                nextImage() {
                    if (this.lightboxImages.length > 0) {
                        this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxImages.length;
                    }
                },
                prevImage() {
                    if (this.lightboxImages.length > 0) {
                        this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxImages.length) % this.lightboxImages.length;
                    }
                },
                get currentImage() {
                    return this.lightboxImages[this.lightboxIndex] || { url: '', title: '', download: '' };
                }
            }"
            @keydown.arrow-right.window="if (isLightboxOpen) nextImage()"
            @keydown.arrow-left.window="if (isLightboxOpen) prevImage()"
        >
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
                                @if (!empty($ref['url']))
                                    <a
                                        href="{{ $ref['url'] }}"
                                        target="_blank"
                                        class="mt-1 inline-flex items-center gap-1 font-mono text-xs font-medium text-brand-600 transition hover:text-brand-700 hover:underline dark:text-brand-400 dark:hover:text-brand-300"
                                        title="Buka dokumen {{ $ref['nama'] }}"
                                    >
                                        <span>{{ $ref['nomor'] }}</span>
                                        <i class="fi fi-rr-arrow-up-right-from-square text-[9px]"></i>
                                    </a>
                                @else
                                    <p class="mt-1 font-mono text-xs text-brand-600 dark:text-brand-400">{{ $ref['nomor'] }}</p>
                                @endif
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

            {{-- Card Informasi Penuntut Umum (Poin 2 & Poin 8, ref: D-053) --}}
            <x-perkara.card-penuntut-umum :perkara="$berkas->perkara" />

            {{-- Card Detail Informasi Tahapan Utama --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-document-signed text-xl text-brand-600"></i>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            Data Dokumen Utama: {{ $tahap->jenis_tahap->label() }}
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
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $isPutusan ? 'Salinan Dokumen Putusan (PDF)' : 'Dokumen Lampiran (PDF)' }}
                        </p>
                        <div class="mt-1 space-y-1.5">
                            @if ($pdfDocs->isNotEmpty())
                                @foreach ($pdfDocs as $doc)
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('dokumen.preview', $doc) }}"
                                           data-pdf-preview="{{ route('dokumen.preview', $doc) }}"
                                           data-pdf-title="{{ $doc->nama_file_asli }}"
                                           data-pdf-download="{{ route('dokumen.download', $doc) }}"
                                           target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            <i class="fi fi-rr-file-pdf text-xs text-red-500"></i>
                                            <span class="truncate">{{ $doc->nama_file_asli }}</span>
                                        </a>
                                        <a href="{{ route('dokumen.download', $doc) }}" class="text-xs text-gray-400 hover:text-gray-600" title="Unduh">
                                            <i class="fi fi-rr-download text-xs"></i>
                                        </a>
                                    </div>
                                @endforeach
                            @elseif(!$isPutusan && $tahap->dokumen->isEmpty())
                                <span class="text-xs italic text-gray-400">Tidak ada dokumen lampiran.</span>
                            @elseif($isPutusan && $pdfDocs->isEmpty() && $imageDocs->isEmpty())
                                <span class="text-xs italic text-gray-400">Tidak ada dokumen lampiran.</span>
                            @elseif($isPutusan && $pdfDocs->isEmpty())
                                <span class="text-xs text-gray-400">Hanya melampirkan berkas foto (lihat galeri di bawah).</span>
                            @endif
                        </div>
                    </div>

                    @if ($isPutusan)
                        <div class="sm:col-span-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Instansi Pengadilan</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                                {{ $tahap->pengadilanInstansi?->nama ?: 'Pengadilan Negeri' }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Amar Putusan Pengadilan --}}
                @if ($isPutusan && $tahap->amar_putusan)
                    <div class="mt-4 rounded-xl border border-brand-100 bg-brand-50/40 p-4 dark:border-brand-500/20 dark:bg-brand-500/5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">
                            <i class="fi fi-rr-gavel me-1 text-xs"></i>
                            Amar Putusan Pengadilan
                        </p>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-800 dark:text-gray-200">{{ $tahap->amar_putusan }}</p>
                    </div>
                @endif

                {{-- Galeri Foto & Dokumentasi Putusan --}}
                @if ($imageDocs->isNotEmpty())
                    <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fi fi-rr-picture text-base text-brand-500"></i>
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Galeri Foto &amp; Dokumentasi Putusan ({{ $imageDocs->count() }} Foto)
                                </h3>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Klik foto untuk melihat resolusi penuh</span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                            @foreach ($imageDocs as $idx => $imgDoc)
                                <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs transition hover:shadow-theme-md dark:border-gray-700 dark:bg-gray-900">
                                    <button
                                        type="button"
                                        @click="openLightbox({{ $idx }})"
                                        class="block w-full text-left"
                                    >
                                        <div class="relative aspect-4/3 w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                                            <img
                                                src="{{ route('dokumen.preview', $imgDoc) }}"
                                                alt="{{ $imgDoc->nama_file_asli }}"
                                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                                loading="lazy"
                                            />
                                            <div class="absolute inset-0 flex items-center justify-center bg-gray-900/40 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-gray-900 shadow backdrop-blur-xs">
                                                    <i class="fi fi-rr-search-alt text-xs"></i>
                                                    Perbesar
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                    <div class="flex items-center justify-between gap-2 p-3">
                                        <span class="truncate text-xs font-medium text-gray-700 dark:text-gray-300" title="{{ $imgDoc->nama_file_asli }}">
                                            {{ $imgDoc->nama_file_asli }}
                                        </span>
                                        <a
                                            href="{{ route('dokumen.download', $imgDoc) }}"
                                            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-brand-400"
                                            title="Unduh file"
                                        >
                                            <i class="fi fi-rr-download text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Catatan --}}
                @if ($tahap->catatan)
                    <div class="mt-6 rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Catatan / Keterangan</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $tahap->catatan }}</p>
                    </div>
                @endif
            </div>

            {{-- SUB-TAHAP TERKAIT UNTUK SPDP: P-16 & P-17 --}}
            @if ($tahap->jenis_tahap === \App\Enums\JenisTahap::SPDP)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-folder-tree text-lg text-brand-600"></i>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Sub-Tahapan Kejaksaan Terkait (P-16 &amp; P-17)
                            </h2>
                        </div>
                        @can('create-cases')
                            @if (! $berkas->is_dpo)
                                <button
                                    type="button"
                                    @click="$dispatch('open-subtahap-modal', { group: 'spdp' })"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400"
                                >
                                    <i class="fi fi-rr-edit text-xs"></i>
                                    <span>Kelola P-16 &amp; P-17P</span>
                                </button>
                            @endif
                        @endcan
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        {{-- Kartu P-16 --}}
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <i class="fi fi-rr-user-police text-base text-brand-600"></i>
                                    <h3 class="font-semibold text-gray-800 text-sm dark:text-white/90">P-16: Penunjukan JPU</h3>
                                </div>
                                @if ($p16)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                        <i class="fi fi-rr-check text-xs"></i>
                                        Tercatat
                                    </span>
                                @else
                                    <span class="text-xs italic text-gray-400">Belum Ada</span>
                                @endif
                            </div>

                            @if ($p16)
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <span class="text-xs text-gray-400">Nomor Surat:</span>
                                        <p class="font-mono font-medium text-gray-800 dark:text-white/90">{{ $p16->nomor_surat ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400">Tanggal Surat:</span>
                                        <p class="text-gray-800 dark:text-white/90">{{ $p16->tanggal_surat?->format('d-m-Y') ?? '-' }}</p>
                                    </div>
                                    @if ($p16->dokumen->isNotEmpty())
                                        <div>
                                            <span class="text-xs text-gray-400">Dokumen:</span>
                                            <div class="mt-1 flex items-center gap-2">
                                                <a href="{{ route('dokumen.preview', $p16->dokumen->first()) }}"
                                                   data-pdf-preview="{{ route('dokumen.preview', $p16->dokumen->first()) }}"
                                                   data-pdf-title="Dokumen P-16 ({{ $p16->dokumen->first()->nama_file_asli }})"
                                                   data-pdf-download="{{ route('dokumen.download', $p16->dokumen->first()) }}"
                                                   target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                                                    <i class="fi fi-rr-document text-xs"></i>
                                                    <span>{{ $p16->dokumen->first()->nama_file_asli }}</span>
                                                </a>
                                                <a href="{{ route('dokumen.download', $p16->dokumen->first()) }}" class="text-xs text-gray-400 hover:text-gray-600">
                                                    <i class="fi fi-rr-download text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($p16->catatan)
                                        <div>
                                            <span class="text-xs text-gray-400">Catatan JPU:</span>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ $p16->catatan }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-gray-400">Surat Perintah Penunjukan JPU (P-16) belum dicatat untuk berkas ini.</p>
                            @endif
                        </div>

                        {{-- Kartu P-17 --}}
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <i class="fi fi-rr-time-forward text-base text-amber-600"></i>
                                    <h3 class="font-semibold text-gray-800 text-sm dark:text-white/90">P-17: Permintaan Perkembangan</h3>
                                </div>
                                @if ($p17p)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                        <i class="fi fi-rr-check text-xs"></i>
                                        Tercatat
                                    </span>
                                @else
                                    <span class="text-xs italic text-gray-400">Belum Ada</span>
                                @endif
                            </div>

                            @if ($p17p)
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <span class="text-xs text-gray-400">Nomor Surat:</span>
                                        <p class="font-mono font-medium text-gray-800 dark:text-white/90">{{ $p17p->nomor_surat ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400">Tanggal Surat:</span>
                                        <p class="text-gray-800 dark:text-white/90">{{ $p17p->tanggal_surat?->format('d-m-Y') ?? '-' }}</p>
                                    </div>
                                    @if ($p17p->dokumen->isNotEmpty())
                                        <div>
                                            <span class="text-xs text-gray-400">Dokumen:</span>
                                            <div class="mt-1 flex items-center gap-2">
                                                <a href="{{ route('dokumen.preview', $p17p->dokumen->first()) }}"
                                                   data-pdf-preview="{{ route('dokumen.preview', $p17p->dokumen->first()) }}"
                                                   data-pdf-title="Dokumen P-17 ({{ $p17p->dokumen->first()->nama_file_asli }})"
                                                   data-pdf-download="{{ route('dokumen.download', $p17p->dokumen->first()) }}"
                                                   target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                                                    <i class="fi fi-rr-document text-xs"></i>
                                                    <span>{{ $p17p->dokumen->first()->nama_file_asli }}</span>
                                                </a>
                                                <a href="{{ route('dokumen.download', $p17p->dokumen->first()) }}" class="text-xs text-gray-400 hover:text-gray-600">
                                                    <i class="fi fi-rr-download text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($p17p->catatan)
                                        <div>
                                            <span class="text-xs text-gray-400">Catatan:</span>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ $p17p->catatan }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-gray-400">Surat Permintaan Perkembangan Penyidikan (P-17) belum dicatat untuk berkas ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- SUB-TAHAP TERKAIT UNTUK TAHAP 1: P-18 & P-19 --}}
            @if ($tahap->jenis_tahap === \App\Enums\JenisTahap::TAHAP_1)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-folder-tree text-lg text-amber-600"></i>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Sub-Tahapan Kejaksaan Terkait (P-18 &amp; P-19)
                            </h2>
                        </div>
                        @can('create-cases')
                            @if (! $berkas->is_dpo)
                                <button
                                    type="button"
                                    @click="$dispatch('open-subtahap-modal', { group: 'tahap_1' })"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400"
                                >
                                    <i class="fi fi-rr-edit text-xs"></i>
                                    <span>Kelola P-18P &amp; P-19</span>
                                </button>
                            @endif
                        @endcan
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        {{-- Kartu P-18 --}}
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <i class="fi fi-rr-document-signed text-base text-amber-600"></i>
                                    <h3 class="font-semibold text-gray-800 text-sm dark:text-white/90">P-18: Berkas Belum Lengkap</h3>
                                </div>
                                @if ($p18p)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                        <i class="fi fi-rr-check text-xs"></i>
                                        Tercatat
                                    </span>
                                @else
                                    <span class="text-xs italic text-gray-400">Belum Ada</span>
                                @endif
                            </div>

                            @if ($p18p)
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <span class="text-xs text-gray-400">Nomor Surat:</span>
                                        <p class="font-mono font-medium text-gray-800 dark:text-white/90">{{ $p18p->nomor_surat ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400">Tanggal Surat:</span>
                                        <p class="text-gray-800 dark:text-white/90">{{ $p18p->tanggal_surat?->format('d-m-Y') ?? '-' }}</p>
                                    </div>
                                    @if ($p18p->dokumen->isNotEmpty())
                                        <div>
                                            <span class="text-xs text-gray-400">Dokumen:</span>
                                            <div class="mt-1 flex items-center gap-2">
                                                <a href="{{ route('dokumen.preview', $p18p->dokumen->first()) }}"
                                                   data-pdf-preview="{{ route('dokumen.preview', $p18p->dokumen->first()) }}"
                                                   data-pdf-title="Dokumen P-18 ({{ $p18p->dokumen->first()->nama_file_asli }})"
                                                   data-pdf-download="{{ route('dokumen.download', $p18p->dokumen->first()) }}"
                                                   target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                                                    <i class="fi fi-rr-document text-xs"></i>
                                                    <span>{{ $p18p->dokumen->first()->nama_file_asli }}</span>
                                                </a>
                                                <a href="{{ route('dokumen.download', $p18p->dokumen->first()) }}" class="text-xs text-gray-400 hover:text-gray-600">
                                                    <i class="fi fi-rr-download text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($p18p->catatan)
                                        <div>
                                            <span class="text-xs text-gray-400">Catatan:</span>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ $p18p->catatan }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-gray-400">Surat Hasil Penyidikan Belum Lengkap (P-18) belum dicatat untuk berkas ini.</p>
                            @endif
                        </div>

                        {{-- Kartu P-19 --}}
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <i class="fi fi-rr-undo text-base text-orange-600"></i>
                                    <h3 class="font-semibold text-gray-800 text-sm dark:text-white/90">P-19: Pengembalian Berkas</h3>
                                </div>
                                @if ($p19)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs font-semibold text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">
                                        <i class="fi fi-rr-check text-xs"></i>
                                        Tercatat
                                    </span>
                                @else
                                    <span class="text-xs italic text-gray-400">Belum Ada</span>
                                @endif
                            </div>

                            @if ($p19)
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <span class="text-xs text-gray-400">Nomor Surat:</span>
                                        <p class="font-mono font-medium text-gray-800 dark:text-white/90">{{ $p19->nomor_surat ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400">Tanggal Surat:</span>
                                        <p class="text-gray-800 dark:text-white/90">{{ $p19->tanggal_surat?->format('d-m-Y') ?? '-' }}</p>
                                    </div>
                                    @if ($p19->dokumen->isNotEmpty())
                                        <div>
                                            <span class="text-xs text-gray-400">Dokumen:</span>
                                            <div class="mt-1 flex items-center gap-2">
                                                <a href="{{ route('dokumen.preview', $p19->dokumen->first()) }}"
                                                   data-pdf-preview="{{ route('dokumen.preview', $p19->dokumen->first()) }}"
                                                   data-pdf-title="Dokumen P-19 ({{ $p19->dokumen->first()->nama_file_asli }})"
                                                   data-pdf-download="{{ route('dokumen.download', $p19->dokumen->first()) }}"
                                                   target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                                                    <i class="fi fi-rr-document text-xs"></i>
                                                    <span>{{ $p19->dokumen->first()->nama_file_asli }}</span>
                                                </a>
                                                <a href="{{ route('dokumen.download', $p19->dokumen->first()) }}" class="text-xs text-gray-400 hover:text-gray-600">
                                                    <i class="fi fi-rr-download text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($p19->catatan)
                                        <div>
                                            <span class="text-xs text-gray-400">Catatan Petunjuk:</span>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ $p19->catatan }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-gray-400">Surat Pengembalian Berkas Perkara (P-19) belum dicatat untuk berkas ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal Sub-Tahap Mandiri --}}
        <x-tahapan.modal-subtahap :berkas="$berkas" :subTahapan="$subTahapan" />

        {{-- Modal Lightbox Pratinjau Gambar Beresolusi Penuh --}}
        <div
            x-show="isLightboxOpen"
            x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-6"
            @keydown.escape.window="isLightboxOpen = false"
        >
            <div class="fixed inset-0 h-full w-full bg-gray-900/80 backdrop-blur-xs" @click="isLightboxOpen = false"></div>

            <div
                class="relative z-10 flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900 sm:p-5"
                @click.stop
            >
                {{-- Header Modal --}}
                <div class="mb-3 flex items-center justify-between gap-2 border-b border-gray-200 pb-3 dark:border-gray-800">
                    <div class="flex min-w-0 items-center gap-2">
                        <i class="fi fi-rr-picture text-base text-brand-500"></i>
                        <h4 class="truncate text-sm font-semibold text-gray-800 dark:text-white/90" x-text="currentImage.title"></h4>
                        <span
                            x-show="lightboxImages.length > 1"
                            class="shrink-0 rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            x-text="(lightboxIndex + 1) + ' dari ' + lightboxImages.length"
                        ></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            :href="currentImage.download"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                            title="Unduh Gambar"
                        >
                            <i class="fi fi-rr-download text-sm"></i>
                        </a>
                        <button
                            type="button"
                            @click="isLightboxOpen = false"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                            aria-label="Tutup pratinjau"
                        >
                            <i class="fi fi-rr-cross-small text-lg"></i>
                        </button>
                    </div>
                </div>

                {{-- Body: Gambar Full Resolusi dengan Tombol Prev / Next --}}
                <div class="relative flex min-h-0 flex-1 items-center justify-center overflow-auto rounded-xl bg-gray-100 p-2 dark:bg-gray-950/60">
                    <img
                        :src="currentImage.url"
                        :alt="currentImage.title"
                        class="max-h-[72vh] w-auto max-w-full rounded-lg object-contain shadow-sm"
                    />

                    {{-- Tombol Prev --}}
                    <button
                        type="button"
                        x-show="lightboxImages.length > 1"
                        @click.stop="prevImage()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full bg-gray-900/60 text-white shadow transition hover:bg-gray-900/90 focus:outline-none"
                        title="Foto Sebelumnya"
                    >
                        <i class="fi fi-rr-angle-left text-base"></i>
                    </button>

                    {{-- Tombol Next --}}
                    <button
                        type="button"
                        x-show="lightboxImages.length > 1"
                        @click.stop="nextImage()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full bg-gray-900/60 text-white shadow transition hover:bg-gray-900/90 focus:outline-none"
                        title="Foto Selanjutnya"
                    >
                        <i class="fi fi-rr-angle-right text-base"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
