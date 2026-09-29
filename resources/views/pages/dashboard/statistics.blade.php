<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5 md:gap-6">

    {{-- Perkara Aktif --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        d="M6 3.75h8.5L19 8.25V20a.25.25 0 0 1-.25.25H6A1.25 1.25 0 0 1 4.75 19V5A1.25 1.25 0 0 1 6 3.75Z"
                    />
                    <path d="M14 3.75V9h5" />
                    <path d="M8 13h8M8 16h5" />
                </svg>
            </div>
        </div>

        <p class="mt-5 text-sm font-medium text-gray-500 dark:text-gray-400">
            Perkara Aktif
        </p>

        <div class="mt-2 flex items-end justify-between">
            <h4 class="text-2xl font-semibold text-gray-900 dark:text-white">
                2
            </h4>

            <span class="text-sm text-gray-500 dark:text-gray-400">
                Perkara aktif
            </span>
        </div>
    </div>


    {{-- Perkara Berjalan --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="12" r="8.5" />
                    <path d="m10 8.75 5 3.25-5 3.25v-6.5Z" />
                </svg>
            </div>
        </div>

        <p class="mt-5 text-sm font-medium text-gray-500 dark:text-gray-400">
            Perkara Berjalan
        </p>

        <div class="mt-2 flex items-end justify-between">
            <h4 class="text-2xl font-semibold text-gray-900 dark:text-white">
                2
            </h4>

            <span class="text-sm text-gray-500 dark:text-gray-400">
                Perkara berjalan
            </span>
        </div>
    </div>


    {{-- Menunggu Verifikasi --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="8" r="3.25" />
                    <path d="M5.5 19.25c.7-3.1 3.1-4.75 6.5-4.75s5.8 1.65 6.5 4.75" />
                </svg>
            </div>
        </div>

        <p class="mt-5 text-sm font-medium leading-5 text-gray-500 dark:text-gray-400">
            Menunggu Verifikasi Penyidik
        </p>

        <div class="mt-2 flex items-end justify-between">
            <h4 class="text-2xl font-semibold text-gray-900 dark:text-white">
                0
            </h4>

            <span class="text-sm text-gray-500 dark:text-gray-400">
                Perkara
            </span>
        </div>
    </div>


    {{-- Selesai --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="12" r="8.5" />
                    <path d="m8.5 12.25 2.25 2.25 4.75-5" />
                </svg>
            </div>
        </div>

        <p class="mt-5 text-sm font-medium text-gray-500 dark:text-gray-400">
            Selesai
        </p>

        <div class="mt-2 flex items-end justify-between">
            <h4 class="text-2xl font-semibold text-gray-900 dark:text-white">
                0
            </h4>

            <span class="text-sm text-gray-500 dark:text-gray-400">
                Perkara
            </span>
        </div>
    </div>


    {{-- Rata-rata Waktu Penyelesaian --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="12" r="8.5" />
                    <path d="M12 7v5l3 2" />
                    <path d="M8.5 4.75 7 3.5M15.5 4.75 17 3.5" />
                </svg>
            </div>
        </div>

        <p class="mt-5 text-sm font-medium leading-5 text-gray-500 dark:text-gray-400">
            Rata-rata Waktu Penyelesaian
        </p>

        <div class="mt-2 flex items-end justify-between gap-3">
            <h4 class="text-2xl font-semibold text-gray-900 dark:text-white">
                -
            </h4>

            <span class="text-right text-xs leading-5 text-gray-500 dark:text-gray-400">
                Dari SPDP s.d. Tahap II
                <br>
                Maks. 60 Hari Kerja
            </span>
        </div>
    </div>

</div>