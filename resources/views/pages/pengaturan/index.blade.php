@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            isModalOpen: false,
            isEdit: false,
            formAction: '{{ route('users.store') }}',
            form: {
                nama: '',
                nip: '',
                email: '',
                role: 'penyidik',
                status: 'pns',
                jabatan_id: '',
                bidang_id: '',
                panggol_id: '',
                is_active: true,
                password: '',
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = '{{ route('users.store') }}';
                this.form = {
                    nama: '',
                    nip: '',
                    email: '',
                    role: 'penyidik',
                    status: 'pns',
                    jabatan_id: '',
                    bidang_id: '',
                    panggol_id: '',
                    is_active: true,
                    password: '',
                };
                this.isModalOpen = true;
            },
            openEditModal(user, updateUrl) {
                this.isEdit = true;
                this.formAction = updateUrl;
                this.form = {
                    nama: user.nama,
                    nip: user.nip ?? '',
                    email: user.email,
                    role: user.role,
                    status: user.status,
                    jabatan_id: user.jabatan_id ? String(user.jabatan_id) : '',
                    bidang_id: user.bidang_id ? String(user.bidang_id) : '',
                    panggol_id: user.panggol_id ? String(user.panggol_id) : '',
                    is_active: Boolean(user.is_active),
                    password: '',
                };
                this.isModalOpen = true;
            }
        }"
        @keydown.escape.window="isModalOpen = false"
    >
        <x-common.page-breadcrumb :pageTitle="__('Manajemen Pengguna')" />

        @if (session('success'))
            <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm font-medium text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-theme-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
                <ul class="list-disc ps-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header & Tombol Tambah --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    {{ __('Daftar Pengguna Sistem') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Kelola akun Penyidik, Pimpinan, dan Administrator BAPTwo (Soft Delete aktif).') }}
                </p>
            </div>

            <button
                type="button"
                @click="openCreateModal()"
                class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition"
            >
                <i class="fi fi-rr-plus-small text-lg"></i>
                <span>{{ __('Tambah Pengguna') }}</span>
            </button>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Toolbar Filter --}}
            <x-common.table-filter
                searchPlaceholder="Cari nama, NIP, atau email..."
                :statusOptions="[
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Nonaktif / Terhapus',
                ]"
                :showDate="false"
                :hasActiveFilter="$search !== '' || $selectedRole !== '' || ($selectedStatus ?? '') !== ''"
            >
                <select
                    name="role"
                    class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">{{ __('Semua Peran') }}</option>
                    @foreach ($roles as $roleOption)
                        <option value="{{ $roleOption->value }}" @selected($selectedRole === $roleOption->value)>
                            {{ $roleOption->label() }}
                        </option>
                    @endforeach
                </select>
            </x-common.table-filter>

            {{-- Tabel Pengguna --}}
            <div id="table-body-partial" class="overflow-x-auto">
                @include('pages.pengaturan._table-partial')
            </div>
        </div>

        {{-- Modal Tambah / Edit Pengguna --}}
        <div
            x-show="isModalOpen"
            x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-xs"
        >
            <div
                @click.outside="isModalOpen = false"
                class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-title-xs font-semibold text-gray-800 dark:text-white/90" x-text="isEdit ? '{{ __('Ubah Pengguna') }}' : '{{ __('Tambah Pengguna') }}'"></h3>
                    <button type="button" @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fi fi-rr-cross-small text-lg"></i>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT" />
                    </template>

                    <div>
                        <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Lengkap') }} *</label>
                        <input type="text" name="nama" x-model="form.nama" required maxlength="150"
                            @input="form.nama = $event.target.value.replace(/[^a-zA-Z\s.,'\-]/g, '')"
                            placeholder="Contoh: Budi Santoso, S.Farm., Apt."
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                        <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya huruf, spasi, dan tanda baca gelar.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('NIP') }}</label>
                            <input type="text" name="nip" x-model="form.nip" inputmode="numeric" maxlength="30"
                                @input="form.nip = $event.target.value.replace(/[^0-9]/g, '')"
                                placeholder="Hanya angka"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                            <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya angka.') }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Email') }} *</label>
                            <input type="email" name="email" x-model="form.email" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Role') }} *</label>
                            <select name="role" x-model="form.role" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach ($roles as $roleOption)
                                    <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Status Kepegawaian') }} *</label>
                            <select name="status" x-model="form.status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="pns">PNS</option>
                                <option value="pppk">PPPK</option>
                                <option value="lainnya">{{ __('Lainnya') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Jabatan') }}</label>
                            <select name="jabatan_id" x-model="form.jabatan_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">- {{ __('Pilih Jabatan') }} -</option>
                                @foreach ($jabatanList as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Bidang') }}</label>
                            <select name="bidang_id" x-model="form.bidang_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">- {{ __('Pilih Bidang') }} -</option>
                                @foreach ($bidangList as $bidang)
                                    <option value="{{ $bidang->id }}">{{ $bidang->nama_bidang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Pangkat & Golongan') }}</label>
                            <select name="panggol_id" x-model="form.panggol_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">- {{ __('Pilih Pangkat/Gol.') }} -</option>
                                @foreach ($panggolList as $panggol)
                                    <option value="{{ $panggol->id }}">{{ $panggol->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Password') }} <span x-show="!isEdit">*</span>
                            <span x-show="isEdit" class="text-gray-400">({{ __('Kosongkan jika tidak diubah') }})</span>
                        </label>
                        <input type="password" name="password" x-model="form.password" :required="!isEdit" minlength="8" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0" />
                        <input type="checkbox" id="is_active_checkbox" name="is_active" value="1" x-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-brand-500" />
                        <label for="is_active_checkbox" class="text-theme-sm text-gray-700 dark:text-gray-300">{{ __('Akun Aktif') }}</label>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs"
                        >
                            {{ __('Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
