<div class="overflow-x-auto">
    <table class="w-full min-w-[900px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Laporan
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tanggal
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Dokumen
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Status Proses
                </th>

                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Aksi
                </th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse ($laporanKejadian as $index => $laporan)
                @php
                    $status = $laporan->status_laporan;
                    $statusClass = match ($status) {
                        'Draft' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                        'Belum Dilanjutkan' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                        'Sprindik Tersedia' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400',
                        'Perkara Terbentuk' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                    };
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="w-12 px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($laporanKejadian->currentPage() - 1) * $laporanKejadian->perPage() }}
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('laporan-kejadian.show', $laporan) }}"
                                class="text-sm font-medium text-gray-800 hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $laporan->nomor_laporan_kejadian ?: '(Draft Belum Bernomor)' }}
                            </a>
                            @if ($laporan->isLaporanLocked())
                                <i class="fi fi-rr-lock text-xs text-gray-400 dark:text-gray-500" title="Tahap Final & Terkunci"></i>
                            @endif
                        </div>
                    </td>

                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $laporan->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}
                    </td>

                    <td class="px-5 py-4">
                        @if ($laporan->dokumenLaporan)
                            <a
                                href="{{ route('dokumen.preview', $laporan->dokumenLaporan) }}"
                                target="_blank"
                                class="inline-flex max-w-[200px] items-center gap-2 truncate text-sm text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400"
                                title="{{ $laporan->dokumenLaporan->nama_file_asli }}"
                                data-pdf-preview
                                data-pdf-title="{{ $laporan->dokumenLaporan->nama_file_asli }}"
                                data-pdf-download="{{ route('dokumen.download', $laporan->dokumenLaporan) }}"
                            >
                                <i class="fi fi-rr-document shrink-0 text-base text-red-500"></i>
                                <span class="truncate">{{ $laporan->dokumenLaporan->nama_file_asli }}</span>
                            </a>
                        @else
                            <span class="text-sm italic text-gray-400 dark:text-gray-500">Tidak ada dokumen</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                            @if ($laporan->isLaporanLocked())
                                <i class="fi fi-rr-lock text-[10px]"></i>
                            @else
                                <i class="fi fi-rr-edit text-[10px]"></i>
                            @endif
                            {{ $status }}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <div x-data="{ confirmDelete: false }" class="flex justify-center">
                            <x-common.table-dropdown>
                                <a
                                    href="{{ route('laporan-kejadian.show', $laporan) }}"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                >
                                    <i class="fi fi-rr-eye text-xs text-brand-600"></i>
                                    <span>Lihat</span>
                                </a>

                                @can('edit-draft', $laporan->isLaporanLocked())
                                    <a
                                        href="{{ route('laporan-kejadian.edit', $laporan) }}"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                    >
                                        <i class="fi fi-rr-edit text-xs text-amber-500"></i>
                                        <span>Edit Draft</span>
                                    </a>
                                @endcan

                                @if ($laporan->isLaporanLocked())
                                    @if (empty($laporan->nomor_surat_perintah) && empty($laporan->tahap_sprindik_status))
                                        @can('create-cases')
                                            <a
                                                href="{{ route('sprindik.create', ['perkara_id' => $laporan->id]) }}"
                                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                            >
                                                <i class="fi fi-rr-plus-circle text-xs text-brand-600"></i>
                                                <span>Input Sprindik</span>
                                            </a>
                                        @endcan
                                    @else
                                        <a
                                            href="{{ route('sprindik.show', $laporan) }}"
                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                        >
                                            <i class="fi fi-rr-document-signed text-xs text-gray-500"></i>
                                            <span>Lihat Sprindik</span>
                                        </a>
                                    @endif
                                @endif
                            </x-common.table-dropdown>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
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
        Menampilkan <span class="font-medium text-gray-700 dark:text-gray-300">{{ $laporanKejadian->firstItem() ?? 0 }}–{{ $laporanKejadian->lastItem() ?? 0 }}</span>
        dari <span class="font-medium text-gray-700 dark:text-gray-300">{{ $laporanKejadian->total() }}</span> laporan
    </p>

    <div>
        {{ $laporanKejadian->links() }}
    </div>
</div>
