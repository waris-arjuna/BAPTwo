<div class="overflow-x-auto">
    <table class="w-full min-w-[1050px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Tahap 1
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tanggal
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    SPDP
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Dokumen BAP
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Status Proses
                </th>

                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Aksi
                </th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse ($tahap1 as $index => $item)

                @php
                    $statusClass = match ($item['status']) {
                        'Tahap 1 Berjalan' =>
                            'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400',

                        'Menunggu P-21' =>
                            'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400',

                        'P-21' =>
                            'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',

                        default =>
                            'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                    };
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $index + 1 }}
                    </td>

                    <td class="px-5 py-4">
                        <a
                            href="{{ url('/tahap-1/' . $item['id']) }}"
                            class="text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                        >
                            {{ $item['nomor'] }}
                        </a>
                    </td>

                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $item['tanggal'] }}
                    </td>

                    <td class="px-5 py-4">
                        <a
                            href="{{ url('/spdp/1') }}"
                            class="text-sm text-gray-700 transition hover:text-brand-500 dark:text-gray-300"
                        >
                            {{ $item['spdp'] }}
                        </a>
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-start gap-2">

                            <i class="fi fi-rr-document mt-0.5 shrink-0 text-base text-red-500"></i>

                            <div class="min-w-0">
                                {{-- BAP utama --}}
                                <p class="truncate text-sm text-gray-700 dark:text-gray-300">
                                    {{ $item['dokumen'] }}
                                </p>

                                {{-- Dokumen pendukung --}}
                                @if ($item['p16'] || $item['p17'])
                                    <div class="mt-1 space-y-0.5">

                                        @if ($item['p16'])
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                P-16: {{ $item['p16'] }}
                                            </p>
                                        @endif

                                        @if ($item['p17'])
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                P-17P: {{ $item['p17'] }}
                                            </p>
                                        @endif

                                    </div>
                                @endif
                            </div>

                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                            {{ $item['status'] }}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <div
                            x-data="{ open: false }"
                            class="relative flex justify-center"
                        >
                            <button
                                type="button"
                                @click="open = !open"
                                class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                            >
                                Aksi

                                <i class="fi fi-rr-angle-small-down text-base"></i>
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                @click.outside="open = false"
                                class="absolute right-0 top-11 z-20 w-44 rounded-lg border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            >
                                <a
                                    href="{{ url('/tahap-1/' . $item['id']) }}"
                                    class="block rounded-md px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                >
                                    Lihat
                                </a>

                                @can('edit-draft')
                                    <a
                                        href="#"
                                        class="block rounded-md px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                    >
                                        Edit
                                    </a>
                                @endcan

                                @if ($item['status'] !== 'P-21')
                                    @can('create-cases')
                                        <a
                                            href="#"
                                            class="block rounded-md px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                        >
                                            Lanjut P-21
                                        </a>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No data available.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="flex flex-col gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Menampilkan
        <span class="font-medium text-gray-700 dark:text-gray-300">1–{{ count($tahap1) }}</span>
        dari
        <span class="font-medium text-gray-700 dark:text-gray-300">{{ count($tahap1) }}</span>
        data Tahap 1
    </p>

    <div class="flex items-center gap-1">
        <button
            type="button"
            disabled
            class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-400 dark:border-gray-800"
        >
            Sebelumnya
        </button>

        <button
            type="button"
            class="rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white"
        >
            1
        </button>

        <button
            type="button"
            disabled
            class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-400 dark:border-gray-800"
        >
            Berikutnya
        </button>
    </div>
</div>
