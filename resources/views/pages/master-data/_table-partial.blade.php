<table class="min-w-full">
    <thead>
        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
            <th class="w-12 px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('No.') }}</th>
            @if ($type === 'jabatan')
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Nama Jabatan') }}</th>
            @elseif ($type === 'bidang')
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Nama Bidang / Unit') }}</th>
            @elseif ($type === 'panggol')
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Pangkat') }}</th>
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Golongan') }}</th>
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Format Tampilan') }}</th>
            @elseif ($type === 'pasal')
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Undang-Undang') }}</th>
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Pasal') }}</th>
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Isi Ringkas') }}</th>
            @elseif ($type === 'instansi-hukum')
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Jenis') }}</th>
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Nama Instansi') }}</th>
                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Wilayah') }}</th>
            @endif
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
            @if ($canManage)
                <th class="px-4 py-3 text-center text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
            @endif
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($records as $row)
            <tr class="{{ $row->trashed() ? 'opacity-60 bg-gray-50/50 dark:bg-gray-900/30' : '' }}">
                <td class="w-12 px-4 py-3 text-theme-sm text-gray-500 dark:text-gray-400 tabular-nums">
                    {{ method_exists($records, 'firstItem') && $records->firstItem() ? $records->firstItem() + $loop->index : $loop->iteration }}
                </td>
                @if ($type === 'jabatan')
                    <td class="px-4 py-3 font-medium text-gray-800 text-theme-sm dark:text-white/90">
                        {{ $row->nama_jabatan }}
                    </td>
                @elseif ($type === 'bidang')
                    <td class="px-4 py-3 font-medium text-gray-800 text-theme-sm dark:text-white/90">
                        {{ $row->nama_bidang }}
                    </td>
                @elseif ($type === 'panggol')
                    <td class="px-4 py-3 font-medium text-gray-800 text-theme-sm dark:text-white/90">
                        {{ $row->pangkat }}
                    </td>
                    <td class="px-4 py-3 text-theme-sm text-gray-600 dark:text-gray-300">
                        {{ $row->golongan }}
                    </td>
                    <td class="px-4 py-3 text-theme-sm text-brand-600 dark:text-brand-400 font-medium">
                        {{ $row->label() }}
                    </td>
                @elseif ($type === 'pasal')
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $row->nama_pasal }}</div>
                        <div class="text-theme-xs text-gray-500 dark:text-gray-400">
                            No. {{ $row->nomor ?: '-' }} Tahun {{ $row->tahun ?: '-' }}
                        </div>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 text-theme-sm dark:text-white/90">
                        {{ $row->pasal }}
                    </td>
                    <td class="px-4 py-3 text-theme-xs text-gray-600 dark:text-gray-300 max-w-md">
                        {{ $row->isi ?: '-' }}
                    </td>
                @elseif ($type === 'instansi-hukum')
                    <td class="px-4 py-3">
                        <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            {{ $row->jenisLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 text-theme-sm dark:text-white/90">
                        {{ $row->nama }}
                    </td>
                    <td class="px-4 py-3 text-theme-sm text-gray-600 dark:text-gray-300">
                        {{ $row->wilayah ?: '-' }}
                    </td>
                @endif

                <td class="px-4 py-3">
                    @if ($row->trashed())
                        <span class="rounded-full bg-error-50 px-2.5 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">
                            {{ __('Terhapus (Soft Delete)') }}
                        </span>
                    @elseif ($type === 'pasal' && $row->status === 'nonaktif')
                        <span class="rounded-full bg-warning-50 px-2.5 py-0.5 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                            {{ __('Nonaktif') }}
                        </span>
                    @else
                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            {{ __('Aktif') }}
                        </span>
                    @endif
                </td>

                @if ($canManage)
                    <td class="px-4 py-3 text-center">
                        <div class="inline-flex items-center gap-2">
                            @if ($row->trashed())
                                <form method="POST" action="{{ route('master-data.restore', ['type' => $type, 'id' => $row->id]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-success-500 px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-success-600">
                                        <i class="fi fi-rr-refresh text-xs"></i> {{ __('Restore') }}
                                    </button>
                                </form>
                            @else
                                <button
                                    type="button"
                                    @click="openEdit({{ Js::from($row) }})"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                >
                                    <i class="fi fi-rr-pencil text-xs"></i> {{ __('Edit') }}
                                </button>

                                <form method="POST" action="{{ route('master-data.destroy', ['type' => $type, 'id' => $row->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-error-500 px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-error-600">
                                        <i class="fi fi-rr-trash text-xs"></i> {{ __('Delete') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">
                    {{ __('No data available.') }}
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if (method_exists($records, 'hasPages') && $records->hasPages())
    <div class="border-t border-gray-200 px-4 py-3 sm:px-6 dark:border-gray-800">
        {{ $records->links() }}
    </div>
@endif
