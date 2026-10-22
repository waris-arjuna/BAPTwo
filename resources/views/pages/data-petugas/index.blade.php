@php
    $title = 'Data Petugas';
@endphp

@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            isModalOpen: false,
            selectedUserId: '',
            selectedRole: 'penyidik',
            isActive: true,
            isEdit: false,
            editPetugasNama: '',
            openModal(petugas = null) {
                if (petugas) {
                    this.isEdit = true;
                    this.selectedUserId = String(petugas.id);
                    this.selectedRole = petugas.role;
                    this.isActive = Boolean(petugas.is_active);
                    this.editPetugasNama = petugas.nama;
                } else {
                    this.isEdit = false;
                    this.selectedUserId = '{{ $availableUsers->first()?->id ?? '' }}';
                    this.selectedRole = 'penyidik';
                    this.isActive = true;
                    this.editPetugasNama = '';
                }
                this.isModalOpen = true;
            },
            closeModal() {
                this.isModalOpen = false;
            }
        }"
        @keydown.escape.window="closeModal()"
    >
        {{-- Header & Breadcrumbs --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <x-page-header
                title="Data Petugas"
                :breadcrumbs="[
                    ['label' => 'Menu Utama', 'url' => route('dashboard')],
                    ['label' => 'Sistem', 'url' => '#'],
                    ['label' => 'Data Petugas'],
                ]"
            />

            <div>
                <button
                    type="button"
                    @click="openModal()"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20"
                >
                    <i class="fi fi-rr-user-add text-base"></i>
                    <span>Tambah Penugasan Petugas</span>
                </button>
            </div>
        </div>

        {{-- Card Tabel Utama --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            {{-- Toolbar Filter --}}
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800">
                <x-common.table-filter
                    action="{{ route('data-petugas.index') }}"
                    method="GET"
                    targetContainerId="data-petugas-table-partial"
                    searchPlaceholder="Cari nama, NIP, atau email petugas..."
                    searchName="search"
                    resetUrl="{{ route('data-petugas.index') }}"
                >
                    {{-- Filter Role --}}
                    <div>
                        <select
                            name="role"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-700 shadow-theme-xs outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="">Semua Role</option>
                            <option value="penyidik" {{ request('role') === 'penyidik' ? 'selected' : '' }}>Penyidik</option>
                            <option value="pimpinan" {{ request('role') === 'pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                            <option value="umum" {{ request('role') === 'umum' ? 'selected' : '' }}>Umum</option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div>
                        <select
                            name="status"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-700 shadow-theme-xs outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </x-common.table-filter>
            </div>

            {{-- Container Partial Table --}}
            <div id="data-petugas-table-partial">
                @include('pages.data-petugas._table-partial')
            </div>
        </div>

        {{-- Modal Penugasan Role Petugas --}}
        <div
            x-show="isModalOpen"
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
                @click.outside="closeModal()"
                class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90" x-text="isEdit ? 'Ubah Penugasan Petugas' : 'Tambah Penugasan Petugas'"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Atur peran operasional non-admin untuk petugas kedinasan</p>
                    </div>
                    <button
                        type="button"
                        @click="closeModal()"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <i class="fi fi-rr-cross-small text-lg"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('data-petugas.store') }}" class="space-y-4">
                    @csrf

                    {{-- Combobox Pilih Petugas --}}
                    <div>
                        <label for="petugas_user_id" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Pilih Petugas <span class="text-error-500">*</span>
                        </label>
                        <select
                            id="petugas_user_id"
                            name="user_id"
                            x-model="selectedUserId"
                            :disabled="isEdit"
                            required
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 shadow-theme-xs outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800"
                        >
                            <option value="">-- Pilih Petugas --</option>
                            @foreach ($availableUsers as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->nama }} @if($user->nip)({{ $user->nip }})@endif - [{{ $user->role->label() }}]
                                </option>
                            @endforeach
                        </select>
                        <template x-if="isEdit">
                            <input type="hidden" name="user_id" :value="selectedUserId">
                        </template>
                        @error('user_id')
                            <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Combobox Role Penugasan (Non-Admin) --}}
                    <div>
                        <label for="petugas_role" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Peran / Role yang Diberikan <span class="text-error-500">*</span>
                        </label>
                        <select
                            id="petugas_role"
                            name="role"
                            x-model="selectedRole"
                            required
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 shadow-theme-xs outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <option value="penyidik">Penyidik</option>
                            <option value="pimpinan">Pimpinan</option>
                            <option value="umum">Umum (Pimpinan Terbatas)</option>
                        </select>
                        <p class="mt-1 text-[11px] text-gray-400">
                            Peran Administrator bersifat khusus dan dikelola langsung melalui Manajemen Pengguna.
                        </p>
                        @error('role')
                            <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status Akun --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Status Akun Petugas
                        </label>
                        <div class="flex items-center gap-4 pt-1">
                            <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input
                                    type="radio"
                                    name="is_active"
                                    value="1"
                                    x-model="isActive"
                                    :checked="isActive == true"
                                    class="text-brand-600 focus:ring-brand-500"
                                >
                                <span>Aktif</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input
                                    type="radio"
                                    name="is_active"
                                    value="0"
                                    x-model="isActive"
                                    :checked="isActive == false"
                                    class="text-error-600 focus:ring-error-500"
                                >
                                <span>Nonaktif</span>
                            </label>
                        </div>
                    </div>

                    {{-- Tombol Aksi Modal --}}
                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button
                            type="button"
                            @click="closeModal()"
                            class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-brand-500 px-5 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-hidden"
                        >
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
