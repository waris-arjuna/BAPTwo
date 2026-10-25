@props([
    'availableYears' => null,
])

@php
    $years = $availableYears ?? \App\Models\Perkara::whereNotNull('tanggal_laporan_kejadian')
        ->pluck('tanggal_laporan_kejadian')
        ->map(fn ($d) => (int) \Carbon\Carbon::parse($d)->year)
        ->unique()
        ->sortDesc()
        ->values()
        ->all();

    if (empty($years)) {
        $years = [(int) date('Y'), (int) date('Y') - 1];
    }
@endphp

@can('export-excel')
<div
    x-data="{
        isOpen: false,
        mode: 'semua', // 'semua', 'tahun', 'bulan', 'rentang'
        tahun: '{{ (int) date('Y') }}',
        bulan: '{{ (int) date('n') }}',
        tanggalMulai: '',
        tanggalSelesai: '',
        availableYears: {{ json_encode($years) }},
        months: [
            { val: 1, name: 'Januari' },
            { val: 2, name: 'Februari' },
            { val: 3, name: 'Maret' },
            { val: 4, name: 'April' },
            { val: 5, name: 'Mei' },
            { val: 6, name: 'Juni' },
            { val: 7, name: 'Juli' },
            { val: 8, name: 'Agustus' },
            { val: 9, name: 'September' },
            { val: 10, name: 'Oktober' },
            { val: 11, name: 'November' },
            { val: 12, name: 'Desember' }
        ],
        fpMulai: null,
        fpSelesai: null,
        init() {
            // Watch tanggalMulai to update minDate of tanggalSelesai
            this.$watch('tanggalMulai', (val) => {
                if (this.fpSelesai) {
                    this.fpSelesai.set('minDate', val || null);
                }
            });
            this.$watch('mode', () => {
                this.fpMulai?.close();
                this.fpSelesai?.close();
            });
            this.$watch('isOpen', (val) => {
                if (!val) {
                    this.fpMulai?.close();
                    this.fpSelesai?.close();
                }
            });
        },
        async submitExport() {
            if (this.mode === 'rentang') {
                if (!this.tanggalMulai || !this.tanggalSelesai) {
                    window.dispatchEvent(new CustomEvent('toast-notify', {
                        detail: { type: 'warning', title: 'Rentang Tanggal Belum Lengkap', message: 'Harap pilih Tanggal Mulai dan Tanggal Selesai terlebih dahulu.', duration: 3000 }
                    }));
                    return;
                }
                if (this.tanggalMulai > this.tanggalSelesai) {
                    window.dispatchEvent(new CustomEvent('toast-notify', {
                        detail: { type: 'warning', title: 'Urutan Tanggal Tidak Valid', message: 'Tanggal Mulai tidak boleh lebih besar dari Tanggal Selesai.', duration: 3000 }
                    }));
                    return;
                }
            }

            // Pre-check apakah ada data pada periode ini
            const formData = new FormData(this.$refs.exportForm);
            const params = new URLSearchParams(formData);
            params.append('check', '1');

            try {
                const res = await fetch(`{{ route('perkara.export') }}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.count === 0) {
                        window.dispatchEvent(new CustomEvent('toast-notify', {
                            detail: { type: 'warning', title: 'Perhatian', message: 'Tidak ada data pada periode ini', duration: 3000 }
                        }));
                        return;
                    }
                }
            } catch (e) {
                // Lanjut ke submit biasa jika network error
            }

            this.$refs.exportForm.submit();
            this.isOpen = false;
        }
    }"
    @open-export-modal.window="isOpen = true"
>
    {{-- Modal Teleport to Body --}}
    <template x-teleport="body">
        <div
            x-show="isOpen"
            x-cloak
            x-transition:enter="transition-opacity duration-200 ease-out"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-150 ease-in"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto bg-gray-900/60 p-4 backdrop-blur-sm"
            @keydown.escape.window="if (document.querySelector('.flatpickr-calendar.open')) { fpMulai?.close(); fpSelesai?.close(); } else { isOpen = false; }"
        >
            <div
                @click.outside="if (!$event.target.closest('.flatpickr-calendar')) isOpen = false"
                x-transition:enter="transition-[transform,opacity] duration-200 ease-out"
                x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition-[transform,opacity] duration-150 ease-in"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 sm:scale-95"
                class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-7"
            >
                {{-- Header Modal --}}
                <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                            <i class="fi fi-rr-file-excel text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">
                                Ekspor Rekap Perkara (Excel)
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Format LAPJU 45 kolom lengkap berdasarkan tanggal Laporan Kejadian (LK).
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="isOpen = false"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    >
                        <i class="fi fi-rr-cross-small text-xl"></i>
                    </button>
                </div>

                {{-- Form Ekspor --}}
                <form
                    x-ref="exportForm"
                    action="{{ route('perkara.export') }}"
                    method="GET"
                    target="_blank"
                    class="space-y-5"
                >
                    <input type="hidden" name="mode" :value="mode">

                    {{-- 4 Tab Mode Periode (Semua Data / Tahun / Bulan / Rentang Tanggal) --}}
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Pilih Periode Laporan Kejadian
                        </label>
                        <div class="grid grid-cols-2 gap-2 rounded-xl bg-gray-100 p-1.5 dark:bg-gray-800/80 sm:grid-cols-4">
                            {{-- Tab 1: Semua Data --}}
                            <button
                                type="button"
                                @click="mode = 'semua'"
                                :class="mode === 'semua' ? 'bg-white text-gray-900 shadow-theme-xs font-semibold dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium'"
                                class="flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs transition"
                            >
                                <i class="fi fi-rr-apps"></i>
                                <span>Semua Data</span>
                            </button>

                            {{-- Tab 2: Tahun --}}
                            <button
                                type="button"
                                @click="mode = 'tahun'"
                                :class="mode === 'tahun' ? 'bg-white text-gray-900 shadow-theme-xs font-semibold dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium'"
                                class="flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs transition"
                            >
                                <i class="fi fi-rr-calendar"></i>
                                <span>Per Tahun</span>
                            </button>

                            {{-- Tab 3: Bulan --}}
                            <button
                                type="button"
                                @click="mode = 'bulan'"
                                :class="mode === 'bulan' ? 'bg-white text-gray-900 shadow-theme-xs font-semibold dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium'"
                                class="flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs transition"
                            >
                                <i class="fi fi-rr-calendar-clock"></i>
                                <span>Per Bulan</span>
                            </button>

                            {{-- Tab 4: Rentang Tanggal --}}
                            <button
                                type="button"
                                @click="mode = 'rentang'"
                                :class="mode === 'rentang' ? 'bg-white text-gray-900 shadow-theme-xs font-semibold dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium'"
                                class="flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs transition"
                            >
                                <i class="fi fi-rr-calendar-lines"></i>
                                <span>Rentang Tanggal</span>
                            </button>
                        </div>
                    </div>

                    {{-- Konten Tab: Pilihan Semua Data --}}
                    <div x-show="mode === 'semua'" x-cloak class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <i class="fi fi-rr-apps text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Ekspor Seluruh Perkara</h4>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    Mengekspor seluruh data perkara yang tercatat di sistem tanpa batasan tahun atau rentang tanggal.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Konten Tab 2: Pilihan Per Tahun --}}
                    <div x-show="mode === 'tahun'" x-cloak class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <label for="export_tahun" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Pilih Tahun LK
                        </label>
                        <select
                            id="export_tahun"
                            name="tahun"
                            x-model="tahun"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            <template x-for="y in availableYears" :key="y">
                                <option :value="y" x-text="y" :selected="y == tahun"></option>
                            </template>
                        </select>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Seluruh perkara yang Laporan Kejadian (LK) diterbitkan pada tahun terpilih akan diekspor.
                        </p>
                    </div>

                    {{-- Konten Tab 2: Pilihan Per Bulan --}}
                    <div x-show="mode === 'bulan'" x-cloak class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="export_bulan" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Pilih Bulan
                                </label>
                                <select
                                    id="export_bulan"
                                    name="bulan"
                                    x-model="bulan"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                >
                                    <template x-for="m in months" :key="m.val">
                                        <option :value="m.val" x-text="m.name" :selected="m.val == bulan"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label for="export_bulan_tahun" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Tahun
                                </label>
                                <select
                                    id="export_bulan_tahun"
                                    x-model="tahun"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                >
                                    <template x-for="y in availableYears" :key="y">
                                        <option :value="y" x-text="y" :selected="y == tahun"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Hanya mengekspor perkara dengan tanggal LK pada bulan dan tahun terpilih.
                        </p>
                    </div>

                    {{-- Konten Tab 3: Rentang Tanggal Ala Tiket Pesawat --}}
                    <div x-show="mode === 'rentang'" x-cloak class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="mb-2 text-xs font-medium text-gray-700 dark:text-gray-300">
                            Rentang Tanggal LK (Dari &amp; Sampai)
                        </div>

                        {{-- Flight Booking Style Range Box --}}
                        <div class="relative grid grid-cols-1 gap-3 sm:grid-cols-2">
                            {{-- Tanggal Mulai --}}
                            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <i class="fi fi-rr-calendar text-brand-500"></i>
                                    <span>Tanggal Dari (Awal)</span>
                                </div>
                                <input
                                    type="text"
                                    name="tanggal_mulai"
                                    x-model="tanggalMulai"
                                    placeholder="Pilih tanggal mulai..."
                                    x-init="
                                        fpMulai = flatpickr($el, {
                                            dateFormat: 'Y-m-d',
                                            altInput: true,
                                            altFormat: 'j F Y',
                                            locale: 'id',
                                            onChange: function(selectedDates, dateStr) {
                                                tanggalMulai = dateStr;
                                            }
                                        })
                                    "
                                    class="mt-1 h-9 w-full bg-transparent text-sm font-semibold text-gray-800 placeholder-gray-400 focus:outline-none dark:text-white/90"
                                >
                            </div>

                            {{-- Tanggal Selesai --}}
                            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <i class="fi fi-rr-calendar-check text-success-500"></i>
                                    <span>Tanggal Sampai (Akhir)</span>
                                </div>
                                <input
                                    type="text"
                                    name="tanggal_selesai"
                                    x-model="tanggalSelesai"
                                    placeholder="Pilih tanggal akhir..."
                                    x-init="
                                        fpSelesai = flatpickr($el, {
                                            dateFormat: 'Y-m-d',
                                            altInput: true,
                                            altFormat: 'j F Y',
                                            locale: 'id',
                                            onChange: function(selectedDates, dateStr) {
                                                tanggalSelesai = dateStr;
                                            }
                                        })
                                    "
                                    class="mt-1 h-9 w-full bg-transparent text-sm font-semibold text-gray-800 placeholder-gray-400 focus:outline-none dark:text-white/90"
                                >
                            </div>
                        </div>

                        <p class="mt-2.5 text-xs text-gray-500 dark:text-gray-400">
                            Pilih rentang tanggal Laporan Kejadian sebagaimana sistem pemesanan tiket. Data akan difilter dari tanggal awal hingga tanggal akhir inklusif.
                        </p>
                    </div>

                    {{-- Informasi Format Ekspor --}}
                    <div class="flex items-start gap-3 rounded-xl bg-blue-50/70 p-3.5 text-xs text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                        <i class="fi fi-rr-info text-base shrink-0 mt-0.5"></i>
                        <div class="leading-relaxed">
                            <strong>Format Rekapitulasi Resmi:</strong> File hasil ekspor berupa berkas <code>.xlsx</code> dengan 44 kolom terstruktur (LK, Sprindik, Tersangka, TKP, Tahapan Penyidikan SPDP s.d. Putusan/SP3, JPU, Pengadilan, dan Kendala) dengan freeze pane dan sel tanggal asli Excel.
                        </div>
                    </div>

                    {{-- Footer Action Buttons --}}
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button
                            type="button"
                            @click="isOpen = false"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            @click="submitExport()"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-success-600 px-5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-success-700"
                        >
                            <i class="fi fi-rr-download text-base"></i>
                            <span>Download File Excel (.xlsx)</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endcan
