@php
    $perkaraList ??= app(\App\Services\DashboardService::class)->getPriorityProgressPerkara(request());
@endphp

<div x-data="{
    showQuickView: false,
    selectedItem: null,
    openQuickView(item) {
        this.selectedItem = item;
        this.showQuickView = true;
    }
}">

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1350px]">

            <thead>
                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">

                    <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
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

                    <tr
                        @dblclick="openQuickView({{ json_encode($perkara) }})"
                        class="cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        title="Klik ganda untuk membuka Ringkasan Cepat"
                    >

                        {{-- No --}}
                        <td class="w-12 px-5 py-5 text-sm text-gray-600 dark:text-gray-400">
                            {{ $index + 1 }}
                        </td>

                        {{-- Nomor --}}
                        <td class="px-5 py-5">
                            <a
                                href="{{ $perkara['detail_url'] ?? '#' }}"
                                class="text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $perkara['nomor'] }}
                            </a>
                        </td>

                        {{-- Tersangka --}}
                        <td class="px-5 py-5 text-sm text-gray-700 dark:text-gray-300">
                            <div class="flex items-center gap-1.5">
                                <span>{{ $perkara['tersangka'] }}</span>
                                @if (!empty($perkara['is_dpo']))
                                    <span class="inline-flex items-center gap-0.5 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                        <i class="fi fi-rr-shield-exclamation text-[10px]"></i>
                                        <span>DPO</span>
                                    </span>
                                @endif
                            </div>
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

                                        <div class="h-0.5 flex-1 {{ $perkara['progress'] >= 55 ? 'bg-success-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                                    </div>

                                    {{-- Tahap I --}}
                                    <div class="flex min-w-0 flex-1 items-center">

                                        <div class="relative flex shrink-0 items-center justify-center">

                                            @if ($perkara['progress'] >= 55)
                                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-success-500 text-[10px] font-semibold text-white">
                                                    <i class="fi fi-rr-check text-[10px]"></i>
                                                </span>
                                            @else
                                                <span class="flex h-6 w-6 items-center justify-center rounded-full border border-gray-300 bg-white text-[10px] text-gray-400 dark:border-gray-700 dark:bg-gray-900">
                                                    2
                                                </span>
                                            @endif

                                        </div>

                                        <div class="h-0.5 flex-1 {{ $perkara['progress'] >= 85 ? 'bg-success-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                                    </div>

                                    {{-- P-21 --}}
                                    <div class="flex min-w-0 flex-1 items-center">

                                        <div class="relative flex shrink-0 items-center justify-center">

                                            @if ($perkara['progress'] >= 85)
                                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-success-500 text-[10px] font-semibold text-white">
                                                    <i class="fi fi-rr-check text-[10px]"></i>
                                                </span>
                                            @else
                                                <span class="flex h-6 w-6 items-center justify-center rounded-full border border-gray-300 bg-white text-[10px] text-gray-400 dark:border-gray-700 dark:bg-gray-900">
                                                    3
                                                </span>
                                            @endif

                                        </div>

                                        <div class="h-0.5 flex-1 {{ $perkara['progress'] >= 100 ? 'bg-success-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                                    </div>

                                    {{-- Tahap II --}}
                                    <div class="relative flex shrink-0 items-center justify-center">

                                        @if ($perkara['progress'] >= 100)
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-success-500 text-[10px] font-semibold text-white">
                                                <i class="fi fi-rr-check text-[10px]"></i>
                                            </span>
                                        @else
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full border border-gray-300 bg-white text-[10px] text-gray-400 dark:border-gray-700 dark:bg-gray-900">
                                                4
                                            </span>
                                        @endif

                                    </div>

                                </div>

                                {{-- Timeline Labels --}}
                                <div class="mt-2 grid grid-cols-4 text-xs text-gray-400 dark:text-gray-500">

                                    <div>
                                        <p class="font-medium text-gray-600 dark:text-gray-400">
                                            SPDP
                                        </p>

                                        <p>{{ $perkara['spdp'] ?? '-' }}</p>
                                    </div>

                                    <div class="text-center">
                                        <p class="font-medium text-gray-600 dark:text-gray-400">
                                            TAHAP I
                                        </p>

                                        <p>{{ $perkara['tahap1'] ?? '-' }}</p>
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
                        <td class="px-5 py-5 text-center">
                            <x-common.table-dropdown>
                                <button
                                    type="button"
                                    @click="openQuickView({{ json_encode($perkara) }})"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                >
                                    <i class="fi fi-rr-eye text-xs text-brand-600"></i>
                                    <span>Ringkasan Cepat</span>
                                </button>

                                <a
                                    href="{{ $perkara['detail_url'] ?? '#' }}"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                >
                                    <i class="fi fi-rr-arrow-up-right-from-square text-xs text-gray-500"></i>
                                    <span>Lihat Perkara</span>
                                </a>
                            </x-common.table-dropdown>
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
            perkara prioritas
        </p>

        <div>
            <a
                href="{{ route('progress-perkara.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400"
            >
                <span>Buka Seluruh Progress Perkara</span>
                <i class="fi fi-rr-arrow-right text-xs"></i>
            </a>
        </div>

    </div>

    {{-- Modal Quick-View Detail Perkara (Tanpa Kolom Aksi Edit/Delete) --}}
    <div
        x-show="showQuickView"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="showQuickView = false"
    >
        <div
            x-show="showQuickView"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showQuickView = false"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
        ></div>

        <div
            x-show="showQuickView"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            {{-- Header Modal --}}
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <i class="fi fi-rr-document-signed text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Ringkasan Berkas Perkara
                        </h3>
                        <p class="font-mono text-xs text-gray-500 dark:text-gray-400" x-text="selectedItem?.nomor || '-'"></p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="showQuickView = false"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                >
                    <i class="fi fi-rr-cross-small text-xl"></i>
                </button>
            </div>

            {{-- Body Modal --}}
            <div class="flex-1 space-y-4 overflow-y-auto p-6" x-show="selectedItem">
                {{-- Banner DPO jika aktif --}}
                <template x-if="selectedItem?.is_dpo">
                    <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-semibold text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400">
                        <i class="fi fi-rr-shield-exclamation text-base"></i>
                        <span>Status Tersangka: Daftar Pencarian Orang (DPO) Aktif</span>
                    </div>
                </template>

                {{-- Grid Informasi Utama --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nama Tersangka</p>
                        <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-white/90" x-text="selectedItem?.tersangka || '-'"></p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            NIK: <span class="font-mono text-gray-700 dark:text-gray-300" x-text="selectedItem?.nik || '-'"></span>
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nomor Sprindik</p>
                        <p class="mt-0.5 font-mono text-sm font-semibold text-gray-800 dark:text-white/90" x-text="selectedItem?.sprindik || '-'"></p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Tanggal: <span class="text-gray-700 dark:text-gray-300" x-text="selectedItem?.tanggal_sprindik || '-'"></span>
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                        <p class="text-xs text-gray-500 dark:text-gray-400">PPNS Utama</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90" x-text="selectedItem?.ppns_utama || '-'"></p>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            Anggota: <span x-text="selectedItem?.ppns_anggota || '-'"></span>
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pasal Berlapis</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90" x-text="selectedItem?.pasal || '-'"></p>
                    </div>
                </div>

                {{-- Status & Progress Bar --}}
                <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Tahap Terakhir:</span>
                            <span class="ml-1 inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400" x-text="selectedItem?.tahap || '-'"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Progress:</span>
                            <span class="ml-1 text-sm font-bold text-gray-800 dark:text-white/90" x-text="(selectedItem?.progress || 0) + '%'"></span>
                        </div>
                    </div>
                    <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div
                            class="h-full rounded-full bg-brand-500 transition-all duration-300"
                            :style="'width: ' + (selectedItem?.progress || 0) + '%'"
                        ></div>
                    </div>
                </div>

                {{-- Riwayat Tahapan Tercatat --}}
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Riwayat Tahapan Tercatat
                    </h4>
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-800/60">
                                <tr>
                                    <th class="px-3.5 py-2 font-semibold text-gray-600 dark:text-gray-300">Tahapan</th>
                                    <th class="px-3.5 py-2 font-semibold text-gray-600 dark:text-gray-300">Nomor Surat</th>
                                    <th class="px-3.5 py-2 font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                                    <th class="px-3.5 py-2 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                <template x-for="(t, idx) in (selectedItem?.tahapan_list || [])" :key="idx">
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                        <td class="px-3.5 py-2 font-medium text-gray-800 dark:text-white/90" x-text="t.jenis"></td>
                                        <td class="px-3.5 py-2 font-mono text-gray-600 dark:text-gray-300" x-text="t.nomor"></td>
                                        <td class="px-3.5 py-2 text-gray-500 dark:text-gray-400" x-text="t.tanggal"></td>
                                        <td class="px-3.5 py-2">
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                :class="t.status === 'Final' ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400'"
                                                x-text="t.status"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!selectedItem?.tahapan_list || selectedItem?.tahapan_list.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-3.5 py-4 text-center text-gray-400">
                                            Belum ada tahapan progress yang dicatat.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Footer Modal (Read-Only) --}}
            <div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-3.5 dark:border-gray-800 dark:bg-gray-900/50">
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    Mode Pratinjau Cepat (Hanya Baca)
                </span>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="showQuickView = false"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Tutup
                    </button>
                    <a
                        :href="selectedItem?.detail_url"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                    >
                        <span>Buka Detail Penuh</span>
                        <i class="fi fi-rr-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
