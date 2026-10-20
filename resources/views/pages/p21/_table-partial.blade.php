<div class="overflow-x-auto">
    <table class="w-full min-w-[1000px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Berkas Perkara &amp; Tersangka
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Surat P-21
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
            @forelse ($p21s as $item)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="w-12 px-5 py-4 text-sm tabular-nums text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($p21s->currentPage() - 1) * $p21s->perPage() }}
                    </td>

                    <td class="px-5 py-4">
                        <div class="space-y-0.5">
                            <a
                                href="{{ route('perkara.show', $item->berkasPerkara->perkara_id) }}"
                                class="font-mono text-sm font-semibold text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $item->berkasPerkara?->nomor_berkas ?: '-' }}
                            </a>
                            <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                                <i class="fi fi-rr-user text-[11px] text-gray-400"></i>
                                <span>Tersangka: <strong>{{ $item->berkasPerkara?->tersangka?->nama ?: '-' }}</strong></span>
                            </div>
                            <div class="text-[11px] font-mono text-gray-400 dark:text-gray-500">
                                Sprindik: {{ $item->berkasPerkara?->perkara?->nomor_surat_perintah ?: ($item->berkasPerkara?->perkara?->nomor_laporan_kejadian ?: '-') }}
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <span class="font-mono text-xs font-medium text-gray-800 dark:text-white/90">
                            {{ $item->nomor_surat ?: '(Draft Belum Bernomor)' }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $item->tanggal_surat ? $item->tanggal_surat->format('d/m/Y') : '-' }}
                    </td>

                    <td class="px-5 py-4">
                        @if ($item->dokumen->isNotEmpty())
                            @php $doc = $item->dokumen->first(); @endphp
                            <div class="flex items-center gap-1.5">
                                <a
                                    href="{{ route('dokumen.preview', $doc) }}"
                                    target="_blank"
                                    class="inline-flex max-w-[140px] items-center gap-1 truncate text-xs font-medium text-brand-600 hover:underline dark:text-brand-400"
                                    title="{{ $doc->nama_file_asli }}"
                                    data-pdf-preview
                                    data-pdf-title="{{ $doc->nama_file_asli }}"
                                    data-pdf-download="{{ route('dokumen.download', $doc) }}"
                                >
                                    <i class="fi fi-rr-document text-red-500"></i>
                                    <span class="truncate">{{ $doc->nama_file_asli }}</span>
                                </a>
                                <a
                                    href="{{ route('dokumen.download', $doc) }}"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                    title="Unduh PDF"
                                >
                                    <i class="fi fi-rr-download text-xs"></i>
                                </a>
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400">-</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        @if ($item->isFinal())
                            <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                <i class="fi fi-rr-lock text-[10px]"></i>
                                Final (Terkunci)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-semibold text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400">
                                <i class="fi fi-rr-edit text-[10px]"></i>
                                Draft
                            </span>
                        @endif
                    </td>

                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a
                                href="{{ route('berkas-perkara.tahapan.show', [$item->berkas_perkara_id, $item->id]) }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                                title="Lihat Detail Tahapan"
                            >
                                <i class="fi fi-rr-eye text-sm"></i>
                            </a>

                            @can('edit-draft')
                                @if (! $item->isFinal())
                                    <a
                                        href="{{ route('berkas-perkara.tahapan.edit', [$item->berkas_perkara_id, $item->id]) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-800 transition hover:bg-yellow-100 dark:border-yellow-500/20 dark:bg-yellow-500/10 dark:text-yellow-400"
                                        title="Edit Draft"
                                    >
                                        <i class="fi fi-rr-edit text-sm"></i>
                                    </a>
                                @endif
                            @endcan

                            @can('create-cases')
                                @if ($item->isFinal() && ! $item->berkasPerkara?->hasTahap(\App\Enums\JenisTahap::TAHAP_2) && ! $item->berkasPerkara?->hasSp3() && $item->berkasPerkara?->status_formal !== 'tidak_selesai')
                                    <a
                                        href="{{ route('berkas-perkara.tahapan.create', [$item->berkas_perkara_id, 'jenis' => 'tahap_2']) }}"
                                        class="inline-flex h-8 items-center gap-1 rounded-lg bg-brand-500 px-2.5 text-xs font-medium text-white transition hover:bg-brand-600"
                                        title="Lanjut ke Tahap II (Pelimpahan Tersangka &amp; Barang Bukti)"
                                    >
                                        <span>Lanjut Tahap 2</span>
                                        <i class="fi fi-rr-arrow-right text-[10px]"></i>
                                    </a>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i class="fi fi-rr-folder-open text-2xl text-gray-400"></i>
                            <span>Tidak ada data P-21 yang sesuai kriteria filter.</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($p21s->hasPages())
    <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
        {{ $p21s->links() }}
    </div>
@endif
