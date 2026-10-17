@props([
    'id' => 'captcha',
    'name' => 'captcha',
    'label' => __('Verification'),
    'placeholder' => '?',
    'required' => true,
])

@php
    $captchaService = app(\App\Services\MathCaptchaService::class);
    $initialCaptcha = $captchaService->generate();
@endphp

<div x-data="{
    svg: @js($initialCaptcha['svg']),
    loading: false,
    timeLeft: 30,
    timer: null,
    isExpired: false,
    init() {
        this.startTimer();
    },
    startTimer() {
        this.timeLeft = 30;
        this.isExpired = false;
        clearInterval(this.timer);
        this.timer = setInterval(() => {
            if (this.timeLeft > 0) {
                this.timeLeft--;
            } else {
                this.isExpired = true;
                clearInterval(this.timer);
            }
        }, 1000);
    },
    async refreshCaptcha() {
        if (this.loading) return;
        this.loading = true;
        try {
            const response = await fetch('{{ route('captcha.refresh') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (response.ok) {
                const data = await response.json();
                if (data.svg) {
                    this.svg = data.svg;
                    this.startTimer();
                }
            }
        } catch (error) {
            console.error('Gagal memuat ulang captcha:', error);
        } finally {
            this.loading = false;
        }
    }
}" class="space-y-1.5">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
        {{ $label }}@if($required)<span class="text-error-500">*</span>@endif
    </label>

    <div class="flex flex-wrap items-center gap-3">
        <!-- SVG Container (240x80) -->
        <div class="relative flex h-[80px] w-[240px] shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-300 bg-gray-50 text-gray-800 select-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            x-html="svg">
            {!! $initialCaptcha['svg'] !!}
        </div>

        <div class="flex items-center gap-2">
            <!-- Tombol Refresh -->
            <button type="button"
                @click="refreshCaptcha()"
                :disabled="loading"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-transparent text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-hidden disabled:opacity-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                title="{{ __('Muat ulang captcha') }}"
                aria-label="{{ __('Muat ulang captcha') }}">
                <i :class="{ 'animate-spin': loading }" class="fi fi-rr-refresh text-base" aria-hidden="true"></i>
            </button>

            <!-- Input Jawaban -->
            <input type="number"
                name="{{ $name }}"
                id="{{ $id }}"
                inputmode="numeric"
                @if($required) required @endif
                autocomplete="off"
                placeholder="{{ $placeholder }}"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-24 rounded-lg border bg-transparent px-3 py-2.5 text-center text-sm font-semibold text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has($name) ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' : 'border-gray-300 dark:border-gray-700' }}" />
        </div>
    </div>

    @error($name)
        <p class="text-theme-xs text-error-500 mt-1">{{ $message }}</p>
    @enderror

    <!-- Notifikasi Captcha Kedaluwarsa (Poin 16) -->
    <div x-show="isExpired" x-cloak class="mt-2 flex items-center justify-between rounded-lg border border-warning-200 bg-warning-50 p-2.5 text-xs text-warning-800 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
        <div class="flex items-center gap-1.5">
            <i class="fi fi-rr-clock text-sm"></i>
            <span>{{ __('Captcha telah kedaluwarsa (30 detik). Silakan muat ulang captcha.') }}</span>
        </div>
        <button type="button" @click="refreshCaptcha()" class="font-semibold underline hover:text-warning-900 dark:hover:text-warning-200">
            {{ __('Muat Ulang') }}
        </button>
    </div>
    <div x-show="!isExpired" class="mt-1 flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500">
        <i class="fi fi-rr-time-fast text-xs"></i>
        <span>{{ __('Masa berlaku captcha:') }} <strong x-text="timeLeft + ' detik'"></strong></span>
    </div>
</div>
