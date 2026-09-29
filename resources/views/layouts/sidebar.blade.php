@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();

    // Get current path
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col mt-0 top-0 px-5 start-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 ltr:border-r rtl:border-l border-gray-200 w-[90px] [.sidebar-expanded_&]:min-w-[290px]"
    x-data="{
        openSubmenus: {},
        init() {
            // Auto-open Dashboard menu on page load
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';

            @foreach ($menuGroups as $groupIndex => $menuGroup)
                @foreach ($menuGroup['items'] as $itemIndex => $item)
                    @if (isset($item['subItems']))
                        // Check if any submenu item matches current path
                        @foreach ($item['subItems'] as $subItem)
                            if (currentPath === '{{ ltrim($subItem['path'], '/') }}' ||
                                window.location.pathname === '{{ $subItem['path'] }}') {
                                this.openSubmenus['{{ $groupIndex }}-{{ $itemIndex }}'] = true;
                            } @endforeach
            @endif
            @endforeach
            @endforeach
        },
        toggleSubmenu(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            const newState = !this.openSubmenus[key];

            // Close all other submenus when opening a new one
            if (newState) {
                this.openSubmenus = {};
            }

            this.openSubmenus[key] = newState;
        },
        isSubmenuOpen(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            return this.openSubmenus[key] || false;
        },
        isActive(path) {
            return window.location.pathname === path || '{{ $currentPath }}' === path.replace(/^\//, '');
        }
    }"
    :class="{
        'translate-x-0': $store.sidebar.isMobileOpen,
        'max-xl:-translate-x-full max-xl:rtl:translate-x-full': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">
    <!-- Logo Section -->
    <div class="pt-8 pb-7 flex items-center gap-2" :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-evenly'">
        <a href="/">
            <div class="hidden [.sidebar-expanded_&]:block">
                <img class="dark:hidden" src="/images/logo/logo.svg" alt="Logo" width="100" height="40" />
                <img class="hidden dark:block" src="/images/logo/logo-dark.svg" alt="Logo" width="100" height="40" />
            </div>
            <img class="block [.sidebar-expanded_&]:hidden" src="/images/logo/logo-icon.svg" alt="Logo" width="100" height="100" />
        </a>
    </div>

    <!-- Navigation + Sidebar Profile -->
    <div class="flex min-h-0 flex-1 flex-col">

        <!-- Navigation Menu -->
        <div class="min-h-0 flex-1 overflow-y-auto duration-300 ease-linear no-scrollbar">
            <nav class="mb-6">
                <div class="flex flex-col gap-4">

                    @foreach ($menuGroups as $groupIndex => $menuGroup)
                        <div>
                            <!-- Menu Group Title -->
                            <h2
                                class="mb-4 flex text-xs uppercase leading-[20px] text-gray-400"
                                :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen)
                                    ? 'lg:justify-center'
                                    : 'justify-start'"
                            >
                                <template
                                    x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                >
                                    <span>{{ __($menuGroup['title']) }}</span>
                                </template>

                                <template
                                    x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
                                >
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M4.25 12C4.25 11.5858 4.58579 11.25 5 11.25H19C19.4142 11.25 19.75 11.5858 19.75 12C19.75 12.4142 19.4142 12.75 19 12.75H5C4.58579 12.75 4.25 12.4142 4.25 12Z"
                                            fill="currentColor"
                                        />
                                    </svg>
                                </template>
                            </h2>

                            <!-- Menu Items -->
                            <ul class="flex flex-col gap-1">
                                @foreach ($menuGroup['items'] as $itemIndex => $item)
                                    <li>
                                        @if (isset($item['subItems']))
                                            <!-- Dropdown Menu Item -->
                                            <button
                                                @click="toggleSubmenu({{ $groupIndex }}, {{ $itemIndex }})"
                                                class="menu-item group w-full"
                                                :class="[
                                                    isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                                        ? 'menu-item-active'
                                                        : 'menu-item-inactive',

                                                    (!$store.sidebar.isExpanded &&
                                                    !$store.sidebar.isHovered &&
                                                    !$store.sidebar.isMobileOpen)
                                                        ? 'xl:justify-center'
                                                        : 'xl:justify-start'
                                                ]"
                                            >
                                                <!-- Left Icon -->
                                                <span
                                                    :class="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                                        ? 'menu-item-icon-active'
                                                        : 'menu-item-icon-inactive'"
                                                >
                                                    {!! MenuHelper::getIconSvg($item['icon']) !!}
                                                </span>

                                                <!-- Item Text -->
                                                <span
                                                    x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                    class="menu-item-text flex items-center gap-2"
                                                >
                                                    {{ __($item['name']) }}
                                                </span>

                                                <!-- Dropdown Chevron -->
                                                <svg
                                                    x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                    class="ltr:ml-auto rtl:mr-auto h-5 w-5 transition-transform duration-200"
                                                    :class="{
                                                        'rotate-180 text-brand-500': isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}),
                                                        'text-gray-500 dark:text-gray-400': !isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                                    }"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>

                                            <!-- Submenu -->
                                            <div
                                                x-show="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) &&
                                                    ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)"
                                                x-collapse
                                            >
                                                <ul class="mt-2 space-y-1 ltr:ml-9 rtl:mr-9">
                                                    @foreach ($item['subItems'] as $subItem)
                                                        <li>
                                                            <a
                                                                href="{{ $subItem['path'] }}"
                                                                class="menu-dropdown-item"
                                                                :class="isActive('{{ $subItem['path'] }}')
                                                                    ? 'menu-dropdown-item-active'
                                                                    : 'menu-dropdown-item-inactive'"
                                                            >
                                                                {{ __($subItem['name']) }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                        @else

                                            <!-- Simple Menu Item -->
                                            <a
                                                href="{{ $item['path'] }}"
                                                class="menu-item group"
                                                :class="[
                                                    isActive('{{ $item['path'] }}')
                                                        ? 'menu-item-active'
                                                        : 'menu-item-inactive',

                                                    (!$store.sidebar.isExpanded &&
                                                    !$store.sidebar.isHovered &&
                                                    !$store.sidebar.isMobileOpen)
                                                        ? 'xl:justify-center'
                                                        : 'xl:justify-start'
                                                ]"
                                            >
                                                <!-- Left Icon -->
                                                <span
                                                    :class="isActive('{{ $item['path'] }}')
                                                        ? 'menu-item-icon-active'
                                                        : 'menu-item-icon-inactive'"
                                                >
                                                    {!! MenuHelper::getIconSvg($item['icon']) !!}
                                                </span>

                                                <!-- Item Text -->
                                                <span
                                                    x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                    class="menu-item-text flex items-center gap-2"
                                                >
                                                    {{ __($item['name']) }}
                                                </span>
                                            </a>

                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                </div>
            </nav>
        </div>

        <!-- Sidebar Profile -->
        <div class="shrink-0 border-t border-gray-200 pt-4 dark:border-gray-800">
            @include('layouts.sidebar-widget')
        </div>

    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
    class="fixed z-50 h-screen w-full bg-gray-900/50" x-cloak></div>
