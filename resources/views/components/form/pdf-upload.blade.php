@props([
    'name',
    'id' => null,
    'label' => null,
    'required' => false,
    'existingFile' => null,
    'existingUrl' => null,
    'helperText' => null,
    'maxSizeMb' => 5,
    'allowImages' => false,
])

@php
    $inputId = $id ?? $name;
    $acceptTypes = $allowImages
        ? 'application/pdf,.pdf,image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp'
        : 'application/pdf,.pdf';

    if ($helperText === null) {
        $formatDesc = $allowImages ? 'PDF atau Gambar (JPG/PNG/WebP)' : 'PDF';
        $helperText = $existingFile
            ? "Dokumen saat ini: {$existingFile}. Biarkan kosong jika tidak ingin mengubah. Format file: {$formatDesc} maks. {$maxSizeMb} MB"
            : "Format file: {$formatDesc} maks. {$maxSizeMb} MB";
    }
@endphp

<div
    x-data="{
        fileName: '',
        fileSize: '',
        error: '',
        allowImages: {{ $allowImages ? 'true' : 'false' }},
        clearFile() {
            this.$refs.fileInput.value = '';
            this.fileName = '';
            this.fileSize = '';
            this.error = '';
        },
        isImageFile(name) {
            if (!name) return false;
            const n = name.toLowerCase();
            return n.endsWith('.jpg') || n.endsWith('.jpeg') || n.endsWith('.png') || n.endsWith('.webp');
        },
        handleFileChange(event) {
            this.error = '';
            const file = event.target.files[0];
            if (!file) {
                this.clearFile();
                return;
            }

            const validExtensions = this.allowImages
                ? ['.pdf', '.jpg', '.jpeg', '.png', '.webp']
                : ['.pdf'];
            const hasValidExt = validExtensions.some(ext => file.name.toLowerCase().endsWith(ext));
            const isValidMime = this.allowImages
                ? (file.type === 'application/pdf' || file.type.startsWith('image/'))
                : (file.type === 'application/pdf');

            if (!hasValidExt && !isValidMime) {
                this.error = this.allowImages
                    ? 'File harus berformat PDF atau Gambar (JPG, JPEG, PNG, WEBP).'
                    : 'File harus berformat PDF murni.';
                if (typeof window.toast?.error === 'function') {
                    window.toast.error('Format Dokumen Tidak Sesuai', this.error);
                } else if (typeof window.showAppError === 'function') {
                    window.showAppError('Format Dokumen Tidak Sesuai', this.error);
                }
                this.clearFile();
                return;
            }

            if (file.size > {{ $maxSizeMb }} * 1024 * 1024) {
                this.error = 'Ukuran file melebihi batas maksimal {{ $maxSizeMb }} MB.';
                if (typeof window.toast?.error === 'function') {
                    window.toast.error('Ukuran Dokumen Terlalu Besar', this.error);
                } else if (typeof window.showAppError === 'function') {
                    window.showAppError('Ukuran Dokumen Terlalu Besar', this.error);
                }
                this.clearFile();
                return;
            }

            this.fileName = file.name;
            const sizeInKb = file.size / 1024;
            this.fileSize = sizeInKb > 1024
                ? (sizeInKb / 1024).toFixed(1) + ' MB'
                : Math.round(sizeInKb) + ' KB';
        }
    }"
    class="space-y-1.5"
>
    @if ($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            x-ref="fileInput"
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="file"
            accept="{{ $acceptTypes }}"
            class="sr-only"
            @change="handleFileChange($event)"
            @if ($required && ! $existingFile) required @endif
        >

        {{-- Baris Input Standar --}}
        <div class="flex h-11 items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900/40">
            <template x-if="fileName">
                {{-- State: File Baru Terpilih --}}
                <div class="flex min-w-0 flex-1 items-center justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <i :class="isImageFile(fileName) ? 'fi fi-rr-picture text-brand-500' : 'fi fi-rr-file-pdf text-red-500'" class="shrink-0 text-base"></i>
                        <span class="truncate font-medium text-gray-800 dark:text-white/90" x-text="fileName"></span>
                        <span class="shrink-0 text-xs text-gray-400" x-text="'(' + fileSize + ')'"></span>
                    </div>
                    <button
                        type="button"
                        @click="clearFile()"
                        class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-800 dark:hover:text-red-400"
                        title="Hapus file terpilih"
                    >
                        <i class="fi fi-rr-cross-small text-lg"></i>
                    </button>
                </div>
            </template>

            <template x-if="!fileName">
                {{-- State: Belum Ada File Baru Terpilih --}}
                <div class="flex min-w-0 flex-1 items-center justify-between gap-2">
                    <label
                        for="{{ $inputId }}"
                        class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        <i class="{{ $allowImages ? 'fi fi-rr-folder-upload' : 'fi fi-rr-file-pdf' }} text-base text-gray-400"></i>
                        <span class="truncate">
                            @if ($existingFile)
                                Dokumen saat ini: {{ $existingFile }}
                            @else
                                {{ $allowImages ? 'Pilih file PDF atau Gambar...' : 'Pilih file PDF...' }}
                            @endif
                        </span>
                    </label>

                    <label
                        for="{{ $inputId }}"
                        class="shrink-0 cursor-pointer rounded-md border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Browse
                    </label>
                </div>
            </template>
        </div>
    </div>

    {{-- Pesan Error Validasi Client-side --}}
    <p x-show="error" x-text="error" class="text-xs text-red-500" x-cloak></p>

    {{-- Helper text / Link dokumen lama jika ada --}}
    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
        @if ($helperText)
            <p>{{ $helperText }}</p>
        @endif

        @if ($existingUrl)
            <a
                href="{{ $existingUrl }}"
                target="_blank"
                class="inline-flex items-center gap-1 font-medium text-brand-600 hover:underline dark:text-brand-400"
                @if(str_ends_with(strtolower($existingFile ?? ''), '.pdf'))
                    data-pdf-preview
                    data-pdf-title="{{ $existingFile ?? 'Dokumen PDF' }}"
                @endif
            >
                <i class="fi fi-rr-eye text-[11px]"></i>
                <span>Lihat file saat ini</span>
            </a>
        @endif
    </div>
</div>
