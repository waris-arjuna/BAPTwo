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
                        <i class="fi fi-rr-document text-lg"></i>

                    @elseif ($stat['icon'] === 'play-circle')
                        <i class="fi fi-rr-play text-lg"></i>

                    @elseif ($stat['icon'] === 'verification')
                        <i class="fi fi-rr-user-check text-lg"></i>

                    @elseif ($stat['icon'] === 'check')
                        <i class="fi fi-rr-check-circle text-lg"></i>

                    @else
                        <i class="fi fi-rr-clock text-lg"></i>
                    @endif

                </div>
            </div>

        </div>
    @endforeach

</div>