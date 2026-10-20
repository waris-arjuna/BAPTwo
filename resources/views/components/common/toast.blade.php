{{-- resources/views/components/common/toast.blade.php --}}

@props([
    'top' => 'top-20',
])

@php
    $initialToasts = [];

    $normalize = function ($flash, string $type, string $defaultTitle) use (&$initialToasts) {
        if (empty($flash)) {
            return;
        }

        if (is_array($flash)) {
            $initialToasts[] = [
                'type' => $type,
                'title' => $flash['title'] ?? $defaultTitle,
                'message' => $flash['message'] ?? '',
                'duration' => $flash['duration'] ?? 3000,
            ];
        } else {
            $initialToasts[] = [
                'type' => $type,
                'title' => $defaultTitle,
                'message' => (string) $flash,
                'duration' => 3000,
            ];
        }
    };

    // 1. Session Flashes
    $normalize(session('success'), 'success', 'Berhasil');
    $normalize(session('profile_success'), 'success', 'Berhasil');
    $normalize(session('login_success'), 'success', 'Berhasil Masuk');
    $normalize(session('error'), 'error', 'Gagal');
    $normalize(session('warning'), 'warning', 'Perhatian');
    $normalize(session('info'), 'info', 'Informasi');
    $normalize(session('status'), 'info', 'Informasi');

    // 2. Ringkasan Error Validasi Formulir (kolom input inline tetap punya border merah & error text)
    if (isset($errors) && $errors->any()) {
        $errorCount = $errors->count();
        $initialToasts[] = [
            'type' => 'error',
            'title' => 'Periksa Isian Formulir',
            'message' => "Terdapat {$errorCount} isian yang perlu diperbaiki sebelum menyimpan formulir.",
            'duration' => 3000,
        ];
    }
@endphp

<div
    x-data="toastManager(@js($initialToasts))"
    class="fixed {{ $top }} right-4 sm:right-6 left-4 sm:left-auto sm:w-[380px] z-999999 flex flex-col gap-3 pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
            x-transition:enter-start="opacity-0 translate-x-8 motion-reduce:translate-x-0"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="group pointer-events-auto relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 w-full select-none sm:select-text cursor-pointer sm:cursor-default"
            @click="toggleTouchPause(toast.id)"
            :role="toast.type === 'error' || toast.type === 'warning' ? 'alert' : 'status'"
        >
            <div class="flex items-start gap-3">
                {{-- Kotak Ikon Semantik --}}
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                    :class="{
                        'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400': toast.type === 'success',
                        'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400': toast.type === 'error',
                        'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400': toast.type === 'warning',
                        'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400': toast.type === 'info',
                    }"
                >
                    <i x-show="toast.type === 'success'" class="fi fi-rr-check text-base leading-none"></i>
                    <i x-show="toast.type === 'error'" class="fi fi-rr-cross-circle text-base leading-none"></i>
                    <i x-show="toast.type === 'warning'" class="fi fi-rr-triangle-warning text-base leading-none"></i>
                    <i x-show="toast.type === 'info'" class="fi fi-rr-info text-base leading-none"></i>
                </div>

                {{-- Teks Notifikasi (XSS-safe via x-text) --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white" x-text="toast.title"></h4>
                    <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400 leading-relaxed break-words" x-text="toast.message"></p>
                </div>

                {{-- Tombol Tutup Manual --}}
                <button
                    type="button"
                    @click.stop="removeToast(toast.id)"
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition focus:outline-hidden focus:ring-2 focus:ring-brand-500/20"
                    aria-label="Tutup notifikasi"
                >
                    <i class="fi fi-rr-cross-small text-base leading-none"></i>
                </button>
            </div>

            {{-- Progress Bar Tipis Berbasis CSS Animation --}}
            <div class="absolute bottom-0 left-0 right-0 h-0.75 bg-gray-100 dark:bg-gray-800 overflow-hidden">
                <div
                    data-toast-progress
                    class="h-full group-hover:[animation-play-state:paused] group-focus-within:[animation-play-state:paused]"
                    :class="{
                        'bg-success-500': toast.type === 'success',
                        'bg-error-500': toast.type === 'error',
                        'bg-warning-500': toast.type === 'warning',
                        'bg-brand-500': toast.type === 'info',
                        '[animation-play-state:paused]': toast.isTouchPaused
                    }"
                    :style="'animation: toast-shrink ' + toast.duration + 'ms linear forwards;'"
                    @animationend="onAnimationEnd($event, toast)"
                ></div>
            </div>
        </div>
    </template>
</div>
