<div
    class="rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="flex flex-col gap-5 mb-6 sm:flex-row sm:justify-between">
        <div class="w-full">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Statistics
            </h3>
            <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
                Target you’ve set for each month
            </p>
        </div>

        <div class="flex items-start w-full gap-3 sm:justify-end">
            <div x-data="{ selected: 'overview' }"
                class="inline-flex w-fit items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900">

                @php
                    $options = [
                        ['value' => 'overview', 'label' => 'Overview'],
                        ['value' => 'sales', 'label' => 'Sales'],
                        ['value' => 'revenue', 'label' => 'Revenue'],
                    ];
                @endphp

                @foreach ($options as $option)
                    <button @click="selected = '{{ $option['value'] }}'"
                        :class="selected === '{{ $option['value'] }}' ?
                            'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' :
                            'text-gray-500 dark:text-gray-400'"
                        class="px-3 py-2 font-medium rounded-md text-theme-sm hover:text-gray-900 dark:hover:text-white">
                        {{ $option['label'] }}
                    </button>
                @endforeach
            </div>

            <div x-data="{
                init() {
                    flatpickr(this.$refs.datepicker, {
                        mode: 'range',
                        static: true,
                        monthSelectorType: 'static',
                        dateFormat: 'M j',
                        defaultDate: [new Date(Date.now() - 6 * 24 * 60 * 60 * 1000), new Date()],
                        prevArrow: '<i class=\'fi fi-rr-angle-small-left text-lg\'></i>',
                        nextArrow: '<i class=\'fi fi-rr-angle-small-right text-lg\'></i>',
                        onReady: (selectedDates, dateStr, instance) => {
                            instance.element.value = dateStr.replace('to', '-');
                            const customClass = instance.element.getAttribute('data-class');
                            if (instance.calendarContainer) {
                                instance.calendarContainer.classList.add(customClass);
                            }
                        },
                        onChange: (selectedDates, dateStr, instance) => {
                            instance.element.value = dateStr.replace('to', '-');
                        },
                    })
                }
            }" class="relative max-w-40">
                <input x-ref="datepicker" class="h-10 w-full max-w-11 rounded-lg border border-gray-200 bg-white py-2.5 pl-[34px] pr-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs focus:outline-hidden focus:ring-0 focus-visible:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 xl:max-w-fit xl:pl-11" placeholder="Select dates" data-class="flatpickr-right" readonly="readonly" />
                <div class="absolute inset-0 right-auto flex items-center pointer-events-none left-4">
                    <i class="fi fi-rr-calendar text-base text-gray-700 dark:text-gray-400"></i>
                </div>
            </div>

        </div>
    </div>
    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <div id="chartThree" class="-ml-4 min-w-[700px] pl-2 xl:min-w-full"></div>
    </div>
</div>

