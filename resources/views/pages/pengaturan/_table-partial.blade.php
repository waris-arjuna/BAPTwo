<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
        <tr>
            <th class="w-12 px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('No') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Nama & NIP') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Email') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Role') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Kepegawaian') }}</th>
            <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Status Akun') }}</th>
            <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
        @forelse ($users as $item)
            <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02]">
                <td class="w-12 px-4 py-3.5 text-theme-sm text-gray-500 dark:text-gray-400">
                    {{ $loop->iteration }}
                </td>
                <td class="px-4 py-3.5">
                    <div class="font-medium text-theme-sm text-gray-800 dark:text-white/90">{{ $item->nama }}</div>
                    <div class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $item->nip ?? '-' }}</div>
                </td>
                <td class="px-4 py-3.5 text-theme-sm text-gray-600 dark:text-gray-300">
                    {{ $item->email }}
                </td>
                <td class="px-4 py-3.5">
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        {{ $item->role->label() }}
                    </span>
                </td>
                <td class="px-4 py-3.5">
                    <div class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $item->jabatan?->nama_jabatan ?? strtoupper($item->status) }}
                    </div>
                    <div class="text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ $item->bidang?->nama_bidang ?? '-' }} · {{ $item->panggolLabel() }}
                    </div>
                </td>
                <td class="px-4 py-3.5">
                    @if ($item->trashed())
                        <span class="rounded-full bg-error-50 px-2.5 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">
                            {{ __('Terhapus (Soft Delete)') }}
                        </span>
                    @elseif ($item->is_active)
                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            {{ __('Aktif') }}
                        </span>
                    @else
                        <span class="rounded-full bg-warning-50 px-2.5 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                            {{ __('Nonaktif') }}
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-end">
                    <div class="inline-flex items-center gap-2">
                        @if ($item->trashed())
                            <form method="POST" action="{{ route('users.restore', $item->id) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1 rounded-lg border border-success-300 px-3 py-1.5 text-theme-xs font-medium text-success-600 hover:bg-success-50 dark:border-success-500/30 dark:text-success-400 dark:hover:bg-success-500/10"
                                >
                                    <i class="fi fi-rr-refresh text-xs"></i> {{ __('Restore') }}
                                </button>
                            </form>
                        @else
                            <button
                                type="button"
                                @click="openEditModal(@js([
                                    'nama' => $item->nama,
                                    'nip' => $item->nip,
                                    'email' => $item->email,
                                    'role' => $item->role->value,
                                    'status' => $item->status,
                                    'jabatan_id' => $item->jabatan_id,
                                    'bidang_id' => $item->bidang_id,
                                    'panggol_id' => $item->panggol_id,
                                    'is_active' => $item->is_active,
                                ]), '{{ route('users.update', $item) }}')"
                                class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                            >
                                <i class="fi fi-rr-pencil text-xs"></i> {{ __('Edit') }}
                            </button>

                            @if (auth()->id() !== $item->id)
                                <form method="POST" action="{{ route('users.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1 rounded-lg border border-error-300 px-3 py-1.5 text-theme-xs font-medium text-error-600 hover:bg-error-50 dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/10"
                                    >
                                        <i class="fi fi-rr-trash text-xs"></i> {{ __('Delete') }}
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </td>
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
