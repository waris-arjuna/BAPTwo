@php
    $isDraft = $perkara->tahap_perkara_status === 'draft';
    $title = $isDraft ? 'Edit Draft Perkara & Berkas Perkara' : 'Buat Perkara & Berkas Perkara';

    $tkpAlamat = $perkara->tkp?->alamat;
    $existingUtamaId = old('ppns_utama_id', $perkara->ppnsUtama->first()?->id ?? '');
    $existingAnggotaIds = collect(old('ppns_anggota_ids', $perkara->ppnsAnggota->pluck('id')->all()))
        ->map(fn ($id) => (int) $id)
        ->all();
    $ppnsJsonList = $ppnsUsers->map(fn ($u) => [
        'id' => (int) $u->id,
        'nama' => $u->nama,
        'label' => $u->formatted_ppns_label,
        'nip' => $u->nip && $u->nip !== '-' ? $u->nip : '',
        'pangkat' => $u->pangkat ?: '',
    ])->values()->all();

    $pasalJsonList = $pasalList->map(fn ($p) => [
        'id' => (int) $p->id,
        'label' => $p->label(),
        'isi' => $p->isi ?? '',
    ])->values()->all();

    $firstTersangka = $perkara->tersangkas->first();
    $firstBerkas = $firstTersangka?->berkasPerkara ?? $perkara->berkasPerkaras->first();
    $tersangkaAlamat = $firstTersangka?->alamat;

    $oldTersangka = old('tersangka.0', []);
    $tersangkaId = old('tersangka_id', $oldTersangka['id'] ?? $firstTersangka?->id);
    $tersangkaNama = old('tersangka_nama', $oldTersangka['nama'] ?? ($firstTersangka?->nama ?? ''));
    $tersangkaAlias = old('tersangka_alias', $oldTersangka['alias'] ?? ($firstTersangka?->alias ?? ''));
    $tersangkaBinBinti = old('tersangka_bin_binti', $oldTersangka['bin_binti'] ?? ($firstTersangka?->bin_binti ?? ''));
    $tersangkaNik = old('tersangka_nik', $oldTersangka['nik'] ?? ($firstTersangka?->nik ?? ''));
    $tersangkaTempatLahir = old('tersangka_tempat_lahir', $oldTersangka['tempat_lahir'] ?? ($firstTersangka?->tempat_lahir ?? ''));
    $tersangkaTanggalLahir = old('tersangka_tanggal_lahir', $oldTersangka['tanggal_lahir'] ?? ($firstTersangka?->tanggal_lahir?->format('Y-m-d') ?? ''));
    $tersangkaJenisKelamin = old('tersangka_jenis_kelamin', $oldTersangka['jenis_kelamin'] ?? ($firstTersangka?->jenis_kelamin ?? ''));
    $tersangkaAgama = old('tersangka_agama', $oldTersangka['agama'] ?? ($firstTersangka?->agama ?? ''));
    $tersangkaPekerjaan = old('tersangka_pekerjaan', $oldTersangka['pekerjaan'] ?? ($firstTersangka?->pekerjaan ?? ''));
    $tersangkaKewarganegaraan = old('tersangka_kewarganegaraan', $oldTersangka['kewarganegaraan'] ?? ($firstTersangka?->kewarganegaraan ?? 'WNI'));

    $tersangkaProvinsiKode = old('tersangka_provinsi_kode', $oldTersangka['provinsi_kode'] ?? ($tersangkaAlamat?->provinsi_kode ?? ''));
    $tersangkaProvinsiNama = old('tersangka_provinsi_nama', $oldTersangka['provinsi_nama'] ?? ($tersangkaAlamat?->provinsi_nama ?? ''));
    $tersangkaKabupatenKode = old('tersangka_kabupaten_kode', $oldTersangka['kabupaten_kode'] ?? ($tersangkaAlamat?->kabupaten_kode ?? ''));
    $tersangkaKabupatenNama = old('tersangka_kabupaten_nama', $oldTersangka['kabupaten_nama'] ?? ($tersangkaAlamat?->kabupaten_nama ?? ''));
    $tersangkaKecamatanKode = old('tersangka_kecamatan_kode', $oldTersangka['kecamatan_kode'] ?? ($tersangkaAlamat?->kecamatan_kode ?? ''));
    $tersangkaKecamatanNama = old('tersangka_kecamatan_nama', $oldTersangka['kecamatan_nama'] ?? ($tersangkaAlamat?->kecamatan_nama ?? ''));
    $tersangkaDesaKode = old('tersangka_desa_kode', $oldTersangka['desa_kode'] ?? ($tersangkaAlamat?->desa_kode ?? ''));
    $tersangkaDesaNama = old('tersangka_desa_nama', $oldTersangka['desa_nama'] ?? ($tersangkaAlamat?->desa_nama ?? ''));
    $tersangkaRt = old('tersangka_rt', $oldTersangka['rt'] ?? ($tersangkaAlamat?->rt ?? ''));
    $tersangkaRw = old('tersangka_rw', $oldTersangka['rw'] ?? ($tersangkaAlamat?->rw ?? ''));
    $tersangkaAlamatDetail = old('tersangka_alamat_detail', $oldTersangka['alamat_detail'] ?? ($tersangkaAlamat?->alamat_detail ?? ''));

    $existingPasalIds = collect(old('pasal_ids', $oldTersangka['pasal_ids'] ?? ($firstBerkas?->pasals->pluck('id')->all() ?? [])))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-screen-xl px-4 pb-8 pt-6 lg:px-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Home</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('sprindik.index') }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">Sprindik</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <a href="{{ route('sprindik.show', $perkara) }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">{{ $perkara->nomor_surat_perintah }}</a>
                <i class="fi fi-rr-angle-small-right text-sm text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">{{ $title }}</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
                        @if ($isDraft)
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                <i class="fi fi-rr-edit text-[10px]"></i>
                                Draft (Dapat Diedit)
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Lengkapi data Tempat Kejadian Perkara (TKP), Tim Penyidik PPNS, Identitas Tersangka, dan Pasal Berlapis yang Disangkakan.
                    </p>
                </div>
                <a
                    href="{{ route('sprindik.show', $perkara) }}"
                    class="inline-flex h-10 w-fit items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                >
                    Kembali
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                <div class="mb-1 flex items-center gap-2 font-semibold">
                    <i class="fi fi-rr-exclamation text-base"></i>
                    <span>Terdapat kesalahan pada isian formulir:</span>
                </div>
                <ul class="ms-6 list-disc space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Card Referensi Tahapan Sebelumnya (Laporan Kejadian & Sprindik) --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02] sm:p-5">
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
                        <a
                            href="{{ route('dokumen.preview', $perkara->dokumenLaporan) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400"
                            data-pdf-preview
                            data-pdf-title="{{ $perkara->dokumenLaporan->nama_file_asli }}"
                            data-pdf-download="{{ route('dokumen.download', $perkara->dokumenLaporan) }}"
                        >
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
                        <a
                            href="{{ route('dokumen.preview', $perkara->dokumenSprindik) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400"
                            data-pdf-preview
                            data-pdf-title="{{ $perkara->dokumenSprindik->nama_file_asli }}"
                            data-pdf-download="{{ route('dokumen.download', $perkara->dokumenSprindik) }}"
                        >
                            <i class="fi fi-rr-document text-xs"></i>
                            <span class="truncate">{{ $perkara->dokumenSprindik->nama_file_asli }}</span>
                        </a>
                    @else
                        <span class="text-xs italic text-gray-400">Tidak ada dokumen</span>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('perkara.update', $perkara) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. Card Informasi TKP (Tempat Kejadian Perkara) --}}
            <div
                x-data="{
                    kabupatenList: [],
                    kecamatanList: [],
                    desaList: [],
                    provinsiKode: '63',
                    provinsiNama: 'Kalimantan Selatan',
                    kabupatenKode: @js(old('tkp_kabupaten_kode', $tkpAlamat?->kabupaten_kode ?? '')),
                    kabupatenNama: @js(old('tkp_kabupaten_nama', $tkpAlamat?->kabupaten_nama ?? '')),
                    kecamatanKode: @js(old('tkp_kecamatan_kode', $tkpAlamat?->kecamatan_kode ?? '')),
                    kecamatanNama: @js(old('tkp_kecamatan_nama', $tkpAlamat?->kecamatan_nama ?? '')),
                    desaKode: @js(old('tkp_desa_kode', $tkpAlamat?->desa_kode ?? '')),
                    desaNama: @js(old('tkp_desa_nama', $tkpAlamat?->desa_nama ?? '')),
                    kecamatanAbortController: null,
                    desaAbortController: null,
                    async init() {
                        await this.loadKabupaten(false);
                        if (this.kabupatenKode) await this.loadKecamatan(false);
                        if (this.kecamatanKode) await this.loadDesa(false);

                        this.$watch('kabupatenKode', (val) => {
                            const found = this.kabupatenList.find(k => String(k.code || k.kode) === String(val));
                            this.kabupatenNama = found ? (found.name || found.nama) : '';
                        });
                        this.$watch('kecamatanKode', (val) => {
                            const found = this.kecamatanList.find(k => String(k.code || k.kode) === String(val));
                            this.kecamatanNama = found ? (found.name || found.nama) : '';
                        });
                        this.$watch('desaKode', (val) => {
                            const found = this.desaList.find(d => String(d.code || d.kode) === String(val));
                            this.desaNama = found ? (found.name || found.nama) : '';
                        });
                    },
                    async loadKabupaten(reset = true) {
                        if (reset) {
                            this.kabupatenKode = '';
                            this.kabupatenNama = '';
                            this.kecamatanKode = '';
                            this.kecamatanNama = '';
                            this.desaKode = '';
                            this.desaNama = '';
                            this.kecamatanList = [];
                            this.desaList = [];
                        }
                        try {
                            const res = await fetch(`/wilayah/kabupaten/${this.provinsiKode}`);
                            const json = await res.json();
                            this.kabupatenList = json.data || [];
                            const found = this.kabupatenList.find(k => String(k.code || k.kode) === String(this.kabupatenKode));
                            if (found) this.kabupatenNama = found.name || found.nama;
                        } catch (e) {}
                    },
                    async loadKecamatan(reset = true, targetKode = null, autoOpen = false) {
                        if (targetKode !== null && targetKode !== undefined) {
                            this.kabupatenKode = String(targetKode);
                        }
                        if (reset) {
                            this.kecamatanKode = '';
                            this.kecamatanNama = '';
                            this.kecamatanList = [];
                            this.desaKode = '';
                            this.desaNama = '';
                            this.desaList = [];
                            this.$dispatch('searchable-select-loading', { id: 'tkp_kecamatan_kode', loading: false });
                            this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: false });
                        }
                        const found = this.kabupatenList.find(k => String(k.code || k.kode) === String(this.kabupatenKode));
                        this.kabupatenNama = found ? (found.name || found.nama) : '';

                        if (!this.kabupatenKode) {
                            this.kecamatanList = [];
                            this.$dispatch('searchable-select-loading', { id: 'tkp_kecamatan_kode', loading: false });
                            return;
                        }

                        if (this.kecamatanAbortController) {
                            this.kecamatanAbortController.abort();
                        }
                        this.kecamatanAbortController = new AbortController();
                        const currentKab = this.kabupatenKode;

                        this.$dispatch('searchable-select-loading', { id: 'tkp_kecamatan_kode', loading: true });

                        try {
                            const res = await fetch(`/wilayah/kecamatan/${currentKab}`, {
                                signal: this.kecamatanAbortController.signal
                            });
                            const json = await res.json();
                            if (this.kabupatenKode === currentKab) {
                                this.kecamatanList = json.data || [];
                                const foundKec = this.kecamatanList.find(k => String(k.code || k.kode) === String(this.kecamatanKode));
                                if (foundKec) this.kecamatanNama = foundKec.name || foundKec.nama;
                                if (autoOpen && this.kecamatanList.length > 0) {
                                    this.$nextTick(() => {
                                        this.$dispatch('searchable-select-open', { id: 'tkp_kecamatan_kode' });
                                    });
                                }
                            }
                        } catch (e) {
                            if (e.name !== 'AbortError') {
                                this.kecamatanList = [];
                            }
                        } finally {
                            if (this.kabupatenKode === currentKab) {
                                this.$dispatch('searchable-select-loading', { id: 'tkp_kecamatan_kode', loading: false });
                            }
                        }
                    },
                    async loadDesa(reset = true, targetKode = null, autoOpen = false) {
                        if (targetKode !== null && targetKode !== undefined) {
                            this.kecamatanKode = String(targetKode);
                        }
                        if (reset) {
                            this.desaKode = '';
                            this.desaNama = '';
                            this.desaList = [];
                            this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: false });
                        }
                        const found = this.kecamatanList.find(k => String(k.code || k.kode) === String(this.kecamatanKode));
                        this.kecamatanNama = found ? (found.name || found.nama) : '';

                        if (!this.kecamatanKode) {
                            this.desaList = [];
                            this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: false });
                            return;
                        }

                        if (this.desaAbortController) {
                            this.desaAbortController.abort();
                        }
                        this.desaAbortController = new AbortController();
                        const currentKec = this.kecamatanKode;

                        this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: true });

                        try {
                            const res = await fetch(`/wilayah/desa/${currentKec}`, {
                                signal: this.desaAbortController.signal
                            });
                            const json = await res.json();
                            if (this.kecamatanKode === currentKec) {
                                this.desaList = json.data || [];
                                const foundDesa = this.desaList.find(d => String(d.code || d.kode) === String(this.desaKode));
                                if (foundDesa) this.desaNama = foundDesa.name || foundDesa.nama;
                                if (autoOpen && this.desaList.length > 0) {
                                    this.$nextTick(() => {
                                        this.$dispatch('searchable-select-open', { id: 'tkp_desa_kode' });
                                    });
                                }
                            }
                        } catch (e) {
                            if (e.name !== 'AbortError') {
                                this.desaList = [];
                            }
                        } finally {
                            if (this.kecamatanKode === currentKec) {
                                this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: false });
                            }
                        }
                    },
                    onKabupatenChange(event) {
                        const val = event && event.target ? event.target.value : (event || this.kabupatenKode);
                        this.loadKecamatan(true, val, true);
                    },
                    onKecamatanChange(event) {
                        const val = event && event.target ? event.target.value : (event || this.kecamatanKode);
                        this.loadDesa(true, val, true);
                    },
                    onDesaChange(event) {
                        const val = event && event.target ? event.target.value : (event || this.desaKode);
                        if (val !== undefined && val !== null) this.desaKode = String(val);
                        const found = this.desaList.find(d => String(d.code || d.kode) === String(this.desaKode));
                        this.desaNama = found ? (found.name || found.nama) : '';
                    }
                }"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-marker text-base text-brand-500"></i>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">1. Informasi TKP (Tempat Kejadian Perkara)</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lokasi kejadian, klasifikasi sarana, legalitas, dan modus operandi.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 px-4 py-5 sm:grid-cols-2 sm:px-6">
                    {{-- Nama Sarana / TKP --}}
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Sarana / Tempat Kejadian (TKP)
                        </label>
                        <input
                            type="text"
                            name="tkp_nama"
                            maxlength="150"
                            value="{{ old('tkp_nama', $tkpAlamat?->nama ?? '') }}"
                            placeholder="Contoh: Kios Kosmetik Ayu Cantik, Gudang Berkah Mandiri, Depot Jamu, Apotek, dll."
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90 dark:placeholder:text-white/30"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nama toko, kios, gudang, atau tempat usaha (opsional jika bukan sarana usaha).</p>
                    </div>

                    {{-- Provinsi: Read-only Kalimantan Selatan (Poin 4) --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Provinsi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                value="Kalimantan Selatan"
                                readonly
                                class="h-11 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 text-sm font-medium text-gray-700 select-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                            >
                            <input type="hidden" name="tkp_provinsi_kode" value="63">
                            <input type="hidden" name="tkp_provinsi_nama" value="Kalimantan Selatan">
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Wilayah hukum BPOM di Banjarmasin (Provinsi Kalimantan Selatan).</p>
                    </div>

                    {{-- Kabupaten / Kota --}}
                    <div>
                        <x-form.searchable-select
                            name="tkp_kabupaten_kode"
                            id="tkp_kabupaten_kode"
                            label="Kabupaten / Kota"
                            placeholder="-- Pilih Kabupaten/Kota --"
                            search-placeholder="Ketik nama kabupaten/kota..."
                            x-model="kabupatenKode"
                            @change="onKabupatenChange($event)"
                            required
                        >
                            <option value="">-- Pilih Kabupaten/Kota --</option>
                            <template x-for="item in kabupatenList" :key="item.code || item.kode">
                                <option :value="item.code || item.kode" :selected="(item.code || item.kode) === kabupatenKode" x-text="item.name || item.nama"></option>
                            </template>
                        </x-form.searchable-select>
                        <input type="hidden" name="tkp_kabupaten_nama" :value="kabupatenNama">
                    </div>

                    {{-- Kecamatan --}}
                    <div>
                        <x-form.searchable-select
                            name="tkp_kecamatan_kode"
                            id="tkp_kecamatan_kode"
                            label="Kecamatan"
                            placeholder="-- Pilih Kecamatan --"
                            search-placeholder="Ketik nama kecamatan..."
                            x-model="kecamatanKode"
                            @change="onKecamatanChange($event)"
                            required
                        >
                            <option value="">-- Pilih Kecamatan --</option>
                            <template x-for="item in kecamatanList" :key="item.code || item.kode">
                                <option :value="item.code || item.kode" :selected="(item.code || item.kode) === kecamatanKode" x-text="item.name || item.nama"></option>
                            </template>
                        </x-form.searchable-select>
                        <input type="hidden" name="tkp_kecamatan_nama" :value="kecamatanNama">
                    </div>

                    {{-- Desa / Kelurahan --}}
                    <div>
                        <x-form.searchable-select
                            name="tkp_desa_kode"
                            id="tkp_desa_kode"
                            label="Desa / Kelurahan"
                            placeholder="-- Pilih Desa/Kelurahan --"
                            search-placeholder="Ketik nama desa/kelurahan..."
                            x-model="desaKode"
                            @change="onDesaChange($event)"
                            required
                        >
                            <option value="">-- Pilih Desa/Kelurahan --</option>
                            <template x-for="item in desaList" :key="item.code || item.kode">
                                <option :value="item.code || item.kode" :selected="(item.code || item.kode) === desaKode" x-text="item.name || item.nama"></option>
                            </template>
                        </x-form.searchable-select>
                        <input type="hidden" name="tkp_desa_nama" :value="desaNama">
                    </div>

                    {{-- RT / RW --}}
                    <div class="grid grid-cols-2 gap-4 sm:col-span-2 sm:max-w-xs">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">RT</label>
                            <input
                                type="text"
                                name="tkp_rt"
                                maxlength="5"
                                value="{{ old('tkp_rt', $tkpAlamat?->rt ?? '') }}"
                                placeholder="001"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                            >
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">RW</label>
                            <input
                                type="text"
                                name="tkp_rw"
                                maxlength="5"
                                value="{{ old('tkp_rw', $tkpAlamat?->rw ?? '') }}"
                                placeholder="002"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                            >
                        </div>
                    </div>

                    {{-- Detail Alamat --}}
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Detail Alamat TKP <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="tkp_alamat_detail"
                            rows="2"
                            placeholder="Contoh: Gudang Distribusi Jl. A. Yani Km. 14 No. 88"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90 dark:placeholder:text-white/30"
                        >{{ old('tkp_alamat_detail', $tkpAlamat?->alamat_detail ?? '') }}</textarea>
                    </div>

                    {{-- Klasifikasi Sarana --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Klasifikasi Sarana / TKP <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tkp_klasifikasi"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="">-- Pilih Klasifikasi --</option>
                            <option value="retailer" @selected(old('tkp_klasifikasi', $perkara->tkp?->klasifikasi) === 'retailer')>Retailer</option>
                            <option value="distributor" @selected(old('tkp_klasifikasi', $perkara->tkp?->klasifikasi) === 'distributor')>Distributor</option>
                        </select>
                    </div>

                    {{-- Legalitas Sarana: Radio Buttons (Poin 3) --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Legalitas Sarana <span class="text-red-500">*</span>
                        </label>
                        <div class="flex h-11 items-center gap-6 rounded-lg border border-gray-300 bg-transparent px-4 dark:border-gray-700">
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input
                                    type="radio"
                                    name="tkp_legalitas"
                                    value="legal"
                                    @checked(old('tkp_legalitas', $perkara->tkp?->legalitas) === 'legal')
                                    class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                                >
                                <span>Legal</span>
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input
                                    type="radio"
                                    name="tkp_legalitas"
                                    value="ilegal"
                                    @checked(old('tkp_legalitas', $perkara->tkp?->legalitas) === 'ilegal')
                                    class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                                >
                                <span>Ilegal</span>
                            </label>
                        </div>
                    </div>

                    {{-- Modus Operandi: Radio Buttons (Poin 3) --}}
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Modus Operandi <span class="text-red-500">*</span>
                        </label>
                        <div class="flex h-11 max-w-md items-center gap-6 rounded-lg border border-gray-300 bg-transparent px-4 dark:border-gray-700">
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input
                                    type="radio"
                                    name="tkp_modus"
                                    value="online"
                                    @checked(old('tkp_modus', $perkara->tkp?->modus) === 'online')
                                    class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                                >
                                <span>Online</span>
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input
                                    type="radio"
                                    name="tkp_modus"
                                    value="offline"
                                    @checked(old('tkp_modus', $perkara->tkp?->modus) === 'offline')
                                    class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                                >
                                <span>Offline</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Card Tim Penyidik PPNS (Utama & Anggota) --}}
            <div
                x-data="{
                    ppnsList: @js($ppnsJsonList),
                    selectedUtamaId: @js((int) $existingUtamaId ?: null),
                    selectedAnggotaIds: @js($existingAnggotaIds),
                    addAnggota(id) {
                        if (!id) return;
                        const numId = parseInt(id, 10);
                        if (!this.selectedAnggotaIds.includes(numId)) {
                            this.selectedAnggotaIds.push(numId);
                        }
                        this.$nextTick(() => {
                            const el = document.getElementById('ppns_anggota_select');
                            if (el) {
                                el.value = '';
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                    },
                    removeAnggota(id) {
                        this.selectedAnggotaIds = this.selectedAnggotaIds.filter(i => i !== id);
                    },
                    onUtamaChange(val) {
                        this.selectedUtamaId = val ? parseInt(val, 10) : null;
                        if (this.selectedUtamaId && this.selectedAnggotaIds.includes(this.selectedUtamaId)) {
                            this.removeAnggota(this.selectedUtamaId);
                        }
                    },
                    getAvailableAnggota() {
                        return this.ppnsList.filter(u => u.id !== this.selectedUtamaId && !this.selectedAnggotaIds.includes(u.id));
                    },
                    getSelectedAnggotaDetails() {
                        return this.selectedAnggotaIds
                            .map(id => this.ppnsList.find(u => u.id === id))
                            .filter(Boolean);
                    }
                }"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-users-alt text-base text-brand-500"></i>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">2. Tim Penyidik PPNS (Utama &amp; Anggota)</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tentukan Penyidik PPNS Utama dan Anggota yang menangani perkara ini.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 px-4 py-5 sm:px-6">
                    {{-- Hidden inputs untuk pengiriman array ppns_anggota_ids[] --}}
                    <template x-for="id in selectedAnggotaIds" :key="'hidden-ppns-' + id">
                        <input type="hidden" name="ppns_anggota_ids[]" :value="id">
                    </template>

                    {{-- PPNS Utama (Searchable Select) --}}
                    <div>
                        <x-form.searchable-select
                            id="ppns_utama_id"
                            name="ppns_utama_id"
                            label="Penyidik PPNS Utama"
                            placeholder="-- Pilih PPNS Utama --"
                            search-placeholder="Ketik nama atau NIP penyidik..."
                            :value="old('ppns_utama_id', $existingUtamaId)"
                            @change="onUtamaChange($event.target.value)"
                            required
                        >
                            <option value="">-- Pilih PPNS Utama --</option>
                            @foreach ($ppnsUsers as $ppns)
                                <option value="{{ $ppns->id }}" @selected((int) $existingUtamaId === $ppns->id)>
                                    {{ $ppns->formatted_ppns_label }}
                                </option>
                            @endforeach
                        </x-form.searchable-select>
                        @error('ppns_utama_id')
                            <p class="mt-1.5 text-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PPNS Anggota Combo Box & List --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="ppns_anggota_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Penyidik PPNS Anggota (Opsional)
                            </label>
                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedAnggotaIds.length + ' anggota terpilih'"></span>
                        </div>

                        {{-- Combo Box Dropdown (Searchable Select) --}}
                        <div class="relative">
                            <x-form.searchable-select
                                id="ppns_anggota_select"
                                name="ppns_anggota_select"
                                placeholder="+ Pilih dan tambah anggota penyidik..."
                                search-placeholder="Ketik nama atau NIP penyidik..."
                                :clearable="false"
                                @change="addAnggota($event.target.value)"
                            >
                                <option value="" x-text="getAvailableAnggota().length === 0 ? 'Semua PPNS sudah terpilih' : '+ Pilih dan tambah anggota penyidik...'"></option>
                                <template x-for="ppns in getAvailableAnggota()" :key="ppns.id">
                                    <option :value="ppns.id" x-text="ppns.label || ppns.nama"></option>
                                </template>
                            </x-form.searchable-select>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Pilih penyidik dari daftar combo box untuk menambahkannya ke tim anggota. Anda dapat memilih lebih dari satu penyidik.</p>

                        @error('ppns_anggota_ids')
                            <p class="mt-1.5 text-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror

                        {{-- Kartu Detail Penyidik Terpilih --}}
                        <div class="mt-4">
                            <div x-show="selectedAnggotaIds.length > 0" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <template x-for="ppns in getSelectedAnggotaDetails()" :key="ppns.id">
                                    <div class="relative flex items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50/40 p-3.5 transition dark:border-brand-500/20 dark:bg-brand-500/5">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                                <i class="fi fi-rr-user text-base"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90" x-text="ppns.nama"></p>
                                                <p class="truncate text-xs text-gray-500 dark:text-gray-400" x-text="ppns.nip ? ('NIP: ' + ppns.nip + (ppns.pangkat ? ' · ' + ppns.pangkat : '')) : (ppns.pangkat || 'Penyidik PPNS')"></p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="removeAnggota(ppns.id)"
                                            class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 hover:bg-error-50 hover:text-error-600 transition dark:hover:bg-error-500/10 dark:hover:text-error-400"
                                            title="Hapus dari anggota"
                                        >
                                            <i class="fi fi-rr-cross-small text-base"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- State Kosong / Belum Ada Anggota --}}
                            <div
                                x-show="selectedAnggotaIds.length === 0"
                                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 py-6 text-center dark:border-gray-800"
                            >
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                    <i class="fi fi-rr-users text-sm"></i>
                                </div>
                                <p class="mt-2 text-xs font-medium text-gray-500 dark:text-gray-400">Belum ada PPNS Anggota yang dipilih</p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Pilih dari combo box di atas untuk menambahkan anggota tim penyidik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Card Data Tersangka & Berkas Perkara (1 Perkara = 1 Berkas Perkara = 1 Tersangka, Poin 11 & 13) --}}
            <div
                x-data="{
                    isAlamatModalOpen: false,
                    alamat: {
                        provinsi_kode: @js($tersangkaProvinsiKode),
                        provinsi_nama: @js($tersangkaProvinsiNama),
                        kabupaten_kode: @js($tersangkaKabupatenKode),
                        kabupaten_nama: @js($tersangkaKabupatenNama),
                        kecamatan_kode: @js($tersangkaKecamatanKode),
                        kecamatan_nama: @js($tersangkaKecamatanNama),
                        desa_kode: @js($tersangkaDesaKode),
                        desa_nama: @js($tersangkaDesaNama),
                        rt: @js($tersangkaRt),
                        rw: @js($tersangkaRw),
                        alamat_detail: @js($tersangkaAlamatDetail),
                    },
                    modalWilayah: {
                        provinsi_kode: '',
                        provinsi_nama: '',
                        kabupaten_kode: '',
                        kabupaten_nama: '',
                        kecamatan_kode: '',
                        kecamatan_nama: '',
                        desa_kode: '',
                        desa_nama: '',
                    },
                    modalRt: '',
                    modalRw: '',
                    modalAlamatDetail: '',
                    listProvinsi: [],
                    listKabupaten: [],
                    listKecamatan: [],
                    listDesa: [],
                    provinsiAbortController: null,
                    kabupatenAbortController: null,
                    kecamatanAbortController: null,
                    desaAbortController: null,

                    get alamatRingkasan() {
                        if (!this.alamat.provinsi_kode && !this.alamat.alamat_detail) {
                            return 'Belum diatur (klik untuk memilih alamat)';
                        }
                        const parts = [];
                        if (this.alamat.alamat_detail) parts.push(this.alamat.alamat_detail);
                        if (this.alamat.rt || this.alamat.rw) {
                            parts.push('RT ' + (this.alamat.rt || '-') + ' / RW ' + (this.alamat.rw || '-'));
                        }
                        if (this.alamat.desa_nama) parts.push('Desa/Kel. ' + this.alamat.desa_nama);
                        if (this.alamat.kecamatan_nama) parts.push('Kec. ' + this.alamat.kecamatan_nama);
                        if (this.alamat.kabupaten_nama) parts.push(this.alamat.kabupaten_nama);
                        if (this.alamat.provinsi_nama) parts.push(this.alamat.provinsi_nama);
                        return parts.join(', ');
                    },

                    async openAlamatModal() {
                        this.modalWilayah.provinsi_kode = this.alamat.provinsi_kode || '';
                        this.modalWilayah.provinsi_nama = this.alamat.provinsi_nama || '';
                        this.modalWilayah.kabupaten_kode = this.alamat.kabupaten_kode || '';
                        this.modalWilayah.kabupaten_nama = this.alamat.kabupaten_nama || '';
                        this.modalWilayah.kecamatan_kode = this.alamat.kecamatan_kode || '';
                        this.modalWilayah.kecamatan_nama = this.alamat.kecamatan_nama || '';
                        this.modalWilayah.desa_kode = this.alamat.desa_kode || '';
                        this.modalWilayah.desa_nama = this.alamat.desa_nama || '';
                        this.modalRt = this.alamat.rt || '';
                        this.modalRw = this.alamat.rw || '';
                        this.modalAlamatDetail = this.alamat.alamat_detail || '';

                        this.isAlamatModalOpen = true;

                        await this.fetchProvinsi();
                        if (this.modalWilayah.provinsi_kode) {
                            await this.fetchKabupaten(this.modalWilayah.provinsi_kode);
                        }
                        if (this.modalWilayah.kabupaten_kode) {
                            await this.fetchKecamatan(this.modalWilayah.kabupaten_kode);
                        }
                        if (this.modalWilayah.kecamatan_kode) {
                            await this.fetchDesa(this.modalWilayah.kecamatan_kode);
                        }
                    },

                    async fetchProvinsi() {
                        if (this.listProvinsi.length > 0) return;
                        try {
                            const res = await fetch('{{ route('wilayah.provinsi') }}');
                            const json = await res.json();
                            this.listProvinsi = json.data || [];
                        } catch (e) {
                            this.listProvinsi = [];
                        }
                    },

                    async fetchKabupaten(provKode, autoOpen = false) {
                        if (!provKode) {
                            this.listKabupaten = [];
                            this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kabupaten_kode', loading: false });
                            return;
                        }
                        if (this.kabupatenAbortController) {
                            this.kabupatenAbortController.abort();
                        }
                        this.kabupatenAbortController = new AbortController();
                        this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kabupaten_kode', loading: true });
                        try {
                            const res = await fetch(`{{ url('/wilayah/kabupaten') }}/${provKode}`, {
                                signal: this.kabupatenAbortController.signal
                            });
                            const json = await res.json();
                            if (String(this.modalWilayah.provinsi_kode) === String(provKode)) {
                                this.listKabupaten = json.data || [];
                                if (autoOpen && this.listKabupaten.length > 0) {
                                    this.$nextTick(() => {
                                        this.$dispatch('searchable-select-open', { id: 'modal_tersangka_kabupaten_kode' });
                                    });
                                }
                            }
                        } catch (e) {
                            if (e.name !== 'AbortError') {
                                this.listKabupaten = [];
                            }
                        } finally {
                            if (String(this.modalWilayah.provinsi_kode) === String(provKode)) {
                                this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kabupaten_kode', loading: false });
                            }
                        }
                    },

                    async fetchKecamatan(kabKode, autoOpen = false) {
                        if (!kabKode) {
                            this.listKecamatan = [];
                            this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kecamatan_kode', loading: false });
                            return;
                        }
                        if (this.kecamatanAbortController) {
                            this.kecamatanAbortController.abort();
                        }
                        this.kecamatanAbortController = new AbortController();
                        this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kecamatan_kode', loading: true });
                        try {
                            const res = await fetch(`{{ url('/wilayah/kecamatan') }}/${kabKode}`, {
                                signal: this.kecamatanAbortController.signal
                            });
                            const json = await res.json();
                            if (String(this.modalWilayah.kabupaten_kode) === String(kabKode)) {
                                this.listKecamatan = json.data || [];
                                if (autoOpen && this.listKecamatan.length > 0) {
                                    this.$nextTick(() => {
                                        this.$dispatch('searchable-select-open', { id: 'modal_tersangka_kecamatan_kode' });
                                    });
                                }
                            }
                        } catch (e) {
                            if (e.name !== 'AbortError') {
                                this.listKecamatan = [];
                            }
                        } finally {
                            if (String(this.modalWilayah.kabupaten_kode) === String(kabKode)) {
                                this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kecamatan_kode', loading: false });
                            }
                        }
                    },

                    async fetchDesa(kecKode, autoOpen = false) {
                        if (!kecKode) {
                            this.listDesa = [];
                            this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_desa_kode', loading: false });
                            return;
                        }
                        if (this.desaAbortController) {
                            this.desaAbortController.abort();
                        }
                        this.desaAbortController = new AbortController();
                        this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_desa_kode', loading: true });
                        try {
                            const res = await fetch(`{{ url('/wilayah/desa') }}/${kecKode}`, {
                                signal: this.desaAbortController.signal
                            });
                            const json = await res.json();
                            if (String(this.modalWilayah.kecamatan_kode) === String(kecKode)) {
                                this.listDesa = json.data || [];
                                if (autoOpen && this.listDesa.length > 0) {
                                    this.$nextTick(() => {
                                        this.$dispatch('searchable-select-open', { id: 'modal_tersangka_desa_kode' });
                                    });
                                }
                            }
                        } catch (e) {
                            if (e.name !== 'AbortError') {
                                this.listDesa = [];
                            }
                        } finally {
                            if (String(this.modalWilayah.kecamatan_kode) === String(kecKode)) {
                                this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_desa_kode', loading: false });
                            }
                        }
                    },

                    onProvinsiChange(event) {
                        const val = event && event.target ? event.target.value : (event || this.modalWilayah.provinsi_kode);
                        this.modalWilayah.provinsi_kode = val ? String(val) : '';
                        const found = this.listProvinsi.find(p => String(p.code) === String(val));
                        this.modalWilayah.provinsi_nama = found ? (found.name || found.nama) : '';

                        this.modalWilayah.kabupaten_kode = '';
                        this.modalWilayah.kabupaten_nama = '';
                        this.modalWilayah.kecamatan_kode = '';
                        this.modalWilayah.kecamatan_nama = '';
                        this.modalWilayah.desa_kode = '';
                        this.modalWilayah.desa_nama = '';
                        this.listKabupaten = [];
                        this.listKecamatan = [];
                        this.listDesa = [];

                        this.fetchKabupaten(val, true);
                    },

                    onKabupatenChange(event) {
                        const val = event && event.target ? event.target.value : (event || this.modalWilayah.kabupaten_kode);
                        this.modalWilayah.kabupaten_kode = val ? String(val) : '';
                        const found = this.listKabupaten.find(k => String(k.code) === String(val));
                        this.modalWilayah.kabupaten_nama = found ? (found.name || found.nama) : '';

                        this.modalWilayah.kecamatan_kode = '';
                        this.modalWilayah.kecamatan_nama = '';
                        this.modalWilayah.desa_kode = '';
                        this.modalWilayah.desa_nama = '';
                        this.listKecamatan = [];
                        this.listDesa = [];

                        this.fetchKecamatan(val, true);
                    },

                    onKecamatanChange(event) {
                        const val = event && event.target ? event.target.value : (event || this.modalWilayah.kecamatan_kode);
                        this.modalWilayah.kecamatan_kode = val ? String(val) : '';
                        const found = this.listKecamatan.find(k => String(k.code) === String(val));
                        this.modalWilayah.kecamatan_nama = found ? (found.name || found.nama) : '';

                        this.modalWilayah.desa_kode = '';
                        this.modalWilayah.desa_nama = '';
                        this.listDesa = [];

                        this.fetchDesa(val, true);
                    },

                    onDesaChange(event) {
                        const val = event && event.target ? event.target.value : (event || this.modalWilayah.desa_kode);
                        this.modalWilayah.desa_kode = val ? String(val) : '';
                        const found = this.listDesa.find(d => String(d.code) === String(val));
                        this.modalWilayah.desa_nama = found ? (found.name || found.nama) : '';
                    },

                    applyAlamat() {
                        this.alamat.provinsi_kode = this.modalWilayah.provinsi_kode;
                        this.alamat.provinsi_nama = this.modalWilayah.provinsi_nama;
                        this.alamat.kabupaten_kode = this.modalWilayah.kabupaten_kode;
                        this.alamat.kabupaten_nama = this.modalWilayah.kabupaten_nama;
                        this.alamat.kecamatan_kode = this.modalWilayah.kecamatan_kode;
                        this.alamat.kecamatan_nama = this.modalWilayah.kecamatan_nama;
                        this.alamat.desa_kode = this.modalWilayah.desa_kode;
                        this.alamat.desa_nama = this.modalWilayah.desa_nama;
                        this.alamat.rt = this.modalRt;
                        this.alamat.rw = this.modalRw;
                        this.alamat.alamat_detail = this.modalAlamatDetail;

                        this.isAlamatModalOpen = false;
                    }
                }"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-user text-base text-brand-500"></i>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">3. Data Identitas Tersangka</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Satu Perkara menangani satu Tersangka dan membentuk satu Berkas Perkara dengan nomor berkas otomatis mengikuti Nomor Laporan Kejadian (<span class="font-semibold text-gray-700 dark:text-gray-300">{{ $perkara->nomor_laporan_kejadian }}</span>).
                    </p>
                </div>

                <div class="space-y-5 p-4 sm:p-6">
                    <input type="hidden" name="tersangka_id" value="{{ $tersangkaId }}">
                    <input type="hidden" name="nomor_berkas" value="{{ $perkara->nomor_laporan_kejadian }}">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {{-- Nama Lengkap --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Lengkap Tersangka <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="tersangka_nama"
                                value="{{ $tersangkaNama }}"
                                placeholder="Nama lengkap tersangka"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                required
                            >
                        </div>

                        {{-- Alias --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Alias / Panggilan
                            </label>
                            <input
                                type="text"
                                name="tersangka_alias"
                                value="{{ $tersangkaAlias }}"
                                placeholder="Contoh: Alias Budi"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                            >
                        </div>

                        {{-- Bin / Binti --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Bin / Binti
                            </label>
                            <input
                                type="text"
                                name="tersangka_bin_binti"
                                value="{{ $tersangkaBinBinti }}"
                                placeholder="Nama orang tua (Bin/Binti)"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                            >
                        </div>

                        {{-- NIK 16 Digit (Poin 17) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                NIK (Tepat 16 Digit Angka) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="tersangka_nik"
                                id="tersangka_nik"
                                value="{{ $tersangkaNik }}"
                                maxlength="16"
                                inputmode="numeric"
                                pattern="[0-9]{16}"
                                placeholder="Contoh: 6371011234560001"
                                oninput="this.value = this.value.replace(/\D/g, '').slice(0, 16)"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                required
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Harus tepat 16 digit angka sesuai KTP.</p>
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="tersangka_jenis_kelamin"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                required
                            >
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="laki-laki" @selected($tersangkaJenisKelamin === 'laki-laki')>Laki-laki</option>
                                <option value="perempuan" @selected($tersangkaJenisKelamin === 'perempuan')>Perempuan</option>
                            </select>
                        </div>

                        {{-- Tempat Lahir --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tempat Lahir <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="tersangka_tempat_lahir"
                                value="{{ $tersangkaTempatLahir }}"
                                placeholder="Contoh: Banjarmasin"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                required
                            >
                        </div>

                        {{-- Tanggal Lahir (Datepicker Flatpickr) --}}
                        <div>
                            <label for="tersangka_tanggal_lahir" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <x-form.date-picker
                                id="tersangka_tanggal_lahir"
                                name="tersangka_tanggal_lahir"
                                placeholder="Pilih Tanggal Lahir"
                                :default-date="$tersangkaTanggalLahir"
                                date-format="Y-m-d"
                                max-date="today"
                                :required="true"
                            />
                        </div>

                        {{-- Agama --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Agama <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="tersangka_agama"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                required
                            >
                                <option value="">-- Pilih Agama --</option>
                                <option value="islam" @selected($tersangkaAgama === 'islam')>Islam</option>
                                <option value="kristen" @selected($tersangkaAgama === 'kristen')>Kristen</option>
                                <option value="katolik" @selected($tersangkaAgama === 'katolik')>Katolik</option>
                                <option value="hindu" @selected($tersangkaAgama === 'hindu')>Hindu</option>
                                <option value="buddha" @selected($tersangkaAgama === 'buddha')>Buddha</option>
                                <option value="konghucu" @selected($tersangkaAgama === 'konghucu')>Konghucu</option>
                                <option value="lainnya" @selected($tersangkaAgama === 'lainnya')>Lainnya</option>
                            </select>
                        </div>

                        {{-- Pekerjaan --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Pekerjaan <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="tersangka_pekerjaan"
                                value="{{ $tersangkaPekerjaan }}"
                                placeholder="Contoh: Wiraswasta"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                required
                            >
                        </div>

                        {{-- Kewarganegaraan --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kewarganegaraan <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="tersangka_kewarganegaraan"
                                value="{{ $tersangkaKewarganegaraan }}"
                                placeholder="Contoh: WNI"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                required
                            >
                        </div>

                        {{-- Alamat Lengkap Tersangka (Pilih via Modal) --}}
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Alamat Lengkap Tersangka <span class="text-red-500">*</span>
                            </label>
                            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                                <div class="relative flex-1">
                                    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-gray-400 dark:text-gray-500">
                                        <i class="fi fi-rr-marker text-sm"></i>
                                    </div>
                                    <input
                                        type="text"
                                        readonly
                                        id="tersangka_alamat_ringkasan"
                                        :value="alamatRingkasan"
                                        placeholder="Belum diatur (klik untuk memilih alamat)"
                                        class="h-11 w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50/80 ps-10 pe-4 text-sm text-gray-700 select-none dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-300"
                                        @click="openAlamatModal()"
                                    >
                                </div>
                                <button
                                    type="button"
                                    @click="openAlamatModal()"
                                    class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border border-brand-300 bg-brand-50 px-4 text-sm font-medium text-brand-700 transition hover:bg-brand-100 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20"
                                >
                                    <i class="fi fi-rr-edit text-sm"></i>
                                    <span>Atur Alamat Tersangka</span>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Alamat KTP/domisili tersangka mencakup 38 Provinsi di seluruh Indonesia.
                            </p>

                            {{-- Hidden inputs form POST --}}
                            <input type="hidden" name="tersangka_provinsi_kode" :value="alamat.provinsi_kode">
                            <input type="hidden" name="tersangka_provinsi_nama" :value="alamat.provinsi_nama">
                            <input type="hidden" name="tersangka_kabupaten_kode" :value="alamat.kabupaten_kode">
                            <input type="hidden" name="tersangka_kabupaten_nama" :value="alamat.kabupaten_nama">
                            <input type="hidden" name="tersangka_kecamatan_kode" :value="alamat.kecamatan_kode">
                            <input type="hidden" name="tersangka_kecamatan_nama" :value="alamat.kecamatan_nama">
                            <input type="hidden" name="tersangka_desa_kode" :value="alamat.desa_kode">
                            <input type="hidden" name="tersangka_desa_nama" :value="alamat.desa_nama">
                            <input type="hidden" name="tersangka_rt" :value="alamat.rt">
                            <input type="hidden" name="tersangka_rw" :value="alamat.rw">
                            <input type="hidden" name="tersangka_alamat_detail" :value="alamat.alamat_detail">
                        </div>
                    </div>

                    {{-- Modal Pemilihan Alamat Tersangka --}}
                    @include('pages.perkara._modal-alamat-tersangka')
                </div>
            </div>

            {{-- 4. Card Pemilihan Pasal Berlapis (Komponen Persis PPNS Anggota, Poin 8) --}}
            <div
                x-data="{
                    pasalList: @js($pasalJsonList),
                    selectedPasalIds: @js($existingPasalIds),
                    addPasal(id) {
                        if (!id) return;
                        const numId = parseInt(id, 10);
                        if (!this.selectedPasalIds.includes(numId)) {
                            this.selectedPasalIds.push(numId);
                        }
                        this.$nextTick(() => {
                            const el = document.getElementById('pasal_select');
                            if (el) {
                                el.value = '';
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                    },
                    removePasal(id) {
                        this.selectedPasalIds = this.selectedPasalIds.filter(i => i !== id);
                    },
                    getAvailablePasal() {
                        return this.pasalList.filter(p => !this.selectedPasalIds.includes(p.id));
                    },
                    getSelectedPasalDetails() {
                        return this.selectedPasalIds
                            .map(id => this.pasalList.find(p => p.id === id))
                            .filter(Boolean);
                    }
                }"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-book-alt text-base text-brand-500"></i>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">4. Pasal Berlapis yang Disangkakan</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih satu atau lebih pasal pelanggaran yang disangkakan pada berkas perkara ini.</p>
                </div>

                <div class="p-4 sm:p-6 space-y-4">
                    {{-- Hidden inputs untuk pengiriman array pasal_ids[] --}}
                    <template x-for="id in selectedPasalIds" :key="'hidden-pasal-' + id">
                        <input type="hidden" name="pasal_ids[]" :value="id">
                    </template>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="pasal_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Pilih Pasal <span class="text-red-500">*</span>
                            </label>
                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedPasalIds.length + ' pasal dipilih'"></span>
                        </div>

                        {{-- Combo Box Dropdown (Searchable Select ala PPNS) --}}
                        <div class="relative">
                            <x-form.searchable-select
                                id="pasal_select"
                                name="pasal_select"
                                placeholder="+ Pilih dan tambah pasal yang disangkakan..."
                                search-placeholder="Ketik pasal atau isi undang-undang..."
                                :clearable="false"
                                @change="addPasal($event.target.value)"
                            >
                                <option value="" x-text="getAvailablePasal().length === 0 ? 'Semua pasal sudah terpilih' : '+ Pilih dan tambah pasal yang disangkakan...'"></option>
                                <template x-for="pasal in getAvailablePasal()" :key="pasal.id">
                                    <option :value="pasal.id" x-text="pasal.label"></option>
                                </template>
                            </x-form.searchable-select>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Pilih pasal dari daftar combo box untuk menambahkannya ke berkas perkara. Anda dapat memilih lebih dari satu pasal.</p>

                        @error('pasal_ids')
                            <p class="mt-1.5 text-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror

                        {{-- Kartu Detail Pasal Terpilih --}}
                        <div class="mt-4">
                            <div x-show="selectedPasalIds.length > 0" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <template x-for="pasal in getSelectedPasalDetails()" :key="pasal.id">
                                    <div class="relative flex items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50/40 p-3.5 transition dark:border-brand-500/20 dark:bg-brand-500/5">
                                        <div class="flex items-start gap-3 min-w-0">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                                <i class="fi fi-rr-book-alt text-base"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90" x-text="pasal.label"></p>
                                                <p class="line-clamp-2 text-xs text-gray-500 dark:text-gray-400" x-text="pasal.isi || '-'"></p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="removePasal(pasal.id)"
                                            class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 hover:bg-error-50 hover:text-error-600 transition dark:hover:bg-error-500/10 dark:hover:text-error-400"
                                            title="Hapus pasal"
                                        >
                                            <i class="fi fi-rr-cross-small text-base"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- State Kosong / Belum Ada Pasal --}}
                            <div
                                x-show="selectedPasalIds.length === 0"
                                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 py-6 text-center dark:border-gray-800"
                            >
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                    <i class="fi fi-rr-book-alt text-sm"></i>
                                </div>
                                <p class="mt-2 text-xs font-medium text-gray-500 dark:text-gray-400">Belum ada Pasal yang dipilih</p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Pilih dari combo box di atas untuk menambahkan pasal yang disangkakan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer 3 Tombol: Batal, Simpan Draft, Simpan & Lanjut Tahap Berikutnya --}}
            <div class="rounded-2xl border border-gray-200 bg-white px-4 py-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <i class="fi fi-rr-info me-1 text-brand-500"></i>
                        Gunakan <strong>Simpan Draft</strong> untuk menyimpan sementara, atau <strong>Simpan &amp; Lanjut Tahap Berikutnya</strong> untuk memfinalkan dan mengunci berkas perkara.
                    </p>

                    <div class="flex flex-wrap items-center justify-end gap-2.5">
                        {{-- 1. Batal --}}
                        <a
                            href="{{ route('sprindik.show', $perkara) }}"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                        >
                            Batal
                        </a>

                        {{-- 2. Simpan Draft --}}
                        <button
                            type="submit"
                            name="action_type"
                            value="draft"
                            formnovalidate
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-brand-300 bg-brand-50 px-4 text-sm font-medium text-brand-700 transition hover:bg-brand-100 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20"
                        >
                            <i class="fi fi-rr-disk text-sm"></i>
                            <span>Simpan Draft</span>
                        </button>

                        {{-- 3. Simpan & Lanjut Tahap Berikutnya --}}
                        <button
                            type="submit"
                            name="action_type"
                            value="lanjut"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white transition hover:bg-brand-700"
                        >
                            <i class="fi fi-rr-check-circle text-sm"></i>
                            <span>Simpan &amp; Lanjut Tahap Berikutnya</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
