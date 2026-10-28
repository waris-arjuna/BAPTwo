{{-- Header Tabel Data Petugas --}}
<div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <i class="fi fi-rr-users text-brand-500"></i>
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
            Daftar Petugas BAPTwo
        </h3>
        <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold tabular-nums text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
            {{ $petugasList->total() }} Petugas
        </span>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="w-full min-w-[950px]">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <th class="w-12 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    No
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nama Petugas &amp; NIP
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Email &amp; No. Telepon
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Jabatan &amp; Bidang
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Pangkat / Golongan
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Role Penugasan
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
            @forelse ($petugasList as $petugas)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                    {{-- No --}}
                    <td class="w-12 px-5 py-4 text-sm tabular-nums text-gray-600 dark:text-gray-400">
                        {{ $loop->iteration + ($petugasList->currentPage() - 1) * $petugasList->perPage() }}
                    </td>

                    {{-- Nama & NIP --}}
                    <td class="px-5 py-4">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ $petugas->nama }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-400 font-mono tabular-nums">
                            NIP: {{ $petugas->nip ?: '-' }}
                        </p>
                    </td>

                    {{-- Email & Telepon --}}
                    <td class="px-5 py-4 text-xs">
                        <p class="font-medium text-gray-700 dark:text-gray-300">{{ $petugas->email }}</p>
                        <p class="text-gray-400 tabular-nums">{{ $petugas->no_telp ?: '-' }}</p>
                    </td>

                    {{-- Jabatan & Bidang --}}
                    <td class="px-5 py-4 text-xs">
                        <p class="font-medium text-gray-700 dark:text-gray-300">{{ $petugas->jabatan?->nama_jabatan ?: '-' }}</p>
                        <p class="text-gray-400">{{ $petugas->bidang?->nama_bidang ?: '-' }}</p>
                    </td>

                    {{-- Pangkat & Golongan --}}
                    <td class="px-5 py-4 text-xs">
                        @if ($petugas->panggol)
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                {{ $petugas->panggol->pangkat }} ({{ $petugas->panggol->golongan }})
                            </span>
                        @else
                            <span class="italic text-gray-400">-</span>
                        @endif
                    </td>

                    {{-- Role Penugasan --}}
                    <td class="px-5 py-4">
                        @php
                            $roleBadgeClass = match ($petugas->role) {
                                \App\Enums\UserRole::PENYIDIK => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-500/10 dark:text-brand-300 dark:border-brand-500/20',
                                \App\Enums\UserRole::UMUM => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20',
                                default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $roleBadgeClass }}">
                            <i class="fi fi-rr-badge text-[10px]"></i>
                            <span>{{ $petugas->role->label() }}</span>
                        </span>
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-4">
                        @if ($petugas->is_active && ! $petugas->trashed())
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/10 dark:text-error-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-error-500"></span>
                                Nonaktif
                            </span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="px-5 py-4 text-center">
                        <button
                            type="button"
                            @click="openModal({{ json_encode([
                                'id' => $petugas->id,
                                'nama' => $petugas->nama,
                                'nip' => $petugas->nip,
                                'role' => $petugas->role->value,
                                'is_active' => (bool) $petugas->is_active,
                            ]) }})"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <i class="fi fi-rr-edit text-xs text-brand-600"></i>
                            <span>Ubah Role</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                        <div class="mx-auto flex max-w-xs flex-col items-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
                                <i class="fi fi-rr-users text-xl"></i>
                            </div>
                            <p class="font-medium text-gray-800 dark:text-white/90">Belum ada data petugas</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tidak ada data petugas yang cocok dengan kriteria filter.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginasi --}}
@if ($petugasList->hasPages())
    <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
        {{ $petugasList->links() }}
    </div>
@endif
