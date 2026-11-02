@props(['perkara'])

@php
    $defaultPenyidikUtama = $perkara->ppnsUtama->first();
    $pegawaiList = \App\Models\User::with(['jabatan', 'panggol'])
        ->where('is_active', true)
        ->orderBy('nama')
        ->get();

    $defaultPenandatanganId = $defaultPenyidikUtama?->id ?? ($pegawaiList->first()?->id ?? '');

    $pegawaiOptions = $pegawaiList->map(function ($u) {
        $nipText = $u->nip && $u->nip !== '-' ? " (NIP: {$u->nip})" : '';
        $jabatanText = $u->jabatan?->nama_jabatan ? " - {$u->jabatan->nama_jabatan}" : '';
        return [
            'value' => (string) $u->id,
            'label' => "{$u->nama}{$nipText}{$jabatanText}",
        ];
    })->values()->toArray();

    $pegawaiMeta = $pegawaiList->keyBy('id')->map(function ($u) {
        return [
            'nama' => $u->nama,
            'nip' => $u->nip && $u->nip !== '-' ? $u->nip : '-',
            'jabatan' => $u->jabatan?->nama_jabatan ?? '-',
            'panggol' => $u->panggol?->nama_pangkat ? $u->panggol->nama_pangkat . ' (' . ($u->panggol->golongan ?? '') . ')' : '-',
        ];
    });
@endphp

<div
    x-show="isSp3ModalOpen"
    x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-data="{
        selectedPenandatanganId: @js((string) $defaultPenandatanganId),
        pegawaiMeta: {{ json_encode($pegawaiMeta) }},
        getSelectedPegawai() {
            return this.pegawaiMeta[this.selectedPenandatanganId] || null;
        }
    }"
    @change="if ($event.target.name === 'penandatangan_id') { selectedPenandatanganId = $event.target.value; }"
>
    <div
        @click.outside="closeSp3Modal()"
        class="relative w-full max-w-xl max-h-[90vh] flex flex-col rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        {{-- Header Modal --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2.5 text-red-600 dark:text-red-400">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <i class="fi fi-rr-ban text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Terbitkan SP3 (Penghentian Penyidikan)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Penerbitan Surat Perintah Penghentian Penyidikan (SP3)
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="closeSp3Modal()"
                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Tutup modal"
            >
                <i class="fi fi-rr-cross-small text-lg"></i>
            </button>
        </div>

        {{-- Form Content --}}
        <form :action="sp3ActionUrl" method="POST" enctype="multipart/form-data" class="flex flex-1 flex-col overflow-hidden">
            @csrf

            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                {{-- Banner Peringatan --}}
                <div class="rounded-xl border border-red-200 bg-red-50/70 p-3.5 text-xs text-red-800 dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-300">
                    <p class="mb-1 font-semibold">Perhatian Penting:</p>
                    Tersangka: <strong x-text="sp3TersangkaNama"></strong>. Penerbitan SP3 akan menetapkan status berkas perkara menjadi <strong>Tidak Selesai</strong> secara formal dan mengunci seluruh tahapan perkara untuk tersangka ini.
                </div>

                {{-- Nomor Surat & Tanggal --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="sp3_nomor_surat" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Nomor Surat SP3 <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="sp3_nomor_surat"
                            name="nomor_surat"
                            required
                            placeholder="Contoh: SPPP/01/BPOM/BJM/2026"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                        />
                    </div>

                    <div>
                        <x-form.date-picker
                            id="sp3_tanggal_sp3"
                            name="tanggal_sp3"
                            label="Tanggal Penetapan SP3"
                            placeholder="Pilih tanggal penetapan..."
                            defaultDate="{{ date('Y-m-d') }}"
                        />
                    </div>
                </div>

                {{-- Pejabat Penandatangan SP3 (Pilihan Pegawai dari Tabel User) --}}
                <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-800 dark:text-white/90">
                            <i class="fi fi-rr-feather text-brand-500"></i>
                            <span>Pejabat Penandatangan SP3 <span class="text-red-500">*</span></span>
                        </div>
                        <span class="text-[11px] text-gray-400">Pilih dari daftar pegawai aktif</span>
                    </div>

                    <div>
                        <x-form.searchable-select
                            id="sp3_penandatangan_id"
                            name="penandatangan_id"
                            placeholder="-- Pilih Pejabat Penandatangan --"
                            searchPlaceholder="Cari nama, NIP, atau jabatan..."
                            :options="$pegawaiOptions"
                            :value="$defaultPenandatanganId"
                            :required="true"
                        />
                    </div>

                    {{-- Informasi Preview Pegawai Terpilih --}}
                    <template x-if="getSelectedPegawai()">
                        <div class="mt-3 rounded-lg border border-gray-200 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-start gap-2.5">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                    <i class="fi fi-rr-id-badge text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold text-gray-900 dark:text-white" x-text="getSelectedPegawai().nama"></span>
                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[11px] text-gray-600 dark:bg-gray-700 dark:text-gray-300" x-text="'NIP: ' + getSelectedPegawai().nip"></span>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Jabatan: <span class="font-medium text-gray-800 dark:text-gray-200" x-text="getSelectedPegawai().jabatan"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Dokumen Fisik PDF --}}
                <div>
                    <x-form.pdf-upload
                        id="sp3_dokumen"
                        name="dokumen"
                        label="Dokumen Fisik SP3 (PDF)"
                        required="true"
                    />
                </div>

                {{-- Alasan & Catatan --}}
                <div>
                    <label for="sp3_keterangan" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                        Alasan &amp; Catatan Penghentian Penyidikan <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="sp3_keterangan"
                        name="keterangan"
                        required
                        rows="3"
                        placeholder="Tuliskan alasan penghentian penyidikan beserta pertimbangan hukum atau catatan kronologis..."
                        class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
                    ></textarea>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                <button
                    type="button"
                    @click="closeSp3Modal()"
                    class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-red-600 px-4 text-xs font-medium text-white transition hover:bg-red-700"
                >
                    <i class="fi fi-rr-check text-xs"></i>
                    <span>Simpan &amp; Terbitkan SP3</span>
                </button>
            </div>
        </form>
    </div>
</div>
