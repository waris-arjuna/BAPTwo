@props(['data' => null, 'tahun' => null])

@php
    $tahun = (int) ($tahun ?: date('Y'));
    $data ??= app(\App\Services\DashboardService::class)->getCaseStatusData($tahun);
    $availableYears = $data['availableYears'] ?? app(\App\Services\DashboardService::class)->getAvailableYears();
    $periodeText = $data['periodeText'] ?? "Tahun {$tahun}";
    $updatedAt = $data['updatedAt'] ?? '';
@endphp

<div
    x-data="caseStatusCard({
        url: '{{ route('dashboard.case-status', [], false) }}',
        currentTahun: {{ $tahun }},
        currentBulan: '{{ $data['bulan'] ?? '' }}',
        periodeText: '{{ $periodeText }}',
        updatedAt: '{{ $updatedAt }}'
    })"
    class="@container h-full rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
>

    {{-- Card Header --}}
    <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Perkara Tahun Berjalan (<span x-text="periodeText">{{ $periodeText }}</span>)
                </h2>

                {{-- Info Tooltip Button (A-2) --}}
                <div class="relative inline-block" @keydown.escape.window="showInfo = false">
                    <button
                        type="button"
                        @click="showInfo = !showInfo"
                        @mouseenter="showInfo = true"
                        @mouseleave="showInfo = false"
                        @focus="showInfo = true"
                        @blur="showInfo = false"
                        class="flex h-5 w-5 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Informasi perhitungan status perkara"
                        aria-haspopup="dialog"
                        :aria-expanded="showInfo"
                    >
                        <i class="fi fi-rr-info text-xs"></i>
                    </button>

                    <div
                        x-show="showInfo"
                        x-cloak
                        @click.outside="showInfo = false"
                        class="absolute left-0 top-full z-50 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-3.5 text-xs shadow-lg dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        role="tooltip"
                    >
                        <div class="flex items-center gap-1.5 font-semibold text-gray-800 dark:text-white/90">
                            <i class="fi fi-rr-info text-brand-500"></i>
                            <span>Informasi Monitoring</span>
                        </div>
                        <p class="mt-1.5 leading-relaxed text-gray-600 dark:text-gray-400">
                            Proporsi dihitung dengan metode Largest Remainder berdasarkan total perkara pada periode Laporan Kejadian terpilih (total selalu 100%). Kategori Dalam Proses merupakan ringkasan visual monitoring perkara aktif, bukan status resmi berkas perkara.
                        </p>
                    </div>
                </div>
            </div>

            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Diperbarui: <span x-text="updatedAt">{{ $updatedAt }}</span>
            </p>
        </div>

        {{-- Period Filters (C-1) --}}
        <div class="flex items-center gap-2">
            {{-- Dropdown Bulan --}}
            <select
                x-model="selectedBulan"
                @change="fetchData()"
                class="h-8 rounded-lg border border-gray-200 bg-white px-2.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:border-gray-300 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-700"
                aria-label="Pilih Bulan"
            >
                <option value="">Semua Bulan</option>
                @foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $mNum => $mName)
                    <option value="{{ $mNum }}" {{ ($data['bulan'] ?? null) == $mNum ? 'selected' : '' }}>
                        {{ $mName }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Loading Skeleton (A-7) --}}
    <div x-show="isLoading" x-cloak class="p-5 sm:p-6" aria-busy="true" aria-label="Memuat status perkara...">
        <div class="grid grid-cols-1 @[600px]:grid-cols-[150px_1fr] gap-6 items-center">
            {{-- Left column: Donut skeleton --}}
            <div class="flex flex-col items-center justify-center">
                <div class="relative flex h-[136px] w-[136px] items-center justify-center rounded-full border-[16px] border-gray-100 dark:border-gray-800 animate-pulse">
                    <div class="flex flex-col items-center gap-1">
                        <div class="h-6 w-12 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                        <div class="h-2.5 w-16 rounded bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                    </div>
                </div>
                <div class="mt-3 h-3 w-24 rounded bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
            </div>

            {{-- Right column: Status List skeleton --}}
            <div class="space-y-2.5">
                <div class="h-7 w-full rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                <div class="rounded-lg border border-gray-100 dark:border-gray-800 p-3 space-y-2">
                    <div class="h-4 w-1/3 rounded bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                    <div class="h-3 w-full rounded bg-gray-50 dark:bg-gray-800/60 animate-pulse"></div>
                    <div class="h-3 w-full rounded bg-gray-50 dark:bg-gray-800/60 animate-pulse"></div>
                </div>
                <div class="h-7 w-full rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
            </div>
        </div>
    </div>

    {{-- Dynamic Content Container --}}
    <div x-show="!isLoading" x-ref="contentContainer" class="p-5 sm:p-6">
        @include('components.dashboard._case-status-content', [
            'data' => $data,
            'tahun' => $tahun,
            'bulan' => $data['bulan'] ?? null,
        ])
    </div>

</div>

<script>
    (function () {
        const initCaseStatus = () => {
            if (window.Alpine && !window.Alpine._caseStatusRegistered) {
                window.Alpine._caseStatusRegistered = true;
                window.Alpine.data('caseStatusCard', (config) => ({
                    url: config.url,
                    selectedTahun: config.currentTahun,
                    selectedBulan: config.currentBulan,
                    periodeText: config.periodeText,
                    updatedAt: config.updatedAt,
                    showInfo: false,
                    isLoading: false,

                    async fetchData() {
                        this.isLoading = true;
                        try {
                            const params = new URLSearchParams();
                            if (this.selectedTahun) params.append('tahun', this.selectedTahun);
                            if (this.selectedBulan) params.append('bulan', this.selectedBulan);

                            const res = await fetch(`${this.url}?${params.toString()}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                }
                            });

                            if (!res.ok) throw new Error('Gagal memuat status perkara');

                            const html = await res.text();
                            const container = this.$refs.contentContainer;
                            if (container) {
                                container.innerHTML = html;
                                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                                    window.Alpine.initTree(container);
                                }
                            }

                            const headerPeriode = res.headers.get('X-Periode-Text');
                            const headerUpdated = res.headers.get('X-Updated-At');
                            if (headerPeriode) {
                                this.periodeText = headerPeriode;
                            }
                            if (headerUpdated) {
                                this.updatedAt = headerUpdated;
                            }
                        } catch (err) {
                            console.error('CaseStatus fetch error:', err);
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }));
            }
        };

        if (window.Alpine) {
            initCaseStatus();
        } else {
            document.addEventListener('alpine:init', initCaseStatus);
        }
    })();
</script>
