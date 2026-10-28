@props(['items' => null, 'tahun' => null])

@php
    $tahun = (int) ($tahun ?: date('Y'));
    $items ??= app(\App\Services\DashboardService::class)->getTopPasals($tahun);
@endphp

<div class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    {{-- Card Header --}}
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 sm:px-6 dark:border-gray-800">
        <div>
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Pasal yang Paling Banyak Disangkakan
            </h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Dominasi pelanggaran Undang-Undang pada Tahun Anggaran {{ $tahun }}
            </p>
        </div>

        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
            <i class="fi fi-rr-book-alt text-base"></i>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="flex-1 p-4 sm:p-5">
        @if (count($items) > 0)
            <div class="space-y-2.5">
                @foreach ($items as $item)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50/60 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-semibold text-gray-800 dark:text-white/90" title="{{ $item['title'] }}">
                                {{ $item['title'] }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400" title="{{ $item['description'] }}">
                                {{ $item['description'] }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold tabular-nums text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                            {{ $item['count'] }} Berkas
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-center px-4">
                <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                    <i class="fi fi-rr-book-alt text-lg"></i>
                </div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Belum ada pasal yang disangkakan
                </p>
                <p class="mt-1 max-w-xs text-xs text-gray-400 dark:text-gray-500">
                    Daftar pasal dominan akan muncul secara otomatis setelah berkas perkara memiliki pasal yang disangkakan pada tahun {{ $tahun }}.
                </p>
            </div>
        @endif
    </div>

    <div class="border-t border-gray-200 px-5 py-3 sm:px-6 dark:border-gray-800">
        <p class="text-xs text-gray-400 dark:text-gray-500">
            Catatan: Satu berkas perkara dapat memuat lebih dari satu pasal yang disangkakan.
        </p>
    </div>
</div>
