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
                    PPNS Utama
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Tahap &amp; Progress
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Batas Waktu &amp; Sisa Hari
                </th>

                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Rekomendasi Tindakan
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
                    $statusProses = $berkas->status_proses ?? 'Baru';
                    $progress = $berkas->progress_persen ?? 0;
                    $deadline = $berkas->effective_deadline;
                    $sisaHari = $berkas->sisa_hari_sla;
                    $slaBadge = $berkas->sla_badge;
                @endphp

                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    {{-- Kolom No --}}
                    <td class="w-12 px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($berkasList->currentPage() - 1) * $berkasList->perPage() }}
                    </td>

                    {{-- Nomor Berkas & Perkara --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-folder text-brand-500"></i>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $berkas->nomor_berkas ?: 'Tanpa Nomor Berkas' }}
                            </span>
                        </div>
                        @if ($perkara)
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                LK: <a href="{{ route('laporan-kejadian.show', $perkara) }}" class="text-brand-600 hover:underline">{{ $perkara->nomor_laporan_kejadian }}</a>
                            </p>
                            @if ($perkara->nomor_surat_perintah)
                                <p class="text-xs text-gray-400">
                                    Sprindik: {{ $perkara->nomor_surat_perintah }}
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
                            NIK: {{ $tersangka?->nik ?: '-' }}
                        </p>
                    </td>

                    {{-- PPNS Utama --}}
                    <td class="px-5 py-4">
                        @if ($ppnsUtama)
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $ppnsUtama->nama }}</p>
                            <p class="text-xs text-gray-400">NIP: {{ $ppnsUtama->nip ?: '-' }}</p>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">-</span>
                        @endif
                    </td>

                    {{-- Tahap & Progress --}}
                    <td class="px-5 py-4">
                        <div class="w-32">
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $statusProses }}</span>
                                <span class="font-semibold text-gray-800 dark:text-white/90">{{ $progress }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full w-full origin-left rounded-full transition-transform duration-300 ease-out {{ $progress >= 85 ? 'bg-success-500' : ($progress >= 55 ? 'bg-brand-500' : 'bg-warning-500') }}"
                                    style="transform: scaleX({{ $progress / 100 }})"
                                ></div>
                            </div>
                        </div>
                    </td>

                    {{-- Batas Waktu & Sisa Hari --}}
                    <td class="px-5 py-4">
                        @if ($deadline)
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-800 dark:text-white/90">
                                    {{ $deadline->format('d-m-Y') }}
                                </span>
                                @if ($sisaHari !== null)
                                    @if ($sisaHari < 0)
                                        <span class="inline-flex w-fit items-center gap-1 rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                                            <i class="fi fi-rr-cross-circle text-[9px]"></i>
                                            Lewat {{ abs($sisaHari) }} hari
                                        </span>
                                    @elseif ($sisaHari <= 7)
                                        <span class="inline-flex w-fit items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400">
                                            <i class="fi fi-rr-hourglass-end text-[9px]"></i>
                                            Sisa {{ $sisaHari }} hari
                                        </span>
                                    @else
                                        <span class="inline-flex w-fit items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                            <i class="fi fi-rr-calendar text-[9px]"></i>
                                            Sisa {{ $sisaHari }} hari
                                        </span>
                                    @endif
                                @endif
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">Belum terbit SPDP</span>
                        @endif
                    </td>

                    {{-- Rekomendasi Tindakan --}}
                    <td class="px-5 py-4">
                        @if ($berkas->is_dpo)
                            <div class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50/80 px-2.5 py-1 text-xs font-medium text-red-700 dark:border-red-800 dark:bg-red-950/20 dark:text-red-300">
                                <i class="fi fi-rr-user-slash text-xs"></i>
                                <span>Terkendala DPO: Tangkap tersangka untuk membuka Tahap II</span>
                            </div>
                        @elseif ($progress >= 85)
                            <div class="inline-flex items-center gap-1.5 rounded-lg border border-success-200 bg-success-50/80 px-2.5 py-1 text-xs font-medium text-success-700 dark:border-success-800 dark:bg-success-950/20 dark:text-success-300">
                                <i class="fi fi-rr-badge-check text-xs"></i>
                                <span>P-21 Lengkap: Segera lakukan Pelimpahan Tahap II</span>
                            </div>
                        @elseif ($progress >= 55)
                            <div class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50/80 px-2.5 py-1 text-xs font-medium text-brand-700 dark:border-brand-800 dark:bg-brand-950/20 dark:text-brand-300">
                                <i class="fi fi-rr-paper-plane text-xs"></i>
                                <span>Tahap I Terkirim: Monitor koordinasi P-21 Jaksa</span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-gray-800/40 dark:text-gray-300">
                                <i class="fi fi-rr-document-signed text-xs"></i>
                                <span>Percepat penyusunan berkas menuju Tahap I</span>
                            </div>
                        @endif
                    </td>

                    {{-- Aksi Dropdown --}}
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
                                @can('create-cases')
                                    @if (! $berkas->hasSp3() && $berkas->status_formal !== 'tidak_selesai' && ! $berkas->hasTahap(\App\Enums\JenisTahap::KEPUTUSAN_PENGADILAN))
                                        <a
                                            href="{{ route('berkas-perkara.tahapan.create', $berkas) }}"
                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                                        >
                                            <i class="fi fi-rr-plus text-xs text-success-600"></i>
                                            <span>Lanjut Tahapan</span>
                                        </a>
                                    @endif
                                @endcan
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
                                <i class="fi fi-rr-check-circle text-xl text-success-500"></i>
                            </div>
                            <p class="font-medium text-gray-600 dark:text-gray-300">Tidak ada berkas yang berisiko pada kategori ini</p>
                            <p class="mt-1 text-xs text-gray-400">Seluruh berkas perkara berjalan terpantau aman dan sesuai jadwal.</p>
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
