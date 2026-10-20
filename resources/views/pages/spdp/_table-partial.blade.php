<div class="overflow-x-auto">
    <table class="w-full min-w-[1050px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Berkas Perkara &amp; Tersangka
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tahapan
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Surat
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tanggal Surat
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Dokumen
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
            @forelse ($spdps as $spdp)
                @php
                    $subStageBadge = match ($spdp->jenis_tahap->value) {
                        'spdp' => [
                            'bg' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400 border border-brand-200/50 dark:border-brand-500/20',
                            'label' => 'SPDP',
                            'icon' => 'fi fi-rr-document-signed',
                        ],
                        'p16' => [
                            'bg' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-400 border border-blue-light-200/50 dark:border-blue-light-500/20',
                            'label' => 'P-16 (Opsional)',
                            'icon' => 'fi fi-rr-search-alt',
                        ],
                        'p17p' => [
                            'bg' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 border border-purple-200/50 dark:border-purple-500/20',
                            'label' => 'P-17P (Opsional)',
                            'icon' => 'fi fi-rr-interrogation',
                        ],
                        default => [
                            'bg' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700',
                            'label' => $spdp->jenis_tahap->label(),
                            'icon' => 'fi fi-rr-document',
                        ],
                    };
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="w-12 px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($spdps->currentPage() - 1) * $spdps->perPage() }}
                    </td>

                    <td class="px-5 py-4">
                        <div class="space-y-0.5">
                            <a
                                href="{{ route('perkara.show', $spdp->berkasPerkara->perkara_id) }}"
                                class="text-sm font-semibold text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $spdp->berkasPerkara?->nomor_berkas ?: '-' }}
                            </a>
                            <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                                <i class="fi fi-rr-user text-[11px] text-gray-400"></i>
                                <span>Tersangka: <strong>{{ $spdp->berkasPerkara?->tersangka?->nama ?: '-' }}</strong></span>
                            </div>
                            <div class="text-[11px] font-mono text-gray-400 dark:text-gray-500">
                                Sprindik: {{ $spdp->berkasPerkara?->perkara?->nomor_surat_perintah ?: ($spdp->berkasPerkara?->perkara?->nomor_laporan_kejadian ?: '-') }}
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $subStageBadge['bg'] }}">
                            <i class="{{ $subStageBadge['icon'] }} text-[11px]"></i>
                            <span>{{ $subStageBadge['label'] }}</span>
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <span class="font-mono text-xs font-medium text-gray-800 dark:text-white/90">
                            {{ $spdp->nomor_surat ?: '(Draft Belum Bernomor)' }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-400">
                        {{ $spdp->tanggal_surat?->format('d M Y') ?? '-' }}
                    </td>

                    <td class="px-5 py-4">
                        @if ($doc = $spdp->dokumen->first())
                            <a
                                href="{{ route('dokumen.preview', $doc) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 transition hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                                title="{{ $doc->nama_file_asli }}"
                                data-pdf-preview
                                data-pdf-title="{{ $doc->nama_file_asli }}"
                                data-pdf-download="{{ route('dokumen.download', $doc) }}"
                            >
                                <i class="fi fi-rr-document text-sm text-red-500"></i>
                                <span class="max-w-[130px] truncate">{{ $doc->nama_file_asli }}</span>
                            </a>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        @if ($spdp->isFinal())
                            <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                <i class="fi fi-rr-lock text-[10px]"></i>
                                <span>Final</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                <i class="fi fi-rr-edit text-[10px]"></i>
                                <span>Draft</span>
                            </span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <a
                                href="{{ route('berkas-perkara.tahapan.show', [$spdp->berkas_perkara_id, $spdp]) }}"
                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                title="Lihat Detail Tahapan"
                            >
                                <i class="fi fi-rr-eye text-xs"></i>
                                <span>Lihat</span>
                            </a>

                            @if ($spdp->isDraft())
                                @can('edit-draft')
                                    <a
                                        href="{{ route('berkas-perkara.tahapan.edit', [$spdp->berkas_perkara_id, $spdp]) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-warning-200 bg-warning-50/50 px-2.5 py-1.5 text-xs font-medium text-warning-700 shadow-theme-xs transition hover:bg-warning-100 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400"
                                        title="Edit Draft Tahapan"
                                    >
                                        <i class="fi fi-rr-edit text-xs"></i>
                                        <span>Edit</span>
                                    </a>
                                @endcan
                            @endif

                            @if ($spdp->isFinal() && $spdp->jenis_tahap === \App\Enums\JenisTahap::SPDP && ! $spdp->berkasPerkara?->hasTahap(\App\Enums\JenisTahap::TAHAP_1) && ! $spdp->berkasPerkara?->hasSp3() && $spdp->berkasPerkara?->status_formal !== 'tidak_selesai')
                                @can('create-cases')
                                    <a
                                        href="{{ route('berkas-perkara.tahapan.create', [$spdp->berkas_perkara_id, 'jenis' => 'tahap_1']) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1.5 text-xs font-medium text-brand-700 shadow-theme-xs transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400"
                                        title="Lanjut ke Tahapan Berikutnya (Tahap I)"
                                    >
                                        <i class="fi fi-rr-arrow-right text-xs"></i>
                                        <span>Mulai Tahap 1</span>
                                    </a>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                <i class="fi fi-rr-search-alt text-xl"></i>
                            </div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">
                                Tidak ada data tahapan SPDP yang sesuai filter.
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Coba sesuaikan kata kunci pencarian, filter status, tanggal, atau tab sub-tahap.
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="flex flex-col gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Menampilkan
        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $spdps->firstItem() ?? 0 }}–{{ $spdps->lastItem() ?? 0 }}</span>
        dari
        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $spdps->total() }}</span>
        data
    </p>

    <div>
        {{ $spdps->links() }}
    </div>
</div>
