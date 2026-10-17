{{-- Notification Dropdown Component --}}
<div class="relative" x-data="{
    dropdownOpen: false,
    notifying: true,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
        this.notifying = false;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    },
    handleItemClick() {
        console.log('Notification item clicked');
        this.closeDropdown();
    },
    handleViewAllClick() {
        console.log('View All Notifications clicked');
        this.closeDropdown();
    }
}" @click.away="closeDropdown()">
    <!-- Notification Button -->
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
        @click="toggleDropdown()"
        type="button"
    >
        <!-- Notification Badge -->
        <span
            x-show="notifying"
            class="absolute right-0 top-0.5 z-1 h-2 w-2 rounded-full bg-orange-400"
        >
            <span
                class="absolute inline-flex w-full h-full bg-orange-400 rounded-full opacity-75 -z-1 animate-ping"
            ></span>
        </span>

        <!-- Bell Icon -->
        <i class="fi fi-rr-bell text-base" aria-hidden="true"></i>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:w-[361px] lg:right-0"
        style="display: none;"
    >
        <!-- Dropdown Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
            <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notification</h5>

            <button @click="closeDropdown()" class="text-gray-500 dark:text-gray-400" type="button">
                <i class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Notification List -->
        <ul class="flex flex-col h-auto overflow-y-auto custom-scrollbar">
            @php
                $notifications = [
                    [
                        'id' => 1,
                        'userName' => 'Terry Franci',
                        'userImage' => '/images/user/owner.png',
                        'action' => 'requests permission to change',
                        'project' => 'Project - Nganter App',
                        'type' => 'Project',
                        'time' => '5 min ago',
                        'status' => 'online',
                    ],
                    [
                        'id' => 2,
                        'userName' => 'Alex Johnson',
                        'userImage' => '/images/user/owner.png',
                        'action' => 'requests permission to change',
                        'project' => 'Project - Nganter App',
                        'type' => 'Project',
                        'time' => '10 min ago',
                        'status' => 'offline',
                    ],
                    [
                        'id' => 3,
                        'userName' => 'Sarah Williams',
                        'userImage' => '/images/user/owner.png',
                        'action' => 'requests permission to change',
                        'project' => 'Project - Dashboard UI',
                        'type' => 'Project',
                        'time' => '15 min ago',
                        'status' => 'online',
                    ],
                    [
                        'id' => 4,
                        'userName' => 'Mike Brown',
                        'userImage' => '/images/user/owner.png',
                        'action' => 'requests permission to change',
                        'project' => 'Project - E-commerce',
                        'type' => 'Project',
                        'time' => '20 min ago',
                        'status' => 'online',
                    ],
                    [
                        'id' => 5,
                        'userName' => 'Emma Davis',
                        'userImage' => '/images/user/owner.png',
                        'action' => 'requests permission to change',
                        'project' => 'Project - Mobile App',
                        'type' => 'Project',
                        'time' => '25 min ago',
                        'status' => 'offline',
                    ],
                    [
                        'id' => 6,
                        'userName' => 'John Smith',
                        'userImage' => '/images/user/owner.png',
                        'action' => 'requests permission to change',
                        'project' => 'Project - Landing Page',
                        'type' => 'Project',
                        'time' => '30 min ago',
                        'status' => 'online',
                    ],
                    [
                        'id' => 7,
                        'userName' => 'Lisa Anderson',
                        'userImage' => '/images/user/owner.png',
                        'action' => 'requests permission to change',
                        'project' => 'Project - Blog System',
                        'type' => 'Project',
                        'time' => '35 min ago',
                        'status' => 'online',
                    ],
                    [
                        'id' => 8,
                        'userName' => 'David Wilson',
                        'userImage' => '/images/user/owner.png',
                        'action' => 'requests permission to change',
                        'project' => 'Project - CRM Dashboard',
                        'type' => 'Project',
                        'time' => '40 min ago',
                        'status' => 'online',
                    ],
                ];
            @endphp

            @foreach ($notifications as $notification)
                <li @click="handleItemClick()">
                    <a
                        class="flex gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5"
                        href="#"
                    >
                        <span class="relative block w-full h-10 rounded-full z-1 max-w-10">
                            <img src="{{ $notification['userImage'] }}" alt="User" class="overflow-hidden rounded-full" />
                            <span
                                class="absolute bottom-0 right-0 z-10 h-2.5 w-full max-w-2.5 rounded-full border-[1.5px] border-white dark:border-gray-900 {{ $notification['status'] === 'online' ? 'bg-success-500' : 'bg-error-500' }}"
                            ></span>
                        </span>

                        <span class="block">
                            <span class="mb-1.5 block text-theme-sm text-gray-500 dark:text-gray-400">
                                <span class="font-medium text-gray-800 dark:text-white/90">
                                    {{ $notification['userName'] }}
                                </span>
                                {{ $notification['action'] }}
                                <span class="font-medium text-gray-800 dark:text-white/90">
                                    {{ $notification['project'] }}
                                </span>
                            </span>

                            <span class="flex items-center gap-2 text-gray-500 text-theme-xs dark:text-gray-400">
                                <span>{{ $notification['type'] }}</span>
                                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                <span>{{ $notification['time'] }}</span>
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- View All Button -->
        <a
            href="#"
            class="mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            @click.prevent="handleViewAllClick()"
        >
            View All Notification
        </a>
    </div>
    <!-- Dropdown End -->
</div>
