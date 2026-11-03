@props(['statistics' => null, 'tahun' => null])

@php
    $tahun ??= (int) date('Y');
    $statistics ??= app(\App\Services\DashboardService::class)->getStatistics($tahun);
@endphp

<div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-5">

    @foreach ($statistics as $stat)
        @php
            $isUrgentAction = $stat['title'] === 'Menunggu Verifikasi Penyidik' && (int)$stat['value'] > 0;
            $isPrimaryWorkload = $stat['title'] === 'Perkara Berjalan';
        @endphp
        <div class="relative flex h-full flex-col justify-between rounded-2xl border bg-white p-4 shadow-theme-xs dark:bg-white/[0.03] {{ $isUrgentAction ? 'border-warning-300 ring-1 ring-warning-400/20 dark:border-warning-500/40' : ($isPrimaryWorkload ? 'border-brand-300/80 dark:border-brand-500/30' : 'border-gray-200 dark:border-gray-800') }}">

            <div class="flex items-start justify-between gap-3">

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ $stat['title'] }}
                        </p>
                        @if ($isUrgentAction)
                            <span class="inline-flex rounded-full bg-warning-100 px-1.5 py-0.5 text-[10px] font-semibold text-warning-800 dark:bg-warning-500/20 dark:text-warning-300">
                                {{ __('Perlu Tindakan') }}
                            </span>
                        @endif
                    </div>

                    <h3 class="mt-1.5 text-2xl font-bold tabular-nums text-gray-800 dark:text-white/90">
                        {{ $stat['value'] }}
                    </h3>

                    <p class="mt-1.5 text-xs leading-4 text-gray-400 dark:text-gray-500">
                        {{ $stat['description'] }}
                    </p>
                </div>

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $stat['iconClass'] }}">

                    @if ($stat['icon'] === 'document')
                        <i class="fi fi-rr-document text-base"></i>

                    @elseif ($stat['icon'] === 'play-circle')
                        <i class="fi fi-rr-play text-base"></i>

                    @elseif ($stat['icon'] === 'verification')
                        <i class="fi fi-rr-user-check text-base"></i>

                    @elseif ($stat['icon'] === 'check')
                        <i class="fi fi-rr-check-circle text-base"></i>

                    @else
                        <i class="fi fi-rr-clock text-base"></i>
                    @endif

                </div>
            </div>

        </div>
    @endforeach

</div>