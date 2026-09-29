<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
>
    {{-- Header --}}
    <div
        class="flex flex-col gap-4 border-b border-gray-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800"
    >
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Progress Perkara
            </h3>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Monitoring progress tahapan perkara
            </p>
        </div>

        <div class="flex items-center gap-3">
            <select
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
                <option>Semua Perkara</option>
                <option>Perkara Aktif</option>
                <option>Perkara Berjalan</option>
                <option>Case Closed</option>
            </select>

            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                title="Filter"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M4 6h16" />
                    <path d="M7 12h10" />
                    <path d="M10 18h4" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-[1200px] w-full">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        No.
                    </th>

                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Nomor Perkara
                    </th>

                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Tersangka
                    </th>

                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Pasal
                    </th>

                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Tahap Saat Ini
                    </th>

                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Progress Tahapan
                    </th>

                    <th class="px-5 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Progress
                    </th>

                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Estimasi Selesai
                    </th>

                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Status
                    </th>

                    <th class="px-5 py-4"></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">

                {{-- Perkara 1 --}}
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="px-5 py-5 text-sm text-gray-600 dark:text-gray-400">
                        1
                    </td>

                    <td class="px-5 py-5">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            BPOM.BJB-001/IV/2026/PPNS
                        </p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Dilaporkan: 02 Jan 2026
                        </p>
                    </td>

                    <td class="px-5 py-5">
                        <p class="text-sm text-gray-900 dark:text-white">
                            Andi Saputra
                        </p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            1 orang
                        </p>
                    </td>

                    <td class="px-5 py-5 text-sm text-gray-600 dark:text-gray-400">
                        Pasal 196 UU 36/2009
                        <br>
                        (Pangan)
                    </td>

                    <td class="px-5 py-5">
                        <span
                            class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"
                        >
                            Tahap I
                        </span>
                    </td>

                    <td class="px-5 py-5">
                        <div class="min-w-[250px]">
                            <div class="flex items-center gap-0">

                                {{-- SPDP --}}
                                <div class="flex flex-1 items-center">
                                    <div class="flex flex-col items-center">
                                        <span class="h-3 w-3 rounded-full bg-green-500"></span>
                                    </div>

                                    <div class="h-0.5 flex-1 bg-green-500"></div>
                                </div>

                                {{-- Tahap I --}}
                                <div class="flex flex-1 items-center">
                                    <div class="flex flex-col items-center">
                                        <span class="h-3 w-3 rounded-full bg-blue-600"></span>
                                    </div>

                                    <div class="h-0.5 flex-1 bg-gray-200 dark:bg-gray-700"></div>
                                </div>

                                {{-- P-21 --}}
                                <div class="flex flex-1 items-center">
                                    <div>
                                        <span class="block h-3 w-3 rounded-full border-2 border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-900"></span>
                                    </div>

                                    <div class="h-0.5 flex-1 bg-gray-200 dark:bg-gray-700"></div>
                                </div>

                                {{-- Tahap II --}}
                                <div>
                                    <span class="block h-3 w-3 rounded-full border-2 border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-900"></span>
                                </div>
                            </div>

                            <div class="mt-2 grid grid-cols-4 text-[10px] text-gray-500 dark:text-gray-400">
                                <div>
                                    <p class="font-medium">SPDP</p>
                                    <p>15% · 02 Jan</p>
                                </div>

                                <div>
                                    <p class="font-medium">Tahap I</p>
                                    <p>55% · 20 Feb</p>
                                </div>

                                <div>
                                    <p class="font-medium">P-21</p>
                                    <p>-</p>
                                </div>

                                <div>
                                    <p class="font-medium">Tahap II</p>
                                    <p>-</p>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-5 text-center">
                        <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                            55%
                        </span>
                    </td>

                    <td class="px-5 py-5 text-sm text-gray-600 dark:text-gray-400">
                        10 Jul 2026
                    </td>

                    <td class="px-5 py-5">
                        <span
                            class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"
                        >
                            Dalam Proses
                        </span>
                    </td>

                    <td class="px-5 py-5 text-right">
                        <button
                            type="button"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <circle cx="5" cy="12" r="1.5" />
                                <circle cx="12" cy="12" r="1.5" />
                                <circle cx="19" cy="12" r="1.5" />
                            </svg>
                        </button>
                    </td>
                </tr>

                {{-- Perkara 2 --}}
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="px-5 py-5 text-sm text-gray-600 dark:text-gray-400">
                        2
                    </td>

                    <td class="px-5 py-5">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            BPOM.BJB-002/IV/2026/PPNS
                        </p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Dilaporkan: 10 Feb 2026
                        </p>
                    </td>

                    <td class="px-5 py-5">
                        <p class="text-sm text-gray-900 dark:text-white">
                            Budi Hartono
                        </p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            1 orang
                        </p>
                    </td>

                    <td class="px-5 py-5 text-sm text-gray-600 dark:text-gray-400">
                        Pasal 197 UU 36/2009
                        <br>
                        (Obat)
                    </td>

                    <td class="px-5 py-5">
                        <span
                            class="inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-600 dark:bg-green-500/10 dark:text-green-400"
                        >
                            P-21
                        </span>
                    </td>

                    <td class="px-5 py-5">
                        <div class="min-w-[250px]">
                            <div class="flex items-center">

                                {{-- SPDP --}}
                                <div class="flex flex-1 items-center">
                                    <span class="h-3 w-3 rounded-full bg-green-500"></span>
                                    <div class="h-0.5 flex-1 bg-green-500"></div>
                                </div>

                                {{-- Tahap I --}}
                                <div class="flex flex-1 items-center">
                                    <span class="h-3 w-3 rounded-full bg-green-500"></span>
                                    <div class="h-0.5 flex-1 bg-green-500"></div>
                                </div>

                                {{-- P-21 --}}
                                <div class="flex flex-1 items-center">
                                    <span class="h-3 w-3 rounded-full bg-blue-600"></span>
                                    <div class="h-0.5 flex-1 bg-gray-200 dark:bg-gray-700"></div>
                                </div>

                                {{-- Tahap II --}}
                                <span class="h-3 w-3 rounded-full border-2 border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-900"></span>
                            </div>

                            <div class="mt-2 grid grid-cols-4 text-[10px] text-gray-500 dark:text-gray-400">
                                <div>
                                    <p class="font-medium">SPDP</p>
                                    <p>15% · 10 Feb</p>
                                </div>

                                <div>
                                    <p class="font-medium">Tahap I</p>
                                    <p>55% · 12 Mar</p>
                                </div>

                                <div>
                                    <p class="font-medium">P-21</p>
                                    <p>85% · 20 Apr</p>
                                </div>

                                <div>
                                    <p class="font-medium">Tahap II</p>
                                    <p>-</p>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-5 text-center">
                        <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                            85%
                        </span>
                    </td>

                    <td class="px-5 py-5 text-sm text-gray-600 dark:text-gray-400">
                        05 Jun 2026
                    </td>

                    <td class="px-5 py-5">
                        <span
                            class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"
                        >
                            Dalam Proses
                        </span>
                    </td>

                    <td class="px-5 py-5 text-right">
                        <button
                            type="button"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <circle cx="5" cy="12" r="1.5" />
                                <circle cx="12" cy="12" r="1.5" />
                                <circle cx="19" cy="12" r="1.5" />
                            </svg>
                        </button>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>