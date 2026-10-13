<div x-data="{
    transactions: [
        {
            id: 1,
            name: 'Bought PYPL',
            image: '/images/brand/brand-08.svg',
            date: 'Nov 23, 01:00 PM',
            price: '$2,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 2,
            name: 'Bought AAPL',
            image: '/images/brand/brand-07.svg',
            date: 'Nov 23, 01:00 PM',
            price: '$2,567.88',
            category: 'Finance',
            status: 'Pending',
        },
        {
            id: 3,
            name: 'Sell KKST',
            image: '/images/brand/brand-15.svg',
            date: 'Nov 23, 01:00 PM',
            price: '$2,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 4,
            name: 'Bought FB',
            image: '/images/brand/brand-02.svg',
            date: 'Nov 23, 01:00 PM',
            price: '$2,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 5,
            name: 'Sell AMZN',
            image: '/images/brand/brand-10.svg',
            date: 'Nov 23, 01:00 PM',
            price: '$2,567.88',
            category: 'Finance',
            status: 'Failed',
        },
        {
            id: 6,
            name: 'Bought MSFT',
            image: '/images/brand/brand-09.svg',
            date: 'Nov 22, 01:00 PM',
            price: '$1,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 7,
            name: 'Bought GOOG',
            image: '/images/brand/brand-01.svg',
            date: 'Nov 22, 01:00 PM',
            price: '$3,567.88',
            category: 'Finance',
            status: 'Pending',
        },
        {
            id: 8,
            name: 'Sell TSLA',
            image: '/images/brand/brand-12.svg',
            date: 'Nov 22, 01:00 PM',
            price: '$4,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 9,
            name: 'Bought NVDA',
            image: '/images/brand/brand-11.svg',
            date: 'Nov 22, 01:00 PM',
            price: '$5,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 10,
            name: 'Sell META',
            image: '/images/brand/brand-03.svg',
            date: 'Nov 22, 01:00 PM',
            price: '$6,567.88',
            category: 'Finance',
            status: 'Failed',
        },
        {
            id: 11,
            name: 'Bought DIS',
            image: '/images/brand/brand-04.svg',
            date: 'Nov 21, 01:00 PM',
            price: '$7,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 12,
            name: 'Bought NFLX',
            image: '/images/brand/brand-05.svg',
            date: 'Nov 21, 01:00 PM',
            price: '$8,567.88',
            category: 'Finance',
            status: 'Pending',
        },
        {
            id: 13,
            name: 'Sell CRM',
            image: '/images/brand/brand-06.svg',
            date: 'Nov 21, 01:00 PM',
            price: '$9,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 14,
            name: 'Bought TSLA',
            image: '/images/brand/brand-13.svg',
            date: 'Nov 21, 01:00 PM',
            price: '$10,567.88',
            category: 'Finance',
            status: 'Success',
        },
        {
            id: 15,
            name: 'Sell AAPL',
            image: '/images/brand/brand-14.svg',
            date: 'Nov 21, 01:00 PM',
            price: '$11,567.88',
            category: 'Finance',
            status: 'Failed',
        },
    ],
    itemsPerPage: 5,
    currentPage: 1,
    dropdownOpen: null,
    get totalPages() {
        return Math.ceil(this.transactions.length / this.itemsPerPage);
    },
    get paginatedTransactions() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.transactions.slice(start, end);
    },
    get displayedPages() {
        const range = [];
        for (let i = 1; i <= this.totalPages; i++) {
            if (
                i === 1 ||
                i === this.totalPages ||
                (i >= this.currentPage - 1 && i <= this.currentPage + 1)
            ) {
                range.push(i);
            } else if (range[range.length - 1] !== '...') {
                range.push('...');
            }
        }
        return range;
    },
    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
        }
    },
    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
        }
    },
    goToPage(page) {
        if (typeof page === 'number' && page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
        }
    },
    getStatusClass(status) {
        const classes = {
            'Success': 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500',
            'Pending': 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-orange-400',
            'Failed': 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500',
        };
        return classes[status] || '';
    },
    toggleDropdown(id) {
        this.dropdownOpen = this.dropdownOpen === id ? null : id;
    }
}">
    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="flex flex-col gap-2 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Latest Transactions</h3>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form>
                    <div class="relative">
                        <button type="button" class="absolute -translate-y-1/2 ltr:left-4 rtl:right-4 top-1/2 text-gray-500 dark:text-gray-400">
                            <i class="fi fi-rr-search text-base"></i>
                        </button>
                        <input type="text" placeholder="Search..." class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 ltr:pl-[42px] ltr:pr-4 rtl:pr-[42px] rtl:pl-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[300px]"/>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden">
            <div class="max-w-full px-5 overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead>
                        <tr class="border-gray-200 border-y dark:border-gray-700">
                            <th scope="col" class="px-4 py-3 font-normal text-gray-500 text-start text-theme-sm dark:text-gray-400">Name</th>
                            <th scope="col" class="px-4 py-3 font-normal text-gray-500 text-start text-theme-sm dark:text-gray-400">Date</th>
                            <th scope="col" class="px-4 py-3 font-normal text-gray-500 text-start text-theme-sm dark:text-gray-400">Price</th>
                            <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-start text-gray-500 capitalize">Category</th>
                            <th scope="col" class="px-4 py-3 font-normal text-gray-500 text-start text-theme-sm dark:text-gray-400">Status</th>
                            <th scope="col" class="relative px-4 py-3 capitalize">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="transaction in paginatedTransactions" :key="transaction.id">
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="shrink-0 w-8 h-8">
                                            <img class="w-8 h-8 rounded-full" :src="transaction.image" alt="">
                                        </div>
                                        <div class="ltr:ml-4 rtl:mr-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="transaction.name"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="transaction.date"></div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="transaction.price"></div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="transaction.category"></div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(transaction.status)" x-text="transaction.status"></span>
                                </td>
                                <td class="px-4 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex justify-center relative">
                                        <x-common.table-dropdown>
                                            <x-slot name="button">
                                                <button type="button" id="options-menu" aria-haspopup="true" aria-expanded="true" class="text-gray-500 dark:text-gray-400">
                                                    <i class="fi fi-rr-menu-dots text-lg"></i>
                                                </button>
                                            </x-slot>
        
                                            <x-slot name="content">
                                                <a href="#" class="flex w-full px-3 py-2 font-medium text-start text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300" role="menuitem">
                                                    View More
                                                </a>
                                                <a href="#" class="flex w-full px-3 py-2 font-medium text-start text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300" role="menuitem">
                                                    Delete
                                                </a>
                                            </x-slot>
                                        </x-common.table-dropdown>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/[0.05]">
            <div class="flex items-center justify-between">
                <button @click="prevPage" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                    <i class="fi fi-rr-arrow-small-left text-base"></i>
                    <span class="hidden sm:inline">Previous</span>
                </button>

                <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </span>

                <ul class="hidden items-center gap-0.5 sm:flex">
                    <template x-for="page in displayedPages" :key="page">
                        <li>
                            <button x-show="page !== '...'" @click="goToPage(page)" :class="currentPage === page ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-blue-500/[0.08] hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500'" class="flex h-10 w-10 items-center justify-center rounded-lg text-theme-sm font-medium" x-text="page"></button>
                            <span x-show="page === '...'" class="flex h-10 w-10 items-center justify-center text-gray-500">...</span>
                        </li>
                    </template>
                </ul>

                <button @click="nextPage" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                    <span class="hidden sm:inline">Next</span>
                    <i class="fi fi-rr-arrow-small-right text-base"></i>
                </button>
            </div>
        </div>
    </div>
</div>