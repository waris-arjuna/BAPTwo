@php
    $title = 'Detail Perkara & Berkas Perkara';
    $isLocked = $perkara->isPerkaraLocked();
    $tkpAlamat = $perkara->tkp?->alamat;
@endphp

@extends('layouts.app')

@section('content')
    <div
        class="mx-auto max-w-screen-xl"
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

            // Modal Penangkapan / Penahanan DPO (Poin 4, ref: D-053)
            isPenangkapanModalOpen: false,
            penangkapanBerkasId: null,
            penangkapanTersangkaNama: '',
            penangkapanActionUrl: '',
            openPenangkapanDpoModal(berkasId, tersangkaNama) {
                this.penangkapanBerkasId = berkasId;
                this.penangkapanTersangkaNama = tersangkaNama;
                this.penangkapanActionUrl = '{{ url('/berkas-perkara') }}/' + berkasId + '/dpo/penangkapan';
                this.isPenangkapanModalOpen = true;
            },
            closePenangkapanModal() {
                this.isPenangkapanModalOpen = false;
                this.penangkapanBerkasId = null;
                this.penangkapanTersangkaNama = '';
                this.penangkapanActionUrl = '';
            },

            // Modal Pencabutan Status DPO (Fallback)
            isRevokeModalOpen: false,
            revokeBerkasId: null,
            revokeTersangkaNama: '',
            revokeActionUrl: '',
            openRevokeDpoModal(berkasId, tersangkaNama) {
                this.openPenangkapanDpoModal(berkasId, tersangkaNama);
            },
            closeRevokeModal() {
                this.closePenangkapanModal();
            },

            // Modal Penerbitan SP3
            isSp3ModalOpen: false,
            sp3BerkasId: null,
            sp3TersangkaNama: '',
            sp3ActionUrl: '',
            openSp3Modal(berkasId, tersangkaNama) {
                this.sp3BerkasId = berkasId;
                this.sp3TersangkaNama = tersangkaNama;
                this.sp3ActionUrl = '{{ url('/berkas-perkara') }}/' + berkasId + '/sp3';
                this.isSp3ModalOpen = true;
            },
            closeSp3Modal() {
                this.isSp3ModalOpen = false;
                this.sp3BerkasId = null;
                this.sp3TersangkaNama = '';
                this.sp3ActionUrl = '';
            },

            // Modal Lihat Detail SP3
            isViewSp3ModalOpen: false,
            viewSp3Data: {
                tersangka_nama: '',
                nomor_surat: '',
                tanggal_sp3: '',
                penyidik_nama: '',
                penyidik_utama: '',
                penyidik_anggota: [],
                penandatangan_nama: '',
                penandatangan_jabatan: '',
                keterangan: '',
                dokumen_nama: '',
                dokumen_preview_url: '',
                dokumen_download_url: '',
            },
            openViewSp3Modal(data) {
                this.viewSp3Data = data;
                this.isViewSp3ModalOpen = true;
            },
            closeViewSp3Modal() {
                this.isViewSp3ModalOpen = false;
            }
        }"
        @keydown.escape.window="closeDpoModal(); closePenangkapanModal(); closeRevokeModal(); closeSp3Modal(); closeViewSp3Modal();"
    >
        {{-- Header --}}
        <div class="mb-6">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Home</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('sprindik.index') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Sprindik</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('sprindik.show', $perkara) }}" class="font-mono transition hover:text-brand-600 dark:hover:text-brand-400">{{ $perkara->nomor_surat_perintah }}</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Detail Perkara</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detail Perkara &amp; Berkas Perkara</h1>
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
                            @php
                                $firstBerkas = $perkara->berkasPerkara?->first() ?? $perkara->tersangkas?->first()?->berkasPerkara;
                                $hideSpdpBtn = $firstBerkas && ($firstBerkas->hasTahap(\App\Enums\JenisTahap::SPDP) || $firstBerkas->hasSp3() || $firstBerkas->status_formal === 'tidak_selesai');
                            @endphp
                            @if (! $hideSpdpBtn)
                                <a
                                    href="{{ $firstBerkas ? route('berkas-perkara.tahapan.create', ['berkas' => $firstBerkas, 'jenis' => 'spdp']) : route('spdp.index') }}"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600"
                                >
                                    <i class="fi fi-rr-arrow-right text-sm"></i>
                                    <span>Lanjut ke Tahap SPDP</span>
                                </a>
                            @endif
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
                        <a href="{{ route('laporan-kejadian.show', $perkara) }}" class="inline-flex items-center gap-1 font-mono font-medium text-brand-600 hover:underline dark:text-brand-400">
                            <span>{{ $perkara->nomor_laporan_kejadian }}</span>
                            <i class="fi fi-rr-arrow-up-right-from-square text-xs"></i>
                        </a>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tanggal: <span class="tabular-nums">{{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}</span></p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen Laporan Kejadian</p>
                        @if ($perkara->dokumenLaporan)
                            <a href="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}"
                               data-pdf-preview="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}"
                               data-pdf-title="Laporan Kejadian ({{ $perkara->dokumenLaporan->nama_file_asli }})"
                               data-pdf-download="{{ route('dokumen.download', $perkara->dokumenLaporan) }}"
                               target="_blank" class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400">
                                <i class="fi fi-rr-document text-xs"></i>
                                <span class="truncate">{{ $perkara->dokumenLaporan->nama_file_asli }}</span>
                            </a>
                        @else
                            <span class="text-xs italic text-gray-400">Tidak ada dokumen</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nomor Sprindik</p>
                        <a href="{{ route('sprindik.show', $perkara) }}" class="inline-flex items-center gap-1 font-mono font-medium text-brand-600 hover:underline dark:text-brand-400">
                            <span>{{ $perkara->nomor_surat_perintah }}</span>
                            <i class="fi fi-rr-arrow-up-right-from-square text-xs"></i>
                        </a>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tanggal: <span class="tabular-nums">{{ $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}</span></p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen Sprindik</p>
                        @if ($perkara->dokumenSprindik)
                            <a href="{{ route('dokumen.preview', $perkara->dokumenSprindik) }}"
                               data-pdf-preview="{{ route('dokumen.preview', $perkara->dokumenSprindik) }}"
                               data-pdf-title="Sprindik ({{ $perkara->dokumenSprindik->nama_file_asli }})"
                               data-pdf-download="{{ route('dokumen.download', $perkara->dokumenSprindik) }}"
                               target="_blank" class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400">
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
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Nama Sarana / TKP</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $tkpAlamat?->nama ?: '-' }}</dd>
                        </div>

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
                                            <span>{{ $utama->nama }}{{ $utama->nip && $utama->nip !== '-' ? " (NIP: {$utama->nip})" : '' }}</span>
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
                                        <span>{{ $anggota->nama }}{{ $anggota->nip && $anggota->nip !== '-' ? " (NIP: {$anggota->nip})" : '' }}</span>
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

            {{-- Card Informasi Penuntut Umum (Poin 2 & Poin 8, ref: D-053) --}}
            <x-perkara.card-penuntut-umum :perkara="$perkara" />

            {{-- Daftar Tersangka & Berkas Perkara (1 Tersangka = 1 Berkas Perkara) --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Daftar Tersangka &amp; Berkas Perkara ({{ $perkara->tersangkas->count() }} Berkas)
                            </h2>
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
                                            <strong class="font-mono text-gray-800 dark:text-white/90">{{ $berkas?->nomor_berkas ?: '(Belum Bernomor / Draft)' }}</strong>
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

                                    {{-- Visual Indicator Status SP3 --}}
                                    @if ($berkas?->hasSp3() || $berkas?->isTidakSelesai())
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/20 dark:text-red-300">
                                            <i class="fi fi-rr-ban text-xs"></i>
                                            <span>SP3 Terbit (Tidak Selesai)</span>
                                        </span>
                                        @if ($berkas->sp3)
                                            @php
                                                $sp3Utama = $perkara->ppnsUtama->first() ?? $berkas->sp3->penyidikUtama ?? $berkas->sp3->creator;
                                                $sp3UtamaLabel = $sp3Utama
                                                    ? $sp3Utama->nama . ($sp3Utama->nip && $sp3Utama->nip !== '-' ? " (NIP: {$sp3Utama->nip})" : '')
                                                    : '-';
                                                $sp3AnggotaLabels = $perkara->ppnsAnggota->isNotEmpty()
                                                    ? $perkara->ppnsAnggota->map(fn ($u) => $u->nama . ($u->nip && $u->nip !== '-' ? " (NIP: {$u->nip})" : ''))->values()->toArray()
                                                    : $berkas->sp3->penyidikAnggota->map(fn ($u) => $u->nama . ($u->nip && $u->nip !== '-' ? " (NIP: {$u->nip})" : ''))->values()->toArray();

                                                $sp3ModalPayload = [
                                                    'tersangka_nama' => $tersangka->nama,
                                                    'nomor_surat' => $berkas->sp3->nomor_surat ?: '-',
                                                    'tanggal_sp3' => $berkas->sp3->tanggal_sp3?->format('d-m-Y') ?? '-',
                                                    'penyidik_utama' => $sp3UtamaLabel,
                                                    'penyidik_anggota' => $sp3AnggotaLabels,
                                                    'penandatangan_nama' => $berkas->sp3->penandatangan_nama ?: '-',
                                                    'penandatangan_jabatan' => $berkas->sp3->penandatangan_jabatan ?: '-',
                                                    'keterangan' => $berkas->sp3->keterangan ?: '-',
                                                    'dokumen_nama' => $berkas->sp3->dokumen?->nama_file_asli ?? '',
                                                    'dokumen_preview_url' => $berkas->sp3->dokumen ? route('dokumen.preview', $berkas->sp3->dokumen) : '',
                                                    'dokumen_download_url' => $berkas->sp3->dokumen ? route('dokumen.download', $berkas->sp3->dokumen) : '',
                                                ];
                                            @endphp
                                            <button
                                                type="button"
                                                @click="openViewSp3Modal({{ json_encode($sp3ModalPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }})"
                                                class="inline-flex items-center gap-1.5 rounded-full border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                                title="Lihat Detail SP3"
                                            >
                                                <i class="fi fi-rr-document text-xs"></i>
                                                <span>Lihat SP3</span>
                                            </button>
                                        @endif
                                    @endif

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">
                                        Status Proses: {{ $berkas?->status_proses ?? 'Baru' }} ({{ $berkas?->progress_persen ?? 0 }}%)
                                    </span>

                                    {{-- Visual Indicator SLA & Batas Waktu --}}
                                    @if ($berkas && $berkas->tanggal_spdp)
                                        @php
                                            $slaBadge = $berkas->sla_badge;
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium {{ $slaBadge['badge_class'] }}"
                                            title="Jatuh Tempo: {{ $berkas->effective_deadline?->format('d-m-Y') }} (Batas tahun selalu mutlak atas Tenggang Waktu)"
                                        >
                                            <i class="fi {{ $slaBadge['icon'] }} text-xs"></i>
                                            <span>{{ $slaBadge['label'] }}</span>
                                        </span>
                                    @endif

                                    @if ($berkas)
                                        {{-- Tombol Toggle DPO (Penyidik & Administrator) --}}
                                        @can('toggle-dpo')
                                            @if ($berkas->is_dpo)
                                                <button
                                                    type="button"
                                                    @click="openPenangkapanDpoModal({{ $berkas->id }}, '{{ addslashes($tersangka->nama) }}')"
                                                    class="inline-flex items-center gap-1.5 rounded-full border border-success-300 bg-success-50 px-3 py-1 text-xs font-medium text-success-700 transition hover:bg-success-100 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300"
                                                >
                                                    <i class="fi fi-rr-shield-check text-xs"></i>
                                                    <span>Catat Penangkapan &amp; Penahanan</span>
                                                </button>
                                            @elseif ($berkas->canActivateDpo())
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

                                        {{-- Tombol Terbitkan SP3 (Penyidik & Administrator) --}}
                                        @can('create-cases')
                                            @if (! $berkas->hasSp3() && $berkas->status_formal !== 'selesai' && ! $berkas->isTidakSelesai())
                                                <button
                                                    type="button"
                                                    @click="openSp3Modal({{ $berkas->id }}, '{{ addslashes($tersangka->nama) }}')"
                                                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-red-500/40 dark:hover:bg-red-500/10 dark:hover:text-red-300"
                                                    title="Terbitkan Surat Perintah Penghentian Penyidikan (SP3)"
                                                >
                                                    <i class="fi fi-rr-document-signed text-xs text-red-500"></i>
                                                    <span>Terbitkan SP3</span>
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
                                        @elseif ($isLocked && ! $berkas->is_dpo && ! $berkas->hasTahap(\App\Enums\JenisTahap::KEPUTUSAN_PENGADILAN) && app(\App\Services\TahapanService::class)->getNextAvailableStages($berkas) !== [])
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
                                                        <a href="{{ route('dokumen.preview', $activeDpo->dokumen) }}"
                                                           data-pdf-preview="{{ route('dokumen.preview', $activeDpo->dokumen) }}"
                                                           data-pdf-title="Dokumen DPO ({{ $activeDpo->dokumen->nama_file_asli }})"
                                                           data-pdf-download="{{ route('dokumen.download', $activeDpo->dokumen) }}"
                                                           target="_blank" class="flex items-center gap-1 font-medium text-brand-600 hover:underline dark:text-brand-400">
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
                                                <span>Penambahan dan pelanjutan seluruh tahapan perkara dinonaktifkan di sistem sampai status DPO dicabut.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Catatan Riwayat DPO yang Pernah Dicatat / Tertangkap --}}
                            @if (! $berkas?->is_dpo && $revokedDpos?->isNotEmpty())
                                @php
                                    $lastRevoked = $revokedDpos->first();
                                    $lastRevoked->loadMissing(['dokumenPenangkapan', 'dokumenPenangkapanList']);
                                    $allDokumenPenangkapan = $lastRevoked->dokumenPenangkapanList->isNotEmpty()
                                        ? $lastRevoked->dokumenPenangkapanList
                                        : ($lastRevoked->dokumenPenangkapan ? collect([$lastRevoked->dokumenPenangkapan]) : collect());
                                @endphp
                                <div class="mb-4 rounded-xl border border-success-200 bg-success-50/70 p-4 text-xs text-gray-700 dark:border-success-900/40 dark:bg-success-950/20 dark:text-gray-300">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-success-100 text-success-600 dark:bg-success-900/40 dark:text-success-400">
                                            <i class="fi fi-rr-shield-check text-sm"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <h5 class="text-xs font-semibold text-success-800 dark:text-success-300">
                                                    Riwayat DPO: Tersangka Telah Ditangkap / Ditahan
                                                </h5>
                                                @if ($lastRevoked->tanggal_penangkapan)
                                                    <span class="rounded-full bg-success-100 px-2.5 py-0.5 text-[11px] font-medium text-success-700 dark:bg-success-900/40 dark:text-success-400">
                                                        Tanggal Penangkapan: {{ $lastRevoked->tanggal_penangkapan->format('d-m-Y') }}
                                                    </span>
                                                @elseif ($lastRevoked->tanggal_pencabutan)
                                                    <span class="rounded-full bg-success-100 px-2.5 py-0.5 text-[11px] font-medium text-success-700 dark:bg-success-900/40 dark:text-success-400">
                                                        Dicabut: {{ $lastRevoked->tanggal_pencabutan->format('d-m-Y') }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mt-2.5 grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Surat DPO Asal:</span>
                                                    <p class="font-medium text-gray-800 dark:text-white/90">{{ $lastRevoked->nomor_surat }}</p>
                                                </div>

                                                @if ($lastRevoked->nomor_surat_penangkapan)
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Surat Penangkapan (Sp.Kap):</span>
                                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $lastRevoked->nomor_surat_penangkapan }}</p>
                                                    </div>
                                                @endif

                                                @if ($lastRevoked->nomor_surat_penahanan)
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Surat Penahanan (Sp.Han):</span>
                                                        <p class="font-medium text-gray-800 dark:text-white/90">
                                                            {{ $lastRevoked->nomor_surat_penahanan }}
                                                            @if ($lastRevoked->tanggal_penahanan)
                                                                <span class="text-gray-500 font-normal">({{ $lastRevoked->tanggal_penahanan->format('d-m-Y') }})</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                @endif

                                                @if ($allDokumenPenangkapan->isNotEmpty())
                                                    <div class="sm:col-span-2 lg:col-span-3">
                                                        <span class="text-gray-500 dark:text-gray-400">Berkas Dokumen Penangkapan &amp; Penahanan:</span>
                                                        <div class="mt-1.5 flex flex-wrap gap-2">
                                                            @foreach ($allDokumenPenangkapan as $dokPenangkapan)
                                                                <a href="{{ route('dokumen.preview', $dokPenangkapan) }}"
                                                                   data-pdf-preview="{{ route('dokumen.preview', $dokPenangkapan) }}"
                                                                   data-pdf-title="Dokumen Penangkapan ({{ $dokPenangkapan->nama_file_asli }})"
                                                                   data-pdf-download="{{ route('dokumen.download', $dokPenangkapan) }}"
                                                                   target="_blank"
                                                                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-brand-600 shadow-theme-xs transition hover:border-brand-300 hover:bg-brand-50/50 hover:underline dark:border-gray-700 dark:bg-gray-800 dark:text-brand-400 dark:hover:bg-gray-700">
                                                                    <i class="fi fi-rr-file-pdf text-red-500 text-xs"></i>
                                                                    <span class="max-w-[200px] truncate">{{ $dokPenangkapan->nama_file_asli }}</span>
                                                                    <span class="text-[10px] text-gray-400">({{ $dokPenangkapan->formatted_file_size }})</span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($lastRevoked->keterangan_penangkapan)
                                                    <div class="sm:col-span-2 lg:col-span-3">
                                                        <span class="text-gray-500 dark:text-gray-400">Keterangan:</span>
                                                        <p class="text-gray-700 dark:text-gray-300">{{ $lastRevoked->keterangan_penangkapan }}</p>
                                                    </div>
                                                @elseif ($lastRevoked->alasan_pencabutan)
                                                    <div class="sm:col-span-2 lg:col-span-3">
                                                        <span class="text-gray-500 dark:text-gray-400">Alasan:</span>
                                                        <p class="text-gray-700 dark:text-gray-300">{{ $lastRevoked->alasan_pencabutan }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 gap-4 rounded-xl border border-gray-100 bg-gray-50/60 p-4 text-sm sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-800 dark:bg-white/[0.02]">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">NIK / Jenis Kelamin</p>
                                    <p class="capitalize font-medium text-gray-800 dark:text-white/90">
                                        <span class="font-mono tabular-nums">{{ $tersangka->nik ?: '-' }}</span> / {{ $tersangka->jenis_kelamin ?: '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir</p>
                                    <p class="font-medium text-gray-800 dark:text-white/90">
                                        {{ $tersangka->tempat_lahir ?: '-' }}, <span class="tabular-nums">{{ $tersangka->tanggal_lahir?->format('d-m-Y') ?? '-' }}</span>
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

                                <div class="sm:col-span-2 lg:col-span-4 border-t border-gray-200/60 pt-3 dark:border-gray-800/60">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Alamat Lengkap Tersangka</p>
                                    <p class="font-medium text-gray-800 dark:text-white/90">
                                        @if ($tersangka->alamat)
                                            {{ $tersangka->alamat->alamat_detail }},
                                            @if ($tersangka->alamat->rt || $tersangka->alamat->rw)
                                                RT {{ $tersangka->alamat->rt ?: '-' }} / RW {{ $tersangka->alamat->rw ?: '-' }},
                                            @endif
                                            @if ($tersangka->alamat->desa_nama)
                                                Desa/Kel. {{ $tersangka->alamat->desa_nama }},
                                            @endif
                                            @if ($tersangka->alamat->kecamatan_nama)
                                                Kec. {{ $tersangka->alamat->kecamatan_nama }},
                                            @endif
                                            {{ $tersangka->alamat->kabupaten_nama }}, {{ $tersangka->alamat->provinsi_nama }}
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 italic">Belum diisi</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Informasi Batas Waktu & Tenggang Waktu Perkara --}}
                            @if ($berkas?->tanggal_spdp)
                                <div class="mt-4 flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-gray-50/80 p-4 sm:flex-row sm:items-center sm:p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                                    <div class="flex flex-wrap items-center gap-5 sm:gap-8">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                                <i class="fi fi-rr-calendar text-base"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal Terbit SPDP</p>
                                                <p class="mt-0.5 text-base font-bold tabular-nums text-gray-900 dark:text-white">
                                                    {{ $berkas->tanggal_spdp->format('d-m-Y') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="hidden h-9 w-px bg-gray-200 sm:block dark:bg-gray-800"></div>

                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                                <i class="fi fi-rr-hourglass-end text-base"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Batas Waktu Penyelesaian</p>
                                                <div class="mt-0.5 flex flex-wrap items-baseline gap-1.5">
                                                    <span class="text-base font-bold tabular-nums text-gray-900 dark:text-white">
                                                        {{ $berkas->effective_deadline?->format('d-m-Y') }}
                                                    </span>
                                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        ({{ $berkas->deadline_keterangan }})
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        @php
                                            $slaBadge = $berkas->sla_badge;
                                        @endphp
                                        <span class="inline-flex items-center gap-2 rounded-lg border px-3.5 py-1.5 text-sm font-semibold {{ $slaBadge['badge_class'] }}">
                                            <i class="fi {{ $slaBadge['icon'] }} text-sm"></i>
                                            <span>{{ $slaBadge['label'] }}</span>
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Pasal yang Disangkakan --}}
                            <div class="mt-4">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Pasal yang Disangkakan ({{ $berkas?->pasals->count() ?? 0 }} Pasal)
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
                            @php
                                $orderedTahapan = $berkas?->ordered_tahapan ?? collect();
                            @endphp
                            <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Riwayat Tahapan Progress ({{ $orderedTahapan->count() }} Tahap)
                                </p>
                                <div class="flex flex-wrap items-center gap-2">
                                    @forelse ($orderedTahapan as $tahap)
                                        <a
                                            href="{{ route('berkas-perkara.tahapan.show', [$berkas, $tahap]) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border {{ $tahap->isFinal() ? 'border-success-200 bg-success-50 text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300' : 'border-yellow-200 bg-yellow-50 text-yellow-800 dark:border-yellow-500/30 dark:bg-yellow-500/10 dark:text-yellow-300' }} px-3 py-1.5 text-xs font-medium hover:underline"
                                        >
                                            <i class="fi {{ $tahap->isFinal() ? 'fi-rr-lock' : 'fi-rr-edit' }} text-xs"></i>
                                            <span>{{ $tahap->jenis_tahap->label() }} ({{ $tahap->status === 'final' ? 'Final' : 'Draft' }})</span>
                                        </a>
                                        @if (! $loop->last)
                                            <i class="fi fi-rr-arrow-small-right text-sm text-gray-400 dark:text-gray-500" aria-hidden="true"></i>
                                        @endif
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
            <x-perkara.case-history :perkara="$perkara" />
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

        {{-- Modal Catat Penangkapan & Penahanan DPO (Poin 4, ref: D-053) --}}
        <x-perkara.modal-penangkapan-dpo />

        {{-- Modal Penerbitan SP3 (Poin 6, ref: D-030) --}}
        <x-perkara.modal-sp3 :perkara="$perkara" />

        @include('pages.perkara._modal-detail-sp3')
    </div>
@endsection
