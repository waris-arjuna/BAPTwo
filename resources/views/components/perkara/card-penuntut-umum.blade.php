@props(['perkara'])

@if ($perkara && $perkara->hasPenuntutUmum())
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    <i class="fi fi-rr-scale text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                        Penuntut Umum (JPU)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Data koordinasi Kejaksaan dan Jaksa Penuntut Umum mitra perkara.
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                <i class="fi fi-rr-building text-[10px]"></i>
                Mitra Kejaksaan
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Instansi Kejaksaan --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Asal Instansi Kejaksaan</p>
                <div class="mt-1 flex items-start gap-2">
                    <i class="fi fi-rr-building mt-0.5 text-sm text-gray-500 dark:text-gray-400"></i>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $perkara->penuntutUmumInstansi?->nama ?: 'Belum ditentukan' }}
                    </p>
                </div>
            </div>

            {{-- Nama Jaksa Penuntut --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Nama Jaksa Penuntut Umum</p>
                <div class="mt-1 flex items-start gap-2">
                    <i class="fi fi-rr-user mt-0.5 text-sm text-gray-500 dark:text-gray-400"></i>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $perkara->penuntut_umum_nama ?: 'Belum diisi' }}
                    </p>
                </div>
            </div>

            {{-- NIP Jaksa Penuntut --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500">NIP Jaksa Penuntut</p>
                <div class="mt-1 flex items-start gap-2">
                    <i class="fi fi-rr-id-badge mt-0.5 text-sm text-gray-500 dark:text-gray-400"></i>
                    <p class="font-mono text-sm font-medium tabular-nums text-gray-800 dark:text-white/90">
                        {{ $perkara->penuntut_umum_nip ?: '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
