<div class="overflow-x-auto">
    <table class="w-full min-w-[1050px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nomor Sprindik
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tanggal
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Laporan Kejadian
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
            @forelse ($sprindiks as $index => $sprindik)
                @php
                    $status = $sprindik->status_sprindik;
                    $statusClass = match ($status) {
                        'Draft', 'Draft Perkara' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                        'Belum Menjadi Perkara' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                        'Perkara Terbentuk' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                    };
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    <td class="w-12 px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($sprindiks->currentPage() - 1) * $sprindiks->perPage() }}
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('sprindik.show', $sprindik) }}"
                                class="text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $sprindik->nomor_surat_perintah ?: '(Draft Belum Bernomor)' }}
                            </a>
                            @if ($sprindik->isSprindikLocked())
                                <i class="fi fi-rr-lock text-xs text-gray-400 dark:text-gray-500" title="Tahap Final & Terkunci"></i>
                            @endif
                        </div>
                    </td>

                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $sprindik->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}
                    </td>

                    <td class="px-5 py-4">
                        <a
                            href="{{ route('laporan-kejadian.show', $sprindik) }}"
                            class="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400"
                        >
                            {{ $sprindik->nomor_laporan_kejadian }}
                        </a>
                    </td>

                    <td class="px-5 py-4">
                        @if ($sprindik->dokumenSprindik)
                            <a
                                href="{{ route('dokumen.preview', $sprindik->dokumenSprindik) }}"
                                target="_blank"
                                class="inline-flex max-w-[200px] items-center gap-2 truncate text-sm text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400"
                                title="{{ $sprindik->dokumenSprindik->nama_file_asli }}"
                            >
                                <i class="fi fi-rr-document shrink-0 text-base text-red-500"></i>
                                <span class="truncate">{{ $sprindik->dokumenSprindik->nama_file_asli }}</span>
                            </a>
                        @else
                            <span class="text-sm italic text-gray-400 dark:text-gray-500">Tidak ada dokumen</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                            @if ($sprindik->isSprindikLocked())
                                <i class="fi fi-rr-lock text-[10px]"></i>
                            @else
                                <i class="fi fi-rr-edit text-[10px]"></i>
                            @endif
                            {{ $status }}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <div
                            x-data="{ open: false, confirmDelete: false }"
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
                                @click.outside="open = false"
                                x-cloak
                                class="absolute right-0 top-11 z-20 w-44 rounded-lg border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            >
                                <a
                                    href="{{ route('sprindik.show', $sprindik) }}"
                                    class="block rounded-md px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                >
                                    Lihat
                                </a>

                                @if ($sprindik->isSprindikLocked())
                                    @if ($sprindik->isPerkaraLocked() || $sprindik->tahap_perkara_status === 'draft')
                                        <a
                                            href="{{ route('perkara.show', $sprindik) }}"
                                            class="block rounded-md px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                        >
                                            Lihat Perkara
                                        </a>
                                    @endif

                                    @can('edit-draft', $sprindik->isPerkaraLocked())
                                        <a
                                            href="{{ route('perkara.edit', $sprindik) }}"
                                            class="block rounded-md px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                        >
                                            {{ $sprindik->tahap_perkara_status === 'draft' ? 'Edit Draft Perkara' : 'Buat Perkara' }}
                                        </a>
                                    @endcan
                                @endif

                                @can('edit-draft', $sprindik->isSprindikLocked())
                                    <a
                                        href="{{ route('sprindik.edit', $sprindik) }}"
                                        class="block rounded-md px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                    >
                                        Edit Draft
                                    </a>
                                @endcan

                                @can('soft-delete')
                                    <button
                                        type="button"
                                        @click="open = false; confirmDelete = true"
                                        class="block w-full rounded-md px-3 py-2 text-left text-sm text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10"
                                    >
                                        Hapus Sprindik
                                    </button>
                                @endcan
                            </div>

                            {{-- Modal Konfirmasi Hapus --}}
                            @can('soft-delete')
                                <template x-teleport="body">
                                    <div
                                        x-show="confirmDelete"
                                        x-cloak
                                        class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4 backdrop-blur-sm"
                                        @keydown.escape.window="confirmDelete = false"
                                    >
                                        <div
                                            @click.outside="confirmDelete = false"
                                            class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                        >
                                            <div class="mb-4 flex items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/10">
                                                    <i class="fi fi-rr-trash text-lg"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Sprindik</h3>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Data nomor sprindik dan dokumen sprindik akan direset.</p>
                                                </div>
                                            </div>

                                            <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
                                                Yakin ingin menghapus sprindik <strong>{{ $sprindik->nomor_surat_perintah }}</strong> dari perkara ini?
                                            </p>

                                            <div class="flex justify-end gap-3">
                                                <button
                                                    type="button"
                                                    @click="confirmDelete = false"
                                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                >
                                                    Batal
                                                </button>
                                                <form action="{{ route('sprindik.destroy', $sprindik) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="rounded-lg bg-error-600 px-4 py-2 text-sm font-medium text-white hover:bg-error-700"
                                                    >
                                                        Ya, Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
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
        Menampilkan <span class="font-medium text-gray-700 dark:text-gray-300">{{ $sprindiks->firstItem() ?? 0 }}–{{ $sprindiks->lastItem() ?? 0 }}</span>
        dari <span class="font-medium text-gray-700 dark:text-gray-300">{{ $sprindiks->total() }}</span> Sprindik
    </p>

    <div>
        {{ $sprindiks->links() }}
    </div>
</div>
