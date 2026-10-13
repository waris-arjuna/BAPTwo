@props([
    'searchPlaceholder' => 'Cari data...',
    'statusOptions' => [],
    'showDate' => true,
    'hasActiveFilter' => null,
    'target' => '#table-body-partial',
])

@php
    $currentSearch = trim((string) request('search', ''));
    $currentStatus = trim((string) request('status', ''));
    $currentDate = trim((string) request('tanggal_dari', request('tanggal', '')));
    $isFiltered = $hasActiveFilter ?? ($currentSearch !== '' || $currentStatus !== '' || ($showDate && $currentDate !== ''));
@endphp

<div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
    <form
        method="GET"
        action="{{ url()->current() }}"
        x-data="tableFilter('{{ $target }}')"
        x-init="hasFilter = {{ $isFiltered ? 'true' : 'false' }}"
        @change="if ($event.target.name !== 'search') doFilter($el)"
        @submit.prevent="doFilter($el)"
        class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
    >
        {{-- Search Input (Left) --}}
        <div class="relative w-full lg:max-w-md">
            <span class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2">
                <i class="fi fi-rr-search text-base text-gray-400"></i>
            </span>

            <input
                type="text"
                name="search"
                value="{{ $currentSearch }}"
                placeholder="{{ __($searchPlaceholder) }}"
                @input.debounce.500ms="doFilter($el.form)"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent ps-10 pe-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500"
            />
        </div>

        {{-- Filter Controls (Right) --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            {{ $slot }}

            @if (! empty($statusOptions))
                <select
                    name="status"
                    class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">{{ __('Semua Status') }}</option>
                    @foreach ($statusOptions as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected($currentStatus === (string) $optValue)>
                            {{ __($optLabel) }}
                        </option>
                    @endforeach
                </select>
            @endif

            @if ($showDate)
                <x-form.date-picker
                    name="tanggal_dari"
                    id="filter-tanggal-dari-{{ uniqid() }}"
                    placeholder="{{ __('Dari Tanggal') }}"
                    defaultDate="{{ $currentDate ?: null }}"
                    class="w-full sm:w-44"
                />
            @endif

            <button
                type="submit"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]"
            >
                <i class="fi fi-rr-settings-sliders text-sm"></i>
                <span>{{ __('Filter') }}</span>
            </button>

            <a
                href="{{ url()->current() }}"
                x-show="hasFilter"
                x-cloak
                @click.prevent="resetFilter($el.closest('form'))"
                class="inline-flex h-11 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                <i class="fi fi-rr-refresh text-xs"></i>
                <span>{{ __('Reset') }}</span>
            </a>
        </div>
    </form>
</div>
