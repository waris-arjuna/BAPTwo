@php
    $title = 'Detail Perkara & Berkas Perkara';
    $isLocked = $perkara->isPerkaraLocked();
    $tkpAlamat = $perkara->tkp?->alamat;

    $actionMeta = [
        'aktifkan_dpo' => [
            'label' => 'Penetapan Status DPO',
            'icon' => 'fi-rr-shield-exclamation',
            'badge' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
            'icon_bg' => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
        ],
        'nonaktifkan_dpo' => [
            'label' => 'Pencabutan Status DPO',
            'icon' => 'fi-rr-user-check',
            'badge' => 'bg-success-50 text-success-700 border-success-200 dark:bg-success-900/20 dark:text-success-300 dark:border-success-800',
            'icon_bg' => 'bg-success-100 text-success-600 dark:bg-success-900/40 dark:text-success-400',
        ],
        'finalisasi_tahap' => [
            'label' => 'Finalisasi Tahapan Perkara',
            'icon' => 'fi-rr-badge-check',
            'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
            'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
        ],
        'simpan_draft_tahap' => [
            'label' => 'Simpan Draft Tahapan Perkara',
            'icon' => 'fi-rr-edit',
            'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
            'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
        ],
        'simpan_sub_tahap' => [
            'label' => 'Simpan Sub-Tahap Tersemat',
            'icon' => 'fi-rr-document-signed',
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
            'icon_bg' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
        ],
        'finalisasi_perkara' => [
            'label' => 'Finalisasi Data Perkara Pra-SPDP',
            'icon' => 'fi-rr-lock',
            'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
            'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
        ],
        'simpan_draft_perkara' => [
            'label' => 'Simpan Draft Data Perkara Pra-SPDP',
            'icon' => 'fi-rr-edit',
            'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
            'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
        ],
        'finalisasi_sprindik' => [
            'label' => 'Finalisasi Surat Perintah Penyidikan',
            'icon' => 'fi-rr-lock',
            'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
            'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
        ],
        'simpan_draft_sprindik' => [
            'label' => 'Simpan Draft Sprindik',
            'icon' => 'fi-rr-edit',
            'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
            'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
        ],
        'finalisasi_laporan' => [
            'label' => 'Finalisasi Laporan Kejadian',
            'icon' => 'fi-rr-lock',
            'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
            'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
        ],
        'simpan_draft_laporan' => [
            'label' => 'Simpan Draft Laporan Kejadian',
            'icon' => 'fi-rr-edit',
            'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
            'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
        ],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div
        class="mx-auto max-w-screen-xl px-4 pb-8 pt-6 lg:px-6"
        x-data="{
            // Modal Penetapan DPO
            isDpoModalOpen: false,
            dpoBerkasId: null,
            dpoTersangkaNama: '',
            dpoActionUrl: '',
            openDpoModal(berkasId, tersangkaNama) {
                this.dpoBerkasId = berkasId;
                this.dpoTersangkaNama = tersangkaNama;
                this.dpoActionUrl = '{{ url('/berkas-perkara') }}/' + berkasId + '/dpo';
                this.isDpoModalOpen = true;
            },
            closeDpoModal() {
                this.isDpoModalOpen = false;
                this.dpoBerkasId = null;
                this.dpoTersangkaNama = '';
                this.dpoActionUrl = '';
            },

            // Modal Pencabutan Status DPO
            isRevokeModalOpen: false,
            revokeBerkasId: null,
            revokeTersangkaNama: '',
            revokeActionUrl: '',
            openRevokeDpoModal(berkasId, tersangkaNama) {
                this.revokeBerkasId = berkasId;
                this.revokeTersangkaNama = tersangkaNama;
                this.revokeActionUrl = '{{ url('/berkas-perkara') }}/' + berkasId + '/dpo/cabut';
                this.isRevokeModalOpen = true;
            },
            closeRevokeModal() {
                this.isRevokeModalOpen = false;
                this.revokeBerkasId = null;
                this.revokeTersangkaNama = '';
                this.revokeActionUrl = '';
            }
        }"
        @keydown.escape.window="closeDpoModal(); closeRevokeModal();"
    >
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
                            $activeDpo = $berkas?->activeDpo();
                            $revokedDpos = $berkas?->dpos->where('status', 'nonaktif');
                        @endphp
                        <div class="p-4 sm:p-6">
                            {{-- Header Berkas & Aksi DPO --}}
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
                                    {{-- Visual Indicator Status DPO --}}
                                    @if ($berkas?->is_dpo)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/20 dark:text-red-300">
                                            <i class="fi fi-rr-shield-exclamation text-xs"></i>
                                            <span>DPO Aktif</span>
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">
                                        Status Proses: {{ $berkas?->status_proses ?? 'Baru' }} ({{ $berkas?->progress_persen ?? 0 }}%)
                                    </span>

                                    @if ($berkas)
                                        {{-- Tombol Toggle DPO (Penyidik & Administrator) --}}
                                        @can('toggle-dpo')
                                            @if ($berkas->is_dpo)
                                                <button
                                                    type="button"
                                                    @click="openRevokeDpoModal({{ $berkas->id }}, '{{ addslashes($tersangka->nama) }}')"
                                                    class="inline-flex items-center gap-1.5 rounded-full border border-red-300 bg-red-50 px-3 py-1 text-xs font-medium text-red-700 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"
                                                >
                                                    <i class="fi fi-rr-user-check text-xs"></i>
                                                    <span>Cabut Status DPO</span>
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    @click="openDpoModal({{ $berkas->id }}, '{{ addslashes($tersangka->nama) }}')"
                                                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-red-500/40 dark:hover:bg-red-500/10 dark:hover:text-red-300"
                                                >
                                                    <i class="fi fi-rr-shield-exclamation text-xs text-red-500"></i>
                                                    <span>Tetapkan DPO</span>
                                                </button>
                                            @endif
                                        @endcan

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

                            {{-- Card Detail DPO Aktif (jika ada) --}}
                            @if ($berkas?->is_dpo && $activeDpo)
                                <div class="mb-4 rounded-xl border border-red-200 bg-red-50/80 p-4 dark:border-red-900/50 dark:bg-red-950/20">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">
                                            <i class="fi fi-rr-shield-exclamation text-base"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">
                                                    Status Tersangka: Daftar Pencarian Orang (DPO) Aktif
                                                </h4>
                                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-[11px] font-medium text-red-700 dark:bg-red-900/40 dark:text-red-400">
                                                    Ditetapkan: {{ $activeDpo->tanggal_penetapan?->format('d-m-Y') ?? '-' }}
                                                </span>
                                            </div>

                                            <div class="mt-2.5 grid grid-cols-1 gap-2 text-xs sm:grid-cols-2 lg:grid-cols-3">
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Nomor Surat DPO:</span>
                                                    <p class="font-medium text-gray-800 dark:text-white/90">{{ $activeDpo->nomor_surat }}</p>
                                                </div>

                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Dokumen Penetapan:</span>
                                                    @if ($activeDpo->dokumen)
                                                        <a href="{{ route('dokumen.preview', $activeDpo->dokumen) }}" target="_blank" class="flex items-center gap-1 font-medium text-brand-600 hover:underline dark:text-brand-400">
                                                            <i class="fi fi-rr-document text-xs"></i>
                                                            <span class="truncate">{{ $activeDpo->dokumen->nama_file_asli }}</span>
                                                        </a>
                                                    @else
                                                        <p class="italic text-gray-400">Tidak ada dokumen terlampir</p>
                                                    @endif
                                                </div>

                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Keterangan / Catatan:</span>
                                                    <p class="text-gray-700 dark:text-gray-300">{{ $activeDpo->keterangan ?: '-' }}</p>
                                                </div>
                                            </div>

                                            <div class="mt-2.5 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">
                                                <i class="fi fi-rr-ban text-xs"></i>
                                                <span>Pelimpahan ke Tahap II (Pelimpahan Tersangka &amp; Barang Bukti) dinonaktifkan di sistem sampai status DPO dicabut.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Catatan Riwayat DPO yang Pernah Dicabut --}}
                            @if (! $berkas?->is_dpo && $revokedDpos?->isNotEmpty())
                                @php
                                    $lastRevoked = $revokedDpos->first();
                                @endphp
                                <div class="mb-4 rounded-xl border border-gray-200 bg-gray-50/70 p-3 text-xs text-gray-600 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <i class="fi fi-rr-info text-brand-500"></i>
                                        <span>
                                            Riwayat DPO: Pernah berstatus DPO (Surat: <strong>{{ $lastRevoked->nomor_surat }}</strong>) dan telah dicabut pada <strong>{{ $lastRevoked->tanggal_pencabutan?->format('d-m-Y') }}</strong> (Alasan: {{ $lastRevoked->alasan_pencabutan }}).
                                        </span>
                                    </div>
                                </div>
                            @endif

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

                            {{-- Riwayat Tahapan Progress --}}
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

            {{-- Jejak Aktivitas / Case History / Audit Trail (PRD F-31) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-time-past text-base text-brand-500"></i>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Riwayat Penanganan Perkara (Case History)</h3>
                        </div>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Catatan audit trail kronologis seluruh aktivitas penetapan tahap, DPO, dan dokumen perkara.
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">
                        Total Aktivitas: {{ $perkara->auditLogs->count() }}
                    </span>
                </div>

                @if ($perkara->auditLogs->isNotEmpty())
                    <div class="relative space-y-6 before:absolute before:bottom-2 before:left-[19px] before:top-2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-800">
                        @foreach ($perkara->auditLogs as $log)
                            @php
                                $meta = $actionMeta[$log->aksi] ?? [
                                    'label' => ucwords(str_replace('_', ' ', $log->aksi)),
                                    'icon' => 'fi-rr-time-past',
                                    'badge' => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:border-gray-800',
                                    'icon_bg' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                ];
                                $dataBaru = $log->data_baru ?? [];
                                $dataLama = $log->data_lama ?? [];
                            @endphp
                            <div class="relative flex items-start gap-4">
                                {{-- Circle Icon --}}
                                <div class="relative z-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-white {{ $meta['icon_bg'] }} shadow-xs dark:border-gray-900">
                                    <i class="fi {{ $meta['icon'] }} text-base"></i>
                                </div>

                                {{-- Detail Content --}}
                                <div class="flex-1 rounded-xl border border-gray-100 bg-gray-50/60 p-4 text-sm transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-0.5 text-xs font-semibold {{ $meta['badge'] }}">
                                                {{ $meta['label'] }}
                                            </span>

                                            @if ($log->berkasPerkara?->tersangka)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    &bull; Tersangka: <strong class="text-gray-700 dark:text-gray-300">{{ $log->berkasPerkara->tersangka->nama }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <span class="text-xs text-gray-400">
                                            {{ $log->created_at?->format('d-m-Y H:i:s') }} WITA
                                        </span>
                                    </div>

                                    {{-- Data Rinci Aksi (jika ada) --}}
                                    @if (! empty($dataBaru))
                                        <div class="mt-2.5 rounded-lg border border-gray-200/60 bg-white/70 p-2.5 text-xs dark:border-gray-800 dark:bg-gray-900/50">
                                            <div class="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                                                @if (! empty($dataBaru['nomor_surat']))
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Nomor Surat:</span>
                                                        <strong class="text-gray-800 dark:text-white/90">{{ $dataBaru['nomor_surat'] }}</strong>
                                                    </div>
                                                @endif

                                                @if (! empty($dataBaru['tanggal_penetapan']))
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Tanggal Penetapan:</span>
                                                        <span class="text-gray-700 dark:text-gray-300">{{ $dataBaru['tanggal_penetapan'] }}</span>
                                                    </div>
                                                @endif

                                                @if (! empty($dataBaru['tanggal_pencabutan']))
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Tanggal Pencabutan:</span>
                                                        <span class="text-gray-700 dark:text-gray-300">{{ $dataBaru['tanggal_pencabutan'] }}</span>
                                                    </div>
                                                @endif

                                                @if (! empty($dataBaru['jenis_tahap']))
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Tahapan:</span>
                                                        <strong class="text-gray-800 dark:text-white/90">{{ $dataBaru['jenis_tahap'] }}</strong>
                                                    </div>
                                                @endif
                                            </div>

                                            @if (! empty($dataBaru['keterangan']))
                                                <div class="mt-1.5">
                                                    <span class="text-gray-500 dark:text-gray-400">Keterangan:</span>
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $dataBaru['keterangan'] }}</span>
                                                </div>
                                            @endif

                                            @if (! empty($dataBaru['alasan_pencabutan']))
                                                <div class="mt-1.5">
                                                    <span class="text-gray-500 dark:text-gray-400">Alasan Pencabutan:</span>
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $dataBaru['alasan_pencabutan'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fi fi-rr-user text-[11px]"></i>
                                            Pelaksana: <strong>{{ $log->user?->nama ?? 'Sistem Otomatis' }}</strong>
                                            @if ($log->user?->role)
                                                ({{ $log->user->role->label() }})
                                            @endif
                                        </span>

                                        @if ($log->ip_address)
                                            <span class="inline-flex items-center gap-1 text-gray-400">
                                                <i class="fi fi-rr-network text-[11px]"></i>
                                                IP: {{ $log->ip_address }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-sm text-gray-400">
                        Belum ada riwayat aktivitas yang tercatat.
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal Tetapkan Status DPO --}}
        <div
            x-show="isDpoModalOpen"
            x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                @click.outside="closeDpoModal()"
                class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                        <i class="fi fi-rr-shield-exclamation text-xl"></i>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Tetapkan Status DPO
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="closeDpoModal()"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <i class="fi fi-rr-cross-small text-lg"></i>
                    </button>
                </div>

                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                    Tersangka: <strong x-text="dpoTersangkaNama" class="text-gray-800 dark:text-white/90"></strong>. Selama DPO aktif, pelimpahan berkas ke Tahap II akan dinonaktifkan di sistem.
                </p>

                <form :action="dpoActionUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="dpo_nomor_surat" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Nomor Surat Penetapan DPO <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="dpo_nomor_surat"
                            name="nomor_surat"
                            required
                            placeholder="Contoh: B/DPO/01/X/2026"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Note: Contoh format B/DPO/XX/BULAN/TAHUN</p>
                    </div>

                    <div>
                        <x-form.date-picker
                            id="dpo_tanggal_penetapan"
                            name="tanggal_penetapan"
                            label="Tanggal Penetapan DPO"
                            placeholder="Pilih tanggal penetapan..."
                            defaultDate="{{ date('Y-m-d') }}"
                        />
                    </div>

                    <div>
                        <x-form.pdf-upload
                            id="dpo_dokumen"
                            name="dokumen"
                            label="Dokumen Surat Penetapan DPO (PDF, Opsional)"
                            helperText="Format file: PDF murni (Maksimal 20 MB)."
                        />
                    </div>

                    <div>
                        <label for="dpo_keterangan" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Keterangan / Kronologi Singkat
                        </label>
                        <textarea
                            id="dpo_keterangan"
                            name="keterangan"
                            rows="3"
                            placeholder="Tuliskan keterangan penetapan DPO (opsional)..."
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                        ></textarea>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button
                            type="button"
                            @click="closeDpoModal()"
                            class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 text-xs font-medium text-white transition hover:bg-red-700"
                        >
                            <i class="fi fi-rr-check text-xs"></i>
                            <span>Tetapkan DPO</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Pencabutan Status DPO --}}
        <div
            x-show="isRevokeModalOpen"
            x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                @click.outside="closeRevokeModal()"
                class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-2 text-success-600 dark:text-success-400">
                        <i class="fi fi-rr-user-check text-xl"></i>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Cabut Status DPO
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="closeRevokeModal()"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <i class="fi fi-rr-cross-small text-lg"></i>
                    </button>
                </div>

                <div class="mb-4 rounded-lg border border-success-200 bg-success-50/70 p-3 text-xs text-success-800 dark:border-success-900/40 dark:bg-success-950/20 dark:text-success-300">
                    Tersangka: <strong x-text="revokeTersangkaNama"></strong>. Setelah status DPO dicabut, berkas perkara dapat dilanjutkan secara normal dan pelimpahan Tahap II dapat diproses kembali.
                </div>

                <form :action="revokeActionUrl" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <x-form.date-picker
                            id="revoke_tanggal_pencabutan"
                            name="tanggal_pencabutan"
                            label="Tanggal Pencabutan DPO"
                            placeholder="Pilih tanggal pencabutan..."
                            defaultDate="{{ date('Y-m-d') }}"
                        />
                    </div>

                    <div>
                        <label for="revoke_alasan_pencabutan" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Alasan Pencabutan Status DPO <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="revoke_alasan_pencabutan"
                            name="alasan_pencabutan"
                            required
                            rows="3"
                            placeholder="Contoh: Tersangka telah berhasil diamankan oleh tim PPNS di lokasi..."
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                        ></textarea>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button
                            type="button"
                            @click="closeRevokeModal()"
                            class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-4 text-xs font-medium text-white transition hover:bg-brand-700"
                        >
                            <i class="fi fi-rr-check text-xs"></i>
                            <span>Simpan &amp; Cabut Status DPO</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
