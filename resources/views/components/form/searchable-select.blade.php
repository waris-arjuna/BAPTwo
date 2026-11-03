@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'value' => null,
    'placeholder' => '-- Pilih --',
    'searchPlaceholder' => 'Ketik untuk mencari...',
    'options' => [],
    'required' => false,
    'disabled' => false,
    'clearable' => true,
    'emptyMessage' => 'Data tidak ditemukan',
    'loading' => false,
    'loadingMessage' => 'Memuat data...',
    'hint' => null,
    'error' => null,
])

@php
    $selectId = $id ?? ($name ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) : 'searchable_select_' . uniqid());
    $initialValue = old($name, $value ?? '');
@endphp

<div
    class="relative"
    x-data="{
        isOpen: false,
        search: '',
        options: [],
        selectedValue: @js((string) $initialValue),
        selectedLabel: '',
        placeholder: @js($placeholder),
        disabled: {{ $disabled ? 'true' : 'false' }},
        clearable: {{ $clearable ? 'true' : 'false' }},
        loading: {{ $loading ? 'true' : 'false' }},
        focusedIndex: -1,
        openUp: false,
        mutationObserver: null,

        init() {
            this.syncFromSelect();

            if (this.$refs.nativeSelect) {
                // Observer untuk mendeteksi perubahan dinamis (seperti template x-for wilayah atau AJAX)
                this.mutationObserver = new MutationObserver(() => {
                    this.syncFromSelect();
                });
                this.mutationObserver.observe(this.$refs.nativeSelect, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['disabled', 'selected', 'value']
                });
            }

            // Watcher untuk perubahan selectedValue
            this.$watch('selectedValue', (val) => {
                const found = this.options.find(o => String(o.value) === String(val));
                this.selectedLabel = (found && found.value !== '') ? found.label : '';
                if (this.$refs.nativeSelect && this.$refs.nativeSelect.value !== String(val)) {
                    this.$refs.nativeSelect.value = val;
                    this.$refs.nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        },

        destroy() {
            if (this.mutationObserver) {
                this.mutationObserver.disconnect();
            }
        },

        syncFromSelect() {
            if (!this.$refs.nativeSelect) return;
            const native = this.$refs.nativeSelect;
            this.disabled = native.disabled;

            const opts = Array.from(native.options).map(o => ({
                value: o.value,
                label: o.text.trim(),
                disabled: o.disabled
            }));
            this.options = opts;

            const currentVal = native.value !== undefined ? native.value : this.selectedValue;
            if (currentVal !== '' && currentVal !== null) {
                this.selectedValue = currentVal;
            }

            const found = opts.find(o => String(o.value) === String(this.selectedValue));
            this.selectedLabel = (found && found.value !== '') ? found.label : '';
            if (found && native.value !== String(this.selectedValue)) {
                native.value = this.selectedValue;
            }
        },

        get filteredOptions() {
            const list = this.options.filter(o => o.value !== '');
            if (!this.search || !this.search.trim()) return list;
            const q = this.search.toLowerCase().trim();
            return list.filter(o => o.label.toLowerCase().includes(q));
        },

        toggle() {
            if (this.disabled) return;
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        },

        open() {
            if (this.disabled) return;
            this.isOpen = true;
            this.search = '';
            this.focusedIndex = -1;
            this.calculatePosition();
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        close() {
            this.isOpen = false;
            this.search = '';
            this.focusedIndex = -1;
        },

        selectOption(option) {
            if (!option || option.disabled) return;
            this.selectedValue = option.value;
            this.selectedLabel = option.label;
            if (this.$refs.nativeSelect) {
                this.$refs.nativeSelect.value = option.value;
            }
            this.$nextTick(() => {
                if (this.$refs.nativeSelect) {
                    this.$refs.nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    this.$refs.nativeSelect.dispatchEvent(new Event('input', { bubbles: true }));
                }
                this.$dispatch('change', option.value);
            });
            this.close();
        },

        clear() {
            if (this.disabled) return;
            this.selectedValue = '';
            this.selectedLabel = '';
            if (this.$refs.nativeSelect) {
                this.$refs.nativeSelect.value = '';
            }
            this.$nextTick(() => {
                if (this.$refs.nativeSelect) {
                    this.$refs.nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    this.$refs.nativeSelect.dispatchEvent(new Event('input', { bubbles: true }));
                }
                this.$dispatch('change', '');
            });
        },

        calculatePosition() {
            if (!this.$refs.trigger) return;
            const rect = this.$refs.trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            this.openUp = spaceBelow < 260 && spaceAbove > spaceBelow;
        },

        focusNext() {
            const items = this.filteredOptions;
            if (items.length === 0) return;
            this.focusedIndex = (this.focusedIndex + 1) % items.length;
            this.scrollToFocused();
        },

        focusPrev() {
            const items = this.filteredOptions;
            if (items.length === 0) return;
            this.focusedIndex = (this.focusedIndex - 1 + items.length) % items.length;
            this.scrollToFocused();
        },

        selectFocused() {
            const items = this.filteredOptions;
            if (this.focusedIndex >= 0 && this.focusedIndex < items.length) {
                this.selectOption(items[this.focusedIndex]);
            } else if (items.length === 1) {
                this.selectOption(items[0]);
            }
        },

        scrollToFocused() {
            this.$nextTick(() => {
                if (!this.$refs.optionsList) return;
                const el = this.$refs.optionsList.querySelector(`[data-index='${this.focusedIndex}']`);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        }
    }"
    x-modelable="selectedValue"
    @click.outside="close()"
    @keydown.escape.window="close()"
    @searchable-select-loading.window="if ($event.detail?.id === '{{ $selectId }}' || $event.detail === '{{ $selectId }}') loading = (typeof $event.detail === 'object' && 'loading' in $event.detail ? !!$event.detail.loading : true)"
    @searchable-select-open.window="if ($event.detail?.id === '{{ $selectId }}' || $event.detail === '{{ $selectId }}') { $nextTick(() => open()); }"
    {{ $attributes->merge(['class' => 'w-full']) }}
>
    {{-- Label Field --}}
    @if ($label)
        <label for="{{ $selectId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if ($required)
                <span class="text-error-500">*</span>
            @endif
        </label>
    @endif

    {{-- Trigger Input Box --}}
    <div
        x-ref="trigger"
        @click="toggle()"
        role="combobox"
        :aria-expanded="isOpen"
        :aria-disabled="disabled"
        tabindex="0"
        @keydown.enter.prevent="toggle()"
        @keydown.space.prevent="toggle()"
        @keydown.down.prevent="open()"
        class="group shadow-theme-xs flex h-11 w-full cursor-pointer items-center justify-between rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
        :class="{
            'border-brand-500 ring-2 ring-brand-500/10': isOpen,
            'cursor-not-allowed opacity-60 bg-gray-100 dark:bg-gray-800': disabled
        }"
    >
        {{-- Selected Text / Placeholder --}}
        <div class="flex-1 truncate pr-2">
            <span x-show="selectedLabel" x-text="selectedLabel" class="text-gray-800 dark:text-white/90"></span>
            <span x-show="!selectedLabel" x-text="placeholder" class="text-gray-400 dark:text-gray-500"></span>
        </div>

        {{-- Action Buttons: Clear, Loading Spinner & Arrow --}}
        <div class="flex items-center gap-1.5 shrink-0">
            {{-- Tombol Clear --}}
            <button
                type="button"
                x-show="clearable && selectedValue && !disabled"
                x-cloak
                @click.stop="clear()"
                title="Kosongkan pilihan"
                class="flex h-5 w-5 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
            >
                <i class="fi fi-rr-cross-small text-sm leading-none"></i>
            </button>

            {{-- Loading Spinner saat async fetch --}}
            <span x-show="loading" x-cloak class="flex items-center text-brand-500">
                <i class="fi fi-rr-spinner animate-spin text-sm leading-none"></i>
            </span>

            {{-- Panah Dropdown --}}
            <span x-show="!loading" class="text-gray-400 transition-transform duration-200 dark:text-gray-500" :class="isOpen ? 'rotate-180 text-brand-500' : ''">
                <i class="fi fi-rr-angle-small-down text-base leading-none"></i>
            </span>
        </div>
    </div>

    {{-- Dropdown Search & Options Panel --}}
    <div
        x-show="isOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-98"
        class="absolute left-0 right-0 z-50 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-700 dark:bg-gray-900"
        :class="openUp ? 'bottom-full mb-1.5' : 'top-full mt-1.5'"
        style="min-width: 100%;"
    >
        {{-- Search Box di dalam Dropdown (Mirip Select2) --}}
        <div class="border-b border-gray-100 bg-gray-50/70 p-2 dark:border-gray-800 dark:bg-gray-800/50">
            <div class="relative flex items-center">
                <i class="fi fi-rr-search absolute left-3 text-xs text-gray-400 dark:text-gray-500 pointer-events-none"></i>
                <input
                    type="text"
                    x-ref="searchInput"
                    x-model="search"
                    @keydown.down.prevent="focusNext()"
                    @keydown.up.prevent="focusPrev()"
                    @keydown.enter.prevent="selectFocused()"
                    @keydown.escape.prevent="close()"
                    placeholder="{{ $searchPlaceholder }}"
                    class="h-9 w-full rounded-lg border border-gray-300 bg-white pl-8 pr-7 text-xs text-gray-800 placeholder:text-gray-400 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                >
                <button
                    type="button"
                    x-show="search.length > 0"
                    @click="search = ''; $refs.searchInput.focus()"
                    class="absolute right-2 flex h-5 w-5 items-center justify-center rounded-full text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                >
                    <i class="fi fi-rr-cross-small text-xs leading-none"></i>
                </button>
            </div>
        </div>

        {{-- Daftar Pilihan (Options List) --}}
        <div
            x-ref="optionsList"
            class="max-h-60 overflow-y-auto p-1 text-sm custom-scrollbar"
        >
            <template x-for="(opt, idx) in filteredOptions" :key="String(opt.value) + '-' + idx">
                <div
                    :data-index="idx"
                    @click="selectOption(opt)"
                    @mouseenter="focusedIndex = idx"
                    class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-xs transition sm:text-sm"
                    :class="{
                        'bg-brand-50 font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300': String(opt.value) === String(selectedValue),
                        'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white': focusedIndex === idx &amp;&amp; String(opt.value) !== String(selectedValue),
                        'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/60': String(opt.value) !== String(selectedValue) &amp;&amp; focusedIndex !== idx,
                        'opacity-50 cursor-not-allowed': opt.disabled
                    }"
                >
                    <span x-text="opt.label" class="truncate pr-2"></span>
                    <span x-show="String(opt.value) === String(selectedValue)" class="shrink-0 text-brand-600 dark:text-brand-400">
                        <i class="fi fi-rr-check text-xs"></i>
                    </span>
                </div>
            </template>

            {{-- Status Loading saat data sedang dimuat via AJAX --}}
            <div
                x-show="loading"
                x-cloak
                class="py-6 px-4 text-center text-xs text-brand-600 dark:text-brand-400"
            >
                <i class="fi fi-rr-spinner mb-1.5 inline-block animate-spin text-base"></i>
                <span class="block">{{ $loadingMessage }}</span>
            </div>

            {{-- Status Kosong jika Hasil Pencarian Tidak Ada --}}
            <div
                x-show="!loading && filteredOptions.length === 0"
                class="py-6 px-4 text-center text-xs text-gray-500 dark:text-gray-400"
            >
                <i class="fi fi-rr-search-alt mb-1.5 block text-base text-gray-400 dark:text-gray-500"></i>
                <span>{{ $emptyMessage }}</span>
            </div>
        </div>
    </div>

    {{-- Native Hidden Select Bridge (Menjamin Kompatibilitas Form & Validasi Laravel) --}}
    <select
        x-ref="nativeSelect"
        name="{{ $name }}"
        id="{{ $selectId }}"
        class="absolute inset-0 h-full w-full opacity-0 pointer-events-none -z-10"
        tabindex="-1"
        aria-hidden="true"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
    >
        @if (!empty($options))
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optKey => $optVal)
                @php
                    $optValue = is_array($optVal) ? ($optVal['value'] ?? $optVal['id'] ?? $optKey) : (is_object($optVal) ? ($optVal->value ?? $optVal->id ?? $optKey) : $optKey);
                    $optLabel = is_array($optVal) ? ($optVal['label'] ?? $optVal['nama'] ?? $optVal['name'] ?? $optVal) : (is_object($optVal) ? ($optVal->label ?? $optVal->nama ?? $optVal->name ?? $optVal) : $optVal);
                @endphp
                <option value="{{ $optValue }}" @selected((string)$optValue === (string)$initialValue)>
                    {{ $optLabel }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    {{-- Hint & Error Message --}}
    @if ($hint)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="mt-1.5 text-xs text-error-600 dark:text-error-400">{{ $error }}</p>
    @elseif ($name && isset($errors) && $errors->has($name))
        <p class="mt-1.5 text-xs text-error-600 dark:text-error-400">{{ $errors->first($name) }}</p>
    @endif
</div>
