@props([
    'label' => 'Aksi',
    'width' => 'w-48',
    'align' => 'right',
])

<div
    x-data="{
        isOpen: false,
        openUp: false,
        coords: { top: 0, left: 0 },
        scrollListener: null,
        resizeListener: null,

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        },

        open() {
            this.isOpen = true;
            this.updatePosition();
            this.$nextTick(() => {
                this.updatePosition();
            });
        },

        close() {
            this.isOpen = false;
        },

        updatePosition() {
            if (!this.$refs.button) return;
            const btn = this.$refs.button.getBoundingClientRect();
            const panel = this.$refs.panel;

            const menuWidth = panel && panel.offsetWidth > 0 ? panel.offsetWidth : 192;
            const menuHeight = panel && panel.offsetHeight > 0 ? panel.offsetHeight : 190;

            const viewportHeight = window.innerHeight;
            const viewportWidth = window.innerWidth;
            const spaceBelow = viewportHeight - btn.bottom;
            const spaceAbove = btn.top;

            // Auto-flip: jika ruang di bawah tombol tidak cukup, dropdown terbuka ke atas
            this.openUp = spaceBelow < (menuHeight + 12) && spaceAbove > spaceBelow;

            let top = this.openUp ? (btn.top - menuHeight - 6) : (btn.bottom + 6);
            let left = '{{ $align }}' === 'left' ? btn.left : (btn.right - menuWidth);

            // Batas layar (viewport safety margins)
            if (left < 10) left = 10;
            if (left + menuWidth > viewportWidth - 10) {
                left = viewportWidth - menuWidth - 10;
            }
            if (top < 10) top = 10;
            if (top + menuHeight > viewportHeight - 10) {
                top = viewportHeight - menuHeight - 10;
            }

            this.coords = {
                top: Math.round(top),
                left: Math.round(left)
            };
        },

        init() {
            // Tutup saat tabel atau halaman di-scroll (capture: true menangkap scroll di dalam container tabel overflow-x-auto)
            this.scrollListener = () => {
                if (this.isOpen) this.close();
            };
            window.addEventListener('scroll', this.scrollListener, true);

            this.resizeListener = () => {
                if (this.isOpen) this.close();
            };
            window.addEventListener('resize', this.resizeListener);
        },

        destroy() {
            window.removeEventListener('scroll', this.scrollListener, true);
            window.removeEventListener('resize', this.resizeListener);
        }
    }"
    x-init="init()"
    @keydown.escape.window="if (isOpen) close()"
    {{ $attributes->merge(['class' => 'relative inline-flex justify-center']) }}
>
    {{-- Trigger Button --}}
    <div x-ref="button" class="inline-flex">
        @if (isset($button))
            <div @click="toggle()" class="cursor-pointer">
                {{ $button }}
            </div>
        @elseif (isset($trigger))
            <div @click="toggle()" class="cursor-pointer">
                {{ $trigger }}
            </div>
        @else
            <button
                type="button"
                @click="toggle()"
                aria-haspopup="true"
                :aria-expanded="isOpen.toString()"
                class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
            >
                <span>{{ __($label) }}</span>
                <i
                    class="fi fi-rr-angle-small-down text-base transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                ></i>
            </button>
        @endif
    </div>

    {{-- Dropdown Panel via Portal to Body --}}
    <template x-teleport="body">
        <div
            x-ref="panel"
            x-show="isOpen"
            x-cloak
            @click.outside="if (!$refs.button.contains($event.target)) close()"
            @click="close()"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed z-99999 {{ $width }} rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
            :style="`top: ${coords.top}px; left: ${coords.left}px;`"
            role="menu"
            aria-orientation="vertical"
        >
            <div class="space-y-0.5">
                {{ $slot->isEmpty() ? ($content ?? '') : $slot }}
            </div>
        </div>
    </template>
</div>
