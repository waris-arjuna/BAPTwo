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

        <x-common.component-card :title="__('Daftar Pengguna Sistem')" :desc="__('Kelola akun Penyidik, Pimpinan, dan Administrator BAPTwo (Soft Delete aktif).')">
            {{-- Filter & Tombol Tambah --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form method="GET" action="{{ route('users.index') }}" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="{{ __('Cari nama, NIP, atau email...') }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden sm:max-w-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    />
                    <select
                        name="role"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-3.5 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    >
                        <option value="">{{ __('Semua Peran') }}</option>
                        @foreach ($roles as $roleOption)
                            <option value="{{ $roleOption->value }}" @selected($selectedRole === $roleOption->value)>
                                {{ $roleOption->label() }}
                            </option>
                        @endforeach
                    </select>
                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/5"
                    >
                        {{ __('Filter') }}
                    </button>
                </form>

                <button
                    type="button"
                    @click="openCreateModal()"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex h-11 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium text-white shadow-theme-xs transition"
                >
                    <span>+ {{ __('Tambah Pengguna') }}</span>
                </button>
            </div>

            {{-- Tabel Pengguna --}}
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Nama & NIP') }}</th>
                            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Email') }}</th>
                            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Role') }}</th>
                            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Kepegawaian') }}</th>
                            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Status Akun') }}</th>
                            <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        @forelse ($users as $item)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-theme-sm text-gray-800 dark:text-white/90">{{ $item->nama }}</div>
                                    <div class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $item->nip ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-theme-sm text-gray-600 dark:text-gray-300">
                                    {{ $item->email }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                        {{ $item->role->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-theme-sm uppercase text-gray-600 dark:text-gray-300">
                                    {{ $item->status }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($item->trashed())
                                        <span class="rounded-full bg-error-50 px-2.5 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                            {{ __('Terhapus (Soft Delete)') }}
                                        </span>
                                    @elseif ($item->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                            {{ __('Aktif') }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-warning-50 px-2.5 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                                            {{ __('Nonaktif') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-end">
                                    <div class="inline-flex items-center gap-2">
                                        @if ($item->trashed())
                                            <form method="POST" action="{{ route('users.restore', $item->id) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="rounded-lg border border-success-300 px-3 py-1.5 text-theme-xs font-medium text-success-600 hover:bg-success-50 dark:border-success-500/30 dark:text-success-400 dark:hover:bg-success-500/10"
                                                >
                                                    {{ __('Restore') }}
                                                </button>
                                            </form>
                                        @else
                                            <button
                                                type="button"
                                                @click="openEditModal(@js([
                                                    'nama' => $item->nama,
                                                    'nip' => $item->nip,
                                                    'email' => $item->email,
                                                    'role' => $item->role->value,
                                                    'status' => $item->status,
                                                    'is_active' => $item->is_active,
                                                ]), '{{ route('users.update', $item) }}')"
                                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                                            >
                                                {{ __('Edit') }}
                                            </button>

                                            @if (auth()->id() !== $item->id)
                                                <form method="POST" action="{{ route('users.destroy', $item) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="rounded-lg border border-error-300 px-3 py-1.5 text-theme-xs font-medium text-error-600 hover:bg-error-50 dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/10"
                                                    >
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No data available.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

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
                    <button type="button" @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT" />
                    </template>

                    <div>
                        <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Lengkap') }} *</label>
                        <input type="text" name="nama" x-model="form.nama" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('NIP') }}</label>
                            <input type="text" name="nip" x-model="form.nip" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
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
