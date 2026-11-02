@props([
    'name' => 'dokumen',
    'id' => 'dokumen_multi_upload',
    'label' => 'Unggah Salinan Dokumen Putusan (PDF & Foto)',
    'required' => false,
    'existingDocuments' => collect(),
    'maxTotalMb' => 10,
])

@php
    $inputId = $id;
    $maxBytes = $maxTotalMb * 1024 * 1024;
    $initialExisting = $existingDocuments->map(function ($doc) {
        $ext = strtolower(pathinfo($doc->nama_file_asli, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
        return [
            'id' => $doc->id,
            'name' => $doc->nama_file_asli,
            'size' => (int) $doc->file_size,
            'size_formatted' => $doc->file_size ? number_format($doc->file_size / 1024 / 1024, 2) . ' MB' : '0 MB',
            'is_image' => $isImage,
            'preview_url' => route('dokumen.preview', $doc),
            'download_url' => route('dokumen.download', $doc),
        ];
    })->values()->toArray();
@endphp

<div
    x-data="{
        maxBytes: {{ $maxBytes }},
        maxTotalMb: {{ $maxTotalMb }},
        isDragging: false,
        error: '',
        existingFiles: {{ Js::from($initialExisting) }},
        deletedIds: [],
        newFiles: [],
        dataTransfer: new DataTransfer(),

        init() {
            this.syncFiles();
        },

        get existingActiveSize() {
            return this.existingFiles
                .filter(f => !this.deletedIds.includes(f.id))
                .reduce((acc, f) => acc + (f.size || 0), 0);
        },

        get newFilesSize() {
            return this.newFiles.reduce((acc, f) => acc + (f.size || 0), 0);
        },

        get totalBytes() {
            return this.existingActiveSize + this.newFilesSize;
        },

        get totalMbFormatted() {
            return (this.totalBytes / 1024 / 1024).toFixed(2);
        },

        get quotaPercentage() {
            const pct = (this.totalBytes / this.maxBytes) * 100;
            return Math.min(Math.round(pct), 100);
        },

        formatSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        },

        isImage(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'webp'].includes(ext) || file.type.startsWith('image/');
        },

        isPdf(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            return ext === 'pdf' || file.type === 'application/pdf';
        },

        handleFiles(fileList) {
            this.error = '';
            const validExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

            for (let i = 0; i < fileList.length; i++) {
                const file = fileList[i];
                const ext = file.name.split('.').pop().toLowerCase();

                if (!validExtensions.includes(ext) && !this.isPdf(file) && !this.isImage(file)) {
                    this.error = 'File ' + file.name + ' tidak didukung. Hanya file PDF, JPG, JPEG, PNG, dan WebP yang diizinkan.';
                    continue;
                }

                // Cek apakah file sudah pernah dimasukkan
                const isDuplicate = this.newFiles.some(f => f.name === file.name && f.size === file.size);
                if (isDuplicate) {
                    continue;
                }

                if (this.totalBytes + file.size > this.maxBytes) {
                    this.error = 'Total ukuran berkas melebihi kuota maksimal ' + this.maxTotalMb + ' MB.';
                    break;
                }

                const previewUrl = this.isImage(file) ? URL.createObjectURL(file) : null;
                this.newFiles.push({
                    file: file,
                    name: file.name,
                    size: file.size,
                    is_image: this.isImage(file),
                    preview_url: previewUrl
                });

                this.dataTransfer.items.add(file);
            }

            this.syncFiles();
        },

        removeNewFile(index) {
            const item = this.newFiles[index];
            if (item && item.preview_url) {
                URL.revokeObjectURL(item.preview_url);
            }
            this.newFiles.splice(index, 1);

            // Rebuild dataTransfer
            this.dataTransfer = new DataTransfer();
            this.newFiles.forEach(item => {
                this.dataTransfer.items.add(item.file);
            });

            this.syncFiles();
        },

        markDeleted(id) {
            if (!this.deletedIds.includes(id)) {
                this.deletedIds.push(id);
            }
        },

        undoDelete(id) {
            const index = this.deletedIds.indexOf(id);
            if (index !== -1) {
                this.deletedIds.splice(index, 1);
            }
        },

        syncFiles() {
            if (this.$refs.fileInput) {
                this.$refs.fileInput.files = this.dataTransfer.files;
            }
        }
    }"
    class="space-y-4"
