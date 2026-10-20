@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div class="min-w-0 flex-1">
        @if (!empty($breadcrumbs))
            <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                @foreach ($breadcrumbs as $item)
                    @if (!empty($item['url']))
                        <a href="{{ $item['url'] }}" class="transition hover:text-brand-500">
                            {{ $item['label'] }}
                        </a>
                        <i class="fi fi-rr-angle-small-right text-xs text-gray-400 dark:text-gray-500" aria-hidden="true"></i>
                    @else
                        <span class="font-medium text-gray-700 dark:text-gray-300">
                            {{ $item['label'] }}
                        </span>
                    @endif
                @endforeach
            </nav>
        @endif

        <div class="flex min-h-[42px] items-center">
            <h1 data-page-title class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ $title }}
            </h1>
        </div>

        @if ($subtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if (isset($actions) && trim($actions) !== '')
        <div class="flex min-h-[42px] shrink-0 items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
