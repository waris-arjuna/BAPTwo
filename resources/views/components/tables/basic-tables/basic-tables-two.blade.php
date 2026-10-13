<div x-data="{
    tableRowData: [
        {
            id: 'DE124321',
            checked: false,
            customerName: 'John Doe',
            customerEmail: 'johndoe@gmail.com',
            initials: 'JD',
            avatarBg: 'bg-blue-100',
            avatarColor: 'text-blue-500',
            product: 'Software License',
            value: '$18,50.34',
            closeDate: '2024-06-15',
            status: 'Complete',
        },
        {
            id: 'DE124322',
            checked: false,
            customerName: 'Kierra Franci',
            customerEmail: 'kierra@gmail.com',
            initials: 'KF',
            avatarBg: 'bg-[#fdf2fa]',
            avatarColor: 'text-[#dd2590]',
            product: 'Software License',
            value: '$18,50.34',
            closeDate: '2024-06-15',
            status: 'Complete',
        },
        {
            id: 'DE124323',
            checked: false,
            customerName: 'Emerson Workman',
            customerEmail: 'emerson@gmail.com',
            initials: 'EW',
            avatarBg: 'bg-[#f0f9ff]',
            avatarColor: 'text-[#0086c9]',
            product: 'Software License',
            value: '$18,50.34',
            closeDate: '2024-06-15',
            status: 'Pending',
        },
        {
            id: 'DE124324',
            checked: false,
            customerName: 'Chance Philips',
            customerEmail: 'chance@gmail.com',
            initials: 'CP',
            avatarBg: 'bg-[#fff6ed]',
            avatarColor: 'text-[#ec4a0a]',
            product: 'Software License',
            value: '$18,50.34',
            closeDate: '2024-06-15',
            status: 'Complete',
        },
        {
            id: 'DE124325',
            checked: false,
            customerName: 'Terry Geidt',
            customerEmail: 'terry@gmail.com',
            initials: 'TG',
            avatarBg: 'bg-green-50',
            avatarColor: 'text-green-600',
            product: 'Software License',
            value: '$18,50.34',
            closeDate: '2024-06-15',
            status: 'Complete',
        },
    ],
    selectedRows: [],
    selectAll: false,
    handleSelectAll() {
        this.selectAll = !this.selectAll;
        if (this.selectAll) {
            this.selectedRows = this.tableRowData.map(row => row.id);
        } else {
            this.selectedRows = [];
        }
    },
    handleRowSelect(id) {
        if (this.selectedRows.includes(id)) {
            this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
        } else {
            this.selectedRows.push(id);
        }
    },
    getStatusClass(status) {
        const classes = {
            'Complete': 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500',
            'Pending': 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400',
            'Cancel': 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500',
        };
        return classes[status] || '';
    },
    deleteRow(id) {
        if (confirm('Are you sure you want to delete this order?')) {
            this.tableRowData = this.tableRowData.filter(row => row.id !== id);
            this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
        }
    }
}">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="flex flex-col gap-4 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Recent Orders
                </h3>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <i class="fi fi-rr-settings-sliders text-base"></i>
                    Filter
                </button>
                <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    See all
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="max-w-full overflow-x-auto">
            <table class="w-full">
                <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            <div class="flex items-center gap-3">
                                <div @click="handleSelectAll()"
                                    class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                    :class="selectAll ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                    <i :class="selectAll ? 'block' : 'hidden'" class="fi fi-rr-check text-xs text-white"></i>
                                </div>
                                <span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Deal ID</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">Customer</th>
                        <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">Product/Service</th>
                        <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">Deal Value</th>
                        <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">Close Date</th>
                        <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">Status</th>
                        <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in tableRowData" :key="row.id">
                        <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div @click="handleRowSelect(row.id)"
                                        class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                        :class="selectedRows.includes(row.id) ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                        <i :class="selectedRows.includes(row.id) ? 'block' : 'hidden'" class="fi fi-rr-check text-xs text-white"></i>
                                    </div>
                                    <div>
                                        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.id"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-medium text-sm"
                                        :class="[row.avatarBg, row.avatarColor]">
                                        <span x-text="row.initials"></span>
                                    </div>
                                    <div>
                                        <span class="mb-0.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400" x-text="row.customerName"></span>
                                        <span class="text-gray-500 text-theme-sm dark:text-gray-400" x-text="row.customerEmail"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.product"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.value"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.closeDate"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <span class="text-theme-xs inline-block rounded-full px-2 py-0.5 font-medium" 
                                    :class="getStatusClass(row.status)" 
                                    x-text="row.status"></span>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <button @click="deleteRow(row.id)" class="text-gray-700 cursor-pointer hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500">
                                    <i class="fi fi-rr-trash text-base"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>