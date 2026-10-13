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
        'nip' => $u->nip ?: '-',
        'pangkat' => $u->pangkat ?? '-',
    ])->values()->all();

    $initialTersangka = old('tersangka');
    if (! is_array($initialTersangka) || empty($initialTersangka)) {
        if ($perkara->tersangkas->isNotEmpty()) {
            $initialTersangka = $perkara->tersangkas->map(function ($tersangka) {
                return [
                    'id' => $tersangka->id,
                    'nomor_berkas' => $tersangka->berkasPerkara?->nomor_berkas ?? '',
                    'nama' => $tersangka->nama ?? '',
                    'alias' => $tersangka->alias ?? '',
                    'bin_binti' => $tersangka->bin_binti ?? '',
                    'nik' => $tersangka->nik ?? '',
                    'tempat_lahir' => $tersangka->tempat_lahir ?? '',
                    'tanggal_lahir' => $tersangka->tanggal_lahir?->format('Y-m-d') ?? '',
                    'jenis_kelamin' => $tersangka->jenis_kelamin ?? '',
                    'agama' => $tersangka->agama ?? '',
                    'pekerjaan' => $tersangka->pekerjaan ?? '',
                    'kewarganegaraan' => $tersangka->kewarganegaraan ?? 'WNI',
                    'pasal_ids' => $tersangka->berkasPerkara?->pasals->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [],
                ];
            })->values()->all();
        } else {
            $initialTersangka = [
                [
                    'id' => null,
                    'nomor_berkas' => '',
                    'nama' => '',
                    'alias' => '',
                    'bin_binti' => '',
                    'nik' => '',
                    'tempat_lahir' => '',
                    'tanggal_lahir' => '',
                    'jenis_kelamin' => '',
                    'agama' => '',
                    'pekerjaan' => '',
                    'kewarganegaraan' => 'WNI',
                    'pasal_ids' => [],
                ],
            ];
        }
    }
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
                        Lengkapi data TKP, Tim Penyidik PPNS, Tersangka (1 Tersangka = 1 Berkas Perkara), Nomor Berkas Manual, dan Pasal Berlapis.
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

        <form action="{{ route('perkara.update', $perkara) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. Card Informasi TKP (Tempat Kejadian Perkara) --}}
            <div
                x-data="{
                    provinsiList: [],
                    kabupatenList: [],
                    kecamatanList: [],
                    desaList: [],
                    provinsiKode: @js(old('tkp_provinsi_kode', $tkpAlamat?->provinsi_kode ?? '')),
                    provinsiNama: @js(old('tkp_provinsi_nama', $tkpAlamat?->provinsi_nama ?? '')),
                    kabupatenKode: @js(old('tkp_kabupaten_kode', $tkpAlamat?->kabupaten_kode ?? '')),
                    kabupatenNama: @js(old('tkp_kabupaten_nama', $tkpAlamat?->kabupaten_nama ?? '')),
                    kecamatanKode: @js(old('tkp_kecamatan_kode', $tkpAlamat?->kecamatan_kode ?? '')),
                    kecamatanNama: @js(old('tkp_kecamatan_nama', $tkpAlamat?->kecamatan_nama ?? '')),
                    desaKode: @js(old('tkp_desa_kode', $tkpAlamat?->desa_kode ?? '')),
                    desaNama: @js(old('tkp_desa_nama', $tkpAlamat?->desa_nama ?? '')),
                    async init() {
                        await this.loadProvinsi();
                        if (this.provinsiKode) await this.loadKabupaten(false);
                        if (this.kabupatenKode) await this.loadKecamatan(false);
                        if (this.kecamatanKode) await this.loadDesa(false);
                    },
                    async loadProvinsi() {
                        try {
                            const res = await fetch('{{ route('wilayah.provinsi') }}');
                            const json = await res.json();
                            this.provinsiList = json.data || [];
                        } catch (e) {}
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
                        const found = this.provinsiList.find(p => p.code === this.provinsiKode || p.kode === this.provinsiKode);
                        if (found) this.provinsiNama = found.name || found.nama;
                        if (!this.provinsiKode) {
                            this.kabupatenList = [];
                            return;
                        }
                        try {
                            const res = await fetch(`/wilayah/kabupaten/${this.provinsiKode}`);
                            const json = await res.json();
                            this.kabupatenList = json.data || [];
                        } catch (e) {}
                    },
                    async loadKecamatan(reset = true) {
                        if (reset) {
                            this.kecamatanKode = '';
                            this.kecamatanNama = '';
                            this.desaKode = '';
                            this.desaNama = '';
                            this.desaList = [];
                        }
                        const found = this.kabupatenList.find(k => k.code === this.kabupatenKode || k.kode === this.kabupatenKode);
                        if (found) this.kabupatenNama = found.name || found.nama;
                        if (!this.kabupatenKode) {
                            this.kecamatanList = [];
                            return;
                        }
                        try {
                            const res = await fetch(`/wilayah/kecamatan/${this.kabupatenKode}`);
                            const json = await res.json();
                            this.kecamatanList = json.data || [];
                        } catch (e) {}
                    },
                    async loadDesa(reset = true) {
                        if (reset) {
                            this.desaKode = '';
                            this.desaNama = '';
                        }
                        const found = this.kecamatanList.find(k => k.code === this.kecamatanKode || k.kode === this.kecamatanKode);
                        if (found) this.kecamatanNama = found.name || found.nama;
                        if (!this.kecamatanKode) {
                            this.desaList = [];
                            return;
                        }
                        try {
                            const res = await fetch(`/wilayah/desa/${this.kecamatanKode}`);
                            const json = await res.json();
                            this.desaList = json.data || [];
                        } catch (e) {}
                    },
                    onDesaChange() {
                        const found = this.desaList.find(d => d.code === this.desaKode || d.kode === this.desaKode);
                        if (found) this.desaNama = found.name || found.nama;
                    }
                }"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-marker text-base text-brand-500"></i>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">1. Informasi TKP (Tempat Kejadian Perkara)</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lokasi kejadian, klasifikasi sarana, legalitas, dan modus pelanggaran.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 px-4 py-5 sm:grid-cols-2 sm:px-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Provinsi <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tkp_provinsi_kode"
                            x-model="provinsiKode"
                            @change="loadKabupaten(true)"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="">-- Pilih Provinsi --</option>
                            <template x-for="item in provinsiList" :key="item.code || item.kode">
                                <option :value="item.code || item.kode" :selected="(item.code || item.kode) === provinsiKode" x-text="item.name || item.nama"></option>
                            </template>
                        </select>
                        <input type="hidden" name="tkp_provinsi_nama" :value="provinsiNama">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Kabupaten / Kota <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tkp_kabupaten_kode"
                            x-model="kabupatenKode"
                            @change="loadKecamatan(true)"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="">-- Pilih Kabupaten/Kota --</option>
                            <template x-for="item in kabupatenList" :key="item.code || item.kode">
                                <option :value="item.code || item.kode" :selected="(item.code || item.kode) === kabupatenKode" x-text="item.name || item.nama"></option>
                            </template>
                        </select>
                        <input type="hidden" name="tkp_kabupaten_nama" :value="kabupatenNama">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Kecamatan <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tkp_kecamatan_kode"
                            x-model="kecamatanKode"
                            @change="loadDesa(true)"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="">-- Pilih Kecamatan --</option>
                            <template x-for="item in kecamatanList" :key="item.code || item.kode">
                                <option :value="item.code || item.kode" :selected="(item.code || item.kode) === kecamatanKode" x-text="item.name || item.nama"></option>
                            </template>
                        </select>
                        <input type="hidden" name="tkp_kecamatan_nama" :value="kecamatanNama">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Desa / Kelurahan <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tkp_desa_kode"
                            x-model="desaKode"
                            @change="onDesaChange()"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="">-- Pilih Desa/Kelurahan --</option>
                            <template x-for="item in desaList" :key="item.code || item.kode">
                                <option :value="item.code || item.kode" :selected="(item.code || item.kode) === desaKode" x-text="item.name || item.nama"></option>
                            </template>
                        </select>
                        <input type="hidden" name="tkp_desa_nama" :value="desaNama">
                    </div>

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

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Legalitas Sarana <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tkp_legalitas"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="">-- Pilih Legalitas --</option>
                            <option value="legal" @selected(old('tkp_legalitas', $perkara->tkp?->legalitas) === 'legal')>Legal</option>
                            <option value="ilegal" @selected(old('tkp_legalitas', $perkara->tkp?->legalitas) === 'ilegal')>Ilegal</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Modus Operandi <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tkp_modus"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="">-- Pilih Modus Operandi --</option>
                            <option value="online" @selected(old('tkp_modus', $perkara->tkp?->modus) === 'online')>Online</option>
                            <option value="offline" @selected(old('tkp_modus', $perkara->tkp?->modus) === 'offline')>Offline</option>
                        </select>
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
                        if (this.$refs.anggotaSelect) {
                            this.$refs.anggotaSelect.value = '';
                        }
                        this.$nextTick(() => {
                            if (this.$refs.anggotaSelect) {
                                this.$refs.anggotaSelect.value = '';
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

                    {{-- PPNS Utama --}}
                    <div>
                        <label for="ppns_utama_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Penyidik PPNS Utama <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="ppns_utama_id"
                            name="ppns_utama_id"
                            @change="onUtamaChange($event.target.value)"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="">-- Pilih PPNS Utama --</option>
                            @foreach ($ppnsUsers as $ppns)
                                <option value="{{ $ppns->id }}" @selected((int) $existingUtamaId === $ppns->id)>
                                    {{ $ppns->nama }} ({{ $ppns->nip ?: $ppns->role }}) — {{ $ppns->pangkat ?? '-' }}
                                </option>
                            @endforeach
                        </select>
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

                        {{-- Combo Box Dropdown --}}
                        <div class="relative">
                            <select
                                id="ppns_anggota_select"
                                x-ref="anggotaSelect"
                                @change="addAnggota($event.target.value)"
                                :disabled="getAvailableAnggota().length === 0"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500"
                            >
                                <option value="" x-text="getAvailableAnggota().length === 0 ? 'Semua PPNS sudah terpilih' : '+ Pilih dan tambah anggota penyidik...'"></option>
                                <template x-for="ppns in getAvailableAnggota()" :key="ppns.id">
                                    <option :value="ppns.id" x-text="ppns.nama + (ppns.nip && ppns.nip !== '-' ? ' (NIP: ' + ppns.nip + ')' : '') + (ppns.pangkat && ppns.pangkat !== '-' ? ' — ' + ppns.pangkat : '')"></option>
                                </template>
                            </select>
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
                                                <p class="truncate text-xs text-gray-500 dark:text-gray-400" x-text="'NIP: ' + ppns.nip"></p>
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

            {{-- 3. Card Tersangka & Pembentukan Berkas Perkara (1 Tersangka = 1 Berkas Perkara + Pasal Berlapis) --}}
            <div
                x-data="{
                    tersangkas: @js($initialTersangka),
                    addTersangka() {
                        this.tersangkas.push({
                            id: null,
                            nomor_berkas: '',
                            nama: '',
                            alias: '',
                            bin_binti: '',
                            nik: '',
                            tempat_lahir: '',
                            tanggal_lahir: '',
                            jenis_kelamin: '',
                            agama: '',
                            pekerjaan: '',
                            kewarganegaraan: 'WNI',
                            pasal_ids: [],
                        });
                    },
                    removeTersangka(idx) {
                        if (this.tersangkas.length > 1) {
                            this.tersangkas.splice(idx, 1);
                        }
                    }
                }"
                class="space-y-4"
            >
                <div class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div>
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-user text-base text-brand-500"></i>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                3. Data Tersangka &amp; Berkas Perkara (<span x-text="tersangkas.length"></span> Berkas)
                            </h2>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Aturan domain BAPTwo: <strong>1 Tersangka = 1 Berkas Perkara Terpisah</strong> dengan Nomor Berkas manual dan Pasal Berlapis masing-masing.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="addTersangka()"
                        class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600"
                    >
                        <i class="fi fi-rr-plus-small text-base"></i>
                        <span>Tambah Tersangka (Berkas Baru)</span>
                    </button>
                </div>

                <template x-for="(item, index) in tersangkas" :key="index">
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400" x-text="index + 1"></span>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                        Tersangka #<span x-text="index + 1"></span> — Berkas Perkara #<span x-text="index + 1"></span>
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Data identitas tersangka, nomor berkas manual, dan pasal berlapis.</p>
                                </div>
                            </div>

                            <button
                                type="button"
                                x-show="tersangkas.length > 1"
                                @click="removeTersangka(index)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-error-200 bg-error-50 px-3 py-1.5 text-xs font-medium text-error-600 transition hover:bg-error-100 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-400"
                            >
                                <i class="fi fi-rr-trash text-xs"></i>
                                <span>Hapus Tersangka</span>
                            </button>
                        </div>

                        <div class="space-y-5 p-4 sm:p-6">
                            <input type="hidden" :name="`tersangka[${index}][id]`" :value="item.id || ''">

                            {{-- Nomor Berkas Perkara Manual (PRD F-33) --}}
                            <div class="rounded-xl border border-brand-100 bg-brand-50/40 p-4 dark:border-brand-500/20 dark:bg-brand-500/5">
                                <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white/90">
                                    Nomor Berkas Perkara (Input Manual Bebas Format) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    :name="`tersangka[${index}][nomor_berkas]`"
                                    x-model="item.nomor_berkas"
                                    placeholder="Contoh: BP/01/X/2026/PPNS-BPOM atau penomoran bebas sesuai dokumen"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                >
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Sesuai aturan PRD F-33, nomor berkas perkara diinput secara manual tanpa pembatasan pola format.
                                </p>
                            </div>

                            {{-- Identitas Tersangka --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Nama Lengkap Tersangka <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        :name="`tersangka[${index}][nama]`"
                                        x-model="item.nama"
                                        placeholder="Nama lengkap tersangka"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Alias / Panggilan
                                    </label>
                                    <input
                                        type="text"
                                        :name="`tersangka[${index}][alias]`"
                                        x-model="item.alias"
                                        placeholder="Contoh: Alias Budi"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Bin / Binti
                                    </label>
                                    <input
                                        type="text"
                                        :name="`tersangka[${index}][bin_binti]`"
                                        x-model="item.bin_binti"
                                        placeholder="Nama orang tua (Bin/Binti)"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        NIK (16 Digit)
                                    </label>
                                    <input
                                        type="text"
                                        maxlength="16"
                                        :name="`tersangka[${index}][nik]`"
                                        x-model="item.nik"
                                        placeholder="6371xxxxxxxxxxxx"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Jenis Kelamin <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        :name="`tersangka[${index}][jenis_kelamin]`"
                                        x-model="item.jenis_kelamin"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    >
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="laki-laki">Laki-laki</option>
                                        <option value="perempuan">Perempuan</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tempat Lahir
                                    </label>
                                    <input
                                        type="text"
                                        :name="`tersangka[${index}][tempat_lahir]`"
                                        x-model="item.tempat_lahir"
                                        placeholder="Contoh: Banjarmasin"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Lahir
                                    </label>
                                    <input
                                        type="date"
                                        :name="`tersangka[${index}][tanggal_lahir]`"
                                        x-model="item.tanggal_lahir"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Agama
                                    </label>
                                    <select
                                        :name="`tersangka[${index}][agama]`"
                                        x-model="item.agama"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    >
                                        <option value="">-- Pilih Agama --</option>
                                        <option value="islam">Islam</option>
                                        <option value="kristen">Kristen</option>
                                        <option value="katolik">Katolik</option>
                                        <option value="hindu">Hindu</option>
                                        <option value="buddha">Buddha</option>
                                        <option value="konghucu">Konghucu</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Pekerjaan
                                    </label>
                                    <input
                                        type="text"
                                        :name="`tersangka[${index}][pekerjaan]`"
                                        x-model="item.pekerjaan"
                                        placeholder="Contoh: Wiraswasta"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white/90"
                                    >
                                </div>
                            </div>

                            {{-- Pasal Berlapis per Berkas Perkara (PRD F-04 / T-022) --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Pasal Berlapis yang Disangkakan pada Berkas Ini <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                    @foreach ($pasalList as $pasal)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-3 text-sm transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.02]">
                                            <input
                                                type="checkbox"
                                                :name="`tersangka[${index}][pasal_ids][]`"
                                                value="{{ $pasal->id }}"
                                                :checked="(item.pasal_ids || []).includes({{ $pasal->id }})"
                                                @change="
                                                    if ($event.target.checked) {
                                                        if (!item.pasal_ids.includes({{ $pasal->id }})) item.pasal_ids.push({{ $pasal->id }});
                                                    } else {
                                                        item.pasal_ids = item.pasal_ids.filter(id => id !== {{ $pasal->id }});
                                                    }
                                                "
                                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                                            >
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-800 dark:text-white/90">{{ $pasal->label() }}</p>
                                                @if ($pasal->isi)
                                                    <p class="line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $pasal->isi }}</p>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer 3 Tombol Wajib: Batal, Simpan Draft, Simpan & Lanjut Tahap Berikutnya --}}
            <div class="rounded-2xl border border-gray-200 bg-white px-4 py-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <i class="fi fi-rr-info me-1 text-brand-500"></i>
                        Gunakan <strong>Simpan Draft</strong> untuk menyimpan sementara (masih dapat diedit), atau <strong>Simpan &amp; Lanjut Tahap Berikutnya</strong> untuk memfinalkan Berkas Perkara dan mengunci tahap ini.
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
