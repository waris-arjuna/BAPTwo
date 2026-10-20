<header
    class="sticky top-0 z-99999 w-full border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
>
    @php
    $now = \Carbon\Carbon::now('Asia/Makassar')->locale('id');
    @endphp
    <div class="flex h-16 items-center justify-between px-4 xl:px-6">

        {{-- Left Section --}}
        <div class="flex items-center gap-3">

            {{-- Desktop Sidebar Toggle --}}
            <button
                type="button"
                class="hidden h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 xl:flex dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-white"
                :class="{
                    'bg-gray-100 dark:bg-white/[0.03]': !$store.sidebar.isExpanded
                }"
                @click="$store.sidebar.toggleExpanded()"
                aria-label="Toggle Sidebar"
            >
                <i x-show="!$store.sidebar.isMobileOpen" class="fi fi-rr-menu-burger text-base" aria-hidden="true"></i>
                <i x-show="$store.sidebar.isMobileOpen" class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
            </button>

            {{-- Mobile Sidebar Toggle --}}
            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 xl:hidden dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-white"
                :class="{
                    'bg-gray-100 dark:bg-white/[0.03]': $store.sidebar.isMobileOpen
                }"
                @click="$store.sidebar.toggleMobileOpen()"
                aria-label="Toggle Mobile Menu"
            >
                <i x-show="!$store.sidebar.isMobileOpen" class="fi fi-rr-menu-burger text-base" aria-hidden="true"></i>
                <i x-show="$store.sidebar.isMobileOpen" class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
            </button>

            {{-- Mobile Logo --}}
            <a
                href="/"
                class="flex items-center xl:hidden"
                aria-label="BAP TWO Dashboard"
            >
                <img
                    class="h-8 w-auto dark:hidden"
                    src="/images/logo/logo.svg"
                    alt="BAP TWO"
                    width="150"
                    height="32"
                />

                <img
                    class="hidden h-8 w-auto dark:block"
                    src="/images/logo/logo-dark.svg"
                    alt="BAP TWO"
                    width="150"
                    height="32"
                />
            </a>
        </div>

    {{-- Right Section --}}
    <div class="flex items-center gap-2">

        {{-- Calendar --}}
        <div
            class="hidden items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-800 dark:bg-gray-900 sm:flex"
        >
            <i class="fi fi-rr-calendar text-base text-gray-500 dark:text-gray-400" aria-hidden="true"></i>

            <div class="leading-tight">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    {{ $now->translatedFormat('l, d F Y') }}
                </p>

                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $now->format('H:i') }} WITA
                </p>
            </div>
        </div>

        {{-- Theme Toggle --}}
        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
            @click="$store.theme.toggle()"
            aria-label="Toggle Theme"
        >
            {{-- Light Mode Icon --}}
            <i class="fi fi-rr-sun hidden text-base dark:inline-flex" aria-hidden="true"></i>

            {{-- Dark Mode Icon --}}
            <i class="fi fi-rr-moon text-base dark:hidden" aria-hidden="true"></i>
        </button>

    </div>
</header>