>
    {{-- Header Label & Indikator Kuota --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <label class="block text-sm font-semibold text-gray-800 dark:text-white/90">
                {{ $label }}
                @if ($required)
                    <span class="text-red-500">*</span>
                @endif
            </label>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Dapat memilih lebih dari 1 file sekaligus (PDF, JPG, JPEG, PNG, WebP).
            </p>
        </div>

        {{-- Kuota Bar --}}
        <div class="w-full sm:w-56">
            <div class="flex items-center justify-between text-xs font-medium">
                <span class="text-gray-600 dark:text-gray-300">Total Ukuran:</span>
                <span
                    :class="quotaPercentage > 90 ? 'text-red-600 dark:text-red-400 font-bold' : 'text-brand-600 dark:text-brand-400'"
                    x-text="totalMbFormatted + ' MB / ' + maxTotalMb + '.00 MB'"
                ></span>
            </div>
            <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                <div
                    class="h-full transition-[width] duration-300"
                    :class="quotaPercentage > 90 ? 'bg-red-500' : 'bg-brand-500'"
                    :style="'width: ' + quotaPercentage + '%'"
                ></div>
            </div>
        </div>
    </div>

    {{-- Hidden Inputs for Deleted Existing Documents --}}
    <template x-for="delId in deletedIds" :key="delId">
        <input type="hidden" name="deleted_dokumen_ids[]" :value="delId">
    </template>

    {{-- Real Hidden File Input --}}
    <input
        x-ref="fileInput"
        type="file"
        id="{{ $inputId }}"
        name="{{ $name }}[]"
        multiple
        accept="application/pdf,.pdf,image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
        class="sr-only"
        @change="handleFiles($event.target.files)"
    />

    {{-- Dropzone Area --}}
    <div
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
        :class="{
            'border-brand-500 bg-brand-50/40 dark:bg-brand-500/10': isDragging,
            'border-gray-300 hover:border-gray-400 dark:border-gray-700 dark:hover:border-gray-600 bg-gray-50/50 dark:bg-gray-900/40': !isDragging
        }"
        class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition cursor-pointer"
        @click="$refs.fileInput.click()"
    >
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-theme-xs dark:bg-gray-800">
            <i class="fi fi-rr-folder-upload text-2xl text-brand-500"></i>
        </div>

        <p class="mt-3 text-sm font-medium text-gray-800 dark:text-white/90">
            <span class="text-brand-600 hover:underline dark:text-brand-400">Klik untuk memilih file</span> atau seret file ke area ini
        </p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Format: PDF, JPG, PNG, atau WebP (Batas total akumulasi {{ $maxTotalMb }} MB)
        </p>
    </div>

    {{-- Pesan Error Validasi Client-side --}}
    <div x-show="error" x-cloak class="rounded-lg bg-red-50 p-3 text-xs text-red-600 dark:bg-red-500/10 dark:text-red-400 flex items-center gap-2">
        <i class="fi fi-rr-cross-circle shrink-0 text-sm"></i>
        <span x-text="error"></span>
    </div>

    {{-- 1. Daftar Dokumen Lama Tersimpan (Saat Mode Edit Draft) --}}
    <template x-if="existingFiles.length > 0">
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Dokumen Tersimpan Saat Ini (<span x-text="existingFiles.filter(f => !deletedIds.includes(f.id)).length"></span>)
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                <template x-for="doc in existingFiles" :key="doc.id">
                    <div
                        class="relative flex items-center justify-between gap-3 rounded-xl border p-3 transition"
                        :class="deletedIds.includes(doc.id)
                            ? 'border-red-200 bg-red-50/60 opacity-60 dark:border-red-900/50 dark:bg-red-950/20'
                            : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-theme-xs'"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <template x-if="doc.is_image">
                                <img :src="doc.preview_url" :alt="doc.name" class="h-10 w-10 shrink-0 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                            </template>
                            <template x-if="!doc.is_image">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-500 dark:bg-red-500/10">
                                    <i class="fi fi-rr-file-pdf text-xl"></i>
                                </div>
                            </template>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-gray-800 dark:text-white/90" :class="deletedIds.includes(doc.id) ? 'line-through text-gray-500' : ''" x-text="doc.name"></p>
                                <p class="text-[11px] text-gray-400" x-text="doc.size_formatted"></p>
                            </div>
                        </div>

                        <div>
                            <template x-if="!deletedIds.includes(doc.id)">
                                <button
                                    type="button"
                                    @click="markDeleted(doc.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                    title="Hapus berkas ini"
                                >
                                    <i class="fi fi-rr-trash text-sm"></i>
                                </button>
                            </template>

                            <template x-if="deletedIds.includes(doc.id)">
                                <button
                                    type="button"
                                    @click="undoDelete(doc.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-500 transition hover:bg-amber-50 dark:hover:bg-amber-500/10"
                                    title="Batalkan penghapusan"
                                >
                                    <i class="fi fi-rr-undo text-sm"></i>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- 2. Daftar File Baru Terpilih (Antrean Upload) --}}
    <template x-if="newFiles.length > 0">
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                    File Baru Terpilih (<span x-text="newFiles.length"></span>)
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                <template x-for="(item, idx) in newFiles" :key="idx">
                    <div class="relative flex items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50/30 p-3 shadow-theme-xs dark:border-brand-500/20 dark:bg-brand-500/5">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <template x-if="item.is_image">
                                <img :src="item.preview_url" :alt="item.name" class="h-10 w-10 shrink-0 rounded-lg object-cover border border-brand-200 dark:border-brand-800">
                            </template>
                            <template x-if="!item.is_image">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-500/20">
                                    <i class="fi fi-rr-file-pdf text-xl"></i>
                                </div>
                            </template>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-gray-800 dark:text-white/90" x-text="item.name"></p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400" x-text="formatSize(item.size)"></p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="removeNewFile(idx)"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                            title="Hapus dari antrean"
                        >
                            <i class="fi fi-rr-cross-small text-lg"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
