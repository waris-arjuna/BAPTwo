@php
    /*
    |--------------------------------------------------------------------------
    | Mock Data — Rata-rata Penyelesaian per Tahap
    |--------------------------------------------------------------------------
    | Urutan workflow BAPTwo:
    |
    | SPDP
    |   ↓
    | Tahap I
    |   ↓
    | P-21
    |   ↓
    | Tahap II
    |   ↓
    | Putusan Pengadilan
    |
    | Nilai saat ini masih mockup UI.
    | Nantinya akan dihitung dari data perkara aktual.
    |--------------------------------------------------------------------------
    */
    $stageAverages = [
        [
            'code' => 'SPDP',
            'label' => 'SPDP',
            'avg_days' => 3,
            'icon_type' => 'spdp',
            'icon_bg' => 'bg-blue-50 dark:bg-blue-500/10',
            'icon_text' => 'text-blue-600 dark:text-blue-400',
            'number_text' => 'text-blue-600 dark:text-blue-400',
        ],
        [
            'code' => 'TAHAP_1',
            'label' => 'Tahap I',
            'avg_days' => 29,
            'icon_type' => 'stage-1',
            'icon_bg' => 'bg-green-50 dark:bg-green-500/10',
            'icon_text' => 'text-green-600 dark:text-green-400',
            'number_text' => 'text-green-600 dark:text-green-400',
        ],
        [
            'code' => 'P21',
            'label' => 'P-21',
            'avg_days' => 55,
            'icon_type' => 'p21',
            'icon_bg' => 'bg-purple-50 dark:bg-purple-500/10',
            'icon_text' => 'text-purple-600 dark:text-purple-400',
            'number_text' => 'text-purple-600 dark:text-purple-400',
        ],
        [
            'code' => 'TAHAP_2',
            'label' => 'Tahap II',
            'avg_days' => null,
            'icon_type' => 'stage-2',
            'icon_bg' => 'bg-orange-50 dark:bg-orange-500/10',
            'icon_text' => 'text-orange-600 dark:text-orange-400',
            'number_text' => 'text-orange-600 dark:text-orange-400',
        ],
        [
            'code' => 'PUTUSAN_PENGADILAN',
            'label' => 'Putusan Pengadilan',
            'avg_days' => null,
            'icon_type' => 'court',
            'icon_bg' => 'bg-indigo-50 dark:bg-indigo-500/10',
            'icon_text' => 'text-indigo-600 dark:text-indigo-400',
            'number_text' => 'text-indigo-600 dark:text-indigo-400',
        ],
    ];
@endphp


<div
    class="h-full rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
>
    {{-- ============================================================
         HEADER
         ============================================================ --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Rata-rata Penyelesaian per Tahap
        </h3>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Tahun Berjalan 2026
        </p>
    </div>


    {{-- ============================================================
         STAGE FLOW
         ============================================================ --}}
    <div class="overflow-x-auto">
        <div class="grid min-w-[900px] grid-cols-5 gap-3">

            @foreach ($stageAverages as $index => $stage)

                <div class="relative">

                    {{-- ==================================================
                         STAGE CARD
                         ================================================== --}}
                    <div
                        class="h-full rounded-xl border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-900"
                    >

                        {{-- Icon + Step --}}
                        <div class="flex items-start justify-between gap-3">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $stage['icon_bg'] }} {{ $stage['icon_text'] }}"
                            >
                                @if ($stage['icon_type'] === 'spdp')

                                    {{-- SPDP Icon --}}
                                    <svg
                                        class="h-6 w-6"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M5 12H19"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M14 7L19 12L14 17"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>

                                @elseif ($stage['icon_type'] === 'stage-1')

                                    {{-- Tahap I Icon --}}
                                    <span class="text-base font-bold">
                                        1
                                    </span>

                                @elseif ($stage['icon_type'] === 'p21')

                                    {{-- P-21 Icon --}}
                                    <span class="text-xs font-bold">
                                        P-21
                                    </span>

                                @elseif ($stage['icon_type'] === 'stage-2')

                                    {{-- Tahap II Icon --}}
                                    <span class="text-base font-bold">
                                        2
                                    </span>

                                @elseif ($stage['icon_type'] === 'court')

                                    {{-- Putusan Pengadilan Icon --}}
                                    <svg
                                        class="h-6 w-6"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M12 4V20"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M5 8H19"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M7 8L4.5 14H9.5L7 8Z"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M17 8L14.5 14H19.5L17 8Z"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M8 20H16"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                        />
                                    </svg>

                                @endif
                            </div>

                        </div>


                        {{-- ==================================================
                             STAGE INFORMATION
                             ================================================== --}}
                        <div class="mt-4">

                            <p class="text-sm font-semibold leading-5 text-gray-900 dark:text-white">
                                {{ $stage['label'] }}
                            </p>


                        </div>


                        {{-- ==================================================
                             AVERAGE DAYS
                             ================================================== --}}
                        <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Rata-rata
                            </p>

                            <div class="mt-1 flex items-baseline gap-1">

                                <span
                                    class="text-2xl font-bold {{ $stage['number_text'] }}"
                                >
                                    {{ $stage['avg_days'] ?? '-' }}
                                </span>

                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Hari Kerja
                                </span>

                            </div>

                        </div>

                    </div>


                    {{-- ==================================================
                         ARROW
                         Hanya desktop.
                         ================================================== --}}
                    @if ($index < count($stageAverages) - 1)
                        <div
                            class="pointer-events-none absolute -right-3 top-1/2 z-10 -translate-y-1/2 xl:flex"
                        >
                            <div
                                class="flex h-6 w-6 
                                items-center justify-center 
                                rounded-full border border-gray-200 bg-white text-gray-400 dark:border-gray-700 dark:bg-gray-900"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M9 5L16 12L9 19"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach

        </div>
    </div>

</div>