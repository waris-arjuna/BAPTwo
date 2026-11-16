{{-- Modal Detail Audit Log --}}
<div
    x-show="detailModalOpen"
    x-cloak
    role="dialog"
    aria-modal="true"
    aria-labelledby="audit-detail-modal-title"
    class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-xs"
>
    <div
        @click.outside="closeDetailModal()"
        class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
    >
        {{-- Header Modal --}}
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div>
                <h3 id="audit-detail-modal-title" class="text-base font-semibold text-gray-800 dark:text-white/90">
                    {{ __('Detail Catatan Audit Log') }}
                </h3>
                <p class="mt-0.5 text-xs tabular-nums text-gray-500 dark:text-gray-400">
                    <span>ID #</span><span class="font-mono" x-text="selectedLog?.id"></span>
                    <span> · </span>
                    <span x-text="selectedLog?.waktu_wita"></span>
                </p>
            </div>
            <button
                type="button"
                @click="closeDetailModal()"
                aria-label="{{ __('Tutup') }}"
                class="rounded-lg p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            >
                <i class="fi fi-rr-cross-small text-lg leading-none" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="custom-scrollbar space-y-5 overflow-y-auto px-6 py-5 text-sm">
            {{-- Ringkasan Aktor & Objek --}}
            <div class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-4 sm:grid-cols-2 dark:border-gray-800 dark:bg-white/[0.02]">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Aktivitas') }}</p>
                    <p class="mt-1 font-medium text-gray-800 dark:text-white/90" x-text="selectedLog?.aksi_label"></p>
                    <p class="mt-0.5 font-mono text-xs text-gray-500 dark:text-gray-400" x-text="selectedLog?.aksi"></p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Pelaku / Aktor') }}</p>
                    <p class="mt-1 font-medium text-gray-800 dark:text-white/90" x-text="selectedLog?.actor_label"></p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" x-text="selectedLog?.actor_email || selectedLog?.actor_role"></p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Alamat IP') }}</p>
                    <p class="mt-1 font-mono text-xs font-medium text-gray-800 dark:text-white/90" x-text="selectedLog?.ip_address"></p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Objek Terkait') }}</p>
                    <p class="mt-1 text-xs font-medium text-gray-800 dark:text-white/90">
                        <span class="font-mono" x-text="selectedLog?.tabel"></span>
                        <span> (#</span><span class="font-mono" x-text="selectedLog?.record_id"></span><span>)</span>
                    </p>
                    <template x-if="selectedLog?.perkara_url">
                        <a
                            :href="selectedLog?.perkara_url"
                            class="mt-1 inline-flex items-center gap-1 font-mono text-xs font-medium text-brand-600 hover:underline dark:text-brand-400"
                        >
                            <span>{{ __('Lihat Perkara') }}: </span>
                            <span x-text="selectedLog?.nomor_lk"></span>
                            <i class="fi fi-rr-arrow-small-right text-sm leading-none" aria-hidden="true"></i>
                        </a>
                    </template>
                </div>
            </div>

            {{-- Transisi Status Terformat --}}
            <template x-if="selectedLog?.transitions && selectedLog.transitions.length > 0">
                <div>
                    <h4 class="mb-2 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">
                        {{ __('Perubahan Status') }}
                    </h4>
                    <div class="space-y-2">
                        <template x-for="(item, idx) in selectedLog.transitions" :key="idx">
                            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs dark:border-gray-800 dark:bg-gray-900">
                                <span class="font-medium text-gray-700 dark:text-gray-300" x-text="item.label"></span>
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-gray-600 dark:bg-gray-800 dark:text-gray-300" x-text="item.from"></span>
                                <i class="fi fi-rr-arrow-small-right text-sm text-gray-400" aria-hidden="true"></i>
                                <span class="rounded bg-brand-50 px-2 py-0.5 font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300" x-text="item.to"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Atribut Terformat --}}
            <template x-if="selectedLog?.attributes && Object.keys(selectedLog.attributes).length > 0">
                <div>
                    <h4 class="mb-2 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">
                        {{ __('Ringkasan Atribut') }}
                    </h4>
                    <div class="grid grid-cols-1 gap-2 rounded-xl border border-gray-200 p-3 sm:grid-cols-2 dark:border-gray-800">
                        <template x-for="[key, val] in Object.entries(selectedLog.attributes)" :key="key">
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400" x-text="key"></p>
                                <p class="break-words text-xs font-medium text-gray-800 dark:text-white/90" x-text="val"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Payload JSON Lama vs Baru --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <h4 class="mb-1.5 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">
                        {{ __('Data Lama (JSON)') }}
                    </h4>
                    <template x-if="selectedLog?.data_lama_json">
                        <pre class="custom-scrollbar max-h-56 overflow-auto rounded-xl border border-gray-200 bg-gray-50 p-3 font-mono text-[11px] leading-relaxed text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300" x-text="selectedLog.data_lama_json"></pre>
                    </template>
                    <template x-if="! selectedLog?.data_lama_json">
                        <div class="rounded-xl border border-dashed border-gray-200 p-3 text-xs text-gray-400 dark:border-gray-800 dark:text-gray-500">
                            {{ __('Tidak ada data lama tercatat.') }}
                        </div>
                    </template>
                </div>

                <div>
                    <h4 class="mb-1.5 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">
                        {{ __('Data Baru (JSON)') }}
                    </h4>
                    <template x-if="selectedLog?.data_baru_json">
                        <pre class="custom-scrollbar max-h-56 overflow-auto rounded-xl border border-gray-200 bg-gray-50 p-3 font-mono text-[11px] leading-relaxed text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300" x-text="selectedLog.data_baru_json"></pre>
                    </template>
                    <template x-if="! selectedLog?.data_baru_json">
                        <div class="rounded-xl border border-dashed border-gray-200 p-3 text-xs text-gray-400 dark:border-gray-800 dark:text-gray-500">
                            {{ __('Tidak ada data baru tercatat.') }}
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="flex justify-end border-t border-gray-100 px-6 py-3.5 dark:border-gray-800">
            <button
                type="button"
                @click="closeDetailModal()"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                {{ __('Tutup') }}
            </button>
        </div>
    </div>
</div>
