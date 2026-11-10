{{-- resources/views/pages/profile/_modal-edit-profil.blade.php --}}
{{-- MODAL 2: EDIT DATA DIRI (HANYA NO.TELP & ALAMAT) --}}
<div x-show="isPersonalModalOpen" x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-6">
    <div class="fixed inset-0 h-full w-full bg-gray-900/50 backdrop-blur-xs" @click="isPersonalModalOpen = false"></div>

    <div class="custom-scrollbar relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl lg:p-8 dark:border-gray-800 dark:bg-gray-900">
        <button type="button" @click="isPersonalModalOpen = false"
            class="absolute top-5 end-5 flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
            <i class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
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
                    <input type="text" id="no_telp" name="no_telp" inputmode="numeric" maxlength="20"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        value="{{ old('no_telp', $currentUser?->no_telp) }}" required
                        placeholder="0812xxxxxxxx"
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya angka (8–20 digit).') }}</p>
                    @error('no_telp')
                        <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Provinsi --}}
                    <div>
                        <input type="hidden" name="provinsi_nama" :value="wilayah.provinsi_nama" />
                        <x-form.searchable-select
                            id="provinsi_kode"
                            name="provinsi_kode"
                            :label="__('Provinsi')"
                            placeholder="{{ __('-- Pilih Provinsi --') }}"
                            search-placeholder="Cari provinsi..."
                            x-model="wilayah.provinsi_kode"
                            @change="onProvinsiChange($event)"
                            required
                        >
                            <option value="">{{ __('-- Pilih Provinsi --') }}</option>
                            <template x-for="item in listProvinsi" :key="item.code">
                                <option :value="item.code" x-text="item.name" :selected="item.code === wilayah.provinsi_kode"></option>
                            </template>
                        </x-form.searchable-select>
                        @error('provinsi_kode')
                            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kabupaten / Kota --}}
                    <div>
                        <input type="hidden" name="kabupaten_nama" :value="wilayah.kabupaten_nama" />
                        <x-form.searchable-select
                            id="kabupaten_kode"
                            name="kabupaten_kode"
                            :label="__('Kabupaten / Kota')"
                            placeholder="{{ __('-- Pilih Kabupaten/Kota --') }}"
                            search-placeholder="Cari kabupaten/kota..."
                            x-model="wilayah.kabupaten_kode"
                            @change="onKabupatenChange($event)"
                            x-bind:disabled="!wilayah.provinsi_kode"
                            required
                        >
                            <option value="">{{ __('-- Pilih Kabupaten/Kota --') }}</option>
                            <template x-for="item in listKabupaten" :key="item.code">
                                <option :value="item.code" x-text="item.name" :selected="item.code === wilayah.kabupaten_kode"></option>
                            </template>
                        </x-form.searchable-select>
                        <div x-show="errorKabupaten" x-cloak class="mt-1.5 flex items-center gap-1.5 text-xs text-error-600 dark:text-error-400">
                            <i class="fi fi-rr-info shrink-0"></i>
                            <span x-text="errorKabupaten"></span>
                            <button type="button" @click="retryFetchKabupaten()" class="font-medium underline hover:text-error-700 dark:hover:text-error-300">{{ __('Coba lagi') }}</button>
                        </div>
                        @error('kabupaten_kode')
                            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kecamatan --}}
                    <div>
                        <input type="hidden" name="kecamatan_nama" :value="wilayah.kecamatan_nama" />
                        <x-form.searchable-select
                            id="kecamatan_kode"
                            name="kecamatan_kode"
                            :label="__('Kecamatan')"
                            placeholder="{{ __('-- Pilih Kecamatan --') }}"
                            search-placeholder="Cari kecamatan..."
                            x-model="wilayah.kecamatan_kode"
                            @change="onKecamatanChange($event)"
                            x-bind:disabled="!wilayah.kabupaten_kode"
                            required
                        >
                            <option value="">{{ __('-- Pilih Kecamatan --') }}</option>
                            <template x-for="item in listKecamatan" :key="item.code">
                                <option :value="item.code" x-text="item.name" :selected="item.code === wilayah.kecamatan_kode"></option>
                            </template>
                        </x-form.searchable-select>
                        <div x-show="errorKecamatan" x-cloak class="mt-1.5 flex items-center gap-1.5 text-xs text-error-600 dark:text-error-400">
                            <i class="fi fi-rr-info shrink-0"></i>
                            <span x-text="errorKecamatan"></span>
                            <button type="button" @click="retryFetchKecamatan()" class="font-medium underline hover:text-error-700 dark:hover:text-error-300">{{ __('Coba lagi') }}</button>
                        </div>
                        @error('kecamatan_kode')
                            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Desa / Kelurahan --}}
                    <div>
                        <input type="hidden" name="desa_nama" :value="wilayah.desa_nama" />
                        <x-form.searchable-select
                            id="desa_kode"
                            name="desa_kode"
                            :label="__('Desa / Kelurahan')"
                            placeholder="{{ __('-- Pilih Desa/Kelurahan --') }}"
                            search-placeholder="Cari desa/kelurahan..."
                            x-model="wilayah.desa_kode"
                            @change="onDesaChange($event)"
                            x-bind:disabled="!wilayah.kecamatan_kode"
                            required
                        >
                            <option value="">{{ __('-- Pilih Desa/Kelurahan --') }}</option>
                            <template x-for="item in listDesa" :key="item.code">
                                <option :value="item.code" x-text="item.name" :selected="item.code === wilayah.desa_kode"></option>
                            </template>
                        </x-form.searchable-select>
                        <div x-show="errorDesa" x-cloak class="mt-1.5 flex items-center gap-1.5 text-xs text-error-600 dark:text-error-400">
                            <i class="fi fi-rr-info shrink-0"></i>
                            <span x-text="errorDesa"></span>
                            <button type="button" @click="retryFetchDesa()" class="font-medium underline hover:text-error-700 dark:hover:text-error-300">{{ __('Coba lagi') }}</button>
                        </div>
                        @error('desa_kode')
                            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- RT & RW --}}
                    <div>
                        <label for="rt" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('RT') }}
                        </label>
                        <input type="text" id="rt" name="rt" inputmode="numeric" maxlength="5"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            value="{{ old('rt', $alamat?->rt) }}" placeholder="001"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya angka.') }}</p>
                        @error('rt')
                            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rw" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('RW') }}
                        </label>
                        <input type="text" id="rw" name="rw" inputmode="numeric" maxlength="5"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            value="{{ old('rw', $alamat?->rw) }}" placeholder="002"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <p class="mt-1 text-theme-xs text-gray-400">{{ __('Hanya angka.') }}</p>
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
