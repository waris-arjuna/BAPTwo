{{-- resources/views/components/ui/alert.blade.php --}}

@props([
    'variant' => 'info',
    'title' => '',
    'message' => '',
    'dismissible' => true,
    'autoDismiss' => false,
    'timeout' => 10000,
    'showLink' => false,
    'linkHref' => '#',
    'linkText' => 'Pelajari lebih lanjut',
])

@php
    $variantClasses = [
        'success' => [
            'container' => 'border-success-200 bg-success-50 dark:border-success-500/30 dark:bg-success-500/10',
            'iconBox' => 'bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-400',
            'title' => 'text-success-900 dark:text-white',
            'message' => 'text-success-800 dark:text-success-300',
            'close' => 'text-success-600 hover:bg-success-100 hover:text-success-800 dark:text-success-400 dark:hover:bg-success-500/20 dark:hover:text-success-200',
            'icon' => '<i class="fi fi-rr-check text-base" aria-hidden="true"></i>',
        ],
        'error' => [
            'container' => 'border-error-200 bg-error-50 dark:border-error-500/30 dark:bg-error-500/10',
            'iconBox' => 'bg-error-100 text-error-600 dark:bg-error-500/20 dark:text-error-400',
            'title' => 'text-error-900 dark:text-white',
            'message' => 'text-error-800 dark:text-error-300',
            'close' => 'text-error-600 hover:bg-error-100 hover:text-error-800 dark:text-error-400 dark:hover:bg-error-500/20 dark:hover:text-error-200',
            'icon' => '<i class="fi fi-rr-cross-circle text-base" aria-hidden="true"></i>',
        ],
        'warning' => [
            'container' => 'border-warning-200 bg-warning-50 dark:border-warning-500/30 dark:bg-warning-500/10',
            'iconBox' => 'bg-warning-100 text-warning-600 dark:bg-warning-500/20 dark:text-warning-400',
            'title' => 'text-warning-900 dark:text-white',
            'message' => 'text-warning-800 dark:text-warning-300',
            'close' => 'text-warning-600 hover:bg-warning-100 hover:text-warning-800 dark:text-warning-400 dark:hover:bg-warning-500/20 dark:hover:text-warning-200',
            'icon' => '<i class="fi fi-rr-triangle-warning text-base" aria-hidden="true"></i>',
        ],
        'info' => [
            'container' => 'border-brand-200 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10',
            'iconBox' => 'bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400',
            'title' => 'text-brand-900 dark:text-white',
            'message' => 'text-brand-800 dark:text-brand-300',
            'close' => 'text-brand-600 hover:bg-brand-100 hover:text-brand-800 dark:text-brand-400 dark:hover:bg-brand-500/20 dark:hover:text-brand-200',
            'icon' => '<i class="fi fi-rr-info text-base" aria-hidden="true"></i>',
        ],
    ];

    $cfg = $variantClasses[$variant] ?? $variantClasses['info'];
@endphp

<div
    @if ($dismissible || $autoDismiss)
        x-data="{
            show: true,
            timer: null,
            startTimer() {
                @if ($autoDismiss)
                    this.clearTimer();
                    this.timer = setTimeout(() => this.show = false, {{ (int) $timeout }});
                @endif
            },
            clearTimer() {
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
            }
        }"
        x-init="startTimer()"
        @mouseenter="clearTimer()"
        @mouseleave="startTimer()"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
    @endif
    class="rounded-xl border p-4 shadow-theme-xs {{ $cfg['container'] }}"
    role="alert"
>
    <div class="flex items-start gap-3">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $cfg['iconBox'] }}">
            {!! $cfg['icon'] !!}
        </div>

        <div class="flex-1 min-w-0 pt-1">
            @if($title)
                <h4 class="mb-1 text-sm font-semibold {{ $cfg['title'] }}">
                    {{ $title }}
                </h4>
            @endif

            @if($message)
                <p class="text-sm font-medium {{ $cfg['message'] }}">{{ $message }}</p>
            @endif

            @if($showLink)
                <a 
                    href="{{ $linkHref }}" 
                    class="inline-block mt-2 text-sm font-medium underline {{ $cfg['message'] }} hover:opacity-80"
                >
                    {{ $linkText }}
                </a>
            @endif

            {{ $slot }}
        </div>

        @if ($dismissible)
            <button
                type="button"
                @click="clearTimer(); show = false"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition {{ $cfg['close'] }}"
                aria-label="Tutup notifikasi"
            >
                <i class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
            </button>
        @endif
    </div>
</div>