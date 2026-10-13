<x-common.component-card title="Input Group">
    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Email
        </label>
        <div class="relative">
            <span class="absolute top-1/2 ltr:left-0 rtl:right-0 -translate-y-1/2 ltr:border-r rtl:border-l border-gray-200 px-3.5 py-3 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                <i class="fi fi-rr-envelope text-base"></i>
            </span>
            <input type="text" placeholder="info@gmail.com" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 ltr:pl-[62px] rtl:pr-[62px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>

    <!-- Elements -->
    <div x-data="{
        selectedCountry: 'US',
        countryCodes: {
            'US': '+1',
            'GB': '+44',
            'CA': '+1',
            'AU': '+61'
        },
        phoneNumber: ''
    }">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Phone
        </label>
        <div class="relative">
            <div class="absolute ltr:left-0 rtl:right-0">
                <select x-model="selectedCountry" @change="phoneNumber = countryCodes[selectedCountry]"
                    class="focus:border-brand-300 focus:ring-brand-500/10 appearance-none ltr:rounded-l-lg rtl:rounded-r-lg rtl:rounded-l-none border-0 ltr:border-r rtl:border-l border-gray-200 bg-transparent bg-none py-3 ltr:pr-8 ltr:pl-3.5 rtl:pl-8 rtl:pr-3.5 leading-tight text-gray-700 focus:ring-3 focus:outline-hidden dark:border-gray-800 dark:text-gray-400">
                    <option value="US" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        US
                    </option>
                    <option value="GB" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        GB
                    </option>
                    <option value="CA" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        CA
                    </option>
                    <option value="AU" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        AU
                    </option>
                    <!-- Add more country codes as needed -->
                </select>
                <div
                    class="pointer-events-none absolute inset-y-0 ltr:right-3 rtl:left-3 flex items-center text-gray-700 dark:text-gray-400">
                    <i class="fi fi-rr-angle-small-down text-base"></i>
                </div>
            </div>
            <input placeholder="+1 (555) 000-0000" x-model="phoneNumber" type="tel"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-3 ltr:pr-4 ltr:pl-[84px] rtl:pl-4 rtl:pr-[84px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>

    <!-- Elements -->
    <div x-data="{
        selectedCountry: 'US',
        countryCodes: {
            'US': '+1',
            'GB': '+44',
            'CA': '+1',
            'AU': '+61'
        },
        phoneNumber: ''
    }">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Phone
        </label>
        <div class="relative">
            <div class="absolute ltr:right-0 rtl:left-0">
                <select x-model="selectedCountry" @change="phoneNumber = countryCodes[selectedCountry]"
                    class="focus:border-brand-300 focus:ring-brand-500/10 appearance-none ltr:rounded-r-lg rtl:rounded-l-lg rtl:rounded-r-none border-0 ltr:border-l rtl:border-r border-gray-200 bg-transparent bg-none py-3 ltr:pr-8 ltr:pl-3.5 rtl:pl-8 rtl:pr-3.5 leading-tight text-gray-700 focus:ring-3 focus:outline-hidden dark:border-gray-800 dark:text-gray-400">
                    <option value="US" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        US
                    </option>
                    <option value="GB" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        GB
                    </option>
                    <option value="CA" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        CA
                    </option>
                    <option value="AU" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        AU
                    </option>
                    <!-- Add more country codes as needed -->
                </select>
                <div
                    class="pointer-events-none absolute inset-y-0 ltr:right-3 rtl:left-3 flex items-center text-gray-700 dark:text-gray-400">
                    <i class="fi fi-rr-angle-small-down text-base"></i>
                </div>
            </div>
            <input placeholder="+1 (555) 000-0000" x-model="phoneNumber" type="tel"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-3 ltr:pl-4 ltr:pr-[84px] rtl:pr-4 rtl:pl-[84px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            URL
        </label>
        <div class="relative">
            <span
                class="absolute top-1/2 ltr:left-0 rtl:right-0 inline-flex h-11 -translate-y-1/2 items-center justify-center ltr:border-r rtl:border-l border-gray-200 py-3 ltr:pr-3 ltr:pl-3.5 rtl:pl-3 rtl:pr-3.5 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                http://
            </span>
            <input type="url" placeholder="www.tailadmin.com"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 ltr:pl-[90px] rtl:pr-[90px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>

    <!-- Elements -->
    <div id="copy-input">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Website
        </label>
        <div class="relative">
            <button id="copy-button"
                class="absolute top-1/2 ltr:right-0 rtl:left-0 inline-flex -translate-y-1/2 cursor-pointer items-center gap-1 ltr:border-l rtl:border-r border-gray-200 py-3 ltr:pr-3 ltr:pl-3.5 rtl:pl-3 rtl:pr-3.5 text-sm font-medium text-gray-700 dark:border-gray-800 dark:text-gray-400">
                <i class="fi fi-rr-copy text-base"></i>
                <div id="copy-text">Copy</div>
            </button>
            <input value="www.tailadmin.com" type="url" id="website-input"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-3 ltr:pr-[90px] ltr:pl-4 rtl:pl-[90px] rtl:pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>
</x-common.component-card>
