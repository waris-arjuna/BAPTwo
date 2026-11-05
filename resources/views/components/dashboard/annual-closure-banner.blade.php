@props([
    'notice' => null,
])

@if (! empty($notice) && is_array($notice))
    <div
        x-data="{
            dismissed: false,
            init() {
                this.dismissed = localStorage.getItem('dismiss_annual_close_{{ $notice['tahun_ini'] }}') === '1';
            },
            dismiss() {
                this.dismissed = true;
                localStorage.setItem('dismiss_annual_close_{{ $notice['tahun_ini'] }}', '1');
            }
        }"
        x-show="!dismissed"
        x-cloak
        class="relative flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 transition sm:flex-row sm:items-center sm:justify-between dark:border-amber-500/30 dark:bg-amber-500/10"
        role="region"
        aria-label="Pemberitahuan Penutupan Berkas Tahunan"
    >
        <div class="flex items-start gap-3 sm:items-center">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">
                <i class="fi fi-rr-bell-ring text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs sm:text-sm font-medium leading-relaxed text-amber-900 dark:text-amber-200">
                    {{ $notice['pesan'] }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <a
                href="{{ $notice['url_daftar'] }}"
                class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-amber-700 focus:outline-hidden focus:ring-2 focus:ring-amber-500/50 dark:bg-amber-500 dark:hover:bg-amber-600 dark:text-gray-950"
            >
                <span>Lihat Daftar Berkas</span>
                <i class="fi fi-rr-arrow-right text-[11px]" aria-hidden="true"></i>
            </a>

            <button
                type="button"
                @click="dismiss()"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 transition hover:bg-amber-100 hover:text-amber-800 focus:outline-hidden focus:ring-2 focus:ring-amber-500/50 dark:text-amber-400 dark:hover:bg-amber-500/20 dark:hover:text-amber-200"
                aria-label="Tutup pemberitahuan"
                title="Tutup pemberitahuan"
            >
                <i class="fi fi-rr-cross text-xs" aria-hidden="true"></i>
            </button>
        </div>
    </div>
@endif
