<div class="overflow-x-auto">
    <table class="w-full min-w-[1100px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Perkara
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    TKP
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    PPNS
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tersangka
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Pasal
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
                    $tkpAlamat = $perkara->tkp?->alamat;
                    $ppnsUtama = $perkara->ppnsUtama->first();

                    // Kumpulkan semua pasal yang disangkakan pada berkas perkara terkait
                    $allPasals = $perkara->tersangkas
                        ->flatMap(fn ($t) => $t->berkasPerkara?->pasals ?? collect())
                        ->unique('id')
                        ->values();
                    if ($allPasals->isEmpty() && $perkara->relationLoaded('berkasPerkaras')) {
                        $allPasals = $perkara->berkasPerkaras->flatMap(fn ($b) => $b->pasals ?? collect())->unique('id')->values();
                    }
                    $fullPasalList = $allPasals->map(fn ($p) => $p->label())->implode("\n");

                    // Status proses dan progress
                    $berkasUtama = $perkara->berkasPerkaras->first() ?? $perkara->tersangkas->first()?->berkasPerkara;
                    $hasSp3 = $berkasUtama?->hasSp3() || $berkasUtama?->status_formal === 'tidak_selesai';
                    $tahapLabel = $berkasUtama?->status_proses ?: ($isLocked ? 'Pra-SPDP' : 'Draft Perkara');
                    $progressPersen = $berkasUtama?->progress_persen ?? 0;
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    {{-- 1. No --}}
                    <td class="w-12 px-5 py-4 text-sm tabular-nums text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($perkaras->currentPage() - 1) * $perkaras->perPage() }}
                    </td>

                    {{-- 2. Perkara (Nomor LK dan tanggal, tersusun vertikal) --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-1.5">
                            <a
                                href="{{ route('perkara.show', $perkara) }}"
                                class="font-mono text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $perkara->nomor_laporan_kejadian ?: '(Draft LK)' }}
                            </a>
                            @if ($isLocked)
                                <i class="fi fi-rr-lock text-xs text-gray-400 dark:text-gray-500" title="Tahap Final & Terkunci"></i>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Tgl: <span class="tabular-nums">{{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}</span>
                        </p>
                        @if ($perkara->nomor_surat_perintah)
                            <p class="mt-0.5 font-mono text-[11px] text-gray-400 dark:text-gray-500 truncate max-w-[180px]" title="Dasar Sprindik: {{ $perkara->nomor_surat_perintah }}">
                                SP: {{ $perkara->nomor_surat_perintah }}
                            </p>
                        @endif
                    </td>

                    {{-- 3. TKP (Kota dan klasifikasi sarana, tersusun vertikal) --}}
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

                    {{-- 4. PPNS (Nama utama dan NIP, tersusun vertikal) --}}
                    <td class="px-5 py-4">
                        @if ($ppnsUtama)
                            <div class="max-w-[160px]">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $ppnsUtama->nama }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">NIP: {{ $ppnsUtama->nip ?: '-' }}</p>
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum ditunjuk</span>
                        @endif
                    </td>

                    {{-- 5. Tersangka (Nama dan alias) --}}
                    <td class="px-5 py-4">
                        @forelse ($perkara->tersangkas as $tsk)
                            <div class="mb-1.5 last:mb-0 max-w-[180px]">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $tsk->nama }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    @if ($tsk->alias)
                                        Alias: <span class="text-gray-700 dark:text-gray-300">{{ $tsk->alias }}</span>
                                    @else
                                        <span class="italic text-gray-400 dark:text-gray-500">Tanpa alias</span>
                                    @endif
                                </p>
                            </div>
                        @empty
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum ada tersangka</span>
                        @endforelse
                    </td>

                    {{-- 6. Pasal (Maksimal 2 pasal, >2 sisanya "..." dengan tooltip) --}}
                    <td class="px-5 py-4">
                        @if ($allPasals->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-1 max-w-[180px]">
                                @foreach ($allPasals->take(2) as $pasal)
                                    <span
                                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/[0.05] dark:text-gray-300"
                                        title="{{ $pasal->label() }}"
                                    >
                                        {{ $pasal->pasal }}
                                    </span>
                                @endforeach
                                @if ($allPasals->count() > 2)
                                    <span
                                        class="inline-flex cursor-help items-center rounded-md bg-gray-100 px-1.5 py-0.5 text-xs font-semibold text-gray-500 hover:text-gray-800 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:text-gray-200"
                                        title="{{ $fullPasalList }}"
                                    >
                                        ...
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum ada pasal</span>
                        @endif
                    </td>

                    {{-- 7. Status (Status perkara dan tahapan terakhir beserta persentase progres) --}}
                    <td class="px-5 py-4">
                        <div class="flex flex-col gap-1">
                            @if ($isLocked)
                                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                    <i class="fi fi-rr-lock text-[10px]"></i>
                                    Perkara Terbentuk
                                </span>
                            @else
                                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                    <i class="fi fi-rr-edit text-[10px]"></i>
                                    Draft Perkara
                                </span>
                            @endif

                            @if ($hasSp3)
                                <p class="text-xs font-semibold text-error-600 dark:text-error-400">
                                    SP3 (<span class="tabular-nums">{{ $progressPersen }}%</span>)
                                </p>
                            @else
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    {{ $tahapLabel }} (<span class="tabular-nums font-semibold">{{ $progressPersen }}%</span>)
                                </p>
                            @endif
                        </div>
                    </td>

                    {{-- 8. Aksi --}}
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
        Menampilkan <span class="font-medium text-gray-700 dark:text-gray-300">{{ $perkaras->firstItem() ?? 0 }}–{{ $perkaras->lastItem() ?? 0 }}</span>
        dari <span class="font-medium text-gray-700 dark:text-gray-300">{{ $perkaras->total() }}</span> Perkara
    </p>

    <div>
        {{ $perkaras->links() }}
    </div>
</div>
