@props([
    'cards' => [],
    'roleTitle' => 'Rincian Peran',
    'roles' => [],
])

@php
    $roleTotal = array_sum(array_column($roles, 'count'));
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($cards as $card)
        <div class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition-colors hover:border-gray-300 dark:border-gray-700/80 dark:bg-gray-800/70 dark:hover:border-gray-600">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        {{ $card['title'] }}
                    </p>
                    <p class="mt-2 text-title-sm font-bold tabular-nums text-gray-900 dark:text-white">
                        {{ number_format((int) $card['value'], 0, ',', '.') }}
                    </p>
                </div>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['iconClass'] }}">
                    <i class="fi {{ $card['icon'] }} text-lg leading-none" aria-hidden="true"></i>
                </div>
            </div>

            <p class="mt-3 text-xs tabular-nums text-gray-600 dark:text-gray-300">
                {{ $card['subtitle'] }}
            </p>
        </div>
    @endforeach

    <div class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition-colors hover:border-gray-300 dark:border-gray-700/80 dark:bg-gray-800/70 dark:hover:border-gray-600">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                    {{ __($roleTitle) }}
                </p>
                <p class="mt-2 text-title-sm font-bold tabular-nums text-gray-900 dark:text-white">
                    {{ count($roles) }}
                </p>
            </div>

            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                <i class="fi fi-rr-user-police text-lg leading-none" aria-hidden="true"></i>
            </div>
        </div>

        <div class="mt-3 space-y-2">
            <div class="flex h-2 w-full gap-0.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700/80" aria-hidden="true">
                @if ($roleTotal > 0)
                    @foreach ($roles as $role)
                        @if ($role['count'] > 0)
                            <span
                                class="h-full min-w-[6px] first:rounded-s-full last:rounded-e-full {{ $role['color'] }}"
                                style="flex: {{ $role['count'] }} 1 0%"
                                title="{{ $role['label'] }}: {{ $role['count'] }}"
                            ></span>
                        @endif
                    @endforeach
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs tabular-nums text-gray-700 dark:text-gray-200">
                @foreach ($roles as $role)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $role['color'] }}" aria-hidden="true"></span>
                        <span>{{ __($role['label']) }}</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($role['count'], 0, ',', '.') }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
