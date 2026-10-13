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
                    <td class="w-12 px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($perkaras->currentPage() - 1) * $perkaras->perPage() }}
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('perkara.show', $perkara) }}"
                                class="text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                            >
                                {{ $perkara->nomor_surat_perintah ?: '(Draft Sprindik)' }}
                            </a>
                            @if ($isLocked)
                                <i class="fi fi-rr-lock text-xs text-gray-400 dark:text-gray-500" title="Tahap Final & Terkunci"></i>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Tgl: {{ $perkara->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}
                        </p>
                    </td>

                    <td class="px-5 py-4">
                        <a
                            href="{{ route('laporan-kejadian.show', $perkara) }}"
                            class="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400"
                        >
                            {{ $perkara->nomor_laporan_kejadian }}
                        </a>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Tgl: {{ $perkara->tanggal_laporan_kejadian?->format('d-m-Y') ?? '-' }}
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
                                    href="{{ route('perkara.show', $perkara) }}"
                                    class="block rounded-md px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                >
                                    Lihat Detail
                                </a>

                                @can('edit-draft', $isLocked)
                                    <a
                                        href="{{ route('perkara.edit', $perkara) }}"
                                        class="block rounded-md px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                    >
                                        Edit Draft
                                    </a>
                                @endcan

                                <a
                                    href="{{ route('sprindik.show', $perkara) }}"
                                    class="block rounded-md px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                >
                                    Lihat Sprindik
                                </a>

                                @can('soft-delete')
                                    <button
                                        type="button"
                                        @click="open = false; confirmDelete = true"
                                        class="block w-full rounded-md px-3 py-2 text-left text-sm text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10"
                                    >
                                        Hapus
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
                                                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Perkara</h3>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini menggunakan soft delete.</p>
                                                </div>
                                            </div>

                                            <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
                                                Yakin ingin menghapus data perkara dari Sprindik <strong>{{ $perkara->nomor_surat_perintah }}</strong>?
                                            </p>

                                            <div class="flex justify-end gap-3">
                                                <button
                                                    type="button"
                                                    @click="confirmDelete = false"
                                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                >
                                                    Batal
                                                </button>
                                                <form action="{{ route('laporan-kejadian.destroy', $perkara) }}" method="POST">
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
