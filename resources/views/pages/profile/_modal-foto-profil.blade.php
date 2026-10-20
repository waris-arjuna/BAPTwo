{{-- resources/views/pages/profile/_modal-foto-profil.blade.php --}}
{{-- MODAL 3: PRATINJAU FOTO PROFIL (LIGHTBOX) --}}
<div x-show="isPhotoModalOpen" x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-6"
    @keydown.escape.window="isPhotoModalOpen = false">
    <div class="fixed inset-0 h-full w-full bg-gray-900/60 backdrop-blur-xs" @click="isPhotoModalOpen = false"></div>

    <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl sm:p-7 dark:border-gray-800 dark:bg-gray-900"
        @click.stop>
        {{-- Tombol Tutup Silang --}}
        <button type="button" @click="isPhotoModalOpen = false"
            class="absolute top-5 end-5 flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
            aria-label="{{ __('Tutup pratinjau foto') }}">
            <i class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
        </button>

        {{-- Header Modal --}}
        <div class="mb-5 pe-10">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                {{ __('Foto Profil') }}
            </h4>
        </div>

        {{-- Body: Foto Beresolusi Penuh & Keterangan Identitas --}}
        <div class="flex flex-col items-center justify-center">
            <div class="relative aspect-square w-full max-w-[340px] overflow-hidden rounded-2xl border-2 border-gray-200 bg-gray-50 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
                <img src="{{ $currentUser?->fotoProfilUrl() }}" alt="{{ $currentUser?->nama }}"
                    class="h-full w-full object-cover" />
            </div>

            {{-- Nama dan Jabatan Lebih Jelas & Besar --}}
            <div class="mt-5 text-center space-y-1.5">
                <h3 class="text-title-sm sm:text-title-md font-bold text-gray-800 dark:text-white/90">
                    {{ $currentUser?->nama ?? '-' }}
                </h3>

                <p class="text-theme-sm sm:text-base font-semibold text-gray-700 dark:text-gray-200">
                    {{ $currentUser?->jabatan?->nama_jabatan ?? '-' }}
                </p>

                <div class="flex flex-wrap items-center justify-center gap-2 pt-0.5">
                    <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        {{ $currentUser?->role?->label() ?? '-' }}
                    </span>
                    @if ($currentUser?->bidang?->nama_bidang)
                        <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                            • {{ $currentUser->bidang->nama_bidang }}
                        </span>
                    @endif
                    @if ($currentUser?->panggol)
                        <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                            • {{ $currentUser->panggolLabel() }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer Modal: Tombol Tutup --}}
        <div class="mt-6 flex items-center justify-end border-t border-gray-100 pt-4 dark:border-gray-800">
            <button type="button" @click="isPhotoModalOpen = false"
                class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-6 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.05]">
                {{ __('Tutup') }}
            </button>
        </div>
    </div>
</div>
