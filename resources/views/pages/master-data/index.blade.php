@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__($meta['title'])" />

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-success-500/30 bg-success-500/10 px-4 py-3 text-theme-sm text-success-700 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-error-500/30 bg-error-500/10 px-4 py-3 text-theme-sm text-error-600 dark:text-error-400">
            <ul class="list-disc ps-5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Submenu Navigation Tabs --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($allTypes as $key => $itemMeta)
            <a
                href="{{ route('master-data.index', ['type' => $key]) }}"
                class="rounded-xl px-4 py-2 text-theme-sm font-medium transition {{ $type === $key
                    ? 'bg-brand-500 text-white shadow-theme-xs'
                    : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04]' }}"
            >
                {{ __($itemMeta['singular']) }}
            </a>
        @endforeach
    </div>

    <div x-data="{
        modalOpen: @js($errors->any()),
        isEdit: false,
        formAction: '{{ route('master-data.store', ['type' => $type]) }}',
        form: {
            nama_jabatan: @js(old('nama_jabatan', '')),
            nama_bidang: @js(old('nama_bidang', '')),
            pangkat: @js(old('pangkat', '')),
            golongan: @js(old('golongan', '')),
            nama_pasal: @js(old('nama_pasal', '')),
            pasal: @js(old('pasal', '')),
            nomor: @js(old('nomor', '')),
            tahun: @js(old('tahun', '')),
            isi: @js(old('isi', '')),
            status: @js(old('status', 'aktif')),
            jenis: @js(old('jenis', 'kejaksaan')),
            nama: @js(old('nama', '')),
            wilayah: @js(old('wilayah', '')),
            instansi_hukum_id: @js((string) old('instansi_hukum_id', $kejaksaanList->first()?->id ?? '')),
            nama_jaksa: @js(old('nama_jaksa', '')),
            nip: @js(old('nip', '')),
        },
        openCreate() {
            this.isEdit = false;
            this.formAction = '{{ route('master-data.store', ['type' => $type]) }}';
            this.form = {
                nama_jabatan: '',
                nama_bidang: '',
                pangkat: '',
                golongan: '',
                nama_pasal: '',
                pasal: '',
                nomor: '',
                tahun: '',
                isi: '',
                status: 'aktif',
                jenis: 'kejaksaan',
                nama: '',
                wilayah: '',
                instansi_hukum_id: '{{ $kejaksaanList->first()?->id ?? '' }}',
                nama_jaksa: '',
                nip: '',
            };
            this.modalOpen = true;
        },
        openEdit(item) {
            this.isEdit = true;
            this.formAction = '/master-data/{{ $type }}/' + item.id;
            this.form = {
                nama_jabatan: item.nama_jabatan || '',
                nama_bidang: item.nama_bidang || '',
                pangkat: item.pangkat || '',
                golongan: item.golongan || '',
                nama_pasal: item.nama_pasal || '',
                pasal: item.pasal || '',
                nomor: item.nomor || '',
                tahun: item.tahun || '',
                isi: item.isi || '',
                status: item.status || 'aktif',
                jenis: item.jenis || 'kejaksaan',
                nama: item.nama || '',
                wilayah: item.wilayah || '',
                instansi_hukum_id: item.instansi_hukum_id ? String(item.instansi_hukum_id) : '',
                nama_jaksa: item.nama_jaksa || '',
                nip: item.nip || '',
            };
            this.modalOpen = true;
        }
    }" @keydown.escape.window="modalOpen = false">

        {{-- Header & Tombol Tambah --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    {{ __($meta['title']) }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __($meta['description']) }}
                </p>
            </div>

            @if ($canManage)
                <button
                    type="button"
                    @click="openCreate()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <i class="fi fi-rr-plus-small text-lg"></i> {{ __('Tambah :item', ['item' => __($meta['singular'])]) }}
                </button>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Toolbar Filter --}}
            <x-common.table-filter
                searchPlaceholder="Cari data..."
                :statusOptions="[
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Nonaktif / Terhapus',
                ]"
                :showDate="false"
            />

            {{-- Tabel Data --}}
            <div id="table-body-partial" class="max-w-full overflow-x-auto">
                @include('pages.master-data._table-partial')
            </div>
        </div>

        {{-- Modal Tambah / Ubah Master Data --}}
        @if ($canManage)
            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-xs">
                <div @click.outside="modalOpen = false" class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                    <h4 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90"
                        x-text="isEdit ? '{{ __('Ubah :item', ['item' => __($meta['singular'])]) }}' : '{{ __('Tambah :item', ['item' => __($meta['singular'])]) }}'"></h4>

                    <form :action="formAction" method="POST" class="space-y-4">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        @if ($type === 'jabatan')
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Jabatan') }} *</label>
                                <input type="text" name="nama_jabatan" x-model="form.nama_jabatan" required maxlength="150"
                                    @input="form.nama_jabatan = $event.target.value.replace(/[^a-zA-Z\s.,\-\/()]/g, '')"
                                    placeholder="Contoh: Penyidik Pegawai Negeri Sipil (PPNS)"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya huruf, spasi, dan tanda baca standar.') }}</p>
                            </div>
                        @elseif ($type === 'bidang')
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Bidang / Unit') }} *</label>
                                <input type="text" name="nama_bidang" x-model="form.nama_bidang" required maxlength="150"
                                    @input="form.nama_bidang = $event.target.value.replace(/[^a-zA-Z0-9\s.,\-\/()&]/g, '')"
                                    placeholder="Contoh: Bidang Penindakan"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                <p class="mt-1 text-theme-xs text-gray-400">{{ __('Huruf dan angka (wajib mengandung huruf).') }}</p>
                            </div>
                        @elseif ($type === 'panggol')
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Pangkat') }} *</label>
                                    <input type="text" name="pangkat" x-model="form.pangkat" required maxlength="100"
                                        @input="form.pangkat = $event.target.value.replace(/[^a-zA-Z\s]/g, '')"
                                        placeholder="Contoh: Pembina"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                    <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya huruf.') }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Golongan Ruang') }} *</label>
                                    <input type="text" name="golongan" x-model="form.golongan" required maxlength="50"
                                        @input="form.golongan = $event.target.value.replace(/[^a-zA-Z0-9\/\-]/g, '')"
                                        placeholder="Contoh: IV/a"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                    <p class="mt-1 text-theme-xs text-gray-400">{{ __('Huruf, angka, dan tanda /.') }}</p>
                                </div>
                            </div>
                        @elseif ($type === 'pasal')
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Undang-Undang / Regulasi') }} *</label>
                                <input type="text" name="nama_pasal" x-model="form.nama_pasal" required maxlength="150"
                                    @input="form.nama_pasal = $event.target.value.replace(/[^a-zA-Z0-9\s.,\-\/()&]/g, '')"
                                    placeholder="Contoh: UU Kesehatan"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                <p class="mt-1 text-theme-xs text-gray-400">{{ __('Huruf dan angka (wajib mengandung huruf).') }}</p>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Nomor UU') }}</label>
                                    <input type="text" name="nomor" x-model="form.nomor" inputmode="numeric" maxlength="20"
                                        @input="form.nomor = $event.target.value.replace(/[^0-9]/g, '')"
                                        placeholder="Contoh: 17"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                    <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya angka.') }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Tahun UU') }}</label>
                                    <input type="text" name="tahun" x-model="form.tahun" inputmode="numeric" maxlength="4"
                                        @input="form.tahun = $event.target.value.replace(/[^0-9]/g, '').slice(0, 4)"
                                        placeholder="Contoh: 2023"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                    <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya 4 digit angka.') }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Pasal & Ayat') }} *</label>
                                <input type="text" name="pasal" x-model="form.pasal" required maxlength="150"
                                    @input="form.pasal = $event.target.value.replace(/[^a-zA-Z0-9\s.,\-\/()]/g, '')"
                                    placeholder="Contoh: Pasal 435 jo. Pasal 138 ayat (2)"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                <p class="mt-1 text-theme-xs text-gray-400">{{ __('Huruf, angka, dan tanda baca pasal.') }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Isi / Uraian Pasal') }}</label>
                                <textarea name="isi" x-model="form.isi" rows="3" maxlength="2000"
                                    placeholder="Ringkasan bunyi pasal..."
                                    class="w-full rounded-lg border border-gray-300 bg-transparent p-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90"></textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Status Pasal') }}</label>
                                <select name="status" x-model="form.status" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="aktif">{{ __('Aktif') }}</option>
                                    <option value="nonaktif">{{ __('Nonaktif') }}</option>
                                </select>
                            </div>
                        @elseif ($type === 'instansi-hukum')
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Jenis Instansi') }} *</label>
                                <select name="jenis" x-model="form.jenis" required class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="kejaksaan">{{ __('Kejaksaan') }}</option>
                                    <option value="pengadilan">{{ __('Pengadilan') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Instansi') }} *</label>
                                <input type="text" name="nama" x-model="form.nama" required maxlength="150"
                                    @input="form.nama = $event.target.value.replace(/[^a-zA-Z0-9\s.,\-\/()]/g, '')"
                                    placeholder="Contoh: Kejaksaan Tinggi Kalimantan Selatan"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                <p class="mt-1 text-theme-xs text-gray-400">{{ __('Huruf dan angka (wajib mengandung huruf).') }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Wilayah') }}</label>
                                <input type="text" name="wilayah" x-model="form.wilayah" maxlength="100"
                                    @input="form.wilayah = $event.target.value.replace(/[^a-zA-Z\s.,\-\/()]/g, '')"
                                    placeholder="Contoh: Kalimantan Selatan / Kota Banjarmasin"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya huruf dan tanda baca wilayah.') }}</p>
                            </div>
                        @elseif ($type === 'jaksa-penuntut')
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Asal Kejaksaan') }} *</label>
                                <select name="instansi_hukum_id" x-model="form.instansi_hukum_id" required class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">{{ __('Pilih Kejaksaan') }}</option>
                                    @foreach ($kejaksaanList as $kejaksaan)
                                        <option value="{{ $kejaksaan->id }}">{{ $kejaksaan->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Lengkap Jaksa') }} *</label>
                                <input type="text" name="nama_jaksa" x-model="form.nama_jaksa" required maxlength="225"
                                    @input="form.nama_jaksa = $event.target.value.replace(/[^a-zA-Z\s.,'\-]/g, '')"
                                    placeholder="Contoh: Ahmad Fauzi, S.H., M.H."
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya huruf, spasi, dan tanda baca gelar.') }}</p>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('NIP') }}</label>
                                    <input type="text" name="nip" x-model="form.nip" inputmode="numeric" maxlength="30"
                                        @input="form.nip = $event.target.value.replace(/[^0-9]/g, '')"
                                        placeholder="NIP Jaksa (Hanya angka)"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                    <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya angka.') }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Pangkat Jaksa') }}</label>
                                    <input type="text" name="pangkat" x-model="form.pangkat" maxlength="100"
                                        @input="form.pangkat = $event.target.value.replace(/[^a-zA-Z0-9\s.,\-\/()]/g, '')"
                                        placeholder="Contoh: Jaksa Madya (IV/a)"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                    <p class="mt-1 text-theme-xs text-gray-400">{{ __('Huruf dan angka.') }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="mt-6 flex items-center justify-end gap-2">
                            <button type="button" @click="modalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white hover:bg-brand-600">
                                {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
