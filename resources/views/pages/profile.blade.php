@extends('layouts.app')

@php
    /** @var \App\Models\User $currentUser */
    $currentUser = $user ?? auth()->user()?->load(['jabatan', 'bidang', 'panggol', 'alamat']);
    $alamat = $currentUser?->alamat;

    $hasAccountErrors = $errors->hasAny(['email', 'current_password', 'password']);
    $hasPersonalErrors = $errors->hasAny([
        'no_telp',
        'provinsi_kode',
        'provinsi_nama',
        'kabupaten_kode',
        'kabupaten_nama',
        'kecamatan_kode',
        'kecamatan_nama',
        'desa_kode',
        'desa_nama',
        'rt',
        'rw',
        'alamat_detail',
    ]);
@endphp

@section('content')
    <div x-data="profilePage({
        openAccountModal: @js($hasAccountErrors),
        openPersonalModal: @js($hasPersonalErrors),
        initialWilayah: {
            provinsi_kode: @js(old('provinsi_kode', $alamat?->provinsi_kode ?? '')),
            provinsi_nama: @js(old('provinsi_nama', $alamat?->provinsi_nama ?? '')),
            kabupaten_kode: @js(old('kabupaten_kode', $alamat?->kabupaten_kode ?? '')),
            kabupaten_nama: @js(old('kabupaten_nama', $alamat?->kabupaten_nama ?? '')),
            kecamatan_kode: @js(old('kecamatan_kode', $alamat?->kecamatan_kode ?? '')),
            kecamatan_nama: @js(old('kecamatan_nama', $alamat?->kecamatan_nama ?? '')),
            desa_kode: @js(old('desa_kode', $alamat?->desa_kode ?? '')),
            desa_nama: @js(old('desa_nama', $alamat?->desa_nama ?? '')),
        }
    })" @keydown.escape.window="closeModals()">
        <x-common.page-breadcrumb :pageTitle="__('Profil Saya')" />

        {{-- Notifikasi Error Validasi Server --}}
        @if ($errors->any())
            <div x-data="{ show: true }" x-show="show" x-cloak
                class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-theme-sm text-error-700 shadow-theme-xs dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"
                role="alert">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-error-100 text-error-600 dark:bg-error-500/20 dark:text-error-400">
                            <i class="fi fi-rr-exclamation text-xs" aria-hidden="true"></i>
                        </span>
                        <div>
                            <p class="font-semibold">{{ __('Terdapat kesalahan pada input Anda:') }}</p>
                            <ul class="mt-1 list-disc ps-5 text-theme-xs space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" @click="show = false" class="text-error-600 hover:text-error-800 dark:text-error-400">
                        <i class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        @endif

        {{-- Notifikasi Error Validasi Client (Foto) --}}
        <div x-show="clientError" x-cloak
            class="mb-6 flex items-center justify-between gap-4 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-theme-sm text-error-700 shadow-theme-xs dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"
            role="alert">
            <span class="font-medium" x-text="clientError"></span>
            <button type="button" @click="clientError = ''" class="text-error-600 hover:text-error-800 dark:text-error-400">
                <i class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- A1. CARD FOTO PROFIL --}}
            <div class="flex h-full flex-col items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 text-center lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex w-full flex-1 flex-col items-center justify-center gap-4">
                    <div
                        @click="isPhotoModalOpen = true"
                        class="group relative mx-auto h-40 w-40 shrink-0 cursor-pointer overflow-hidden rounded-full border-2 border-gray-200 shadow-theme-sm transition hover:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:border-gray-700 dark:hover:border-brand-500"
                        role="button"
                        tabindex="0"
                        @keydown.enter.prevent="isPhotoModalOpen = true"
                        @keydown.space.prevent="isPhotoModalOpen = true"
                        aria-label="{{ __('Lihat Foto Profil') }}"
                        title="{{ __('Klik untuk melihat foto profil') }}"
                    >
                        <img src="{{ $currentUser?->fotoProfilUrl() }}" alt="{{ $currentUser?->nama }}"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105" width="96" height="96" />
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-950/40 opacity-0 transition duration-300 group-hover:opacity-100">
                            <i class="fi fi-rr-search text-xl text-white" aria-hidden="true"></i>
                            <span class="mt-1 text-theme-xs font-medium text-white">{{ __('Lihat Foto') }}</span>
                        </div>
                    </div>
                    <div class="min-w-0 max-w-full text-center">
                        <h3 class="truncate text-title-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $currentUser?->nama ?? '-' }}
                        </h3>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                            {{ $currentUser?->jabatan?->nama_jabatan ?? '-' }} · {{ $currentUser?->bidang?->nama_bidang ?? '-' }}
                        </p>
                        <p class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">
                            {{ __('Format JPG, JPEG, PNG, atau WEBP (Maks. 2 MB)') }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex w-full justify-center border-t border-gray-100 pt-4 dark:border-gray-800">
                    <form x-ref="photoForm" method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
                        @csrf
                        <input x-ref="photoInput" type="file" name="foto_profil" id="foto_profil_input"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="hidden"
                            @change="handlePhotoChange($event)" />
                        <button type="button" @click="$refs.photoInput.click()"
                            class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.05]">
                            <i class="fi fi-rr-camera text-sm" aria-hidden="true"></i>
                            {{ __('Ganti Foto Profil') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- A2. CARD DATA DIRI --}}
            <div class="h-full rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5 flex flex-col justify-between gap-4 border-b border-gray-100 pb-4 sm:flex-row sm:items-center dark:border-gray-800">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ __('Data Diri') }}
                    </h4>
                    <button type="button" @click="openPersonalEditModal()"
                        class="shadow-theme-xs inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.05]">
                        <i class="fi fi-rr-edit text-sm" aria-hidden="true"></i>
                        {{ __('Edit No. Telp & Alamat') }}
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Nama Lengkap') }}</p>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $currentUser?->nama ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Tempat Lahir') }}</p>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $currentUser?->tempat_lahir ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Tanggal Lahir') }}</p>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $currentUser?->tanggal_lahir ? $currentUser->tanggal_lahir->translatedFormat('d F Y') : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Jenis Kelamin') }}</p>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $currentUser?->jenisKelaminLabel() ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Status Pernikahan') }}</p>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $currentUser?->statusPernikahanLabel() ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center gap-1.5">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('No. Telp / WA') }}</p>
                            <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ __('Dapat Diedit') }}
                            </span>
                        </div>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $currentUser?->no_telp ?? '-' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <div class="mb-1 flex items-center gap-1.5">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Alamat Lengkap') }}</p>
                            <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ __('Dapat Diedit') }}
                            </span>
                        </div>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $alamat ? $alamat->formatted() : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- B1. CARD INFORMASI AKUN --}}
            <div class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5 flex flex-col justify-between gap-4 border-b border-gray-100 pb-4 sm:flex-row sm:items-center dark:border-gray-800">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ __('Informasi Akun') }}
                    </h4>
                    <button type="button" @click="isAccountModalOpen = true"
                        class="shadow-theme-xs inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white transition hover:bg-brand-600">
                        <i class="fi fi-rr-pencil text-sm" aria-hidden="true"></i>
                        {{ __('Update Email / Password') }}
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">
                            {{ __('Email') }}
                        </p>
                        <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $currentUser?->email ?? '-' }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">
                            {{ __('Password') }}
                        </p>
                        <p class="text-theme-sm font-semibold tracking-widest text-gray-800 dark:text-white/90">
                            ••••••••••••
                        </p>
                    </div>
                </div>
            </div>

            {{-- B2. CARD INFORMASI KEPEGAWAIAN (READ-ONLY) --}}
            <div class="h-full rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5 flex flex-col justify-between gap-2 border-b border-gray-100 pb-4 sm:flex-row sm:items-center dark:border-gray-800">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ __('Informasi Kepegawaian') }}
                    </h4>
                    <span class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-theme-xs font-medium text-gray-600 sm:self-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <i class="fi fi-rr-lock text-xs" aria-hidden="true"></i>
                        {{ __('Hanya Baca') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('NIP') }}</p>
                        <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $currentUser?->nip ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Jabatan') }}</p>
                        <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $currentUser?->jabatan?->nama_jabatan ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Bidang') }}</p>
                        <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $currentUser?->bidang?->nama_bidang ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pangkat dan Golongan') }}</p>
                        <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $currentUser?->panggolLabel() ?? '-' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Hak Akses') }}</p>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            {{ $currentUser?->role?->label() ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL 1: UPDATE EMAIL / PASSWORD --}}
        @include('pages.profile._modal-update-akun')

        {{-- MODAL 2: EDIT DATA DIRI (HANYA NO.TELP & ALAMAT) --}}
        @include('pages.profile._modal-edit-profil')

        {{-- MODAL 3: PRATINJAU FOTO PROFIL --}}
        @include('pages.profile._modal-foto-profil')
    </div>

    @push('scripts')
        <script>
            function profilePage(config) {
                return {
                    isAccountModalOpen: config.openAccountModal || false,
                    isPersonalModalOpen: config.openPersonalModal || false,
                    isPhotoModalOpen: false,
                    newPassword: '',
                    clientError: '',
                    wilayahLoaded: false,
                    isInitializingWilayah: false,
                    wilayah: {
                        provinsi_kode: config.initialWilayah.provinsi_kode || '',
                        provinsi_nama: config.initialWilayah.provinsi_nama || '',
                        kabupaten_kode: config.initialWilayah.kabupaten_kode || '',
                        kabupaten_nama: config.initialWilayah.kabupaten_nama || '',
                        kecamatan_kode: config.initialWilayah.kecamatan_kode || '',
                        kecamatan_nama: config.initialWilayah.kecamatan_nama || '',
                        desa_kode: config.initialWilayah.desa_kode || '',
                        desa_nama: config.initialWilayah.desa_nama || '',
                    },
                    listProvinsi: [],
                    listKabupaten: [],
                    listKecamatan: [],
                    listDesa: [],

                    kabupatenAbortController: null,
                    kecamatanAbortController: null,
                    desaAbortController: null,

                    init() {
                        if (this.isPersonalModalOpen) {
                            this.loadInitialWilayah();
                        }
                    },

                    closeModals() {
                        this.isAccountModalOpen = false;
                        this.isPersonalModalOpen = false;
                        this.isPhotoModalOpen = false;
                    },

                    openPersonalEditModal() {
                        this.isPersonalModalOpen = true;
                        if (!this.wilayahLoaded) {
                            this.loadInitialWilayah();
                        }
                    },

                    handlePhotoChange(event) {
                        this.clientError = '';
                        const file = event.target.files[0];
                        if (!file) {
                            return;
                        }

                        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                        if (!allowedTypes.includes(file.type)) {
                            this.clientError = '{{ __('Format foto profil harus JPG, JPEG, PNG, atau WEBP.') }}';
                            event.target.value = '';
                            return;
                        }

                        if (file.size > 2 * 1024 * 1024) {
                            this.clientError = '{{ __('Ukuran foto profil maksimal 2 MB.') }}';
                            event.target.value = '';
                            return;
                        }

                        this.$refs.photoForm.submit();
                    },

                    async loadInitialWilayah() {
                        this.isInitializingWilayah = true;
                        this.wilayahLoaded = true;
                        try {
                            await this.fetchProvinsi();

                            if (this.wilayah.provinsi_kode) {
                                await this.fetchKabupaten(this.wilayah.provinsi_kode, false);
                            }
                            if (this.wilayah.kabupaten_kode) {
                                await this.fetchKecamatan(this.wilayah.kabupaten_kode, false);
                            }
                            if (this.wilayah.kecamatan_kode) {
                                await this.fetchDesa(this.wilayah.kecamatan_kode, false);
                            }
                        } finally {
                            this.$nextTick(() => {
                                this.isInitializingWilayah = false;
                            });
                        }
                    },

                    async fetchProvinsi() {
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
                            this.$dispatch('searchable-select-loading', { id: 'kabupaten_kode', loading: false });
                            return;
                        }
                        if (this.kabupatenAbortController) {
                            this.kabupatenAbortController.abort();
                        }
                        this.kabupatenAbortController = new AbortController();
                        this.$dispatch('searchable-select-loading', { id: 'kabupaten_kode', loading: true });
                        try {
                            const res = await fetch(`{{ url('/wilayah/kabupaten') }}/${provKode}`, {
                                signal: this.kabupatenAbortController.signal
                            });
                            const json = await res.json();
                            if (String(this.wilayah.provinsi_kode) === String(provKode)) {
                                this.listKabupaten = json.data || [];
                            }
                        } catch (e) {
                            if (e.name !== 'AbortError') {
                                this.listKabupaten = [];
                            }
                        } finally {
                            if (String(this.wilayah.provinsi_kode) === String(provKode)) {
                                this.$dispatch('searchable-select-loading', { id: 'kabupaten_kode', loading: false });
                            }
                        }
                    },

                    async fetchKecamatan(kabKode, autoOpen = false) {
                        if (!kabKode) {
                            this.listKecamatan = [];
                            this.$dispatch('searchable-select-loading', { id: 'kecamatan_kode', loading: false });
                            return;
                        }
                        if (this.kecamatanAbortController) {
                            this.kecamatanAbortController.abort();
                        }
                        this.kecamatanAbortController = new AbortController();
                        this.$dispatch('searchable-select-loading', { id: 'kecamatan_kode', loading: true });
                        try {
                            const res = await fetch(`{{ url('/wilayah/kecamatan') }}/${kabKode}`, {
                                signal: this.kecamatanAbortController.signal
                            });
                            const json = await res.json();
                            if (String(this.wilayah.kabupaten_kode) === String(kabKode)) {
                                this.listKecamatan = json.data || [];
                            }
                        } catch (e) {
                            if (e.name !== 'AbortError') {
                                this.listKecamatan = [];
                            }
                        } finally {
                            if (String(this.wilayah.kabupaten_kode) === String(kabKode)) {
                                this.$dispatch('searchable-select-loading', { id: 'kecamatan_kode', loading: false });
                            }
                        }
                    },

                    async fetchDesa(kecKode, autoOpen = false) {
                        if (!kecKode) {
                            this.listDesa = [];
                            this.$dispatch('searchable-select-loading', { id: 'desa_kode', loading: false });
                            return;
                        }
                        if (this.desaAbortController) {
                            this.desaAbortController.abort();
                        }
                        this.desaAbortController = new AbortController();
                        this.$dispatch('searchable-select-loading', { id: 'desa_kode', loading: true });
                        try {
                            const res = await fetch(`{{ url('/wilayah/desa') }}/${kecKode}`, {
                                signal: this.desaAbortController.signal
                            });
                            const json = await res.json();
                            if (String(this.wilayah.kecamatan_kode) === String(kecKode)) {
                                this.listDesa = json.data || [];
                            }
                        } catch (e) {
                            if (e.name !== 'AbortError') {
                                this.listDesa = [];
                            }
                        } finally {
                            if (String(this.wilayah.kecamatan_kode) === String(kecKode)) {
                                this.$dispatch('searchable-select-loading', { id: 'desa_kode', loading: false });
                            }
                        }
                    },

                    extractEventValue(event, fallback = '') {
                        if (event === undefined || event === null) return fallback;
                        if (typeof event === 'object') {
                            if ('detail' in event && event.detail !== undefined && event.detail !== null) {
                                return String(event.detail);
                            }
                            if ('target' in event && event.target && 'value' in event.target && event.target.value !== undefined && event.target.value !== null) {
                                return String(event.target.value);
                            }
                            return fallback;
                        }
                        return String(event);
                    },

                    async onProvinsiChange(event) {
                        if (this.isInitializingWilayah) return;
                        const code = this.extractEventValue(event, this.wilayah.provinsi_kode);
                        if (code === undefined || code === null || String(code) === String(this.wilayah.provinsi_kode)) return;
                        this.wilayah.provinsi_kode = String(code);
                        const found = this.listProvinsi.find(i => String(i.code) === String(this.wilayah.provinsi_kode));
                        this.wilayah.provinsi_nama = found ? found.name : '';
                        this.wilayah.kabupaten_kode = '';
                        this.wilayah.kabupaten_nama = '';
                        this.wilayah.kecamatan_kode = '';
                        this.wilayah.kecamatan_nama = '';
                        this.wilayah.desa_kode = '';
                        this.wilayah.desa_nama = '';
                        this.listKabupaten = [];
                        this.listKecamatan = [];
                        this.listDesa = [];
                        this.$dispatch('searchable-select-loading', { id: 'kabupaten_kode', loading: false });
                        this.$dispatch('searchable-select-loading', { id: 'kecamatan_kode', loading: false });
                        this.$dispatch('searchable-select-loading', { id: 'desa_kode', loading: false });
                        if (code) {
                            await this.fetchKabupaten(this.wilayah.provinsi_kode, false);
                        }
                    },

                    async onKabupatenChange(event) {
                        if (this.isInitializingWilayah) return;
                        const code = this.extractEventValue(event, this.wilayah.kabupaten_kode);
                        if (code === undefined || code === null || String(code) === String(this.wilayah.kabupaten_kode)) return;
                        this.wilayah.kabupaten_kode = String(code);
                        const found = this.listKabupaten.find(i => String(i.code) === String(this.wilayah.kabupaten_kode));
                        this.wilayah.kabupaten_nama = found ? found.name : '';
                        this.wilayah.kecamatan_kode = '';
                        this.wilayah.kecamatan_nama = '';
                        this.wilayah.desa_kode = '';
                        this.wilayah.desa_nama = '';
                        this.listKecamatan = [];
                        this.listDesa = [];
                        this.$dispatch('searchable-select-loading', { id: 'kecamatan_kode', loading: false });
                        this.$dispatch('searchable-select-loading', { id: 'desa_kode', loading: false });
                        if (code) {
                            await this.fetchKecamatan(this.wilayah.kabupaten_kode, false);
                        }
                    },

                    async onKecamatanChange(event) {
                        if (this.isInitializingWilayah) return;
                        const code = this.extractEventValue(event, this.wilayah.kecamatan_kode);
                        if (code === undefined || code === null || String(code) === String(this.wilayah.kecamatan_kode)) return;
                        this.wilayah.kecamatan_kode = String(code);
                        const found = this.listKecamatan.find(i => String(i.code) === String(this.wilayah.kecamatan_kode));
                        this.wilayah.kecamatan_nama = found ? found.name : '';
                        this.wilayah.desa_kode = '';
                        this.wilayah.desa_nama = '';
                        this.listDesa = [];
                        this.$dispatch('searchable-select-loading', { id: 'desa_kode', loading: false });
                        if (code) {
                            await this.fetchDesa(this.wilayah.kecamatan_kode, false);
                        }
                    },

                    onDesaChange(event) {
                        if (this.isInitializingWilayah) return;
                        const code = this.extractEventValue(event, this.wilayah.desa_kode);
                        if (code !== undefined && code !== null) {
                            this.wilayah.desa_kode = String(code);
                            const found = this.listDesa.find(i => String(i.code) === String(this.wilayah.desa_kode));
                            this.wilayah.desa_nama = found ? found.name : '';
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
