@php
    $perkaraList ??= app(\App\Services\MockStageService::class)->getProgressPerkara(request());
@endphp

{{-- Table --}}
<div class="overflow-x-auto">
    <table class="w-full min-w-[1350px]">

        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No.
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Perkara
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tersangka
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Pasal
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tahap Saat Ini
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Progress Tahapan
                </th>

                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Progress
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Estimasi Selesai
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Status
                </th>

                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Aksi
                </th>

            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">

            @forelse ($perkaraList as $index => $perkara)

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                    {{-- No --}}
                    <td class="px-5 py-5 text-sm text-gray-600 dark:text-gray-400">
                        {{ $index + 1 }}
                    </td>

                    {{-- Nomor --}}
                    <td class="px-5 py-5">
                        <a
                            href="#"
                            class="text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                        >
                            {{ $perkara['nomor'] }}
                        </a>
                    </td>

                    {{-- Tersangka --}}
                    <td class="px-5 py-5 text-sm text-gray-700 dark:text-gray-300">
                        {{ $perkara['tersangka'] }}
                    </td>

                    {{-- Pasal --}}
                    <td class="px-5 py-5">
                        <p class="max-w-[180px] text-sm leading-5 text-gray-700 dark:text-gray-300">
                            {{ $perkara['pasal'] }}
                        </p>
                    </td>

                    {{-- Tahap --}}
                    <td class="px-5 py-5">
                        <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                            {{ $perkara['tahap'] }}
                        </span>
                    </td>

                    {{-- Timeline --}}
                    <td class="px-5 py-5">
                        <div class="w-[360px]">

                            <div class="flex items-center">

                                {{-- SPDP --}}
                                <div class="flex min-w-0 flex-1 items-center">

                                    <div class="relative flex shrink-0 items-center justify-center">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-success-500 text-[10px] font-semibold text-white">
                                            <i class="fi fi-rr-check text-[10px]"></i>
                                        </span>
                                    </div>

                                    <div class="h-0.5 flex-1 bg-success-500"></div>
                                </div>

                                {{-- Tahap I --}}
                                <div class="flex min-w-0 flex-1 items-center">

                                    <div class="relative flex shrink-0 items-center justify-center">

                                        @if ($perkara['progress'] >= 55)
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-success-500 text-[10px] font-semibold text-white">
                                                <i class="fi fi-rr-check text-[10px]"></i>
                                            </span>
                                        @else
                                            <span class="h-6 w-6 rounded-full border-2 border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"></span>
                                        @endif

                                    </div>

                                    <div class="h-0.5 flex-1 {{ $perkara['progress'] >= 55 ? 'bg-success-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                                </div>

                                {{-- P21 --}}
                                <div class="flex min-w-0 flex-1 items-center">

                                    <div class="relative flex shrink-0 items-center justify-center">

                                        @if ($perkara['progress'] >= 85)
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-success-500 text-[10px] font-semibold text-white">
                                                <i class="fi fi-rr-check text-[10px]"></i>
                                            </span>
                                        @elseif ($perkara['progress'] >= 55)
                                            <span class="h-6 w-6 rounded-full border-2 border-brand-500 bg-brand-50 dark:bg-brand-500/10"></span>
                                        @else
                                            <span class="h-6 w-6 rounded-full border-2 border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"></span>
                                        @endif

                                    </div>

                                    <div class="h-0.5 flex-1 {{ $perkara['progress'] >= 85 ? 'bg-success-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                                </div>

                                {{-- Tahap II --}}
                                <div class="flex shrink-0 items-center">

                                    @if ($perkara['progress'] >= 100)
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-success-500 text-[10px] font-semibold text-white">
                                            <i class="fi fi-rr-check text-[10px]"></i>
                                        </span>
                                    @elseif ($perkara['progress'] >= 85)
                                        <span class="h-6 w-6 rounded-full border-2 border-brand-500 bg-brand-50 dark:bg-brand-500/10"></span>
                                    @else
                                        <span class="h-6 w-6 rounded-full border-2 border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"></span>
                                    @endif

                                </div>

                            </div>

                            {{-- Stage labels --}}
                            <div class="mt-2 grid grid-cols-4 text-[10px] text-gray-400 dark:text-gray-500">

                                <div>
                                    <p class="font-medium text-gray-600 dark:text-gray-400">
                                        SPDP
                                    </p>

                                    <p>{{ $perkara['spdp'] }}</p>
                                </div>

                                <div class="text-center">
                                    <p class="font-medium text-gray-600 dark:text-gray-400">
                                        TAHAP I
                                    </p>

                                    <p>{{ $perkara['tahap1'] }}</p>
                                </div>

                                <div class="text-center">
                                    <p class="font-medium text-gray-600 dark:text-gray-400">
                                        P-21
                                    </p>

                                    <p>{{ $perkara['p21'] ?? '-' }}</p>
                                </div>

                                <div class="text-right">
                                    <p class="font-medium text-gray-600 dark:text-gray-400">
                                        TAHAP II
                                    </p>

                                    <p>{{ $perkara['tahap2'] ?? '-' }}</p>
                                </div>

                            </div>

                        </div>
                    </td>

                    {{-- Progress % --}}
                    <td class="px-5 py-5 text-center">
                        <span class="text-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $perkara['progress'] }}%
                        </span>
                    </td>

                    {{-- Estimasi --}}
                    <td class="px-5 py-5">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $perkara['estimasi'] }}
                        </p>

                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            1 bulan kalender dari SPDP
                        </p>
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-5">
                        @if ($perkara['status'] === 'Dalam Proses')

                            <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                Dalam Proses
                            </span>

                        @elseif ($perkara['status'] === 'Selesai')

                            <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                Selesai
                            </span>

                        @else

                            <span class="inline-flex rounded-full bg-error-50 px-3 py-1 text-xs font-medium text-error-700 dark:bg-error-500/10 dark:text-error-400">
                                Tidak Selesai
                            </span>

                        @endif
                    </td>

                    {{-- Action --}}
                    <td class="px-5 py-5">
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
                                class="absolute right-0 top-11 z-20 w-40 rounded-lg border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            >
                                <a
                                    href="#"
                                    class="block rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                >
                                    Lihat Detail
                                </a>

                                <a
                                    href="#"
                                    class="block rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                >
                                    Lihat Perkara
                                </a>
                            </div>

                        </div>
                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="10" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No data available.') }}
                    </td>
                </tr>
            @endforelse

        </tbody>
    </table>
</div>

{{-- Footer --}}
<div class="flex flex-col gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Menampilkan
        <span class="font-medium text-gray-700 dark:text-gray-300">{{ count($perkaraList) > 0 ? '1–' . count($perkaraList) : '0' }}</span>
        dari
        <span class="font-medium text-gray-700 dark:text-gray-300">{{ count($perkaraList) }}</span>
        perkara
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
