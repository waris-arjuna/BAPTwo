@php
    $isDraft = $perkara->tahap_perkara_status === 'draft';
    $title = $isDraft ? 'Edit Draft Perkara & Berkas Perkara' : 'Buat Perkara & Berkas Perkara';

    $tkp = $isDraft ? $perkara->tkp : null;
    $tkpAlamat = $tkp?->alamat;
    $existingUtamaId = old('ppns_utama_id', $isDraft ? ($perkara->ppnsUtama->first()?->id ?? '') : '');
    $existingAnggotaIds = collect(old('ppns_anggota_ids', $isDraft ? $perkara->ppnsAnggota->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->filter(fn (int $id) => $id > 0 && $id !== (int) $existingUtamaId)
        ->values()
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

    $firstTersangka = $isDraft ? $perkara->tersangkas->first() : null;
    $firstBerkas = $isDraft ? ($firstTersangka?->berkasPerkara ?? $perkara->berkasPerkaras->first()) : null;
    $tersangkaAlamat = $firstTersangka?->alamat;

    $oldTersangka = old('tersangka.0', []);
    $tersangkaId = old('tersangka_id', $oldTersangka['id'] ?? $firstTersangka?->id);
    $tersangkaNama = old('tersangka_nama', $oldTersangka['nama'] ?? ($firstTersangka?->nama ?? ''));
    $tersangkaAlias = old('tersangka_alias', $oldTersangka['alias'] ?? ($firstTersangka?->alias ?? ''));
    $tersangkaBinBinti = old('tersangka_bin_binti', $oldTersangka['bin_binti'] ?? ($firstTersangka?->bin_binti ?? ''));
    $tersangkaNik = old('tersangka_nik', $oldTersangka['nik'] ?? ($firstTersangka?->nik ?? ''));
    $tersangkaTempatLahir = old('tersangka_tempat_lahir', $oldTersangka['tempat_lahir'] ?? ($firstTersangka?->tempat_lahir ?? ''));
    $rawTanggalLahir = old('tersangka_tanggal_lahir', $oldTersangka['tanggal_lahir'] ?? ($firstTersangka?->tanggal_lahir?->format('Y-m-d') ?? ''));
    $tersangkaTanggalLahir = $rawTanggalLahir instanceof \DateTimeInterface
        ? $rawTanggalLahir->format('Y-m-d')
        : (is_string($rawTanggalLahir) && $rawTanggalLahir !== '' ? substr(trim($rawTanggalLahir), 0, 10) : '');
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

    $tersangkaRingkasanParts = array_filter([
        $tersangkaAlamatDetail,
        ($tersangkaRt || $tersangkaRw) ? 'RT '.($tersangkaRt ?: '-').' / RW '.($tersangkaRw ?: '-') : null,
        $tersangkaDesaNama ? 'Desa/Kel. '.$tersangkaDesaNama : null,
        $tersangkaKecamatanNama ? 'Kec. '.$tersangkaKecamatanNama : null,
        $tersangkaKabupatenNama,
        $tersangkaProvinsiNama,
    ]);
    $initialTersangkaAlamatRingkasan = !empty($tersangkaRingkasanParts) ? implode(', ', $tersangkaRingkasanParts) : '';

    $existingPasalIds = collect(old('pasal_ids', $oldTersangka['pasal_ids'] ?? ($firstBerkas?->pasals->pluck('id')->all() ?? [])))
        ->map(fn ($id) => (int) $id)
        ->all();
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
                        Lengkapi data Tempat Kejadian Perkara (TKP), Tim Penyidik PPNS, Identitas Tersangka, dan Pasal yang Disangkakan.
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
                x-data="tkpWilayahForm({
                    provinsiKode: '63',
                    provinsiNama: 'Kalimantan Selatan',
                    kabupatenKode: @js(old('tkp_kabupaten_kode', $tkpAlamat?->kabupaten_kode ?? '')),
                    kabupatenNama: @js(old('tkp_kabupaten_nama', $tkpAlamat?->kabupaten_nama ?? '')),
                    kecamatanKode: @js(old('tkp_kecamatan_kode', $tkpAlamat?->kecamatan_kode ?? '')),
                    kecamatanNama: @js(old('tkp_kecamatan_nama', $tkpAlamat?->kecamatan_nama ?? '')),
                    desaKode: @js(old('tkp_desa_kode', $tkpAlamat?->desa_kode ?? '')),
                    desaNama: @js(old('tkp_desa_nama', $tkpAlamat?->desa_nama ?? ''))
                })"
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
                            :value="old('tkp_kabupaten_kode', $tkpAlamat?->kabupaten_kode ?? '')"
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
                            :value="old('tkp_kecamatan_kode', $tkpAlamat?->kecamatan_kode ?? '')"
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
                        <div x-show="errorKecamatan" x-cloak class="mt-1.5 flex items-center gap-1.5 text-xs text-error-600 dark:text-error-400">
                            <i class="fi fi-rr-info shrink-0"></i>
                            <span x-text="errorKecamatan"></span>
                            <button type="button" @click="retryLoadKecamatan()" class="font-medium underline hover:text-error-700 dark:hover:text-error-300">Coba lagi</button>
                        </div>
                    </div>

                    {{-- Desa / Kelurahan --}}
                    <div>
                        <x-form.searchable-select
                            name="tkp_desa_kode"
                            id="tkp_desa_kode"
                            label="Desa / Kelurahan"
                            :value="old('tkp_desa_kode', $tkpAlamat?->desa_kode ?? '')"
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
                        <div x-show="errorDesa" x-cloak class="mt-1.5 flex items-center gap-1.5 text-xs text-error-600 dark:text-error-400">
                            <i class="fi fi-rr-info shrink-0"></i>
                            <span x-text="errorDesa"></span>
                            <button type="button" @click="retryLoadDesa()" class="font-medium underline hover:text-error-700 dark:hover:text-error-300">Coba lagi</button>
                        </div>
                    </div>

                    {{-- RT / RW --}}
                    <div class="grid grid-cols-2 gap-4 sm:col-span-2 sm:max-w-xs">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">RT</label>
                            <input
                                type="number"
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
                                type="number"
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
                            <option value="retailer" @selected(old('tkp_klasifikasi', $tkp?->klasifikasi) === 'retailer')>Retailer</option>
                            <option value="distributor" @selected(old('tkp_klasifikasi', $tkp?->klasifikasi) === 'distributor')>Distributor</option>
                            <option value="produksi" @selected(old('tkp_klasifikasi', $tkp?->klasifikasi) === 'produksi')>Produksi</option>
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
                                    @checked(old('tkp_legalitas', $tkp?->legalitas) === 'legal')
                                    class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                                >
                                <span>Legal</span>
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input
                                    type="radio"
                                    name="tkp_legalitas"
                                    value="ilegal"
                                    @checked(old('tkp_legalitas', $tkp?->legalitas) === 'ilegal')
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
                                    @checked(old('tkp_modus', $tkp?->modus) === 'online')
                                    class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700"
                                >
                                <span>Online</span>
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input
                                    type="radio"
                                    name="tkp_modus"
                                    value="offline"
                                    @checked(old('tkp_modus', $tkp?->modus) === 'offline')
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
                x-data="ppnsTimForm({
                    ppnsList: @js($ppnsJsonList),
                    selectedUtamaId: @js((int) $existingUtamaId ?: null),
                    selectedAnggotaIds: @js($existingAnggotaIds)
                })"
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
                            @change="onUtamaChange($event)"
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
                                @change="addAnggota($event)"
                            >
                                <option value="" x-text="getAvailableAnggota().length === 0 ? 'Semua PPNS sudah terpilih' : '+ Pilih dan tambah anggota penyidik...'"></option>
                                <template x-for="ppns in getAvailableAnggota()" :key="ppns.id">
                                    <option :value="ppns.id" x-text="ppns.label || ppns.nama"></option>
                                </template>
                            </x-form.searchable-select>
                        </div>

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

            {{-- 3. Card Data Tersangka & Berkas Perkara (1 Perkara = 1 Berkas Perkara = 1 Tersangka) --}}
            <div
                x-data="tersangkaAlamatModalForm({
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
                        alamat_detail: @js($tersangkaAlamatDetail)
                    },
                    provinsiUrl: '/wilayah/provinsi',
                    kabupatenUrlBase: '/wilayah/kabupaten',
                    kecamatanUrlBase: '/wilayah/kecamatan',
                    desaUrlBase: '/wilayah/desa'
                })"
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
                                NIK (16 Digit Angka) <span class="text-red-500">*</span>
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
                                :value="$tersangkaTanggalLahir"
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
                                        value="{{ $initialTersangkaAlamatRingkasan }}"
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

            {{-- 4. Card Pemilihan Pasal yang Disangkakan (Komponen Persis PPNS Anggota, Poin 8) --}}
            <div
                x-data="pasalPerkaraForm({
                    pasalList: @js($pasalJsonList),
                    selectedPasalIds: @js($existingPasalIds)
                })"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-book-alt text-base text-brand-500"></i>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">4. Pasal yang Disangkakan</h2>
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

            {{-- 5. Card Penuntut Umum  --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex flex-col gap-1 border-b border-gray-100 pb-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-scale text-lg text-brand-500"></i>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90" >5. Penuntut Umum (JPU)</h2>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Pilih instansi Kejaksaan mitra dan masukkan data Jaksa Penuntut Umum yang ditunjuk untuk koordinasi penanganan perkara.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1 self-start rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">
                        Opsional
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    {{-- Combobox Instansi Kejaksaan --}}
                    <div>
                        <x-form.searchable-select
                            id="penuntut_umum_instansi_id"
                            name="penuntut_umum_instansi_id"
                            label="Instansi Kejaksaan"
                            placeholder="-- Pilih Instansi Kejaksaan --"
                            search-placeholder="Ketik nama kejaksaan..."
                            :value="old('penuntut_umum_instansi_id', $isDraft ? $perkara->penuntut_umum_instansi_id : '')"
                        >
                            <option value="">-- Pilih Instansi Kejaksaan --</option>
                            @foreach ($kejaksaanList as $kj)
                                <option value="{{ $kj->id }}" @selected((string) old('penuntut_umum_instansi_id', $isDraft ? $perkara->penuntut_umum_instansi_id : '') === (string) $kj->id)>
                                    {{ $kj->nama }}
                                </option>
                            @endforeach
                        </x-form.searchable-select>
                    </div>

                    {{-- Nama Jaksa Penuntut Umum --}}
                    <div>
                        <label for="penuntut_umum_nama" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Jaksa Penuntut Umum
                        </label>
                        <input
                            type="text"
                            id="penuntut_umum_nama"
                            name="penuntut_umum_nama"
                            value="{{ old('penuntut_umum_nama', $isDraft ? $perkara->penuntut_umum_nama : '') }}"
                            placeholder="Contoh: Budi Gunawan, S.H., M.H."
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                    </div>

                    {{-- NIP Jaksa --}}
                    <div>
                        <label for="penuntut_umum_nip" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            NIP Jaksa Penuntut (18 Digit)
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            id="penuntut_umum_nip"
                            name="penuntut_umum_nip"
                            value="{{ old('penuntut_umum_nip', $isDraft ? $perkara->penuntut_umum_nip : '') }}"
                            maxlength="18"
                            pattern="[0-9]{18}"
                            placeholder="Contoh: 198205122008011002"
                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 18)"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
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
