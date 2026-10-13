<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
        Multiple Select Options
    </label>

    <div x-data="{
        open: false,
        selected: [1, 3],
        options: [
            { id: 1, name: 'Option 1' },
            { id: 2, name: 'Option 2' },
            { id: 3, name: 'Option 3' },
            { id: 4, name: 'Option 4' },
            { id: 5, name: 'Option 5' }
        ],
        toggleOption(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(i => i !== id);
            } else {
                this.selected.push(id);
            }
        },
        isSelected(id) {
            return this.selected.includes(id);
        }
    }" class="relative" @click.away="open = false">
        <!-- Hidden input for form submission -->
        <input type="hidden" name="selected_options" :value="selected.join(',')" />

        <!-- Select Input with Selected Tags -->
        <div @click="open = !open"
            class="shadow-theme-xs flex min-h-11 cursor-pointer gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 transition dark:border-gray-700 dark:bg-gray-900">
            <!-- Selected Items as Tags -->
            <div class="flex flex-1 flex-wrap items-center gap-2">
                <template x-for="id in selected" :key="id">
                    <div
                        class="group flex items-center justify-center rounded-full border-[0.7px] border-transparent bg-gray-100 py-1 pr-2 pl-2.5 text-sm text-gray-800 hover:border-gray-200 dark:bg-gray-800 dark:text-white/90 dark:hover:border-gray-800">
                        <span x-text="options.find(o => o.id === id).name"></span>
                        <button type="button" @click.stop="toggleOption(id)"
                            class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            <i class="fi fi-rr-cross-small text-sm"></i>
                        </button>
                    </div>
                </template>

                <!-- Show placeholder when nothing is selected -->
                <span x-show="selected.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                    Select options...
                </span>
            </div>

            <!-- Dropdown Arrow -->
            <div class="flex items-start pt-1.5">
                <i class="fi fi-rr-angle-small-down text-base shrink-0 text-gray-500 transition-transform dark:text-gray-400"
                    :class="open ? 'rotate-180' : ''"></i>
            </div>
        </div>

        <!-- Dropdown Options List -->
        <div x-show="open"
            class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"
            style="max-height: 16rem">
            <div class="overflow-y-auto" style="max-height: 16rem">
                <template x-for="option in options" :key="option.id">
                    <div @click="toggleOption(option.id)"
                        class="cursor-pointer border-b border-gray-200 px-4 py-3 text-sm transition last:border-b-0 dark:border-gray-800">
                        <span class="text-gray-800 dark:text-white/90" x-text="option.name"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
