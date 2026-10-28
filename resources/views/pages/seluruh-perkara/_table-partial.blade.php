<div class="overflow-x-auto">
    <table class="w-full min-w-[1100px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Sprindik / Perkara
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Laporan Kejadian
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    TKP &amp; Wilayah
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    PPNS Utama
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tersangka &amp; Berkas
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
            @forelse ($perkaras as $index => $perkara)
                @php
                    $isLocked = $perkara->isPerkaraLocked();
                    $tkpAlamat = $perkara->tkp?->alamat;
                    $ppnsUtama = $perkara->ppnsUtama->first();
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    {{-- Kolom No --}}
                    <td class="w-12 px-5 py-4 text-sm tabular-nums text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($perkaras->currentPage() - 1) * $perkaras->perPage() }}
                    </td>

                    {{-- Nomor Sprindik & Perkara --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('perkara.show', $perkara) }}"
                                class="font-mono text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $perkara->nomor_surat_perintah ?: '(Draft Sprindik)' }}
                            </a>
                            @if ($isLocked)
                                <i class="fi fi-rr-lock text-xs text-gray-400 dark:text-gray-500" title="Tahap Final &amp; Terkunci"></i>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Tgl: <span class="tabular-nums">{{ $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}</span>
                        </p>
                    </td>

                    {{-- Laporan Kejadian --}}
                    <td class="px-5 py-4">
                        <a
                            href="{{ route('laporan-kejadian.show', $perkara) }}"
                            class="font-mono text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                        >
                            {{ $perkara->nomor_laporan_kejadian }}
                        </a>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Tgl: <span class="tabular-nums">{{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}</span>
                        </p>
                    </td>

                    {{-- TKP & Wilayah --}}
                    <td class="px-5 py-4">
                        @if ($perkara->tkp)
                            <div class="max-w-[200px]">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ $tkpAlamat?->kabupaten_nama ?: ($tkpAlamat?->provinsi_nama ?: '-') }}
                                </p>
                                <p class="truncate text-xs capitalize text-gray-500 dark:text-gray-400">
                                    {{ $perkara->tkp->klasifikasi ?: 'TKP Belum Dispesifikasi' }}
                                </p>
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum diisi</span>
                        @endif
                    </td>

                    {{-- PPNS Utama --}}
                    <td class="px-5 py-4">
                        @if ($ppnsUtama)
                            <div class="max-w-[160px]">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $ppnsUtama->nama }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">NIP: {{ $ppnsUtama->nip ?: '-' }}</p>
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum ditunjuk</span>
                        @endif
                    </td>

                    {{-- Tersangka & Berkas Perkara --}}
                    <td class="px-5 py-4">
                        @if ($perkara->tersangkas->isNotEmpty())
                            <div class="flex flex-col gap-2">
                                @foreach ($perkara->tersangkas as $tersangka)
                                    @php
                                        $berkas = $tersangka->berkasPerkara;
                                    @endphp
                                    <div class="flex flex-col gap-0.5 text-xs">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fi fi-rr-user text-[11px] text-gray-400 shrink-0"></i>
                                            <span class="font-medium text-gray-800 dark:text-white/90 break-words" title="{{ $tersangka->nama }}">{{ $tersangka->nama }}</span>
                                            @if ($berkas?->is_dpo)
                                                <span class="rounded-full bg-error-50 px-1.5 py-0.2 text-[10px] font-semibold text-error-600 dark:bg-error-500/10 dark:text-error-400 shrink-0">
                                                    DPO
                                                </span>
                                            @endif
                                        </div>
                                        @if ($berkas?->nomor_berkas)
                                            <div class="ps-4">
                                                <span class="inline-block rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] font-medium tabular-nums text-gray-600 dark:bg-gray-800 dark:text-gray-400" title="Nomor Berkas: {{ $berkas->nomor_berkas }}">
                                                    {{ $berkas->nomor_berkas }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum ada tersangka</span>
                        @endif
                    </td>

                    {{-- Status & SLA --}}
                    <td class="px-5 py-4">
                        @php
                            $primaryBerkas = $perkara->berkasPerkaras->first();
                            $statusFormal = $primaryBerkas?->status_formal;
                            $statusProses = $primaryBerkas?->status_proses ?? 'Baru';
                            $progress = $primaryBerkas?->progress_persen ?? 0;
                            $slaBadge = $primaryBerkas?->sla_badge;
                        @endphp

                        <div class="flex flex-col gap-1">
                            <span class="inline-flex w-fit items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusFormal === 'selesai' ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' : ($statusFormal === 'tidak_selesai' ? 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400' : 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300') }}">
                                {{ $statusFormal ? ucfirst(str_replace('_', ' ', $statusFormal)) : 'Dalam Proses' }}
                            </span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                Tahap: {{ $statusProses }} ({{ $progress }}%)
                            </span>
                            @if ($slaBadge && $primaryBerkas?->tanggal_spdp)
                                <span class="inline-flex w-fit items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $slaBadge['badge_class'] }}">
                                    <i class="fi {{ $slaBadge['icon'] }} text-[9px]"></i>
                                    {{ $slaBadge['label'] }}
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Aksi Dropdown --}}
                    <td class="px-5 py-4 text-center">
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
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                                <i class="fi fi-rr-inbox text-xl"></i>
                            </div>
                            <p class="font-medium text-gray-600 dark:text-gray-300">Tidak ada perkara ditemukan</p>
                            <p class="mt-1 text-xs text-gray-400">Silakan ubah filter pencarian untuk melihat data lain.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination Links --}}
    @if ($perkaras->hasPages())
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $perkaras->links() }}
        </div>
    @endif
</div>
