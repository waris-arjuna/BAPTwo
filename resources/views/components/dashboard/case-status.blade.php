<div
    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
>
    {{-- Header --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Perkara Tahun Berjalan
        </h3>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Tahun Berjalan 2026
        </p>
    </div>

    {{-- Chart + Legend --}}
    <div class="flex flex-col items-center gap-8 sm:flex-row sm:justify-center">

        {{-- Donut Chart --}}
        <div class="relative flex h-52 w-52 shrink-0 items-center justify-center">
            <div
                class="absolute inset-0 rounded-full"
                style="background: conic-gradient(
                    #22c55e 0% 0%,
                    #3b82f6 0% 100%,
                    #e5e7eb 100% 100%
                );"
            ></div>

            {{-- Inner Circle --}}
            <div
                class="absolute flex h-36 w-36 flex-col items-center justify-center rounded-full bg-white dark:bg-gray-900"
            >
                <span class="text-3xl font-bold text-gray-900 dark:text-white">
                    2
                </span>

                <span class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Total Perkara
                </span>
            </div>
        </div>

        {{-- Legend --}}
        <div class="w-full max-w-xs space-y-4">

            {{-- Selesai --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="h-3 w-3 rounded-full bg-green-500"></span>

                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Selesai
                    </span>
                </div>

                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        0 Perkara
                    </p>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        0%
                    </p>
                </div>
            </div>

            {{-- Dalam Proses --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="h-3 w-3 rounded-full bg-blue-500"></span>

                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Dalam Proses
                    </span>
                </div>

                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        2 Perkara
                    </p>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        100%
                    </p>
                </div>
            </div>

            {{-- Belum Dimulai --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="h-3 w-3 rounded-full bg-gray-300 dark:bg-gray-600"></span>

                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Belum Dimulai
                    </span>
                </div>

                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        0 Perkara
                    </p>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        0%
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- Information --}}
    <div
        class="mt-6 flex items-start gap-3 rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]"
    >
        <svg
            class="mt-0.5 h-5 w-5 shrink-0 text-gray-400"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <circle cx="12" cy="12" r="9" />
            <path d="M12 10v6" />
            <path d="M12 7.25h.01" />
        </svg>

        <p class="text-sm leading-5 text-gray-500 dark:text-gray-400">
            Perhitungan persentase berdasarkan total 2 perkara
            pada Tahun Berjalan 2026.
        </p>
    </div>
</div>
