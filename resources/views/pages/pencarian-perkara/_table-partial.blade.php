{{-- Header Hasil Pencarian --}}
<div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <i class="fi fi-rr-document-signed text-brand-500"></i>
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
            Hasil Pencarian
        </h3>
        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold tabular-nums text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
            {{ $berkasList->total() }} Ditemukan
        </span>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="w-full min-w-[1100px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Berkas &amp; Perkara
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tersangka &amp; NIK
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Pasal Disangkakan
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    PPNS Utama
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tahap &amp; Progress
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Status &amp; Tenggang Waktu
                </th>

                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Aksi
                </th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse ($berkasList as $berkas)
                @php
                    $perkara = $berkas->perkara;
                    $tersangka = $berkas->tersangka;
                    $ppnsUtama = $perkara?->ppnsUtama->first();
                    $statusFormal = $berkas->status_formal;
                    $statusProses = $berkas->status_proses ?? 'Baru';
                    $progress = $berkas->progress_persen ?? 0;
                    $slaBadge = $berkas->sla_badge;
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    {{-- Kolom No --}}
                    <td class="w-12 px-5 py-4 text-sm tabular-nums text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($berkasList->currentPage() - 1) * $berkasList->perPage() }}
                    </td>

                    {{-- Nomor Berkas & Perkara --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-folder text-brand-500"></i>
                            <span class="font-mono text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $berkas->nomor_berkas ?: 'Tanpa Nomor Berkas' }}
                            </span>
                        </div>
                        @if ($perkara)
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                LK: <a href="{{ route('laporan-kejadian.show', $perkara) }}" class="font-mono text-brand-600 hover:underline">{{ $perkara->nomor_laporan_kejadian }}</a>
                            </p>
                            @if ($perkara->nomor_surat_perintah)
                                <p class="text-xs text-gray-400">
                                    Sprindik: <span class="font-mono">{{ $perkara->nomor_surat_perintah }}</span>
                                </p>
                            @endif
                        @endif
                    </td>

                    {{-- Tersangka & NIK --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-1.5">
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $tersangka?->nama ?: '-' }}
                            </p>
                            @if ($berkas->is_dpo)
                                <span class="rounded-full bg-error-50 px-2 py-0.5 text-[10px] font-semibold text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                    DPO
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400">
                            NIK: <span class="font-mono tabular-nums">{{ $tersangka?->nik ?: '-' }}</span>
                        </p>
                    </td>

                    {{-- Pasal Disangkakan --}}
                    <td class="px-5 py-4">
                        <div class="flex max-w-[200px] flex-wrap gap-1">
                            @forelse ($berkas->pasals as $pasal)
                                <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                                    {{ $pasal->pasal }}
                                </span>
                            @empty
                                <span class="text-xs italic text-gray-400 dark:text-gray-500">-</span>
                            @endforelse
                        </div>
                    </td>

                    {{-- PPNS Utama --}}
                    <td class="px-5 py-4">
                        @if ($ppnsUtama)
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $ppnsUtama->nama }}</p>
                            <p class="text-xs text-gray-400">NIP: <span class="font-mono tabular-nums">{{ $ppnsUtama->nip ?: '-' }}</span></p>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">-</span>
                        @endif
                    </td>

                    {{-- Tahap & Progress --}}
                    <td class="px-5 py-4">
                        <div class="w-32">
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $statusProses }}</span>
                                <span class="font-semibold tabular-nums text-gray-800 dark:text-white/90">{{ $progress }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full w-full origin-left rounded-full transition-transform duration-300 ease-out {{ $progress >= 100 ? 'bg-success-500' : ($progress >= 50 ? 'bg-brand-500' : 'bg-warning-500') }}"
                                    style="transform: scaleX({{ $progress / 100 }})"
                                ></div>
                            </div>
                        </div>
                    </td>

                    {{-- Status Formal & SLA --}}
                    <td class="px-5 py-4">
                        <div class="flex flex-col gap-1">
                            <span class="inline-flex w-fit items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusFormal === 'selesai' ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' : ($statusFormal === 'tidak_selesai' ? 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400' : 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300') }}">
                                {{ $statusFormal ? ucfirst(str_replace('_', ' ', $statusFormal)) : 'Dalam Proses' }}
                            </span>
                            @if ($slaBadge && $berkas->tanggal_spdp)
                                <span class="inline-flex w-fit items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $slaBadge['badge_class'] }}">
                                    <i class="fi {{ $slaBadge['icon'] }} text-[9px]"></i>
                                    {{ $slaBadge['label'] }}
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Aksi --}}
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
                                <a
                                    href="{{ route('laporan-kejadian.show', $perkara) }}"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                >
                                    <i class="fi fi-rr-document-signed text-xs text-gray-500"></i>
                                    <span>Lihat Laporan Kejadian</span>
                                </a>
                            </x-common.table-dropdown>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                                <i class="fi fi-rr-search-alt text-xl"></i>
                            </div>
                            <p class="font-medium text-gray-600 dark:text-gray-300">Tidak ada perkara ditemukan</p>
                            <p class="mt-1 text-xs text-gray-400">Silakan ubah kata kunci atau sesuaikan filter pencarian.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination Links --}}
    @if ($berkasList->hasPages())
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $berkasList->links() }}
        </div>
    @endif
</div>
