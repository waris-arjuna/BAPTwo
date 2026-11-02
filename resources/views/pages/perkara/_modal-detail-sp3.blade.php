{{-- Modal Lihat Detail SP3 (Surat Perintah Penghentian Penyidikan) --}}
<div
    x-show="isViewSp3ModalOpen"
    x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center p-4 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-detail-sp3-title"
>
    {{-- Backdrop --}}
    <div
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"
        x-show="isViewSp3ModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeViewSp3Modal()"
    ></div>

    {{-- Dialog Panel --}}
    <div
        @click.stop
        x-show="isViewSp3ModalOpen"
        class="relative z-10 w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        {{-- Header Modal --}}
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3.5 dark:border-gray-800">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                    <i class="fi fi-rr-ban text-base"></i>
                </div>
                <div>
                    <h3 id="modal-detail-sp3-title" class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Detail Surat Perintah Penghentian Penyidikan (SP3)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Tersangka: <strong x-text="viewSp3Data.tersangka_nama || '-'" class="text-gray-700 dark:text-gray-300"></strong>
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="closeViewSp3Modal()"
                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Tutup modal detail SP3"
            >
                <i class="fi fi-rr-cross-small text-lg"></i>
            </button>
        </div>

        {{-- Konten Informasi SP3 --}}
        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-4 sm:grid-cols-2 dark:border-gray-800 dark:bg-white/[0.02]">
                {{-- 1. Nomor SP3 --}}
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nomor SP3</p>
                    <p
                        class="mt-1 font-mono text-sm font-semibold text-gray-900 dark:text-white break-all"
                        x-text="viewSp3Data.nomor_surat || '-'"
                    ></p>
                </div>

                {{-- 2. Tanggal SP3 --}}
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal SP3</p>
                    <p
                        class="mt-1 text-sm font-semibold tabular-nums text-gray-900 dark:text-white"
                        x-text="viewSp3Data.tanggal_sp3 || '-'"
                    ></p>
                </div>

                {{-- 3. Tim Penyidik (Poin 6) --}}
                <div class="sm:col-span-2 border-t border-gray-200/70 pt-3 dark:border-gray-800">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Penyidik Utama</p>
                    <div class="mt-1 flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-white/90">
                        <i class="fi fi-rr-badge-check text-xs text-brand-500"></i>
                        <span x-text="viewSp3Data.penyidik_utama || viewSp3Data.penyidik_nama || '-'"></span>
                    </div>

                    <template x-if="viewSp3Data.penyidik_anggota && viewSp3Data.penyidik_anggota.length > 0">
                        <div class="mt-2.5">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Penyidik Anggota</p>
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                <template x-for="(anggota, idx) in viewSp3Data.penyidik_anggota" :key="idx">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        <i class="fi fi-rr-user text-[10px] text-gray-400"></i>
                                        <span x-text="anggota"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- 4. Pejabat Penandatangan SP3 (Poin 6) --}}
                <div class="sm:col-span-2 border-t border-gray-200/70 pt-3 dark:border-gray-800">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pejabat Penandatangan SP3</p>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                        <span x-text="viewSp3Data.penandatangan_nama || '-'"></span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="viewSp3Data.penandatangan_jabatan || '-'"></p>
                </div>
            </div>

            {{-- 5. Lihat File SP3 --}}
            <div>
                <p class="mb-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                    File Dokumen SP3
                </p>
                <template x-if="viewSp3Data.dokumen_preview_url">
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <i class="fi fi-rr-document text-base"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-gray-800 dark:text-white/90" x-text="viewSp3Data.dokumen_nama || 'Dokumen SP3.pdf'"></p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Format PDF Resmi</p>
                            </div>
                        </div>

                        <a
                            :href="viewSp3Data.dokumen_preview_url"
                            :data-pdf-preview="viewSp3Data.dokumen_preview_url"
                            :data-pdf-title="'Dokumen SP3 (' + (viewSp3Data.dokumen_nama || 'SP3') + ')'"
                            :data-pdf-download="viewSp3Data.dokumen_download_url"
                            target="_blank"
                            class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3.5 text-xs font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                        >
                            <i class="fi fi-rr-eye text-xs"></i>
                            <span>Lihat File SP3</span>
                        </a>
                    </div>
                </template>
                <template x-if="!viewSp3Data.dokumen_preview_url">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs italic text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                        Dokumen file SP3 tidak tersedia.
                    </div>
                </template>
            </div>

            {{-- 5. Alasan & Catatan Penghentian Penyidikan --}}
            <div>
                <p class="mb-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                    Alasan &amp; Catatan Penghentian Penyidikan
                </p>
                <div class="max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50/70 p-3.5 text-sm leading-relaxed text-gray-800 whitespace-pre-line dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-200" x-text="viewSp3Data.keterangan || '-'"></div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="mt-6 flex items-center justify-end border-t border-gray-100 pt-4 dark:border-gray-800">
            <button
                type="button"
                @click="closeViewSp3Modal()"
                class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
