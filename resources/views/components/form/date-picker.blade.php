@props([
    'id' => 'datepicker-' . uniqid(),
    'mode' => 'single', // 'single', 'multiple', 'range', 'time'
    'defaultDate' => null,
    'value' => null,
    'label' => null,
    'placeholder' => 'Dari Tanggal',
    'name' => null,
    'dateFormat' => 'Y-m-d',
    'maxDate' => null,
    'required' => false,
])

@php
    $rawCandidate = ($defaultDate !== null && $defaultDate !== '')
        ? $defaultDate
        : (($value !== null && $value !== '') ? $value : ($name ? old($name) : null));

    if ($rawCandidate instanceof \DateTimeInterface) {
        $resolvedDate = $rawCandidate->format('Y-m-d');
    } elseif (is_string($rawCandidate)) {
        $trimmed = trim($rawCandidate);
        if ($trimmed === '') {
            $resolvedDate = null;
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}/', $trimmed, $matches)) {
            $resolvedDate = $matches[0];
        } else {
            $resolvedDate = $trimmed;
        }
    } elseif (is_array($rawCandidate)) {
        $resolvedDate = $rawCandidate;
    } else {
        $resolvedDate = null;
    }

    $inputValue = is_array($resolvedDate) ? implode(', ', $resolvedDate) : ($resolvedDate ?? '');
@endphp

<div {{ $attributes->merge(['class' => 'relative']) }} x-data="{
    flatpickrInstance: null,
    initialDate: @js($resolvedDate),
    init() {
        this.$nextTick(() => {
            if (typeof flatpickr !== 'function' || !this.$refs.dateInput) return;
            this.flatpickrInstance = flatpickr(this.$refs.dateInput, {
                mode: '{{ $mode }}',
                static: true,
                monthSelectorType: 'static',
                dateFormat: '{{ $dateFormat }}',
                defaultDate: this.initialDate,
                @if($maxDate)
                maxDate: '{{ $maxDate }}',
                @endif
                onChange: (selectedDates, dateStr, instance) => {
                    this.$refs.dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                    this.$dispatch('date-change', {
                        selectedDates,
                        dateStr,
                        instance
                    });
                }
            });
            if (this.initialDate) {
                this.flatpickrInstance.setDate(this.initialDate, false);
            }
        });
    },
    destroy() {
        if (this.flatpickrInstance) {
            this.flatpickrInstance.destroy();
            this.flatpickrInstance = null;
        }
    }
}">
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
            value="{{ $inputValue }}"
            placeholder="{{ __($placeholder) }}"
            {{ $required ? 'required' : '' }}
            class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-3.5 pe-10 text-sm text-gray-700 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:placeholder:text-gray-500"
            autocomplete="off"
        />
        <span class="pointer-events-none absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
            <i class="fi fi-rr-calendar text-base" aria-hidden="true"></i>
        </span>
    </div>
</div>
