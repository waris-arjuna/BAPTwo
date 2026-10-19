@props(['statistics' => null, 'tahun' => null])

@php
    $tahun ??= (int) date('Y');
    $statistics ??= app(\App\Services\DashboardService::class)->getStatistics($tahun);
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

    @foreach ($statistics as $stat)
        @php
            $isUrgentAction = $stat['title'] === 'Menunggu Verifikasi Penyidik' && (int)$stat['value'] > 0;
            $isPrimaryWorkload = $stat['title'] === 'Perkara Berjalan';
        @endphp
        <div class="relative rounded-2xl border bg-white p-5 shadow-theme-xs dark:bg-white/[0.03] {{ $isUrgentAction ? 'border-warning-300 ring-1 ring-warning-400/20 dark:border-warning-500/40' : ($isPrimaryWorkload ? 'border-brand-300/80 dark:border-brand-500/30' : 'border-gray-200 dark:border-gray-800') }}">

            <div class="flex items-start justify-between gap-3">

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ $stat['title'] }}
                        </p>
                        @if ($isUrgentAction)
                            <span class="inline-flex rounded-full bg-warning-100 px-1.5 py-0.5 text-[10px] font-semibold text-warning-800 dark:bg-warning-500/20 dark:text-warning-300">
                                {{ __('Perlu Tindakan') }}
                            </span>
                        @endif
                    </div>

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