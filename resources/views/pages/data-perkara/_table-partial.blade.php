<div class="overflow-x-auto">
    <table class="w-full min-w-[1250px]">
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
                    Tahap Saat Ini (% progress)
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
            @forelse ($perkaras as $index => $perkara)
                @php
                    $isLocked = $perkara->isPerkaraLocked();
                    $isDraft = $perkara->tahap_perkara_status === 'draft';
                    $tkpAlamat = $perkara->tkp?->alamat;
                    $ppnsUtama = $perkara->ppnsUtama->first();
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="w-12 px-5 py-4 text-sm tabular-nums text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($perkaras->currentPage() - 1) * $perkaras->perPage() }}
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('perkara.show', $perkara) }}"
                                class="font-mono text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $perkara->nomor_surat_perintah ?: '(Draft Sprindik)' }}
                            </a>
                            @if ($isLocked)
                                <i class="fi fi-rr-lock text-xs text-gray-400 dark:text-gray-500" title="Tahap Final & Terkunci"></i>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Tgl: <span class="tabular-nums">{{ $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}</span>
                        </p>
                    </td>

                    <td class="px-5 py-4">
                        <a
                            href="{{ route('laporan-kejadian.show', $perkara) }}"
                            class="font-mono text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400"
                        >
                            {{ $perkara->nomor_laporan_kejadian }}
                        </a>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Tgl: <span class="tabular-nums">{{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}</span>
                        </p>
                    </td>

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

                    <td class="px-5 py-4">
                        @if ($ppnsUtama)
                            <div class="max-w-[160px]">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $ppnsUtama->nama }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $ppnsUtama->nip ?: $ppnsUtama->role }}</p>
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum ditunjuk</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        @if ($perkara->tersangkas->isNotEmpty())
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $perkara->tersangkas->count() }} Tersangka / Berkas:
                                </span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($perkara->tersangkas->take(2) as $tsk)
                                        <span class="inline-flex max-w-[160px] items-center gap-1 truncate rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-white/[0.05] dark:text-gray-300" title="{{ $tsk->nama }} ({{ $tsk->berkasPerkara?->nomor_berkas ?: 'No Berkas' }})">
                                            <i class="fi fi-rr-user text-[10px]"></i>
                                            <span class="truncate">{{ $tsk->nama }}</span>
                                        </span>
                                    @endforeach
                                    @if ($perkara->tersangkas->count() > 2)
                                        <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500 dark:bg-white/[0.05]">
                                            +{{ $perkara->tersangkas->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum ada tersangka</span>
                        @endif
                    </td>

                    {{-- Tahap Saat Ini (% progress) (Poin 20) --}}
                    @php
                        $berkasUtama = $perkara->berkasPerkaras->first() ?? $perkara->tersangkas->first()?->berkasPerkara;
                        $hasSp3 = $berkasUtama?->hasSp3() || $berkasUtama?->status_formal === 'tidak_selesai';
                        $tahapLabel = $berkasUtama?->status_proses ?: ($isLocked ? 'Pra-SPDP' : 'Draft Perkara');
                        $progressPersen = $berkasUtama?->progress_persen ?? 0;
                    @endphp
                    <td class="px-5 py-4">
                        @if ($hasSp3)
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-error-600 dark:text-error-400">
                                    SP3 (<span class="tabular-nums">{{ $progressPersen }}</span>%)
                                </span>
                                <span class="inline-flex w-fit items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-[10px] font-medium text-error-700 dark:bg-error-500/10 dark:text-error-400">
                                    <i class="fi fi-rr-cross-circle text-[9px]"></i>
                                    Tidak Selesai
                                </span>
                            </div>
                        @else
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $tahapLabel }} (<span class="tabular-nums">{{ $progressPersen }}</span>%)
                            </span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        @if ($isLocked)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                <i class="fi fi-rr-lock text-[10px]"></i>
                                Perkara Terbentuk
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                <i class="fi fi-rr-edit text-[10px]"></i>
                                Draft Perkara
                            </span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        <div x-data="{ confirmDelete: false }" class="flex justify-center">
                            <x-common.table-dropdown>
                                <a
                                    href="{{ route('perkara.show', $perkara) }}"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                >
                                    <i class="fi fi-rr-eye text-xs text-brand-600"></i>
                                    <span>Lihat Detail</span>
                                </a>

                                @can('edit-draft', $isLocked)
                                    <a
                                        href="{{ route('perkara.edit', $perkara) }}"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                    >
                                        <i class="fi fi-rr-edit text-xs text-amber-500"></i>
                                        <span>Edit Draft</span>
                                    </a>
                                @endcan

                                <a
                                    href="{{ route('sprindik.show', $perkara) }}"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                >
                                    <i class="fi fi-rr-document-signed text-xs text-gray-500"></i>
                                    <span>Lihat Sprindik</span>
                                </a>
                            </x-common.table-dropdown>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
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
        Menampilkan <span class="font-medium text-gray-700 dark:text-gray-300">{{ $perkaras->firstItem() ?? 0 }}–{{ $perkaras->lastItem() ?? 0 }}</span>
        dari <span class="font-medium text-gray-700 dark:text-gray-300">{{ $perkaras->total() }}</span> Perkara
    </p>

    <div>
        {{ $perkaras->links() }}
    </div>
</div>
