{{-- resources/views/pages/profile/_modal-update-akun.blade.php --}}
{{-- MODAL 1: UPDATE EMAIL / PASSWORD --}}
<div x-show="isAccountModalOpen" x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-6">
    <div class="fixed inset-0 h-full w-full bg-gray-900/50 backdrop-blur-xs" @click="isAccountModalOpen = false"></div>

    <div class="relative z-10 w-full max-w-xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl lg:p-8 dark:border-gray-800 dark:bg-gray-900">
        <button type="button" @click="isAccountModalOpen = false"
            class="absolute top-5 end-5 flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
            <i class="fi fi-rr-cross-small text-lg" aria-hidden="true"></i>
        </button>

        <div class="mb-5 pe-10">
            <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ __('Update Email / Password') }}
            </h4>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                {{ __('Masukkan password lama Anda untuk mengonfirmasi perubahan email atau password baru.') }}
            </p>
        </div>

        {{-- Instruksi Syarat Update Password --}}
        <div class="mb-5 rounded-xl border border-brand-200 bg-brand-50/60 p-4 dark:border-brand-500/30 dark:bg-brand-500/10">
            <p class="mb-2 text-theme-xs font-semibold text-brand-700 dark:text-brand-300">
                {{ __('Instruksi Syarat Update Password Baru:') }}
            </p>
            <ol class="list-decimal space-y-1 ps-5 text-theme-xs text-gray-700 dark:text-gray-300">
                <li :class="newPassword.length >= 8 ? 'text-success-600 dark:text-success-400 font-medium' : ''">
                    {{ __('Minimal 8 karakter.') }}
                </li>
                <li :class="(/[a-z]/.test(newPassword) && /[A-Z]/.test(newPassword) && /[0-9]/.test(newPassword)) ? 'text-success-600 dark:text-success-400 font-medium' : ''">
                    {{ __('Kombinasi angka dan huruf (besar dan kecil).') }}
                </li>
                <li :class="/[^A-Za-z0-9]/.test(newPassword) ? 'text-success-600 dark:text-success-400 font-medium' : ''">
                    {{ __('Gunakan simbol (contoh: ! @ # $ % ^ & *).') }}
                </li>
            </ol>
        </div>

        <form method="POST" action="{{ route('profile.account.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="account_email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Email') }} <span class="text-error-500">*</span>
                </label>
                <input type="email" id="account_email" name="email"
                    value="{{ old('email', $currentUser?->email) }}" required
                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                @error('email')
                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="current_password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Password Lama') }} <span class="text-error-500">*</span>
                </label>
                <input type="password" id="current_password" name="current_password" required
                    placeholder="{{ __('Masukkan password saat ini') }}"
                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                @error('current_password')
                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new_password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Password Baru') }}
                    <span class="text-theme-xs font-normal text-gray-400">({{ __('Kosongkan jika hanya mengubah email') }})</span>
                </label>
                <input type="password" id="new_password" name="password" x-model="newPassword"
                    placeholder="{{ __('Minimal 8 karakter, huruf besar/kecil, angka & simbol') }}"
                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                @error('password')
                    <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Konfirmasi Password Baru') }}
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    placeholder="{{ __('Ulangi password baru') }}"
                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="isAccountModalOpen = false"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    {{ __('Batal') }}
                </button>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">
                    {{ __('Simpan Perubahan') }}
                </button>
            </div>
        </form>
    </div>
</div>
