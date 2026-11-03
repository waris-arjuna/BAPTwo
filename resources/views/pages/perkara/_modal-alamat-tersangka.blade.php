{{-- resources/views/pages/perkara/_modal-alamat-tersangka.blade.php --}}
{{-- Modal Pemilihan Alamat Tersangka Bertingkat (38 Provinsi via API Wilayah) --}}
<div
    x-show="isAlamatModalOpen"
    x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-6"
>
    <div
        class="fixed inset-0 h-full w-full bg-gray-900/60 backdrop-blur-xs"
        @click="isAlamatModalOpen = false"
    ></div>

    <div
        class="relative z-10 w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        @click.stop
    >
        <button
            type="button"
            @click="isAlamatModalOpen = false"
            class="absolute top-5 end-5 flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
            aria-label="Tutup modal"
        >
            <i class="fi fi-rr-cross text-sm" aria-hidden="true"></i>
        </button>

        <div class="mb-5 pe-10">
            <div class="flex items-center gap-2">
                <i class="fi fi-rr-marker text-lg text-brand-500"></i>
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Atur Alamat Tersangka
                </h4>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Pilih wilayah bertingkat (38 provinsi seluruh Indonesia) dan lengkapi rincian alamat tersangka.
            </p>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Dropdown Provinsi --}}
                <div>
                    <x-form.searchable-select
                        id="modal_tersangka_provinsi_kode"
                        name="modal_tersangka_provinsi_kode"
                        label="Provinsi"
                        :value="$tersangkaProvinsiKode"
                        placeholder="-- Pilih Provinsi --"
                        search-placeholder="Cari provinsi..."
                        x-model="modalWilayah.provinsi_kode"
                        @change="onProvinsiChange($event)"
                    >
                        <option value="">-- Pilih Provinsi --</option>
                        <template x-for="item in listProvinsi" :key="item.code">
                            <option :value="item.code" x-text="item.name" :selected="String(item.code) === String(modalWilayah.provinsi_kode)"></option>
                        </template>
                    </x-form.searchable-select>
                </div>

                {{-- Dropdown Kabupaten / Kota --}}
                <div>
                    <x-form.searchable-select
                        id="modal_tersangka_kabupaten_kode"
                        name="modal_tersangka_kabupaten_kode"
                        label="Kabupaten / Kota"
                        :value="$tersangkaKabupatenKode"
                        placeholder="-- Pilih Kabupaten/Kota --"
                        search-placeholder="Cari kabupaten/kota..."
                        x-model="modalWilayah.kabupaten_kode"
                        @change="onKabupatenChange($event)"
                        x-bind:disabled="!modalWilayah.provinsi_kode"
                    >
                        <option value="">-- Pilih Kabupaten/Kota --</option>
                        <template x-for="item in listKabupaten" :key="item.code">
                            <option :value="item.code" x-text="item.name" :selected="String(item.code) === String(modalWilayah.kabupaten_kode)"></option>
                        </template>
                    </x-form.searchable-select>
                </div>

                {{-- Dropdown Kecamatan --}}
                <div>
                    <x-form.searchable-select
                        id="modal_tersangka_kecamatan_kode"
                        name="modal_tersangka_kecamatan_kode"
                        label="Kecamatan"
                        :value="$tersangkaKecamatanKode"
                        placeholder="-- Pilih Kecamatan --"
                        search-placeholder="Cari kecamatan..."
                        x-model="modalWilayah.kecamatan_kode"
                        @change="onKecamatanChange($event)"
                        x-bind:disabled="!modalWilayah.kabupaten_kode"
                    >
                        <option value="">-- Pilih Kecamatan --</option>
                        <template x-for="item in listKecamatan" :key="item.code">
                            <option :value="item.code" x-text="item.name" :selected="String(item.code) === String(modalWilayah.kecamatan_kode)"></option>
                        </template>
                    </x-form.searchable-select>
                </div>

                {{-- Dropdown Desa / Kelurahan --}}
                <div>
                    <x-form.searchable-select
                        id="modal_tersangka_desa_kode"
                        name="modal_tersangka_desa_kode"
                        label="Desa / Kelurahan"
                        :value="$tersangkaDesaKode"
                        placeholder="-- Pilih Desa/Kelurahan --"
                        search-placeholder="Cari desa/kelurahan..."
                        x-model="modalWilayah.desa_kode"
                        @change="onDesaChange($event)"
                        x-bind:disabled="!modalWilayah.kecamatan_kode"
                    >
                        <option value="">-- Pilih Desa/Kelurahan --</option>
                        <template x-for="item in listDesa" :key="item.code">
                            <option :value="item.code" x-text="item.name" :selected="String(item.code) === String(modalWilayah.desa_kode)"></option>
                        </template>
                    </x-form.searchable-select>
                </div>

                {{-- RT & RW --}}
                <div class="grid grid-cols-2 gap-3 sm:col-span-2 sm:max-w-xs">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">RT</label>
                        <input
                            type="text"
                            x-model="modalRt"
                            maxlength="5"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            placeholder="001"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                        >
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">RW</label>
                        <input
                            type="text"
                            x-model="modalRw"
                            maxlength="5"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            placeholder="002"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                        >
                    </div>
                </div>

                {{-- Detail Alamat --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                        Detail Jalan / Rumah / Patokan <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        x-model="modalAlamatDetail"
                        rows="2"
                        placeholder="Contoh: Jl. Ahmad Yani No. 12, Komplek Melati Indah"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-500"
                    ></textarea>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2.5 border-t border-gray-100 pt-4 dark:border-gray-800">
            <button
                type="button"
                @click="isAlamatModalOpen = false"
                class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
            >
                Batal
            </button>
            <button
                type="button"
                @click="applyAlamat()"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-5 text-sm font-medium text-white transition hover:bg-brand-700"
            >
                <i class="fi fi-rr-check text-sm"></i>
                <span>Terapkan Alamat</span>
            </button>
        </div>
    </div>
</div>
