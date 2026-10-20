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
                    <i class="fi fi-rr-settings text-sm shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true"></i>
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
                    <i class="fi fi-rr-user text-sm shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true"></i>
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
                <i class="fi fi-rr-sign-out-alt text-sm shrink-0" aria-hidden="true"></i>
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
                        <img src="{{ $currentUser->fotoProfilUrl() }}" alt="{{ $userName }}" class="h-full w-full object-cover" width="44" height="44" />
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
                <i
                    :class="isOpen ? 'rotate-180 text-brand-500' : 'text-gray-400'"
                    class="fi fi-rr-angle-small-down text-base shrink-0 transition-transform duration-200"
                    aria-hidden="true"
                ></i>
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
                <img src="{{ $currentUser->fotoProfilUrl() }}" alt="{{ $userName }}" class="h-full w-full object-cover" width="44" height="44" />
            @else
                {{ $initials }}
            @endif
        </button>
    </div>
</div>