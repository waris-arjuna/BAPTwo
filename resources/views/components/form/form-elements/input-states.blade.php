<x-common.component-card 
  title="Input States"
  desc="Validation styles for error, success and disabled states on form controls."
>
    <div class="space-y-5 sm:space-y-6">
        <!-- Elements -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email
            </label>
            <div class="relative">
                <input type="text" value="demoemail"
                    class="dark:bg-dark-900 border-error-300 shadow-theme-xs focus:border-error-300 focus:ring-error-500/10 dark:border-error-700 dark:focus:border-error-800 w-full rounded-lg border bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                <span class="absolute top-1/2 right-3.5 -translate-y-1/2 text-error-500">
                    <i class="fi fi-rr-exclamation text-sm"></i>
                </span>
            </div>

            <p class="text-theme-xs text-error-500 mt-1.5">
                This is an error message.
            </p>
        </div>

        <!-- Elements -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email
            </label>
            <div class="relative">
                <input type="text" value="demoemail@gmail.com"
                    class="dark:bg-dark-900 border-success-300 shadow-theme-xs focus:border-success-300 focus:ring-success-500/10 dark:border-success-700 dark:focus:border-success-800 w-full rounded-lg border bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                <span class="absolute top-1/2 right-3.5 -translate-y-1/2 text-success-500">
                    <i class="fi fi-rr-check-circle text-sm"></i>
                </span>
            </div>

            <p class="text-theme-xs text-success-500 mt-1.5">
                This is an success message.
            </p>
        </div>

        <!-- Elements -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300 dark:text-white/15">
                Email
            </label>
            <input type="text" placeholder="info@gmail.com" disabled
                class="shadow-theme-xs focus:border-brand-300 focus:shadow-focus-ring dark:focus:border-brand-300 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden disabled:border-gray-100 disabled:placeholder:text-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-400 dark:disabled:border-gray-800 dark:disabled:placeholder:text-white/15" />
        </div>
    </div>
</x-common.component-card>
