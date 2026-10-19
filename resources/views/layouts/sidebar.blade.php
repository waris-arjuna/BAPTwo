@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();

    // Get current path
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col mt-0 top-0 start-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200"
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
    :class="[
        ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen) ? 'w-[290px]' : 'w-[90px]',
        $store.sidebar.isMobileOpen ? 'translate-x-0' : 'max-xl:-translate-x-full'
    ]"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">
    <!-- Logo Section -->
    <div class="h-16 shrink-0 flex items-center border-b border-gray-200 dark:border-gray-800 px-5 transition-all duration-300 ease-in-out"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'justify-center' : 'justify-start'">
        <a href="/" class="flex items-center">
            <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                <img class="h-8 w-auto dark:hidden" src="/images/logo/logo.svg" alt="Logo" />
                <img class="hidden h-8 w-auto dark:block" src="/images/logo/logo-dark.svg" alt="Logo" />
            </div>
            <img x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
                class="h-8 w-auto" src="/images/logo/logo-icon.svg" alt="Logo" />
        </a>
    </div>

    <!-- Navigation + Sidebar Profile -->
    <div class="flex min-h-0 flex-1 flex-col px-5 pt-4">

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
                                    <i class="fi fi-rr-menu-dots text-base" aria-hidden="true"></i>
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
                                                <i
                                                    x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                    class="fi fi-rr-angle-small-down ltr:ml-auto rtl:mr-auto text-base transition-transform duration-200"
                                                    :class="{
                                                        'rotate-180 text-brand-500': isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}),
                                                        'text-gray-500 dark:text-gray-400': !isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                                    }"
                                                    aria-hidden="true"
                                                ></i>
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
