@php
    $title = 'Pengaturan Umum';
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb & Header --}}
        <x-page-header
            title="Pengaturan Umum"
            :breadcrumbs="[
                ['label' => 'Menu Utama', 'url' => route('dashboard')],
                ['label' => 'Sistem', 'url' => '#'],
                ['label' => 'Pengaturan', 'url' => route('users.index')],
                ['label' => 'Umum'],
            ]"
        />

        {{-- Kartu Profil Instansi & Sistem --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Informasi Instansi Kedinasan --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <i class="fi fi-rr-building text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Instansi Penegak Hukum</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Identitas satker penyelenggara penanganan perkara</p>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Nama Balai:</span>
                        <p class="font-medium text-gray-800 dark:text-white/90">Balai Besar Pengawas Obat dan Makanan di Banjarmasin</p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Wilayah Tugas Penindakan:</span>
                        <p class="font-medium text-gray-800 dark:text-white/90">Provinsi Kalimantan Selatan (13 Kabupaten/Kota)</p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Alamat Kantor:</span>
                        <p class="font-medium text-gray-800 dark:text-white/90 leading-relaxed">
                            Jl. Brigjend H. Hasan Basri No. 40, Kelurahan Pangeran, Kecamatan Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70124
                        </p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Bidang Operasional Utama:</span>
                        <p class="font-medium text-gray-800 dark:text-white/90">Bidang Penindakan (PPNS POM BAPTwo)</p>
                    </div>
                </div>
            </div>

            {{-- Informasi Konfigurasi Aplikasi BAPTwo --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                        <i class="fi fi-rr-shield-check text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Status Sistem &amp; Kebijakan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Parameter operasional dan regulasi berkas BAP</p>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Aplikasi:</span>
                        <p class="font-medium text-gray-800 dark:text-white/90">BAPTwo v1.0 (Manajemen Penanganan Perkara BPOM)</p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Tenggang Waktu Penanganan Perkara:</span>
                        <p class="font-medium text-gray-800 dark:text-white/90">1 Bulan Kalender Total (Sejak SPDP diterbitkan)</p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Batas Waktu Tahun Anggaran:</span>
                        <p class="font-medium text-gray-800 dark:text-white/90">Mutlak 31 Desember tahun berjalan (Batas tahun menang atas Tenggang Waktu)</p>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Format Dokumen Legal:</span>
                        <p class="font-medium text-gray-800 dark:text-white/90">Format PDF murni (Maksimal 5 MB per dokumen)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
