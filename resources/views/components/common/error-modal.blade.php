{{-- resources/views/components/common/error-modal.blade.php --}}

<div
    x-data="{
        show: {{ $errors->any() || session('error') ? 'true' : 'false' }},
        title: @js($errors->any() ? 'Terdapat Kesalahan Pengisian Formulir' : (session('error') ? 'Pemberitahuan Kesalahan' : '')),
        message: @js($errors->any() ? 'Mohon periksa dan lengkapi kembali isian formulir berikut dengan benar:' : (session('error') ?? '')),
        errors: @js($errors->any() ? $errors->all() : []),
        init() {
            window.addEventListener('app-error', (e) => {
                this.title = e.detail?.title || 'Terjadi Kesalahan';
                this.message = e.detail?.message || 'Permintaan tidak dapat diproses. Silakan periksa kembali.';
                if (Array.isArray(e.detail?.errors)) {
                    this.errors = e.detail.errors;
                } else if (e.detail?.error) {
                    this.errors = [e.detail.error];
                } else {
                    this.errors = [];
                }
                this.show = true;
            });
        }
    }"
    x-show="show"
    x-cloak
    @keydown.escape.window="show = false"
    class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
>
    <div
        @click.outside="show = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-lg rounded-2xl border border-error-200 bg-white p-6 shadow-theme-xl dark:border-error-500/30 dark:bg-gray-900"
    >
        {{-- Tombol Tutup Silang --}}
        <button
            type="button"
            @click="show = false"
            class="absolute right-4 top-4 inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
            aria-label="Tutup dialog"
        >
            <i class="fi fi-rr-cross-small text-lg"></i>
        </button>

        {{-- Icon & Header --}}
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <i class="fi fi-rr-exclamation text-2xl"></i>
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90" x-text="title">
                    Terdapat Kesalahan
                </h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="message">
                    Mohon periksa kembali isian formulir.
                </p>
            </div>
        </div>

        {{-- Daftar Rincian Error --}}
        <div
            x-show="errors.length > 0"
            class="mt-4 max-h-60 overflow-y-auto rounded-xl border border-error-200 bg-error-50/70 p-3.5 text-xs text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300"
        >
            <div class="mb-1.5 flex items-center gap-1.5 font-semibold">
                <i class="fi fi-rr-cross-circle text-xs"></i>
                <span>Rincian kendala yang perlu diperbaiki:</span>
            </div>
            <ul class="ms-5 list-disc space-y-1">
                <template x-for="(err, idx) in errors" :key="idx">
                    <li x-text="err"></li>
                </template>
            </ul>
        </div>

        {{-- Footer / Tombol Aksi --}}
        <div class="mt-6 flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end">
            <button
                type="button"
                @click="show = false"
                class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-error-600 px-5 text-sm font-medium text-white transition hover:bg-error-700 sm:w-auto"
            >
                Mengerti &amp; Perbaiki
            </button>
        </div>
    </div>
</div>
