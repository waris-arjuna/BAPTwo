@props([
    'id' => 'datepicker-' . uniqid(),
    'mode' => 'single', // 'single', 'multiple', 'range', 'time'
    'defaultDate' => null,
    'label' => null,
    'placeholder' => 'Dari Tanggal',
    'name' => null,
    'dateFormat' => 'Y-m-d',
])

<div {{ $attributes->merge(['class' => 'relative']) }} x-data="{
    flatpickrInstance: null,
    init() {
        this.$nextTick(() => {
            if (typeof flatpickr !== 'function') return;
            this.flatpickrInstance = flatpickr(this.$refs.dateInput, {
                mode: '{{ $mode }}',
                static: true,
                monthSelectorType: 'static',
                dateFormat: '{{ $dateFormat }}',
                defaultDate: {{ $defaultDate ? (is_array($defaultDate) ? json_encode($defaultDate) : "'" . $defaultDate . "'") : 'null' }},
                onChange: (selectedDates, dateStr, instance) => {
                    this.$refs.dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                    this.$dispatch('date-change', {
                        selectedDates,
                        dateStr,
                        instance
                    });
                }
            });
        });
    },
    destroy() {
        if (this.flatpickrInstance) {
            this.flatpickrInstance.destroy();
            this.flatpickrInstance = null;
        }
    }
}" x-init="init()" x-destroy="destroy()">
    @if($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}
        </label>
    @endif

    <div class="relative custom-datepicker">
        <input
            x-ref="dateInput"
            type="text"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $defaultDate ?? '' }}"
            placeholder="{{ __($placeholder) }}"
            class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-3.5 pe-10 text-sm text-gray-700 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:placeholder:text-gray-500"
            autocomplete="off"
        />
        <span class="pointer-events-none absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
            <i class="fi fi-rr-calendar text-base" aria-hidden="true"></i>
        </span>
    </div>
</div>
