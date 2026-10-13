<x-common.component-card title="Default Inputs">
    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Input
        </label>
        <input type="text"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Input with Placeholder
        </label>
        <input type="text" placeholder="info@gmail.com"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Select Input
        </label>
        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
            <select
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none ltr:pl-4 ltr:pr-11 rtl:pr-4 rtl:pl-11 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                    Select Option
                </option>
                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                    Marketing
                </option>
                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                    Template
                </option>
                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                    Development
                </option>
            </select>
            <span
                class="pointer-events-none absolute top-1/2 ltr:right-4 rtl:left-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                <i class="fi fi-rr-angle-small-down text-lg"></i>
            </span>
        </div>
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Password Input
        </label>
        <div x-data="{ showPassword: false }" class="relative">
            <input :type="showPassword ? 'text' : 'password'" placeholder="Enter your password"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 ltr:pr-11 ltr:pl-4 rtl:pl-11 rtl:pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            <span @click="showPassword = !showPassword"
                class="absolute top-1/2 ltr:right-4 rtl:left-4 z-30 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                <i x-show="!showPassword" class="fi fi-rr-eye text-base"></i>
                <i x-show="showPassword" class="fi fi-rr-eye-crossed text-base"></i>
            </span>
        </div>
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Date Picker Input
        </label>

        <x-form.date-picker 
            id="date_pick" 
            name="date_pick"
            placeholder="Date Picker" 
            defaultDate="{{ now()->format('Y-m-d') }}" 
        />
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Time Select Input
        </label>
        <div class="relative">
            <input type="time" placeholder="12:00 AM" onclick="this.showPicker()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 ltr:pr-11 ltr:pl-4 rtl:pl-11 rtl:pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            <span class="absolute top-1/2 ltr:right-3 rtl:left-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                <i class="fi fi-rr-clock text-base"></i>
            </span>
        </div>
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Input with Payment
        </label>

        <div class="relative">
            <input type="text" placeholder="Card number"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 ltr:pl-[62px] rtl:pr-[62px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            <span
                class="absolute top-1/2 ltr:left-0 rtl:right-0 flex h-11 w-[46px] -translate-y-1/2 items-center justify-center ltr:border-r rtl:border-l border-gray-200 dark:border-gray-800 text-gray-500 dark:text-gray-400">
                <i class="fi fi-rr-credit-card text-base"></i>
            </span>
        </div>
    </div>
</x-common.component-card>
