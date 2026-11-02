@props([
    'berkas',
    'subTahapan' => collect(),
])

@php
    $p16 = $subTahapan[\App\Enums\JenisTahap::P16->value] ?? null;
    $p17p = $subTahapan[\App\Enums\JenisTahap::P17P->value] ?? null;
    $p18p = $subTahapan[\App\Enums\JenisTahap::P18P->value] ?? null;
    $p19 = $subTahapan[\App\Enums\JenisTahap::P19->value] ?? null;
@endphp

<div
    x-data="{
        isOpen: false,
        activeGroup: 'spdp',
        openModal(group) {
            this.activeGroup = group || 'spdp';
            this.isOpen = true;
        },
        closeModal() {
            this.isOpen = false;
        }
    }"
    @open-subtahap-modal.window="openModal($event.detail.group)"
    x-cloak
>
    {{-- Modal Backdrop --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-999 bg-gray-900/60 backdrop-blur-sm dark:bg-gray-950/80"
        @click="closeModal()"
    ></div>

    {{-- Modal Content Box --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
        class="fixed inset-0 z-999 flex items-center justify-center p-4 sm:p-6"
    >
        <div
            @click.stop
            class="relative flex max-h-[90vh] w-full max-w-3xl flex-col rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 "
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800" >
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <i class="fi fi-rr-folder-tree text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            <span x-show="activeGroup === 'spdp'">Kelola Sub-Tahap Kejaksaan (P-16 &amp; P-17P)</span>
                            <span x-show="activeGroup === 'tahap_1'">Kelola Sub-Tahap Kejaksaan (P-18P &amp; P-19)</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Berkas: <strong class="font-mono text-gray-700 dark:text-gray-300">{{ $berkas->nomor_berkas }}</strong> &bull; Tersangka: {{ $berkas->tersangka?->nama ?: '-' }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeModal()"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                >
                    <i class="fi fi-rr-cross-small text-lg"></i>
                </button>
            </div>

            {{-- Form Body --}}
            <form
                action="{{ route('berkas-perkara.tahapan.sub-tahap', $berkas) }}"
                method="POST"
                enctype="multipart/form-data"
                class="flex flex-1 flex-col overflow-hidden"
            >
                @csrf
                <input type="hidden" name="sub_tahap_group" :value="activeGroup">

                <div class="custom-scrollbar flex-1 space-y-6 overflow-y-auto p-6">
                    {{-- GROUP SPDP: P-16 & P-17P --}}
                    <template x-if="activeGroup === 'spdp'">
                        <div class="space-y-6">
                            {{-- Section P-16 --}}
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                                <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                                    <div class="flex items-center gap-2">
                                        <i class="fi fi-rr-user-police text-base text-brand-600"></i>
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                            P-16: Surat Perintah Penunjukan JPU
                                        </h4>
                                    </div>
                                    @if ($p16)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                            <i class="fi fi-rr-check text-xs"></i>
                                            Tercatat
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum Dicatat</span>
                                    @endif
                                </div>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="modal_p16_nomor_surat" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                                Nomor Surat P-16
                                            </label>
                                            <input
                                                type="text"
                                                id="modal_p16_nomor_surat"
                                                name="p16_nomor_surat"
                                                value="{{ old('p16_nomor_surat', $p16?->nomor_surat) }}"
                                                placeholder="Contoh: PRINT-12/O.3.10/Fd.1/02/2026"
                                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            >
                                        </div>

                                        <div>
                                            <x-form.date-picker
                                                id="modal_p16_tanggal_surat"
                                                name="p16_tanggal_surat"
                                                label="Tanggal Surat P-16"
                                                placeholder="Pilih Tanggal P-16"
                                                defaultDate="{{ old('p16_tanggal_surat', $p16?->tanggal_surat?->format('Y-m-d')) }}"
                                                value="{{ old('p16_tanggal_surat', $p16?->tanggal_surat?->format('Y-m-d')) }}"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <x-form.pdf-upload
                                            id="modal_p16_dokumen"
                                            name="p16_dokumen"
                                            label="Unggah Dokumen PDF P-16"
                                            :existingFile="$p16?->dokumen?->first()?->nama_file_asli"
                                            :existingUrl="$p16?->dokumen?->first() ? route('dokumen.preview', $p16->dokumen->first()) : null"
                                        />
                                    </div>

                                    <div>
                                        <label for="modal_p16_catatan" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            Catatan / Nama JPU yang Ditunjuk
                                        </label>
                                        <textarea
                                            id="modal_p16_catatan"
                                            name="p16_catatan"
                                            rows="2"
                                            placeholder="Contoh: JPU Utama: Ahmad S.H., M.H."
                                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        >{{ old('p16_catatan', $p16?->catatan) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Section P-17P --}}
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                                <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                                    <div class="flex items-center gap-2">
                                        <i class="fi fi-rr-time-forward text-base text-amber-600"></i>
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                            P-17P: Permintaan Perkembangan Penyidikan
                                        </h4>
                                    </div>
                                    @if ($p17p)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            <i class="fi fi-rr-check text-xs"></i>
                                            Tercatat
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum Dicatat</span>
                                    @endif
                                </div>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="modal_p17p_nomor_surat" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                                Nomor Surat P-17P
                                            </label>
                                            <input
                                                type="text"
                                                id="modal_p17p_nomor_surat"
                                                name="p17p_nomor_surat"
                                                value="{{ old('p17p_nomor_surat', $p17p?->nomor_surat) }}"
                                                placeholder="Contoh: B-14/O.3.10/Fd.1/02/2026"
                                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            >
                                        </div>

                                        <div>
                                            <x-form.date-picker
                                                id="modal_p17p_tanggal_surat"
                                                name="p17p_tanggal_surat"
                                                label="Tanggal Surat P-17P"
                                                placeholder="Pilih Tanggal P-17P"
                                                defaultDate="{{ old('p17p_tanggal_surat', $p17p?->tanggal_surat?->format('Y-m-d')) }}"
                                                value="{{ old('p17p_tanggal_surat', $p17p?->tanggal_surat?->format('Y-m-d')) }}"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <x-form.pdf-upload
                                            id="modal_p17p_dokumen"
                                            name="p17p_dokumen"
                                            label="Unggah Dokumen PDF P-17P"
                                            :existingFile="$p17p?->dokumen?->first()?->nama_file_asli"
                                            :existingUrl="$p17p?->dokumen?->first() ? route('dokumen.preview', $p17p->dokumen->first()) : null"
                                        />
                                    </div>

                                    <div>
                                        <label for="modal_p17p_catatan" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            Catatan Surat P-17P
                                        </label>
                                        <textarea
                                            id="modal_p17p_catatan"
                                            name="p17p_catatan"
                                            rows="2"
                                            placeholder="Catatan koordinasi dengan kejaksaan..."
                                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        >{{ old('p17p_catatan', $p17p?->catatan) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- GROUP TAHAP 1: P-18P & P-19 --}}
                    <template x-if="activeGroup === 'tahap_1'">
                        <div class="space-y-6">
                            {{-- Section P-18P --}}
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                                <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                                    <div class="flex items-center gap-2">
                                        <i class="fi fi-rr-document-signed text-base text-amber-600"></i>
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                            P-18P: Hasil Penyidikan Belum Lengkap
                                        </h4>
                                    </div>
                                    @if ($p18p)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            <i class="fi fi-rr-check text-xs"></i>
                                            Tercatat
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum Dicatat</span>
                                    @endif
                                </div>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="modal_p18p_nomor_surat" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                                Nomor Surat P-18P
                                            </label>
                                            <input
                                                type="text"
                                                id="modal_p18p_nomor_surat"
                                                name="p18p_nomor_surat"
                                                value="{{ old('p18p_nomor_surat', $p18p?->nomor_surat) }}"
                                                placeholder="Contoh: B-20/O.3.10/Fd.1/03/2026"
                                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            >
                                        </div>

                                        <div>
                                            <x-form.date-picker
                                                id="modal_p18p_tanggal_surat"
                                                name="p18p_tanggal_surat"
                                                label="Tanggal Surat P-18P"
                                                placeholder="Pilih Tanggal P-18P"
                                                defaultDate="{{ old('p18p_tanggal_surat', $p18p?->tanggal_surat?->format('Y-m-d')) }}"
                                                value="{{ old('p18p_tanggal_surat', $p18p?->tanggal_surat?->format('Y-m-d')) }}"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <x-form.pdf-upload
                                            id="modal_p18p_dokumen"
                                            name="p18p_dokumen"
                                            label="Unggah Dokumen PDF P-18P"
                                            :existingFile="$p18p?->dokumen?->first()?->nama_file_asli"
                                            :existingUrl="$p18p?->dokumen?->first() ? route('dokumen.preview', $p18p->dokumen->first()) : null"
                                        />
                                    </div>

                                    <div>
                                        <label for="modal_p18p_catatan" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            Catatan / Petunjuk Kejaksaan
                                        </label>
                                        <textarea
                                            id="modal_p18p_catatan"
                                            name="p18p_catatan"
                                            rows="2"
                                            placeholder="Catatan petunjuk penyidikan..."
                                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        >{{ old('p18p_catatan', $p18p?->catatan) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Section P-19 --}}
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                                <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                                    <div class="flex items-center gap-2">
                                        <i class="fi fi-rr-undo text-base text-error-600"></i>
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                            P-19: Pengembalian Berkas Perkara untuk Dilengkapi
                                        </h4>
                                    </div>
                                    @if ($p19)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/10 dark:text-error-400">
                                            <i class="fi fi-rr-check text-xs"></i>
                                            Tercatat
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum Dicatat</span>
                                    @endif
                                </div>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="modal_p19_nomor_surat" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                                Nomor Surat P-19
                                            </label>
                                            <input
                                                type="text"
                                                id="modal_p19_nomor_surat"
                                                name="p19_nomor_surat"
                                                value="{{ old('p19_nomor_surat', $p19?->nomor_surat) }}"
                                                placeholder="Contoh: B-25/O.3.10/Fd.1/03/2026"
                                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            >
                                        </div>

                                        <div>
                                            <x-form.date-picker
                                                id="modal_p19_tanggal_surat"
                                                name="p19_tanggal_surat"
                                                label="Tanggal Surat P-19"
                                                placeholder="Pilih Tanggal P-19"
                                                defaultDate="{{ old('p19_tanggal_surat', $p19?->tanggal_surat?->format('Y-m-d')) }}"
                                                value="{{ old('p19_tanggal_surat', $p19?->tanggal_surat?->format('Y-m-d')) }}"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <x-form.pdf-upload
                                            id="modal_p19_dokumen"
                                            name="p19_dokumen"
                                            label="Unggah Dokumen PDF P-19"
                                            :existingFile="$p19?->dokumen?->first()?->nama_file_asli"
                                            :existingUrl="$p19?->dokumen?->first() ? route('dokumen.preview', $p19->dokumen->first()) : null"
                                        />
                                    </div>

                                    <div>
                                        <label for="modal_p19_catatan" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            Catatan / Petunjuk Tambahan
                                        </label>
                                        <textarea
                                            id="modal_p19_catatan"
                                            name="p19_catatan"
                                            rows="2"
                                            placeholder="Catatan petunjuk tambahan..."
                                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        >{{ old('p19_catatan', $p19?->catatan) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <button
                        type="button"
                        @click="closeModal()"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-700 focus:ring-2 focus:ring-brand-500/20"
                    >
                        <i class="fi fi-rr-disk text-xs"></i>
                        <span>Simpan Sub-Tahap</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
