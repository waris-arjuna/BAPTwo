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
                    this.selectedUserId = '';
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

            <div class="flex flex-wrap items-center gap-2.5">
                <form method="POST" action="{{ route('users.sync-sibob') }}" x-data="{ isSyncing: false }" @submit="isSyncing = true">
                    @csrf
                    <button
                        type="submit"
                        :disabled="isSyncing"
                        class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <i class="fi fi-rr-refresh text-sm" :class="isSyncing && 'animate-spin'"></i>
                        <span x-text="isSyncing ? '{{ __('Menyinkronkan...') }}' : '{{ __('Sinkronisasi SIBOB') }}'">{{ __('Sinkronisasi SIBOB') }}</span>
                    </button>
                </form>

                <button
                    type="button"
                    @click="openModal()"
                    class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition-colors hover:bg-brand-600 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20"
                >
                    <i class="fi fi-rr-user-add text-base"></i>
                    <span>Tambah Petugas</span>
                </button>
            </div>
        </div>

        @php
            $petugasCards = [
                [
                    'title' => __('Total Petugas'),
                    'value' => $stats['total'],
                    'subtitle' => __('+:n baru bulan ini', ['n' => $stats['baru_bulan_ini']]),
                    'icon' => 'fi-rr-users',
                    'iconClass' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                ],
                [
                    'title' => __('Petugas Aktif'),
                    'value' => $stats['aktif'],
                    'subtitle' => __(':pct% dari total', ['pct' => $stats['aktif_pct']]),
                    'icon' => 'fi-rr-user-check',
                    'iconClass' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400',
                ],
                [
                    'title' => __('Petugas Nonaktif'),
                    'value' => $stats['nonaktif'],
                    'subtitle' => __(':pct% dari total', ['pct' => $stats['nonaktif_pct']]),
                    'icon' => 'fi-rr-ban',
                    'iconClass' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
                ],
            ];
        @endphp

        <x-common.user-stats-cards
            :cards="$petugasCards"
            roleTitle="Rincian Peran Petugas"
            :roles="$stats['roles']"
        />

        {{-- Card Tabel Utama --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            {{-- Toolbar Filter --}}
            @php
                $hasPetugasFilter = request()->filled('search') || request()->filled('role') || request()->filled('status');
            @endphp
            <x-common.table-filter
                searchPlaceholder="Cari nama, NIP, atau email petugas..."
                :statusOptions="[
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Nonaktif',
                ]"
                :showDate="false"
                :hasActiveFilter="$hasPetugasFilter"
            >
                {{-- Filter Role --}}
                <div>
                    <select
                        name="role"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-theme-xs outline-none transition-colors focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">{{ __('Semua Peran') }}</option>
                        <option value="penyidik" @selected(request('role') === 'penyidik')>{{ __('Penyidik') }}</option>
                        <option value="umum" @selected(request('role') === 'umum')>{{ __('Umum') }}</option>
                    </select>
                </div>
            </x-common.table-filter>

            {{-- Container Partial Table --}}
            <div id="table-body-partial">
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
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90" x-text="isEdit ? 'Ubah Penugasan Petugas' : 'Tambah Petugas'"></h3>
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

                    {{-- Combobox Pilih Petugas (Searchable) --}}
                    <div>
                        <label for="petugas_user_id" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Pilih Petugas <span class="text-error-500">*</span>
                        </label>
                        <template x-if="!isEdit">
                            <x-form.searchable-select
                                name="user_id"
                                id="petugas_user_id"
                                placeholder="-- Pilih Petugas --"
                                searchPlaceholder="Cari nama, NIP, atau role petugas..."
                                emptyMessage="Petugas tidak ditemukan"
                                :required="true"
                            >
                                <option value="">-- Pilih Petugas --</option>
                                @foreach ($availableUsers as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->nama }} @if($user->nip)({{ $user->nip }})@endif - [{{ $user->role->label() }}]
                                    </option>
                                @endforeach
                            </x-form.searchable-select>
                        </template>
                        <template x-if="isEdit">
                            <div>
                                <input
                                    type="text"
                                    :value="editPetugasNama"
                                    disabled
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-100 px-3.5 text-sm text-gray-500 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                <input type="hidden" name="user_id" :value="selectedUserId">
                            </div>
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
                            <option value="umum">Umum</option>
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
