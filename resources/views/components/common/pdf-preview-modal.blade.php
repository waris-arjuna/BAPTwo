{{-- resources/views/components/common/pdf-preview-modal.blade.php --}}
{{-- Modal Global Pratinjau Dokumen PDF Terpadu (In-App PDF Viewer) --}}
<div
    x-data="{
        isOpen: false,
        url: '',
        title: 'Pratinjau Dokumen PDF',
        downloadUrl: '',
        loading: true,
        hasError: false,

        openModal(detail) {
            this.url = detail.url || '';
            this.title = detail.title || 'Pratinjau Dokumen PDF';
            this.downloadUrl = detail.downloadUrl || (this.url ? this.url.replace('/preview', '/download') : '');
            this.loading = true;
            this.hasError = false;
            this.isOpen = true;
        },

        closeModal() {
            this.isOpen = false;
            this.url = '';
            this.loading = true;
            this.hasError = false;
        }
    }"
    @open-pdf-modal.window="openModal($event.detail)"
    @keydown.escape.window="if (isOpen) closeModal()"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-999999 flex items-center justify-center p-3 sm:p-5 md:p-6 overflow-hidden"
    role="dialog"
    aria-modal="true"
    :aria-label="title"
>
    {{-- Backdrop Gelap dengan Efek Blur --}}
    <div
        class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity duration-200"
        @click="closeModal()"
    ></div>

    {{-- Kotak Modal Utama --}}
    <div
        class="relative z-10 flex flex-col w-full max-w-5xl h-[88vh] rounded-2xl border border-gray-200 bg-white shadow-2xl overflow-hidden dark:border-gray-800 dark:bg-gray-900"
        @click.stop
    >
        {{-- Header Modal --}}
        <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50/90 px-4 py-3 sm:px-6 dark:border-gray-800 dark:bg-gray-800/60">
            <div class="flex items-center gap-3 min-w-0 pr-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <i class="fi fi-rr-document-signed text-base" aria-hidden="true"></i>
                </span>
                <div class="min-w-0">
                    <h3 class="truncate text-sm sm:text-base font-semibold text-gray-800 dark:text-white/90" x-text="title">
                        Pratinjau Dokumen PDF
                    </h3>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                        Dokumen Resmi Penanganan Perkara
                    </p>
                </div>
            </div>

            {{-- Tombol Aksi Header --}}
            <div class="flex items-center gap-2 shrink-0">
                {{-- Tombol Buka di Tab Baru --}}
                <a
                    :href="url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    title="Buka dokumen di tab baru browser"
                >
                    <i class="fi fi-rr-arrow-up-right-from-square text-xs" aria-hidden="true"></i>
                    <span class="hidden sm:inline">Tab Baru</span>
                </a>

                {{-- Tombol Unduh Berkas --}}
                <a
                    :href="downloadUrl"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3 text-theme-xs font-medium text-white transition hover:bg-brand-600 shadow-theme-xs"
                    title="Unduh file dokumen PDF"
                >
                    <i class="fi fi-rr-download text-xs" aria-hidden="true"></i>
                    <span class="hidden sm:inline">Unduh</span>
                </a>

                {{-- Tombol Tutup --}}
                <button
                    type="button"
                    @click="closeModal()"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-200 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    aria-label="Tutup pratinjau dokumen"
                >
                    <i class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        {{-- Body Modal: Area Viewer PDF --}}
        <div class="relative flex-1 bg-gray-100 dark:bg-gray-950 overflow-hidden flex flex-col">
            {{-- Loading State --}}
            <div
                x-show="loading"
                class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/90 backdrop-blur-xs dark:bg-gray-900/90"
            >
                <div class="flex flex-col items-center gap-3">
                    <span class="inline-block h-8 w-8 animate-spin rounded-full border-3 border-solid border-brand-500 border-r-transparent motion-reduce:animate-[spin_1.5s_linear_infinite]"></span>
                    <p class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                        Memuat pratinjau dokumen PDF...
                    </p>
                </div>
            </div>

            {{-- Fallback Box (Jika iframe terhambat / mode mobile) --}}
            <div
                x-show="hasError"
                class="absolute inset-0 z-10 flex flex-col items-center justify-center p-6 text-center bg-white dark:bg-gray-900"
            >
                <div class="max-w-md space-y-3">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                        <i class="fi fi-rr-exclamation text-xl"></i>
                    </div>
                    <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Pratinjau Dalam Peramban Terbatas
                    </h4>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                        Peramban Anda membatasi penyematan berkas PDF langsung di dalam halaman. Silakan buka dokumen di tab baru atau unduh langsung ke komputer Anda.
                    </p>
                    <div class="flex items-center justify-center gap-3 pt-2">
                        <a
                            :href="url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <i class="fi fi-rr-arrow-up-right-from-square text-xs"></i>
                            <span>Buka di Tab Baru</span>
                        </a>
                        <a
                            :href="downloadUrl"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 text-theme-xs font-medium text-white hover:bg-brand-600"
                        >
                            <i class="fi fi-rr-download text-xs"></i>
                            <span>Unduh Dokumen</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Frame PDF --}}
            <template x-if="url">
                <iframe
                    :src="url"
                    class="w-full flex-1 border-0 bg-gray-100 dark:bg-gray-950"
                    x-on:load="loading = false"
                    x-on:error="hasError = true; loading = false"
                ></iframe>
            </template>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Delegated click listener untuk link dokumen dengan atribut data-pdf-preview
        if (!window.__pdfPreviewListenerBound) {
            window.__pdfPreviewListenerBound = true;
            document.addEventListener('click', function(e) {
                const target = e.target.closest('[data-pdf-preview]');
                if (target && !e.ctrlKey && !e.metaKey && !e.shiftKey && e.button === 0) {
                    e.preventDefault();
                    const url = target.getAttribute('data-pdf-preview') || target.getAttribute('href');
                    const title = target.getAttribute('data-pdf-title') || 'Pratinjau Dokumen PDF';
                    const downloadUrl = target.getAttribute('data-pdf-download') || (url ? url.replace('/preview', '/download') : '');

                    window.dispatchEvent(new CustomEvent('open-pdf-modal', {
                        detail: {
                            url: url,
                            title: title,
                            downloadUrl: downloadUrl
                        }
                    }));
                }
            });
        }
    </script>
@endpush
