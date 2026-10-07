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
                <svg :class="{ 'animate-spin': loading }" class="size-5 stroke-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
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
</div>
