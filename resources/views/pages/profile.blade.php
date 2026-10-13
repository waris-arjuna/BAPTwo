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

        {{-- Notifikasi Sukses --}}
        @if (session('profile_success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-cloak
                class="mb-6 flex items-center justify-between gap-4 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm text-success-700 shadow-theme-xs dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400"
                role="alert">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-400">
                        <svg class="h-5 w-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span class="font-medium">{{ session('profile_success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-success-600 hover:text-success-800 dark:text-success-400">
                    <svg class="h-5 w-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- Notifikasi Error Validasi Server --}}
        @if ($errors->any())
            <div x-data="{ show: true }" x-show="show" x-cloak
                class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-theme-sm text-error-700 shadow-theme-xs dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"
                role="alert">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-error-100 text-error-600 dark:bg-error-500/20 dark:text-error-400">
                            <svg class="h-4 w-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
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
                        <svg class="h-5 w-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
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
                <svg class="h-5 w-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- A1. CARD FOTO PROFIL --}}
            <div class="flex h-full flex-col items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 text-center lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex w-full flex-1 flex-col items-center justify-center gap-4">
                    <div class="relative mx-auto h-40 w-40 shrink-0 overflow-hidden rounded-full border-2 border-gray-200 shadow-theme-sm dark:border-gray-700">
                        <img src="{{ $currentUser?->fotoProfilUrl() }}" alt="{{ $currentUser?->nama }}"
                            class="h-full w-full object-cover" />
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
                            <svg class="h-4 w-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
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
                        <svg class="h-4 w-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
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
                        <svg class="h-4 w-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
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
                        <svg class="h-3.5 w-3.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
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
        <div x-show="isAccountModalOpen" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-6">
            <div class="fixed inset-0 h-full w-full bg-gray-900/50 backdrop-blur-xs" @click="isAccountModalOpen = false"></div>

            <div class="relative z-10 w-full max-w-xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl lg:p-8 dark:border-gray-800 dark:bg-gray-900">
                <button type="button" @click="isAccountModalOpen = false"
                    class="absolute top-5 end-5 flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="mb-5 pe-10">
                    <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                        {{ __('Update Email / Password') }}
                    </h4>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ __('Masukkan password lama Anda untuk mengonfirmasi perubahan email atau password baru.') }}
                    </p>
                </div>

                {{-- Instruksi Syarat Update Password --}}
                <div class="mb-5 rounded-xl border border-brand-200 bg-brand-50/60 p-4 dark:border-brand-500/30 dark:bg-brand-500/10">
                    <p class="mb-2 text-theme-xs font-semibold text-brand-700 dark:text-brand-300">
                        {{ __('Instruksi Syarat Update Password Baru:') }}
                    </p>
                    <ol class="list-decimal space-y-1 ps-5 text-theme-xs text-gray-700 dark:text-gray-300">
                        <li :class="newPassword.length >= 8 ? 'text-success-600 dark:text-success-400 font-medium' : ''">
                            {{ __('Minimal 8 karakter.') }}
                        </li>
                        <li :class="(/[a-z]/.test(newPassword) && /[A-Z]/.test(newPassword) && /[0-9]/.test(newPassword)) ? 'text-success-600 dark:text-success-400 font-medium' : ''">
                            {{ __('Kombinasi angka dan huruf (besar dan kecil).') }}
                        </li>
                        <li :class="/[^A-Za-z0-9]/.test(newPassword) ? 'text-success-600 dark:text-success-400 font-medium' : ''">
                            {{ __('Gunakan simbol (contoh: ! @ # $ % ^ & *).') }}
                        </li>
                    </ol>
                </div>

                <form method="POST" action="{{ route('profile.account.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="account_email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Email') }} <span class="text-error-500">*</span>
                        </label>
                        <input type="email" id="account_email" name="email"
                            value="{{ old('email', $currentUser?->email) }}" required
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        @error('email')
                            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="current_password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Password Lama') }} <span class="text-error-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                            placeholder="{{ __('Masukkan password saat ini') }}"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        @error('current_password')
                            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Password Baru') }}
                            <span class="text-theme-xs font-normal text-gray-400">({{ __('Kosongkan jika hanya mengubah email') }})</span>
                        </label>
                        <input type="password" id="new_password" name="password" x-model="newPassword"
                            placeholder="{{ __('Minimal 8 karakter, huruf besar/kecil, angka & simbol') }}"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        @error('password')
                            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Konfirmasi Password Baru') }}
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="{{ __('Ulangi password baru') }}"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="isAccountModalOpen = false"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: EDIT DATA DIRI (HANYA NO.TELP & ALAMAT) --}}
        <div x-show="isPersonalModalOpen" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-6">
            <div class="fixed inset-0 h-full w-full bg-gray-900/50 backdrop-blur-xs" @click="isPersonalModalOpen = false"></div>

            <div class="custom-scrollbar relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl lg:p-8 dark:border-gray-800 dark:bg-gray-900">
                <button type="button" @click="isPersonalModalOpen = false"
                    class="absolute top-5 end-5 flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="mb-5 pe-10">
                    <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                        {{ __('Edit Data Diri') }}
                    </h4>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ __('Hanya Nomor Telepon/WA dan Alamat yang dapat diubah. Data identitas lainnya dikunci sesuai data kepegawaian.') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('profile.personal.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Bagian Read-Only (Terkunci) --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                        <p class="mb-3 text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('Data Identitas (Terkunci)') }}
                        </p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Nama Lengkap') }}
                                </label>
                                <input type="text" value="{{ $currentUser?->nama ?? '-' }}" disabled readonly
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 text-theme-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" />
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Tempat Lahir') }}
                                </label>
                                <input type="text" value="{{ $currentUser?->tempat_lahir ?? '-' }}" disabled readonly
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 text-theme-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" />
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Tanggal Lahir') }}
                                </label>
                                <input type="text" value="{{ $currentUser?->tanggal_lahir ? $currentUser->tanggal_lahir->translatedFormat('d F Y') : '-' }}" disabled readonly
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 text-theme-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" />
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Jenis Kelamin') }}
                                </label>
                                <input type="text" value="{{ $currentUser?->jenisKelaminLabel() ?? '-' }}" disabled readonly
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 text-theme-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" />
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Status Pernikahan') }}
                                </label>
                                <input type="text" value="{{ $currentUser?->statusPernikahanLabel() ?? '-' }}" disabled readonly
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 text-theme-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" />
                            </div>
                        </div>
                    </div>

                    {{-- Bagian Editable: No. Telp/WA & Alamat --}}
                    <div class="space-y-4">
                        <div>
                            <label for="no_telp" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('No. Telp / WA') }} <span class="text-error-500">*</span>
                            </label>
                            <input type="text" id="no_telp" name="no_telp"
                                value="{{ old('no_telp', $currentUser?->no_telp) }}" required
                                placeholder="0812xxxxxxxx"
                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            @error('no_telp')
                                <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {{-- Provinsi --}}
                            <div>
                                <label for="provinsi_kode" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Provinsi') }} <span class="text-error-500">*</span>
                                </label>
                                <input type="hidden" name="provinsi_nama" :value="wilayah.provinsi_nama" />
                                <select id="provinsi_kode" name="provinsi_kode" x-model="wilayah.provinsi_kode"
                                    @change="onProvinsiChange()" required
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">{{ __('-- Pilih Provinsi --') }}</option>
                                    <template x-for="item in listProvinsi" :key="item.code">
                                        <option :value="item.code" x-text="item.name" :selected="item.code === wilayah.provinsi_kode"></option>
                                    </template>
                                </select>
                                @error('provinsi_kode')
                                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Kabupaten / Kota --}}
                            <div>
                                <label for="kabupaten_kode" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Kabupaten / Kota') }} <span class="text-error-500">*</span>
                                </label>
                                <input type="hidden" name="kabupaten_nama" :value="wilayah.kabupaten_nama" />
                                <select id="kabupaten_kode" name="kabupaten_kode" x-model="wilayah.kabupaten_kode"
                                    @change="onKabupatenChange()" :disabled="!wilayah.provinsi_kode" required
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">{{ __('-- Pilih Kabupaten/Kota --') }}</option>
                                    <template x-for="item in listKabupaten" :key="item.code">
                                        <option :value="item.code" x-text="item.name" :selected="item.code === wilayah.kabupaten_kode"></option>
                                    </template>
                                </select>
                                @error('kabupaten_kode')
                                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Kecamatan --}}
                            <div>
                                <label for="kecamatan_kode" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Kecamatan') }} <span class="text-error-500">*</span>
                                </label>
                                <input type="hidden" name="kecamatan_nama" :value="wilayah.kecamatan_nama" />
                                <select id="kecamatan_kode" name="kecamatan_kode" x-model="wilayah.kecamatan_kode"
                                    @change="onKecamatanChange()" :disabled="!wilayah.kabupaten_kode" required
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">{{ __('-- Pilih Kecamatan --') }}</option>
                                    <template x-for="item in listKecamatan" :key="item.code">
                                        <option :value="item.code" x-text="item.name" :selected="item.code === wilayah.kecamatan_kode"></option>
                                    </template>
                                </select>
                                @error('kecamatan_kode')
                                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Desa / Kelurahan --}}
                            <div>
                                <label for="desa_kode" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Desa / Kelurahan') }} <span class="text-error-500">*</span>
                                </label>
                                <input type="hidden" name="desa_nama" :value="wilayah.desa_nama" />
                                <select id="desa_kode" name="desa_kode" x-model="wilayah.desa_kode"
                                    @change="onDesaChange()" :disabled="!wilayah.kecamatan_kode" required
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">{{ __('-- Pilih Desa/Kelurahan --') }}</option>
                                    <template x-for="item in listDesa" :key="item.code">
                                        <option :value="item.code" x-text="item.name" :selected="item.code === wilayah.desa_kode"></option>
                                    </template>
                                </select>
                                @error('desa_kode')
                                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- RT & RW --}}
                            <div>
                                <label for="rt" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('RT') }}
                                </label>
                                <input type="text" id="rt" name="rt" maxlength="5"
                                    value="{{ old('rt', $alamat?->rt) }}" placeholder="001"
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('rt')
                                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="rw" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('RW') }}
                                </label>
                                <input type="text" id="rw" name="rw" maxlength="5"
                                    value="{{ old('rw', $alamat?->rw) }}" placeholder="002"
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                @error('rw')
                                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="alamat_detail" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Detail Alamat (Nama Jalan / No. Rumah)') }} <span class="text-error-500">*</span>
                            </label>
                            <textarea id="alamat_detail" name="alamat_detail" rows="2" required
                                placeholder="{{ __('Contoh: Jl. Brigjend H. Hasan Basri No. 40') }}"
                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('alamat_detail', $alamat?->alamat_detail) }}</textarea>
                            @error('alamat_detail')
                                <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="isPersonalModalOpen = false"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">
                            {{ __('Simpan Data Diri') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function profilePage(config) {
                return {
                    isAccountModalOpen: config.openAccountModal || false,
                    isPersonalModalOpen: config.openPersonalModal || false,
                    newPassword: '',
                    clientError: '',
                    wilayahLoaded: false,
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

                    init() {
                        if (this.isPersonalModalOpen) {
                            this.loadInitialWilayah();
                        }
                    },

                    closeModals() {
                        this.isAccountModalOpen = false;
                        this.isPersonalModalOpen = false;
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
                        this.wilayahLoaded = true;
                        await this.fetchProvinsi();

                        if (this.wilayah.provinsi_kode) {
                            await this.fetchKabupaten(this.wilayah.provinsi_kode);
                        }
                        if (this.wilayah.kabupaten_kode) {
                            await this.fetchKecamatan(this.wilayah.kabupaten_kode);
                        }
                        if (this.wilayah.kecamatan_kode) {
                            await this.fetchDesa(this.wilayah.kecamatan_kode);
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

                    async fetchKabupaten(provKode) {
                        if (!provKode) {
                            this.listKabupaten = [];
                            return;
                        }
                        try {
                            const res = await fetch(`{{ url('/wilayah/kabupaten') }}/${provKode}`);
                            const json = await res.json();
                            this.listKabupaten = json.data || [];
                        } catch (e) {
                            this.listKabupaten = [];
                        }
                    },

                    async fetchKecamatan(kabKode) {
                        if (!kabKode) {
                            this.listKecamatan = [];
                            return;
                        }
                        try {
                            const res = await fetch(`{{ url('/wilayah/kecamatan') }}/${kabKode}`);
                            const json = await res.json();
                            this.listKecamatan = json.data || [];
                        } catch (e) {
                            this.listKecamatan = [];
                        }
                    },

                    async fetchDesa(kecKode) {
                        if (!kecKode) {
                            this.listDesa = [];
                            return;
                        }
                        try {
                            const res = await fetch(`{{ url('/wilayah/desa') }}/${kecKode}`);
                            const json = await res.json();
                            this.listDesa = json.data || [];
                        } catch (e) {
                            this.listDesa = [];
                        }
                    },

                    async onProvinsiChange() {
                        const found = this.listProvinsi.find(i => i.code === this.wilayah.provinsi_kode);
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
                        await this.fetchKabupaten(this.wilayah.provinsi_kode);
                    },

                    async onKabupatenChange() {
                        const found = this.listKabupaten.find(i => i.code === this.wilayah.kabupaten_kode);
                        this.wilayah.kabupaten_nama = found ? found.name : '';
                        this.wilayah.kecamatan_kode = '';
                        this.wilayah.kecamatan_nama = '';
                        this.wilayah.desa_kode = '';
                        this.wilayah.desa_nama = '';
                        this.listKecamatan = [];
                        this.listDesa = [];
                        await this.fetchKecamatan(this.wilayah.kabupaten_kode);
                    },

                    async onKecamatanChange() {
                        const found = this.listKecamatan.find(i => i.code === this.wilayah.kecamatan_kode);
                        this.wilayah.kecamatan_nama = found ? found.name : '';
                        this.wilayah.desa_kode = '';
                        this.wilayah.desa_nama = '';
                        this.listDesa = [];
                        await this.fetchDesa(this.wilayah.kecamatan_kode);
                    },

                    onDesaChange() {
                        const found = this.listDesa.find(i => i.code === this.wilayah.desa_kode);
                        this.wilayah.desa_nama = found ? found.name : '';
                    }
                };
            }
        </script>
    @endpush
@endsection
