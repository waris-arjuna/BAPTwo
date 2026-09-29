<div
    class="mb-4 flex w-full justify-center"
>
    {{-- ============================================================
        EXPANDED PROFILE
        ============================================================ --}}
    <div
        x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
        x-cloak
        class="w-full max-w-[260px]"
    >
        <button
            type="button"
            class="group w-full rounded-2xl border border-gray-200 bg-white px-3 py-3 text-left transition-colors hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:bg-white/[0.05]"
        >
            <div class="flex items-center gap-3">

                {{-- Avatar --}}
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400"
                >
                    BH
                </div>

                {{-- User Information --}}
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                        Bambang Hery Purwanto
                    </p>

                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        PPNS
                    </p>

                    <p class="truncate text-xs text-gray-400 dark:text-gray-500">
                        Balai Besar POM di Banjarbaru
                    </p>
                </div>

                {{-- Arrow --}}
                <svg
                    class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-hover:translate-y-0.5"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
        </button>
    </div>


    {{-- ============================================================
        COLLAPSED PROFILE
        ============================================================ --}}
    <div
        x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
        x-cloak
    >
        <button
            type="button"
            class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-600 transition-colors hover:ring-4 hover:ring-brand-500/10 dark:bg-brand-500/10 dark:text-brand-400"
            title="Bambang Hery Purwanto"
            aria-label="Profil pengguna"
        >
            BH
        </button>
    </div>
</div>