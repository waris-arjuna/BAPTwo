@php
    $currentUser = auth()->user();
    $userName = $currentUser?->nama ?? __('Pengguna');
    $userRole = $currentUser?->role?->label() ?? 'PPNS';
    $initials = collect(explode(' ', trim($userName)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('') ?: 'U';
    $settingsUrl = $currentUser?->isAdmin() ? route('users.index') : route('profile');
@endphp

<div
    x-data="{ isOpen: false }"
    @click.outside="isOpen = false"
    @keydown.escape.window="isOpen = false"
    class="relative mb-4 flex w-full justify-center"
>
    {{-- ============================================================
        POPOVER MENU (MUNCUL DI ATAS PROFIL)
        ============================================================ --}}
    <div
        x-show="isOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute bottom-full start-0 z-50 mb-2 w-56 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
    >
        <ul class="flex flex-col gap-1">
            {{-- Settings / Pengaturan --}}
            <li>
                <a
                    href="{{ $settingsUrl }}"
                    @click="isOpen = false"
                    class="flex items-center gap-3 rounded-xl px-3 py-2 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.06]"
                >
                    <svg class="h-4 w-4 shrink-0 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15.1 18.25 16.25l-.35 1.85-1.8.75-1.6-.75-1.35.95-.35 1.95h-1.6l-.35-1.95-1.35-.95-1.6.75-1.8-.75-.35-1.85L4.6 15.1 3 14.75v-1.6l1.6-.35 1.15-1.15-.35-1.65.7-1.9 1.8-.7 1.6.45 1.35-.9L11.2 5h1.6l.35 1.95 1.35.9 1.6-.45 1.8.7.7 1.9-.35 1.65 1.15 1.15 1.6.35v1.6l-1.6.35Z" />
                    </svg>
                    <span>{{ __('Settings') }}</span>
                </a>
            </li>

            {{-- Profile / Profil --}}
            <li>
                <a
                    href="{{ route('profile') }}"
                    @click="isOpen = false"
                    class="flex items-center gap-3 rounded-xl px-3 py-2 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.06]"
                >
                    <svg class="h-4 w-4 shrink-0 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a7.5 7.5 0 0 1 15 0" />
                    </svg>
                    <span>{{ __('Profile') }}</span>
                </a>
            </li>
        </ul>

        {{-- Sign Out / Keluar --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-gray-100 pt-1 dark:border-gray-800">
            @csrf
            <button
                type="submit"
                @click="isOpen = false"
                class="flex w-full items-center gap-3 rounded-xl bg-gray-50 px-3 py-2 text-theme-sm font-medium text-gray-700 transition hover:bg-error-50 hover:text-error-600 dark:bg-white/[0.04] dark:text-gray-200 dark:hover:bg-error-500/15 dark:hover:text-error-400"
            >
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9m12 0-3-3m3 3-3 3" />
                </svg>
                <span>{{ __('Sign Out') }}</span>
            </button>
        </form>
    </div>

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
            @click="isOpen = !isOpen"
            class="group w-full rounded-2xl border border-gray-200 bg-white px-3 py-3 text-start transition-colors hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:bg-white/[0.05]"
        >
            <div class="flex items-center gap-3">

                {{-- Avatar --}}
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-sm font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400"
                >
                    @if ($currentUser?->foto_profil)
                        <img src="{{ $currentUser->fotoProfilUrl() }}" alt="{{ $userName }}" class="h-full w-full object-cover" />
                    @else
                        {{ $initials }}
                    @endif
                </div>

                {{-- User Information --}}
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $userName }}
                    </p>

                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        {{ $userRole }}
                    </p>

                    <p class="truncate text-xs text-gray-400 dark:text-gray-500">
                        Balai Besar POM di Banjarbaru
                    </p>
                </div>

                {{-- Arrow --}}
                <svg
                    :class="isOpen ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                    class="h-4 w-4 shrink-0 transition-transform duration-200"
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
            @click="isOpen = !isOpen"
            class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-sm font-semibold text-brand-600 transition-colors hover:ring-4 hover:ring-brand-500/10 dark:bg-brand-500/10 dark:text-brand-400"
            title="{{ $userName }}"
            aria-label="Profil pengguna"
        >
            @if ($currentUser?->foto_profil)
                <img src="{{ $currentUser->fotoProfilUrl() }}" alt="{{ $userName }}" class="h-full w-full object-cover" />
            @else
                {{ $initials }}
            @endif
        </button>
    </div>
</div>