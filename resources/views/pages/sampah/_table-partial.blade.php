<div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
        <thead class="border-b border-gray-100 bg-gray-50/70 text-xs uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-800/40 dark:text-gray-400">
            <tr>
                <th scope="col" class="w-12 px-4 py-3.5 sm:px-6">No</th>

                @if ($activeTab === 'berkas')
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Nomor Berkas</th>
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Tersangka &amp; NIK</th>
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Perkara Induk</th>
                @elseif ($activeTab === 'user')
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Nama &amp; NIP</th>
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Email</th>
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Role &amp; Jabatan</th>
                @else
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Nomor Laporan / Sprindik</th>
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Tanggal Laporan</th>
                    <th scope="col" class="px-4 py-3.5 sm:px-6">Deskripsi Kasus</th>
                @endif

                <th scope="col" class="px-4 py-3.5 sm:px-6">Dihapus Pada</th>
                <th scope="col" class="px-4 py-3.5 text-center sm:px-6">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($items as $item)
                <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                    <td class="px-4 py-4 font-medium tabular-nums text-gray-500 sm:px-6">
                        {{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}
                    </td>

                    @if ($activeTab === 'berkas')
                        {{-- Data Berkas Perkara --}}
                        <td class="px-4 py-4 font-medium text-gray-800 dark:text-white/90 sm:px-6">
                            <div class="flex items-center gap-2">
                                <i class="fi fi-rr-folder text-brand-500"></i>
                                <span class="font-mono">{{ $item->nomor_berkas ?: 'Tanpa Nomor Berkas' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 sm:px-6">
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $item->tersangka?->nama ?: '-' }}</p>
                            <p class="text-xs text-gray-400">NIK: <span class="font-mono tabular-nums">{{ $item->tersangka?->nik ?: '-' }}</span></p>
                        </td>
                        <td class="px-4 py-4 sm:px-6">
                            <p class="font-mono text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ $item->perkara?->nomor_laporan_kejadian ?: '-' }}
                            </p>
                            @if ($item->perkara?->nomor_surat_perintah)
                                <p class="text-xs text-gray-400">Sprindik: <span class="font-mono">{{ $item->perkara->nomor_surat_perintah }}</span></p>
                            @endif
                        </td>

                    @elseif ($activeTab === 'user')
                        {{-- Data Pengguna --}}
                        <td class="px-4 py-4 sm:px-6">
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $item->nama }}</p>
                            <p class="text-xs text-gray-400">NIP: <span class="font-mono tabular-nums">{{ $item->nip ?: '-' }}</span></p>
                        </td>
                        <td class="px-4 py-4 sm:px-6">
                            <span class="text-xs text-gray-600 dark:text-gray-300">{{ $item->email }}</span>
                        </td>
                        <td class="px-4 py-4 sm:px-6">
                            <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                                {{ $item->role?->label() ?? $item->role }}
                            </span>
                            @if ($item->jabatan)
                                <p class="mt-0.5 text-xs text-gray-400">{{ $item->jabatan->nama_jabatan }}</p>
                            @endif
                        </td>

                    @else
                        {{-- Data Laporan Kejadian & Perkara --}}
                        <td class="px-4 py-4 sm:px-6">
                            <div class="flex items-center gap-2">
                                <i class="fi fi-rr-document-signed text-brand-500"></i>
                                <span class="font-mono font-medium text-gray-800 dark:text-white/90">
                                    {{ $item->nomor_laporan_kejadian ?: '-' }}
                                </span>
                            </div>
                            @if ($item->nomor_surat_perintah)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Sprindik: <strong class="font-mono">{{ $item->nomor_surat_perintah }}</strong>
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-4 sm:px-6 tabular-nums">
                            {{ $item->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-4 sm:px-6 max-w-xs truncate" title="{{ $item->deskripsi }}">
                            {{ $item->deskripsi ?: '-' }}
                        </td>
                    @endif

                    {{-- Tanggal Dihapus --}}
                    <td class="px-4 py-4 text-xs sm:px-6">
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            {{ $item->deleted_at?->format('d-m-Y H:i') }}
                        </span>
                        <p class="text-[11px] text-gray-400">
                            ({{ $item->deleted_at?->diffForHumans() }})
                        </p>
                    </td>

                    {{-- Aksi Pulihkan (Restore) --}}
                    <td class="px-4 py-4 text-center sm:px-6">
                        @php
                            $canRestore = match ($activeTab) {
                                'user' => auth()->user()?->isAdmin(),
                                default => auth()->user()?->can('soft-delete') ?? false,
                            };
                            $restoreAction = match ($activeTab) {
                                'berkas' => route('sampah.berkas.restore', $item->id),
                                'user' => route('sampah.user.restore', $item->id),
                                default => route('sampah.perkara.restore', $item->id),
                            };
                            $itemName = match ($activeTab) {
                                'berkas' => $item->nomor_berkas ?: 'Berkas #' . $item->id,
                                'user' => $item->nama,
                                default => $item->nomor_laporan_kejadian ?: 'Perkara #' . $item->id,
                            };
                        @endphp

                        @if ($canRestore)
                            <div x-data="{ openConfirm: false }">
                                <button
                                    type="button"
                                    @click="openConfirm = true"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-success-200 bg-success-50 px-3 py-1.5 text-xs font-medium text-success-700 transition hover:bg-success-100 dark:border-success-800/40 dark:bg-success-950/20 dark:text-success-300 dark:hover:bg-success-900/40"
                                    title="Pulihkan data"
                                    aria-label="Pulihkan data"
                                >
                                    <i class="fi fi-rr-refresh text-xs"></i>
                                    <span>Pulihkan</span>
                                </button>

                                {{-- Modal Konfirmasi Pemulihan --}}
                                <template x-teleport="body">
                                    <div
                                        x-show="openConfirm"
                                        x-cloak
                                        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
                                        @keydown.escape.window="openConfirm = false"
                                    >
                                        <div
                                            @click.outside="openConfirm = false"
                                            class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
                                        >
                                            <div class="mb-4 flex items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-success-100 text-success-600 dark:bg-success-900/40 dark:text-success-400">
                                                    <i class="fi fi-rr-refresh text-lg"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                                        Pulihkan Data
                                                    </h3>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        Kembalikan data ke daftar aktif
                                                    </p>
                                                </div>
                                            </div>

                                            <p class="mb-6 text-xs text-gray-600 dark:text-gray-300">
                                                Apakah Anda yakin ingin memulihkan <strong>{{ $itemName }}</strong>? Data akan kembali dapat diakses pada halaman operasional seperti semula.
                                            </p>

                                            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                                                <button
                                                    type="button"
                                                    @click="openConfirm = false"
                                                    class="h-9 rounded-lg border border-gray-300 bg-white px-4 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                                >
                                                    Batal
                                                </button>
                                                <form action="{{ $restoreAction }}" method="POST">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-success-600 px-4 text-xs font-medium text-white hover:bg-success-700"
                                                    >
                                                        <i class="fi fi-rr-check text-xs"></i>
                                                        <span>Ya, Pulihkan</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400">Lihat Saja</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400 sm:px-6">
                        <div class="flex flex-col items-center justify-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                                <i class="fi fi-rr-trash text-xl"></i>
                            </div>
                            <p class="font-medium text-gray-600 dark:text-gray-300">Tidak ada data terhapus</p>
                            <p class="mt-1 text-xs text-gray-400">Belum ada item yang dihapus pada kategori ini.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination Links --}}
    @if ($items->hasPages())
        <div class="border-t border-gray-100 px-4 py-4 dark:border-gray-800 sm:px-6">
            {{ $items->links() }}
        </div>
    @endif
</div>
