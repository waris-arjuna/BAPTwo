<div x-data="{
    saveProfile() {
        console.log('Saving profile...');
    }
}">
    <div class="rounded-2xl border border-gray-200 p-5 lg:p-6 dark:border-gray-800">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h4 class="text-lg font-semibold text-gray-800 lg:mb-6 dark:text-white/90">
                    Address
                </h4>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                            Country
                        </p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            United States
                        </p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                            City/State
                        </p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            Arizona, United States.
                        </p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                            Postal Code
                        </p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            ERT 2489
                        </p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                            TAX ID
                        </p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            AS4568384
                        </p>
                    </div>
                </div>
            </div>

            <button @click="$dispatch('open-profile-address-modal')" class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto">
                <i class="fi fi-rr-pencil text-base"></i>
                Edit
            </button>
        </div>
    </div>

    <x-ui.modal @open-profile-address-modal.window="open = true" :isOpen="false" class="max-w-[700px] p-4 lg:p-11">
        <div class="no-scrollbar">
            <div class="px-2 pe-14">
                <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Edit Address
                </h4>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                    Update your details to keep your profile up-to-date.
                </p>
            </div>
            <form class="flex flex-col">
                <div class="px-2 overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Country
                            </label>
                            <input type="text" value="United States"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                City/State
                            </label>
                            <input type="text" value="Poenix, Arizona, United States"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Postal Code
                            </label>
                            <input type="text" value="ERT 2489"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                TAX ID
                            </label>
                            <input type="text" value="AS4568384"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6 lg:justify-end">
                    <button @click="open = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                        Close
                    </button>
                    <button @click="saveProfile" type="button"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>
</div>
