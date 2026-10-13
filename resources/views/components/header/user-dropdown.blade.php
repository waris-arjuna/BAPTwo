<div class="relative" x-data="{
    isOpen: false,
    toggleDropdown() {
        this.isOpen = !this.isOpen;
    },
    closeDropdown() {
        this.isOpen = false;
    }
}" @click.outside="closeDropdown()">
    <!-- User Trigger -->
    <button
        class="flex items-center text-gray-700 dark:text-gray-400"
        type="button"
        @click="toggleDropdown()"
    >
        <span class="mr-3 overflow-hidden rounded-full h-11 w-11 rtl:mr-0 rtl:ml-3">
            <img src="{{ auth()->user()?->fotoProfilUrl() ?? asset('images/user/owner.png') }}" alt="User" class="h-11 w-11 object-cover" />
        </span>

        <span class="block mr-1 font-medium text-theme-sm rtl:mr-0 rtl:ml-1">
            {{ auth()->user()?->nama ?? __('Pengguna') }}
        </span>

        <!-- Chevron Down Icon -->
        <i
            :class="isOpen ? 'rotate-180' : ''"
            class="fi fi-rr-angle-small-down text-base transition-transform duration-200 text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200"
            aria-hidden="true"
        ></i>
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute ltr:right-0 rtl:left-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark z-50"
        style="display: none;"
    >
        <!-- User Info -->
        <div>
            <div class="flex items-center justify-between gap-2">
                <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                    {{ auth()->user()?->nama ?? __('Pengguna') }}
                </span>
                @if (auth()->user()?->role)
                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        {{ auth()->user()->role->label() }}
                    </span>
                @endif
            </div>
            <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">
                {{ auth()->user()?->email ?? '' }}
            </span>
        </div>

        <!-- Menu Items -->
        <ul class="flex flex-col gap-1 pt-4 pb-3 border-b border-gray-200 dark:border-gray-800">
            <li>
                <a
                    href="/profile"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                        <i class="fi fi-rr-user text-base" aria-hidden="true"></i>
                    </span>
                    {{ __('Edit profile') }}
                </a>
            </li>
            <li>
                <a
                    href="/profile"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                        <i class="fi fi-rr-settings text-base" aria-hidden="true"></i>
                    </span>
                    {{ __('Account settings') }}
                </a>
            </li>
            <li>
                <a
                    href="/profile"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                        <i class="fi fi-rr-info text-base" aria-hidden="true"></i>
                    </span>
                    {{ __('Support') }}
                </a>
            </li>
        </ul>

        <!-- Sign Out -->
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button
                type="submit"
                class="flex items-center w-full gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                @click="closeDropdown()"
            >
                <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                    <i class="fi fi-rr-sign-out-alt text-base" aria-hidden="true"></i>
                </span>
                {{ __('Sign out') }}
            </button>
        </form>
    </div>
</div>
