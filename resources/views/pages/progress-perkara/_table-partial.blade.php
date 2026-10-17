<div class="overflow-x-auto">
    <table class="w-full min-w-[1200px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Berkas / Tersangka
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Sprindik &amp; PPNS Utama
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Pasal Berlapis
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tahap Saat Ini
                </th>

                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Progress
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
            @forelse ($berkasList as $index => $berkas)
                @php
                    $perkara = $berkas->perkara;
                    $tersangka = $berkas->tersangka;
                    $ppnsUtama = $perkara?->ppnsUtama->first();
                    $progress = (int) ($berkas->progress_persen ?? 0);

                    $progressColor = match (true) {
                        $progress >= 100 => 'bg-success-500',
                        $progress >= 85 => 'bg-brand-500',
                        $progress >= 55 => 'bg-warning-500',
                        $progress >= 15 => 'bg-blue-500',
                        default => 'bg-gray-400',
                    };

                    $formalStatus = $berkas->status_formal ?: 'Dalam Proses';
                    $formalClass = match ($formalStatus) {
                        'Selesai' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
                        'Tidak Selesai' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                    };
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="w-12 px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($berkasList->currentPage() - 1) * $berkasList->perPage() }}
                    </td>

                    <td class="px-5 py-4">
                        @if ($perkara)
                            <a
                                href="{{ route('perkara.show', $perkara) }}"
                                class="text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $berkas->nomor_berkas ?: '(Draft Belum Bernomor)' }}
                            </a>
                        @else
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $berkas->nomor_berkas ?: '(Draft Belum Bernomor)' }}
                            </span>
                        @endif
                        <p class="mt-0.5 text-xs text-gray-700 dark:text-gray-300">
                            Tersangka: <strong>{{ $tersangka?->nama ?? '-' }}</strong>
                            @if ($tersangka?->alias)
                                <span class="text-gray-400">({{ $tersangka->alias }})</span>
                            @endif
                        </p>
                    </td>

                    <td class="px-5 py-4">
                        @if ($perkara)
                            <a
                                href="{{ route('sprindik.show', $perkara) }}"
                                class="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400"
                            >
                                {{ $perkara->nomor_surat_perintah ?: '(Tanpa Sprindik)' }}
                            </a>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            PPNS: {{ $ppnsUtama?->nama ?? '-' }}
                        </p>
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex max-w-[220px] flex-wrap gap-1">
                            @forelse ($berkas->pasals as $pasal)
                                <span class="inline-flex items-center rounded bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300" title="{{ $pasal->label() }}">
                                    {{ $pasal->pasal }}
                                </span>
                            @empty
                                <span class="text-xs italic text-gray-400">Belum ada pasal</span>
                            @endforelse
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                            {{ $berkas->status_proses ?: 'Baru' }}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <div class="mx-auto w-32 text-center">
                            <div class="mb-1 flex items-center justify-between text-xs font-medium">
                                <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                <span class="text-gray-800 dark:text-white/90">{{ $progress }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-full {{ $progressColor }} transition-all duration-300" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex flex-col gap-1">
                            <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $formalClass }}">
                                {{ $formalStatus }}
                            </span>
                            @if ($berkas->is_dpo)
                                <span class="inline-flex w-fit items-center gap-1 rounded-full bg-error-100 px-2.5 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                    <i class="fi fi-rr-cross-circle text-[10px]"></i>
                                    DPO Aktif
                                </span>
                            @endif
                            @if ($berkas->tanggal_spdp)
                                @php
                                    $slaBadge = $berkas->sla_badge;
                                @endphp
                                <span class="inline-flex w-fit items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium {{ $slaBadge['badge_class'] }}">
                                    <i class="fi {{ $slaBadge['icon'] }} text-[10px]"></i>
                                    {{ $slaBadge['label'] }}
                                </span>
                            @endif
                        </div>
                    </td>

                    <td class="px-5 py-4 text-center">
                        @if ($perkara)
                            <x-common.table-dropdown>
                                <a
                                    href="{{ route('perkara.show', $perkara) }}"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                >
                                    <i class="fi fi-rr-eye text-xs text-brand-600"></i>
                                    <span>Lihat Detail Perkara</span>
                                </a>

                                @if ($berkas->tahapan->isNotEmpty())
                                    <a
                                        href="{{ route('berkas-perkara.tahapan.show', [$berkas->id, $berkas->tahapan->sortByDesc('created_at')->first()->id]) }}"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                    >
                                        <i class="fi fi-rr-document-signed text-xs text-gray-500"></i>
                                        <span>Tahapan Terakhir</span>
                                    </a>
                                @endif
                            </x-common.table-dropdown>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No data available.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="flex flex-col gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Menampilkan <span class="font-medium text-gray-700 dark:text-gray-300">{{ $berkasList->firstItem() ?? 0 }}–{{ $berkasList->lastItem() ?? 0 }}</span>
        dari <span class="font-medium text-gray-700 dark:text-gray-300">{{ $berkasList->total() }}</span> Berkas Perkara
    </p>

    <div>
        {{ $berkasList->links() }}
    </div>
</div>
