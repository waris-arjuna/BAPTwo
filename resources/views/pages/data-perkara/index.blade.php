@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Header & Modal Tambah Perkara --}}
        <div
            x-data="{
                openTambahModal: false,
                selectedPerkaraId: '{{ $availableSprindiks->first()?->id ?? '' }}',
                lanjutkanBuat() {
                    if (this.selectedPerkaraId) {
                        window.location.href = `/perkara/${this.selectedPerkaraId}/buat`;
                    }
                }
            }"
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ url('/') }}" class="transition hover:text-brand-500">Home</a>
                    <span>/</span>
                    <span class="text-gray-500 dark:text-gray-400">Data Perkara</span>
                    <span>/</span>
                    <span class="text-gray-700 dark:text-gray-300">Daftar Perkara</span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Daftar Perkara
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola dan telusuri seluruh perkara, identitas tersangka, berkas perkara terpisah, dan tim penyidik PPNS.
                </p>
            </div>

            <div class="flex items-center gap-3">
                @can('export-excel')
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-export-modal')"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <i class="fi fi-rr-file-excel text-base text-success-600 dark:text-success-400"></i>
                        <span>Ekspor Excel</span>
                    </button>
                @endcan

                @can('create-cases')
                    <div>
                        <button
                            type="button"
                            @click="openTambahModal = true"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                        >
                            <i class="fi fi-rr-plus-small text-lg"></i>
                            <span>Tambah Perkara</span>
                        </button>

                    {{-- Modal Pemilihan Sprindik Final --}}
                    <template x-teleport="body">
                        <div
                            x-show="openTambahModal"
                            x-cloak
                            class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4 backdrop-blur-sm"
                            @keydown.escape.window="openTambahModal = false"
                        >
                            <div
                                @click.outside="openTambahModal = false"
                                class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                            >
                                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                                            <i class="fi fi-rr-folders text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Pilih Dasar Sprindik</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Pilih Surat Perintah Penyidikan yang sudah final.</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="openTambahModal = false"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                                    >
                                        <i class="fi fi-rr-cross text-xs"></i>
                                    </button>
                                </div>

                                @if ($availableSprindiks->isNotEmpty())
                                    <div class="space-y-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            Sesuai alur administrasi penanganan perkara, pembuatan Perkara &amp; Berkas Perkara memerlukan Surat Perintah Penyidikan (Sprindik) yang telah difinalkan.
                                        </p>

                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Surat Perintah Penyidikan (Sprindik) <span class="text-red-500">*</span>
                                            </label>
                                            <select
                                                x-model="selectedPerkaraId"
                                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                            >
                                                @foreach ($availableSprindiks as $sprindik)
                                                    <option value="{{ $sprindik->id }}">
                                                        {{ $sprindik->nomor_surat_perintah }} (Tanggal: {{ $sprindik->tanggal_surat_perintah?->format('d-m-Y') ?? '-' }}, LK: {{ $sprindik->nomor_laporan_kejadian }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-6 flex justify-end gap-3 pt-3">
                                            <button
                                                type="button"
                                                @click="openTambahModal = false"
                                                class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                            >
                                                Batal
                                            </button>
                                            <button
                                                type="button"
                                                @click="lanjutkanBuat()"
                                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"
                                            >
                                                <span>Lanjutkan Buat Perkara</span>
                                                <i class="fi fi-rr-arrow-right text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="space-y-4 py-2 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-warning-50 text-warning-500 dark:bg-warning-500/10">
                                            <i class="fi fi-rr-info text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-800 dark:text-white/90">Belum Ada Sprindik yang Tersedia</h4>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Seluruh Sprindik yang difinalkan telah dibuatkan Perkara, atau belum ada Sprindik baru yang difinalkan.
                                            </p>
                                        </div>
                                        <div class="pt-2">
                                            <a
                                                href="{{ route('sprindik.create') }}"
                                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white hover:bg-brand-600"
                                            >
                                                <i class="fi fi-rr-plus-small text-sm"></i>
                                                <span>Input Sprindik Baru</span>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </template>
                </div>
            @endcan
            </div>
        </div>

        {{-- 5 Kartu Ringkasan Informasi Perkara (Poin 3B) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            {{-- Total Perkara --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <i class="fi fi-rr-folders text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Perkara</p>
                        <h4 class="mt-1 text-lg font-bold tabular-nums text-gray-800 dark:text-white/90">
                            {{ $summaryCards['total'] ?? 0 }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">perkara</span>
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Dalam Proses --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <i class="fi fi-rr-refresh text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Dalam Proses</p>
                        <h4 class="mt-1 text-lg font-bold tabular-nums text-brand-600 dark:text-brand-400">
                            {{ $summaryCards['dalam_proses'] ?? 0 }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">perkara</span>
                        </h4>
                    </div>
                </div>
            </div>

            {{-- DPO Aktif --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <i class="fi fi-rr-ban text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">DPO Aktif</p>
                        <h4 class="mt-1 text-lg font-bold tabular-nums text-red-600 dark:text-red-400">
                            {{ $summaryCards['dpo_aktif'] ?? 0 }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">perkara</span>
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Selesai --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                        <i class="fi fi-rr-check-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Selesai</p>
                        <h4 class="mt-1 text-lg font-bold tabular-nums text-success-600 dark:text-success-400">
                            {{ $summaryCards['selesai'] ?? 0 }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">perkara</span>
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Tidak Selesai / SP3 --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400">
                        <i class="fi fi-rr-cross-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tidak Selesai / SP3</p>
                        <h4 class="mt-1 text-lg font-bold tabular-nums text-red-700 dark:text-red-400">
                            {{ $summaryCards['tidak_selesai'] ?? 0 }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">perkara</span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Area --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Toolbar Filter --}}
            <x-common.table-filter
                searchPlaceholder="Cari nomor Sprindik, LK, TKP, tersangka..."
                :statusOptions="[
                    'Dalam Proses' => 'Dalam Proses',
                    'Selesai' => 'Selesai',
                    'Tidak Selesai (SP3)' => 'Tidak Selesai / SP3',
                    'DPO Aktif' => 'DPO Aktif',
                    'Draft Perkara' => 'Draft Perkara',
                    'Perkara Terbentuk' => 'Perkara Terbentuk',
                ]"
            >
                {{-- Dropdown PPNS Utama (Khusus Admin, Poin 3E) --}}
                @if (auth()->user()?->isAdmin() && !empty($ppnsList))
                    <select
                        name="ppns_id"
                        class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">Semua PPNS Utama</option>
                        @foreach ($ppnsList as $ppns)
                            <option value="{{ $ppns->id }}" @selected((string) request('ppns_id') === (string) $ppns->id)>
                                {{ $ppns->nama }}
                            </option>
                        @endforeach
                    </select>
                @endif

                {{-- Dropdown Tahapan Terakhir (Poin 3E) --}}
                <select
                    name="tahap"
                    class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">Semua Tahapan</option>
                    <option value="Pra-SPDP" @selected(request('tahap') === 'Pra-SPDP')>Pra-SPDP</option>
                    <option value="SPDP" @selected(request('tahap') === 'SPDP')>SPDP</option>
                    <option value="Tahap I" @selected(request('tahap') === 'Tahap I')>Tahap I</option>
                    <option value="P-21" @selected(request('tahap') === 'P-21')>P-21</option>
                    <option value="Tahap II" @selected(request('tahap') === 'Tahap II')>Tahap II</option>
                    <option value="Keputusan Pengadilan" @selected(request('tahap') === 'Keputusan Pengadilan')>Keputusan Pengadilan</option>
                </select>
            </x-common.table-filter>

            {{-- Table Partial Wrapper --}}
            <div id="table-body-partial">
                @include('pages.data-perkara._table-partial')
            </div>
        </div>
    </div>

    {{-- Modal Ekspor Excel --}}
    <x-common.export-excel-modal />
@endsection
