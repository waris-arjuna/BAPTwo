@php
    $statistics = [
        [
            'title' => 'Perkara Aktif',
            'value' => '2',
            'description' => 'Perkara pada tahun berjalan',
            'icon' => 'document',
            'iconClass' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        ],
        [
            'title' => 'Perkara Berjalan',
            'value' => '2',
            'description' => 'Sedang dalam proses penyidikan',
            'icon' => 'play-circle',
            'iconClass' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
        ],
        [
            'title' => 'Menunggu Verifikasi Penyidik',
            'value' => '0',
            'description' => 'Menunggu tindakan penyidik',
            'icon' => 'verification',
            'iconClass' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
        ],
        [
            'title' => 'Selesai',
            'value' => '0',
            'description' => 'Perkara selesai',
            'icon' => 'check',
            'iconClass' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
        ],
        [
            'title' => 'Rata-rata Waktu Penyelesaian',
            'value' => '-',
            'description' => 'Dari SPDP s.d. Tahap II',
            'icon' => 'time',
            'iconClass' => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400',
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

    @foreach ($statistics as $stat)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="flex items-start justify-between gap-3">

                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $stat['title'] }}
                    </p>

                    <h3 class="mt-3 text-2xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $stat['value'] }}
                    </h3>

                    <p class="mt-1 text-xs leading-5 text-gray-400 dark:text-gray-500">
                        {{ $stat['description'] }}
                    </p>

                    @if ($stat['title'] === 'Rata-rata Waktu Penyelesaian')
                        <p class="mt-1 text-[11px] leading-4 text-gray-400 dark:text-gray-500">
                            Maks. 60 Hari Kalender
                        </p>
                    @endif
                </div>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $stat['iconClass'] }}">

                    @if ($stat['icon'] === 'document')
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M6 3.75A1.75 1.75 0 0 1 7.75 2h6.69c.46 0 .9.18 1.23.51l3.82 3.82c.33.33.51.77.51 1.23v12.69A1.75 1.75 0 0 1 18.25 22h-10.5A1.75 1.75 0 0 1 6 20.25V3.75Z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M14 2.5V8h5.5M9 12h6M9 16h6"
                            />
                        </svg>

                    @elseif ($stat['icon'] === 'play-circle')
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"
                            />
                        </svg>

                    @elseif ($stat['icon'] === 'verification')
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 14.75a4.25 4.25 0 1 0 0-8.5 4.25 4.25 0 0 0 0 8.5Z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M4.75 21a7.25 7.25 0 0 1 14.5 0M16.5 10.5l1.75 1.75L21 9.5"
                            />
                        </svg>

                    @elseif ($stat['icon'] === 'check')
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="8.5"
                                stroke-width="1.8"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="m8.5 12 2.25 2.25L15.5 9.5"
                            />
                        </svg>

                    @else
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 7v5l3 2"
                            />

                            <circle
                                cx="12"
                                cy="12"
                                r="8.5"
                                stroke-width="1.8"
                            />
                        </svg>
                    @endif

                </div>
            </div>

        </div>
    @endforeach

</div>