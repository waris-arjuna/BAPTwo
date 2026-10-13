<x-common.component-card title="Checkboxes">
    <div class="flex flex-wrap items-center gap-8">
        <div x-data="{ checkboxToggle: false }">
            <label for="checkboxLabelOne"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="checkboxLabelOne" class="sr-only"
                        @change="checkboxToggle = !checkboxToggle" />
                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                        'bg-transparent border-gray-300 dark:border-gray-700'"
                        class="hover:border-brand-500 dark:hover:border-brand-500 ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                            <i class="fi fi-rr-check text-xs text-white"></i>
                        </span>
                    </div>
                </div>
                Default
            </label>
        </div>

        <div x-data="{ checkboxToggle: true }">
            <label for="checkboxLabelTwo"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="checkboxLabelTwo" class="sr-only"
                        @change="checkboxToggle = !checkboxToggle" />
                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                        'bg-transparent border-gray-300 dark:border-gray-700'"
                        class="hover:border-brand-500 dark:hover:border-brand-500 ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                            <i class="fi fi-rr-check text-xs text-white"></i>
                        </span>
                    </div>
                </div>
                Checked
            </label>
        </div>

        <div x-data="{ checkboxToggle: true }">
            <label for="checkboxLabelThree"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-300 select-none dark:text-gray-700">
                <div class="relative">
                    <input type="checkbox" id="checkboxLabelThree" class="peer sr-only"
                        @change="checkboxToggle = !checkboxToggle" disabled />
                    <div :class="checkboxToggle ? 'bg-transparent border-gray-200 dark:border-gray-800' :
                        'border-brand-500 bg-brand-500'"
                        class="ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                            <i class="fi fi-rr-check text-xs text-gray-200 dark:text-gray-800"></i>
                        </span>
                    </div>
                </div>
                Disabled
            </label>
        </div>
    </div>
</x-common.component-card>
