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
                <svg
                    x-show="!$store.sidebar.isMobileOpen"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M4 6H20"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M4 12H20"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M4 18H20"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>

                <svg
                    x-show="$store.sidebar.isMobileOpen"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M6 6L18 18"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M18 6L6 18"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>
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
                <svg
                    x-show="!$store.sidebar.isMobileOpen"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M4 6H20"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M4 12H20"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M4 18H20"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>

                <svg
                    x-show="$store.sidebar.isMobileOpen"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M6 6L18 18"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M18 6L6 18"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>
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
                />

                <img
                    class="hidden h-8 w-auto dark:block"
                    src="/images/logo/logo-dark.svg"
                    alt="BAP TWO"
                />
            </a>
        </div>

    {{-- Right Section --}}
    <div class="flex items-center gap-2">

        {{-- Calendar --}}
        <div
            class="hidden items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-800 dark:bg-gray-900 sm:flex"
        >
            <svg
                class="h-5 w-5 text-gray-500 dark:text-gray-400"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >
                <rect
                    x="3"
                    y="4"
                    width="18"
                    height="17"
                    rx="2"
                    stroke="currentColor"
                    stroke-width="1.7"
                />
                <path
                    d="M16 2V6"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                />
                <path
                    d="M8 2V6"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                />
                <path
                    d="M3 9H21"
                    stroke="currentColor"
                    stroke-width="1.7"
                />
            </svg>

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
            <svg
                class="hidden h-5 w-5 dark:block"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >
                <circle
                    cx="10"
                    cy="10"
                    r="3.25"
                    stroke="currentColor"
                    stroke-width="1.5"
                />
                <path
                    d="M10 1.75V3.25"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                />
                <path
                    d="M10 16.75V18.25"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                />
                <path
                    d="M18.25 10H16.75"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                />
                <path
                    d="M3.25 10H1.75"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                />
            </svg>

            {{-- Dark Mode Icon --}}
            <svg
                class="h-5 w-5 dark:hidden"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >
                <path
                    d="M16.75 10.95C16.2 14.3 13.3 16.85 9.8 16.85C5.9 16.85 2.75 13.7 2.75 9.8C2.75 6.3 5.3 3.4 8.65 2.85C8.1 3.7 7.8 4.7 7.8 5.8C7.8 8.7 10.15 11.05 13.05 11.05C14.15 11.05 15.15 10.75 16 10.2C16.25 10.4 16.5 10.65 16.75 10.95Z"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>

        {{-- Notification --}}
        <x-header.notification-dropdown />

    </div>
</header>