<div
    x-show="isPenangkapanModalOpen"
    x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-data="{
        files: [],
        totalSize: 0,
        maxBytes: 5242880,
        errorMessage: '',
        handleFileChange(event) {
            const newFiles = Array.from(event.target.files);
            for (let f of newFiles) {
                if (f.type !== 'application/pdf' && !f.name.toLowerCase().endsWith('.pdf')) {
                    this.errorMessage = 'Seluruh file berkas wajib berformat PDF.';
                    return;
                }
                if (!this.files.some(existing => existing.name === f.name && existing.size === f.size)) {
                    this.files.push(f);
                }
            }
            this.syncFilesToInput();
        },
        removeFile(index) {
            this.files.splice(index, 1);
            this.syncFilesToInput();
        },
        syncFilesToInput() {
            const dt = new DataTransfer();
            let total = 0;
            this.files.forEach(f => {
                dt.items.add(f);
                total += f.size;
            });
            this.totalSize = total;
            if (this.$refs.fileInput) {
                this.$refs.fileInput.files = dt.files;
            }
            if (this.totalSize > this.maxBytes) {
                this.errorMessage = 'Total ukuran gabungan berkas melebihi batas 5 MB (' + this.formatSize(this.totalSize) + '). Harap kurangi berkas.';
            } else {
                this.errorMessage = '';
            }
        },
        formatSize(bytes) {
            if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            }
            if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            }
            return bytes + ' B';
        },
        resetForm() {
            this.files = [];
            this.totalSize = 0;
            this.errorMessage = '';
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        }
    }"
    @open-penangkapan-modal.window="resetForm()"
