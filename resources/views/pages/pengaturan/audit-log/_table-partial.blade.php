@php
    $zonaWaktu = \App\Services\DashboardService::ZONA_WAKTU;
@endphp

<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
        <tr>
            <th class="w-12 px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('No') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Waktu (WITA)') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Pelaku / Aktor') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Aktivitas') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Objek & Konteks') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Alamat IP') }}</th>
            <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
        @forelse ($logs as $log)
            @php
                $meta = $log->action_meta;
                $details = $log->formatted_details;
                $waktuWita = $log->created_at
                    ? $log->created_at->copy()->setTimezone($zonaWaktu)->translatedFormat('d M Y, H:i:s').' WITA'
                    : '-';
                $perkaraUrl = $log->perkara_id ? route('perkara.show', $log->perkara_id) : null;
                $detailPayload = [
                    'id' => $log->id,
                    'aksi' => $log->aksi,
                    'aksi_label' => $meta['label'],
                    'aksi_badge' => $meta['badge'],
                    'aksi_icon' => $meta['icon'],
                    'waktu_wita' => $waktuWita,
                    'actor_label' => $log->actor_label,
                    'actor_role' => $log->actor_role_label,
                    'actor_email' => $log->user?->email ?? ($log->data_baru['email'] ?? null),
                    'ip_address' => $log->ip_address ?? '-',
                    'tabel' => $log->tabel ?? '-',
                    'record_id' => $log->record_id,
                    'nomor_lk' => $log->perkara?->nomor_laporan_kejadian,
                    'nomor_berkas' => $log->berkasPerkara?->nomor_berkas,
                    'perkara_url' => $perkaraUrl,
                    'transitions' => $details['transitions'],
                    'attributes' => $details['attributes'],
                    'notes' => $details['notes'],
                    'data_lama_json' => ! empty($log->data_lama) ? json_encode($log->data_lama, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                    'data_baru_json' => ! empty($log->data_baru) ? json_encode($log->data_baru, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                ];
            @endphp
            <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02]">
                <td class="w-12 px-4 py-3.5 text-theme-sm tabular-nums text-gray-500 dark:text-gray-400">
                    {{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}
                </td>
                <td class="whitespace-nowrap px-4 py-3.5">
                    @if ($log->created_at)
                        @php $dt = $log->created_at->copy()->setTimezone($zonaWaktu); @endphp
                        <div class="text-theme-sm font-medium tabular-nums text-gray-800 dark:text-white/90">
                            {{ $dt->translatedFormat('d M Y') }}
                        </div>
                        <div class="font-mono text-theme-xs tabular-nums text-gray-500 dark:text-gray-400">
                            {{ $dt->format('H:i:s') }} WITA
                        </div>
                    @else
                        <span class="text-theme-xs text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-4 py-3.5">
                    <div class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $log->actor_label }}
                    </div>
                    <div class="text-theme-xs text-gray-500 dark:text-gray-400">
                        @if ($log->user?->email)
                            {{ $log->user->email }}
                        @elseif (! empty($log->data_baru['email']))
                            {{ $log->data_baru['email'] }}
                        @else
                            {{ $log->actor_role_label }}
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-theme-xs font-medium {{ $meta['badge'] }}">
                        <i class="fi {{ $meta['icon'] }} text-xs leading-none" aria-hidden="true"></i>
                        <span>{{ $meta['label'] }}</span>
                    </span>
                    <div class="mt-1 font-mono text-[11px] text-gray-400 dark:text-gray-500">
                        {{ $log->aksi }}
                    </div>
                </td>
                <td class="px-4 py-3.5">
                    @if ($log->perkara)
                        <a
                            href="{{ route('perkara.show', $log->perkara_id) }}"
                            class="inline-flex items-center gap-1 font-mono text-theme-xs font-medium text-brand-600 hover:underline dark:text-brand-400"
                        >
                            <span>{{ $log->perkara->nomor_laporan_kejadian }}</span>
                            <i class="fi fi-rr-arrow-small-right text-sm leading-none" aria-hidden="true"></i>
                        </a>
                        @if ($log->berkasPerkara)
                            <div class="mt-0.5 font-mono text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $log->berkasPerkara->nomor_berkas }}
                            </div>
                        @endif
                    @else
                        <div class="text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Tabel:') }} <span class="font-mono">{{ $log->tabel ?? '-' }}</span>
                        </div>
                        @if (! empty($log->data_baru['filename']))
                            <div class="mt-0.5 max-w-[220px] truncate text-[11px] text-gray-500 dark:text-gray-400" title="{{ $log->data_baru['filename'] }}">
                                {{ $log->data_baru['filename'] }}
                            </div>
                        @elseif (isset($log->data_baru['total']))
                            <div class="mt-0.5 text-[11px] tabular-nums text-gray-500 dark:text-gray-400">
                                {{ __('Diproses: :n pegawai', ['n' => $log->data_baru['total']]) }}
                            </div>
                        @else
                            <div class="mt-0.5 font-mono text-[11px] text-gray-400 dark:text-gray-500">
                                ID #{{ $log->record_id }}
                            </div>
                        @endif
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3.5 font-mono text-theme-xs text-gray-600 dark:text-gray-300">
                    {{ $log->ip_address ?? '-' }}
                </td>
                <td class="px-4 py-3.5 text-end">
                    <button
                        type="button"
                        @click="openDetailModal(@js($detailPayload))"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/5"
                    >
                        <i class="fi fi-rr-eye text-xs leading-none" aria-hidden="true"></i>
                        <span>{{ __('Detail') }}</span>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-12 text-center">
                    <div class="mx-auto flex max-w-md flex-col items-center">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                            <i class="fi fi-rr-time-past text-xl leading-none" aria-hidden="true"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ __('Belum ada catatan audit log yang sesuai') }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Coba ubah kata kunci pencarian, kategori, atau rentang tanggal filter Anda.') }}
                        </p>
                        @if (request()->anyFilled(['search', 'kategori', 'aksi', 'tanggal_dari', 'tanggal_sampai']))
                            <a
                                href="{{ route('audit-log.index') }}"
                                class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                <i class="fi fi-rr-refresh text-xs leading-none" aria-hidden="true"></i>
                                <span>{{ __('Atur Ulang Filter') }}</span>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($logs->hasPages())
    <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
        {{ $logs->links() }}
    </div>
@endif