>
    <div
        @click.outside="closePenangkapanModal()"
        class="relative w-full max-w-xl max-h-[90vh] flex flex-col rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        {{-- Header Modal --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2.5 text-success-600 dark:text-success-400">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                    <i class="fi fi-rr-shield-check text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Catat Penangkapan &amp; Penahanan DPO
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pencatatan resmi penangkapan dan penahanan tersangka DPO
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="closePenangkapanModal()"
                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Tutup modal"
            >
                <i class="fi fi-rr-cross-small text-lg"></i>
            </button>
        </div>

        {{-- Form Content --}}
        <form :action="penangkapanActionUrl" method="POST" enctype="multipart/form-data" class="flex flex-1 flex-col overflow-hidden">
            @csrf

            <div class="custom-scrollbar flex-1 space-y-5 overflow-y-auto px-6 py-5">
                {{-- Banner Peringatan --}}
                <div class="rounded-xl border border-success-200 bg-success-50/70 p-3.5 text-xs text-success-800 dark:border-success-900/40 dark:bg-success-950/20 dark:text-success-300">
                    Tersangka: <strong x-text="penangkapanTersangkaNama" class="font-semibold text-success-900 dark:text-success-200"></strong>. Setelah penangkapan dicatat, status DPO otomatis dinonaktifkan dan tahapan perkara dapat dilanjutkan kembali.
                </div>

                {{-- Bagian 1: Data Penangkapan (Wajib) --}}
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="mb-3 flex items-center gap-2 text-xs font-semibold text-gray-800 dark:text-white/90">
                        <i class="fi fi-rr-badge-check text-success-500"></i>
                        <span>1. Surat Perintah Penangkapan (Sp.Kap) <span class="text-red-500">*</span></span>
                    </div>

                    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="penangkapan_nomor_surat" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Nomor Surat Perintah Penangkapan <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="penangkapan_nomor_surat"
                                name="nomor_surat_penangkapan"
                                required
                                placeholder="Contoh: Sp.Kap/05/X/2026/BPOM"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-success-500 focus:ring-2 focus:ring-success-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <x-form.date-picker
                                id="penangkapan_tanggal_pelaksanaan"
                                name="tanggal_penangkapan"
                                label="Tanggal Pelaksanaan Penangkapan"
                                placeholder="Pilih tanggal penangkapan..."
                                required
                                defaultDate="{{ date('Y-m-d') }}"
                            />
                        </div>
                    </div>
                </div>

                {{-- Bagian 2: Data Penahanan (Wajib) --}}
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="mb-1 flex items-center gap-2 text-xs font-semibold text-gray-800 dark:text-white/90">
                        <i class="fi fi-rr-lock text-brand-500"></i>
                        <span>2. Surat Perintah Penahanan (Sp.Han) <span class="text-red-500">*</span></span>
                    </div>  

                    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                        <div>
                            <label for="penahanan_nomor_surat" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Nomor Surat Perintah Penahanan <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="penahanan_nomor_surat"
                                name="nomor_surat_penahanan"
                                required
                                placeholder="Contoh: Sp.Han/05/X/2026/BPOM"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                            >
                        </div>

                        <div>
                            <x-form.date-picker
                                id="penahanan_tanggal_pelaksanaan"
                                name="tanggal_penahanan"
                                label="Tanggal Pelaksanaan Penahanan"
                                placeholder="Pilih tanggal penahanan..."
                                required
                                defaultDate="{{ date('Y-m-d') }}"
                            />
                        </div>
                    </div>
                </div>

                {{-- Bagian 3: Multi-Upload Berkas PDF (Wajib Minimal 1 File, Total Maks. 5 MB) --}}
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="mb-1 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-800 dark:text-white/90">
                            <i class="fi fi-rr-document text-brand-500"></i>
                            <span>3. Berkas Dokumen PDF <span class="text-red-500">*</span></span>
                        </div>
                        <span
                            class="text-[11px] font-medium"
                            :class="totalSize > maxBytes ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'"
                        >
                            Total: <strong x-text="formatSize(totalSize)">0 B</strong> / 5.00 MB
                        </span>
                    </div>

                    {{-- Hidden Native File Input --}}
                    <input
                        type="file"
                        x-ref="fileInput"
                        name="dokumen_penangkapan[]"
                        multiple
                        accept=".pdf,application/pdf"
                        class="hidden"
                        @change="handleFileChange($event)"
                    >

                    {{-- Tombol Pilih Berkas --}}
                    <div>
                        <button
                            type="button"
                            @click="$refs.fileInput.click()"
                            class="inline-flex items-center gap-2 rounded-lg border border-dashed border-gray-300 bg-white px-4 py-2.5 text-xs font-medium text-gray-700 transition hover:border-brand-500 hover:bg-brand-50/30 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:bg-brand-950/20"
                        >
                            <i class="fi fi-rr-upload text-sm"></i>
                            <span>Pilih Berkas PDF (Bisa Lebih dari 1)</span>
                        </button>
                    </div>

                    {{-- Pesan Peringatan Ukuran / Validasi --}}
                    <template x-if="errorMessage">
                        <div class="mt-2.5 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-2.5 text-xs text-red-700 dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-400">
                            <i class="fi fi-rr-exclamation text-sm shrink-0"></i>
                            <span x-text="errorMessage"></span>
                        </div>
                    </template>

                    {{-- Daftar Berkas yang Dipilih --}}
                    <div class="mt-3 space-y-2" x-show="files.length > 0">
                        <template x-for="(file, index) in files" :key="file.name + file.size + index">
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex min-w-0 items-center gap-2">
                                    <i class="fi fi-rr-file-pdf text-red-500 text-sm shrink-0"></i>
                                    <span class="truncate font-medium text-gray-800 dark:text-white/90" x-text="file.name"></span>
                                    <span class="text-[11px] text-gray-400 shrink-0" x-text="'(' + formatSize(file.size) + ')'"></span>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile(index)"
                                    class="ml-2 rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400"
                                    title="Hapus berkas ini"
                                >
                                    <i class="fi fi-rr-trash text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <p class="mt-2 text-[11px] text-gray-400">
                        Minimal 1 berkas PDF wajib diunggah sebelum menyimpan.
                    </p>
                </div>

                {{-- Bagian 4: Keterangan / Kronologi Penangkapan (Opsional) --}}
                <div>
                    <label for="penangkapan_keterangan" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                        Keterangan / Kronologi Penangkapan &amp; Penahanan (Opsional)
                    </label>
                    <textarea
                        id="penangkapan_keterangan"
                        name="keterangan_penangkapan"
                        rows="3"
                        placeholder="Contoh: Tersangka berhasil diamankan oleh tim penyidik bersama aparat kepolisian setempat, selanjutnya dilakukan penahanan..."
                        class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                    ></textarea>
                </div>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                <button
                    type="button"
                    @click="closePenangkapanModal()"
                    class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    :disabled="files.length === 0 || totalSize > maxBytes"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-success-600 px-4 text-xs font-medium text-white transition hover:bg-success-700 shadow-theme-xs disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <i class="fi fi-rr-check text-xs"></i>
                    <span>Simpan Data Penangkapan</span>
                </button>
            </div>
        </form>
    </div>
</div>
