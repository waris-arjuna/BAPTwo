/**
 * Modul Logika Form Perkara (BAPTwo)
 * Factory functions Alpine.js untuk formulir penanganan perkara:
 * 1. tkpWilayahForm - Wilayah bertingkat TKP Kalimantan Selatan
 * 2. ppnsTimForm - Manajemen PPNS Utama & Anggota dengan proteksi eksklusivitas
 * 3. tersangkaAlamatModalForm - Modal wilayah 38 provinsi seluruh Indonesia
 * 4. pasalPerkaraForm - Pemilihan pasal undang-undang berlapis
 */

/**
 * Komponen Wilayah TKP (Provinsi Kalimantan Selatan)
 */
export function tkpWilayahForm(config = {}) {
    return {
        kabupatenList: [],
        kecamatanList: [],
        desaList: [],
        provinsiKode: config.provinsiKode || '63',
        provinsiNama: config.provinsiNama || 'Kalimantan Selatan',
        kabupatenKode: config.kabupatenKode || '',
        kabupatenNama: config.kabupatenNama || '',
        kecamatanKode: config.kecamatanKode || '',
        kecamatanNama: config.kecamatanNama || '',
        desaKode: config.desaKode || '',
        desaNama: config.desaNama || '',
        prevKabupatenKode: config.kabupatenKode ? String(config.kabupatenKode) : '',
        prevKecamatanKode: config.kecamatanKode ? String(config.kecamatanKode) : '',
        errorKecamatan: '',
        errorDesa: '',
        kecamatanAbortController: null,
        desaAbortController: null,
        isInitializing: false,

        async init() {
            this.isInitializing = true;
            if (config.kabupatenKode) this.kabupatenKode = String(config.kabupatenKode);
            if (config.kabupatenNama) this.kabupatenNama = String(config.kabupatenNama);
            if (config.kecamatanKode) this.kecamatanKode = String(config.kecamatanKode);
            if (config.kecamatanNama) this.kecamatanNama = String(config.kecamatanNama);
            if (config.desaKode) this.desaKode = String(config.desaKode);
            if (config.desaNama) this.desaNama = String(config.desaNama);
            this.prevKabupatenKode = this.kabupatenKode;
            this.prevKecamatanKode = this.kecamatanKode;

            try {
                await this.loadKabupaten(false);
                if (this.kabupatenKode) await this.loadKecamatan(false, this.kabupatenKode, false);
                if (this.kecamatanKode) await this.loadDesa(false, this.kecamatanKode, false);
            } finally {
                this.$nextTick(() => {
                    this.isInitializing = false;
                });
            }

            this.$watch('kabupatenKode', (val) => {
                const found = this.kabupatenList.find(k => String(k.code || k.kode) === String(val));
                this.kabupatenNama = found ? (found.name || found.nama) : '';
            });
            this.$watch('kecamatanKode', (val) => {
                const found = this.kecamatanList.find(k => String(k.code || k.kode) === String(val));
                this.kecamatanNama = found ? (found.name || found.nama) : '';
            });
            this.$watch('desaKode', (val) => {
                const found = this.desaList.find(d => String(d.code || d.kode) === String(val));
                this.desaNama = found ? (found.name || found.nama) : '';
            });
        },

        async loadKabupaten(reset = true) {
            if (reset) {
                this.kabupatenKode = '';
                this.kabupatenNama = '';
                this.prevKabupatenKode = '';
                this.kecamatanKode = '';
                this.kecamatanNama = '';
                this.prevKecamatanKode = '';
                this.desaKode = '';
                this.desaNama = '';
                this.kecamatanList = [];
                this.desaList = [];
            }
            try {
                const res = await fetch(`/wilayah/kabupaten/${this.provinsiKode}`);
                const json = await res.json();
                this.kabupatenList = json.data || [];
                const found = this.kabupatenList.find(k => String(k.code || k.kode) === String(this.kabupatenKode));
                if (found) this.kabupatenNama = found.name || found.nama;
            } catch (e) {}
        },

        async loadKecamatan(reset = true, targetKode = null, autoOpen = false) {
            this.errorKecamatan = '';
            if (targetKode !== null && targetKode !== undefined) {
                this.kabupatenKode = String(targetKode);
            }
            if (reset) {
                this.kecamatanKode = '';
                this.kecamatanNama = '';
                this.prevKecamatanKode = '';
                this.kecamatanList = [];
                this.desaKode = '';
                this.desaNama = '';
                this.desaList = [];
                this.$dispatch('searchable-select-loading', { id: 'tkp_kecamatan_kode', loading: false });
                this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: false });
            }
            const found = this.kabupatenList.find(k => String(k.code || k.kode) === String(this.kabupatenKode));
            this.kabupatenNama = found ? (found.name || found.nama) : '';

            if (!this.kabupatenKode) {
                this.kecamatanList = [];
                this.$dispatch('searchable-select-loading', { id: 'tkp_kecamatan_kode', loading: false });
                return;
            }

            if (this.kecamatanAbortController) {
                this.kecamatanAbortController.abort();
            }
            this.kecamatanAbortController = new AbortController();
            const currentKab = this.kabupatenKode;

            this.$dispatch('searchable-select-loading', { id: 'tkp_kecamatan_kode', loading: true });

            try {
                const res = await fetch(`/wilayah/kecamatan/${currentKab}`, {
                    signal: this.kecamatanAbortController.signal
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                if (this.kabupatenKode === currentKab) {
                    this.kecamatanList = json.data || [];
                    const foundKec = this.kecamatanList.find(k => String(k.code || k.kode) === String(this.kecamatanKode));
                    if (foundKec) this.kecamatanNama = foundKec.name || foundKec.nama;
                    if (autoOpen && this.kecamatanList.length > 0) {
                        this.$nextTick(() => {
                            this.$dispatch('searchable-select-open', { id: 'tkp_kecamatan_kode' });
                        });
                    }
                }
            } catch (e) {
                if (e.name !== 'AbortError') {
                    this.kecamatanList = [];
                    this.errorKecamatan = 'Gagal memuat daftar kecamatan.';
                }
            } finally {
                if (this.kabupatenKode === currentKab) {
                    this.$dispatch('searchable-select-loading', { id: 'tkp_kecamatan_kode', loading: false });
                }
            }
        },

        async loadDesa(reset = true, targetKode = null, autoOpen = false) {
            this.errorDesa = '';
            if (targetKode !== null && targetKode !== undefined) {
                this.kecamatanKode = String(targetKode);
            }
            if (reset) {
                this.desaKode = '';
                this.desaNama = '';
                this.desaList = [];
                this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: false });
            }
            const found = this.kecamatanList.find(k => String(k.code || k.kode) === String(this.kecamatanKode));
            this.kecamatanNama = found ? (found.name || found.nama) : '';

            if (!this.kecamatanKode) {
                this.desaList = [];
                this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: false });
                return;
            }

            if (this.desaAbortController) {
                this.desaAbortController.abort();
            }
            this.desaAbortController = new AbortController();
            const currentKec = this.kecamatanKode;

            this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: true });

            try {
                const res = await fetch(`/wilayah/desa/${currentKec}`, {
                    signal: this.desaAbortController.signal
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                if (this.kecamatanKode === currentKec) {
                    this.desaList = json.data || [];
                    const foundDesa = this.desaList.find(d => String(d.code || d.kode) === String(this.desaKode));
                    if (foundDesa) this.desaNama = foundDesa.name || foundDesa.nama;
                    if (autoOpen && this.desaList.length > 0) {
                        this.$nextTick(() => {
                            this.$dispatch('searchable-select-open', { id: 'tkp_desa_kode' });
                        });
                    }
                }
            } catch (e) {
                if (e.name !== 'AbortError') {
                    this.desaList = [];
                    this.errorDesa = 'Gagal memuat daftar desa/kelurahan.';
                }
            } finally {
                if (this.kecamatanKode === currentKec) {
                    this.$dispatch('searchable-select-loading', { id: 'tkp_desa_kode', loading: false });
                }
            }
        },

        retryLoadKecamatan() {
            if (this.kabupatenKode) {
                this.loadKecamatan(false, this.kabupatenKode, true);
            }
        },

        retryLoadDesa() {
            if (this.kecamatanKode) {
                this.loadDesa(false, this.kecamatanKode, true);
            }
        },

        extractEventValue(event, fallback = '') {
            if (event === undefined || event === null) return fallback;
            if (typeof event === 'object') {
                if ('detail' in event && event.detail !== undefined && event.detail !== null) {
                    const d = event.detail;
                    if (typeof d === 'object') {
                        if (d && 'value' in d) return String(d.value);
                        return fallback;
                    }
                    return String(d);
                }
                if ('target' in event && event.target && 'value' in event.target && event.target.value !== undefined && event.target.value !== null) {
                    return String(event.target.value);
                }
                return fallback;
            }
            return String(event);
        },

        onKabupatenChange(event) {
            if (this.isInitializing) return;
            const val = this.extractEventValue(event, this.kabupatenKode);
            if (val === undefined || val === null) return;
            const newCode = String(val);
            if (newCode === this.prevKabupatenKode) return;

            // Jika kecamatan sebelumnya sudah terisi (re-selection), jangan auto open (opsi A3).
            // Jika kecamatan sebelumnya masih kosong, auto open (opsi A1).
            const shouldAutoOpen = !this.kecamatanKode;
            this.prevKabupatenKode = newCode;
            this.kabupatenKode = newCode;
            this.loadKecamatan(true, newCode, shouldAutoOpen);
        },

        onKecamatanChange(event) {
            if (this.isInitializing) return;
            const val = this.extractEventValue(event, this.kecamatanKode);
            if (val === undefined || val === null) return;
            const newCode = String(val);
            if (newCode === this.prevKecamatanKode) return;

            // Jika desa sebelumnya sudah terisi, jangan auto open. Jika kosong, auto open.
            const shouldAutoOpen = !this.desaKode;
            this.prevKecamatanKode = newCode;
            this.kecamatanKode = newCode;
            this.loadDesa(true, newCode, shouldAutoOpen);
        },

        onDesaChange(event) {
            if (this.isInitializing) return;
            const val = this.extractEventValue(event, this.desaKode);
            if (val !== undefined && val !== null) {
                this.desaKode = String(val);
                const found = this.desaList.find(d => String(d.code || d.kode) === String(this.desaKode));
                this.desaNama = found ? (found.name || found.nama) : '';
            }
        }
    };
}

/**
 * Komponen Tim Penyidik PPNS (Utama & Anggota)
 */
export function ppnsTimForm(config = {}) {
    return {
        ppnsList: config.ppnsList || [],
        selectedUtamaId: config.selectedUtamaId || null,
        selectedAnggotaIds: config.selectedAnggotaIds || [],

        extractId(eventOrVal) {
            if (eventOrVal === null || eventOrVal === undefined) return null;
            if (typeof eventOrVal === 'object') {
                if ('detail' in eventOrVal && eventOrVal.detail !== undefined) {
                    const d = eventOrVal.detail;
                    if (typeof d === 'object') return null;
                    return d !== '' && d !== null ? parseInt(d, 10) || null : null;
                }
                if ('target' in eventOrVal && eventOrVal.target && 'value' in eventOrVal.target) {
                    const v = eventOrVal.target.value;
                    return v !== '' && v !== null ? parseInt(v, 10) || null : null;
                }
                return null;
            }
            return eventOrVal !== '' ? parseInt(eventOrVal, 10) || null : null;
        },

        addAnggota(eventOrVal) {
            const numId = this.extractId(eventOrVal);
            if (!numId) return;
            if (this.selectedUtamaId && Number(numId) === Number(this.selectedUtamaId)) {
                return;
            }
            if (!this.selectedAnggotaIds.map(Number).includes(Number(numId))) {
                this.selectedAnggotaIds.push(Number(numId));
            }
            this.$nextTick(() => {
                const el = document.getElementById('ppns_anggota_select');
                if (el) {
                    el.value = '';
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        },

        removeAnggota(id) {
            const numId = Number(id);
            this.selectedAnggotaIds = this.selectedAnggotaIds.filter(i => Number(i) !== numId);
        },

        onUtamaChange(eventOrVal) {
            if (typeof eventOrVal === 'object' && eventOrVal !== null) {
                const isCustom = 'detail' in eventOrVal && !(eventOrVal.target instanceof HTMLSelectElement);
                if (isCustom && (eventOrVal.detail === undefined || typeof eventOrVal.detail === 'object')) {
                    return;
                }
            }
            const numId = this.extractId(eventOrVal);
            this.selectedUtamaId = numId;
            if (this.selectedUtamaId && this.selectedAnggotaIds.map(Number).includes(Number(this.selectedUtamaId))) {
                this.removeAnggota(this.selectedUtamaId);
            }
        },

        getAvailableAnggota() {
            const utamaId = this.selectedUtamaId ? Number(this.selectedUtamaId) : null;
            const anggotaSet = this.selectedAnggotaIds.map(Number);
            return this.ppnsList.filter(u => Number(u.id) !== utamaId && !anggotaSet.includes(Number(u.id)));
        },

        getSelectedAnggotaDetails() {
            const utamaId = this.selectedUtamaId ? Number(this.selectedUtamaId) : null;
            return this.selectedAnggotaIds
                .filter(id => Number(id) !== utamaId)
                .map(id => this.ppnsList.find(u => Number(u.id) === Number(id)))
                .filter(Boolean);
        }
    };
}

/**
 * Komponen Modal Alamat Tersangka (38 Provinsi Se-Indonesia)
 */
export function tersangkaAlamatModalForm(config = {}) {
    const initialAlamat = config.alamat || {};
    return {
        isAlamatModalOpen: false,
        alamat: {
            provinsi_kode: initialAlamat.provinsi_kode || '',
            provinsi_nama: initialAlamat.provinsi_nama || '',
            kabupaten_kode: initialAlamat.kabupaten_kode || '',
            kabupaten_nama: initialAlamat.kabupaten_nama || '',
            kecamatan_kode: initialAlamat.kecamatan_kode || '',
            kecamatan_nama: initialAlamat.kecamatan_nama || '',
            desa_kode: initialAlamat.desa_kode || '',
            desa_nama: initialAlamat.desa_nama || '',
            rt: initialAlamat.rt || '',
            rw: initialAlamat.rw || '',
            alamat_detail: initialAlamat.alamat_detail || '',
        },
        modalWilayah: {
            provinsi_kode: '',
            provinsi_nama: '',
            kabupaten_kode: '',
            kabupaten_nama: '',
            kecamatan_kode: '',
            kecamatan_nama: '',
            desa_kode: '',
            desa_nama: '',
        },
        prevProvinsiKode: '',
        prevKabupatenKode: '',
        prevKecamatanKode: '',
        errorKabupaten: '',
        errorKecamatan: '',
        errorDesa: '',
        modalRt: '',
        modalRw: '',
        modalAlamatDetail: '',
        listProvinsi: [],
        listKabupaten: [],
        listKecamatan: [],
        listDesa: [],
        provinsiAbortController: null,
        kabupatenAbortController: null,
        kecamatanAbortController: null,
        desaAbortController: null,
        isModalLoading: false,

        provinsiUrl: config.provinsiUrl || '/wilayah/provinsi',
        kabupatenUrlBase: config.kabupatenUrlBase || '/wilayah/kabupaten',
        kecamatanUrlBase: config.kecamatanUrlBase || '/wilayah/kecamatan',
        desaUrlBase: config.desaUrlBase || '/wilayah/desa',

        get alamatRingkasan() {
            if (!this.alamat.provinsi_kode && !this.alamat.alamat_detail) {
                return 'Belum diatur (klik untuk memilih alamat)';
            }
            const parts = [];
            if (this.alamat.alamat_detail) parts.push(this.alamat.alamat_detail);
            if (this.alamat.rt || this.alamat.rw) {
                parts.push('RT ' + (this.alamat.rt || '-') + ' / RW ' + (this.alamat.rw || '-'));
            }
            if (this.alamat.desa_nama) parts.push('Desa/Kel. ' + this.alamat.desa_nama);
            if (this.alamat.kecamatan_nama) parts.push('Kec. ' + this.alamat.kecamatan_nama);
            if (this.alamat.kabupaten_nama) parts.push(this.alamat.kabupaten_nama);
            if (this.alamat.provinsi_nama) parts.push(this.alamat.provinsi_nama);
            return parts.join(', ');
        },

        async openAlamatModal() {
            this.isModalLoading = true;
            this.modalWilayah.provinsi_kode = this.alamat.provinsi_kode ? String(this.alamat.provinsi_kode) : '';
            this.modalWilayah.provinsi_nama = this.alamat.provinsi_nama ? String(this.alamat.provinsi_nama) : '';
            this.modalWilayah.kabupaten_kode = this.alamat.kabupaten_kode ? String(this.alamat.kabupaten_kode) : '';
            this.modalWilayah.kabupaten_nama = this.alamat.kabupaten_nama ? String(this.alamat.kabupaten_nama) : '';
            this.modalWilayah.kecamatan_kode = this.alamat.kecamatan_kode ? String(this.alamat.kecamatan_kode) : '';
            this.modalWilayah.kecamatan_nama = this.alamat.kecamatan_nama ? String(this.alamat.kecamatan_nama) : '';
            this.modalWilayah.desa_kode = this.alamat.desa_kode ? String(this.alamat.desa_kode) : '';
            this.modalWilayah.desa_nama = this.alamat.desa_nama ? String(this.alamat.desa_nama) : '';
            this.prevProvinsiKode = this.modalWilayah.provinsi_kode;
            this.prevKabupatenKode = this.modalWilayah.kabupaten_kode;
            this.prevKecamatanKode = this.modalWilayah.kecamatan_kode;
            this.errorKabupaten = '';
            this.errorKecamatan = '';
            this.errorDesa = '';
            this.modalRt = this.alamat.rt || '';
            this.modalRw = this.alamat.rw || '';
            this.modalAlamatDetail = this.alamat.alamat_detail || '';

            this.isAlamatModalOpen = true;

            try {
                await this.fetchProvinsi();
                if (this.modalWilayah.provinsi_kode) {
                    await this.fetchKabupaten(this.modalWilayah.provinsi_kode, false);
                }
                if (this.modalWilayah.kabupaten_kode) {
                    await this.fetchKecamatan(this.modalWilayah.kabupaten_kode, false);
                }
                if (this.modalWilayah.kecamatan_kode) {
                    await this.fetchDesa(this.modalWilayah.kecamatan_kode, false);
                }
            } finally {
                this.$nextTick(() => {
                    this.isModalLoading = false;
                });
            }
        },

        async fetchProvinsi() {
            if (this.listProvinsi.length > 0) return;
            try {
                const res = await fetch(this.provinsiUrl);
                const json = await res.json();
                this.listProvinsi = json.data || [];
            } catch (e) {
                this.listProvinsi = [];
            }
        },

        async fetchKabupaten(provKode, autoOpen = false) {
            this.errorKabupaten = '';
            if (!provKode) {
                this.listKabupaten = [];
                this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kabupaten_kode', loading: false });
                return;
            }
            if (this.kabupatenAbortController) {
                this.kabupatenAbortController.abort();
            }
            this.kabupatenAbortController = new AbortController();
            this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kabupaten_kode', loading: true });
            try {
                const res = await fetch(`${this.kabupatenUrlBase}/${provKode}`, {
                    signal: this.kabupatenAbortController.signal
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                if (String(this.modalWilayah.provinsi_kode) === String(provKode)) {
                    this.listKabupaten = json.data || [];
                    if (autoOpen && this.listKabupaten.length > 0) {
                        this.$nextTick(() => {
                            this.$dispatch('searchable-select-open', { id: 'modal_tersangka_kabupaten_kode' });
                        });
                    }
                }
            } catch (e) {
                if (e.name !== 'AbortError') {
                    this.listKabupaten = [];
                    this.errorKabupaten = 'Gagal memuat daftar kabupaten/kota.';
                }
            } finally {
                if (String(this.modalWilayah.provinsi_kode) === String(provKode)) {
                    this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kabupaten_kode', loading: false });
                }
            }
        },

        async fetchKecamatan(kabKode, autoOpen = false) {
            this.errorKecamatan = '';
            if (!kabKode) {
                this.listKecamatan = [];
                this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kecamatan_kode', loading: false });
                return;
            }
            if (this.kecamatanAbortController) {
                this.kecamatanAbortController.abort();
            }
            this.kecamatanAbortController = new AbortController();
            this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kecamatan_kode', loading: true });
            try {
                const res = await fetch(`${this.kecamatanUrlBase}/${kabKode}`, {
                    signal: this.kecamatanAbortController.signal
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                if (String(this.modalWilayah.kabupaten_kode) === String(kabKode)) {
                    this.listKecamatan = json.data || [];
                    if (autoOpen && this.listKecamatan.length > 0) {
                        this.$nextTick(() => {
                            this.$dispatch('searchable-select-open', { id: 'modal_tersangka_kecamatan_kode' });
                        });
                    }
                }
            } catch (e) {
                if (e.name !== 'AbortError') {
                    this.listKecamatan = [];
                    this.errorKecamatan = 'Gagal memuat daftar kecamatan.';
                }
            } finally {
                if (String(this.modalWilayah.kabupaten_kode) === String(kabKode)) {
                    this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_kecamatan_kode', loading: false });
                }
            }
        },

        async fetchDesa(kecKode, autoOpen = false) {
            this.errorDesa = '';
            if (!kecKode) {
                this.listDesa = [];
                this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_desa_kode', loading: false });
                return;
            }
            if (this.desaAbortController) {
                this.desaAbortController.abort();
            }
            this.desaAbortController = new AbortController();
            this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_desa_kode', loading: true });
            try {
                const res = await fetch(`${this.desaUrlBase}/${kecKode}`, {
                    signal: this.desaAbortController.signal
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                if (String(this.modalWilayah.kecamatan_kode) === String(kecKode)) {
                    this.listDesa = json.data || [];
                    if (autoOpen && this.listDesa.length > 0) {
                        this.$nextTick(() => {
                            this.$dispatch('searchable-select-open', { id: 'modal_tersangka_desa_kode' });
                        });
                    }
                }
            } catch (e) {
                if (e.name !== 'AbortError') {
                    this.listDesa = [];
                    this.errorDesa = 'Gagal memuat daftar desa/kelurahan.';
                }
            } finally {
                if (String(this.modalWilayah.kecamatan_kode) === String(kecKode)) {
                    this.$dispatch('searchable-select-loading', { id: 'modal_tersangka_desa_kode', loading: false });
                }
            }
        },

        retryFetchKabupaten() {
            if (this.modalWilayah.provinsi_kode) {
                this.fetchKabupaten(this.modalWilayah.provinsi_kode, true);
            }
        },

        retryFetchKecamatan() {
            if (this.modalWilayah.kabupaten_kode) {
                this.fetchKecamatan(this.modalWilayah.kabupaten_kode, true);
            }
        },

        retryFetchDesa() {
            if (this.modalWilayah.kecamatan_kode) {
                this.fetchDesa(this.modalWilayah.kecamatan_kode, true);
            }
        },

        extractEventValue(event, fallback = '') {
            if (event === undefined || event === null) return fallback;
            if (typeof event === 'object') {
                if ('detail' in event && event.detail !== undefined && event.detail !== null) {
                    const d = event.detail;
                    if (typeof d === 'object') {
                        if (d && 'value' in d) return String(d.value);
                        return fallback;
                    }
                    return String(d);
                }
                if ('target' in event && event.target && 'value' in event.target && event.target.value !== undefined && event.target.value !== null) {
                    return String(event.target.value);
                }
                return fallback;
            }
            return String(event);
        },

        onProvinsiChange(event) {
            if (this.isModalLoading) return;
            const val = this.extractEventValue(event, this.modalWilayah.provinsi_kode);
            if (val === undefined || val === null) return;
            const newCode = String(val);
            if (newCode === this.prevProvinsiKode) return;

            const shouldAutoOpen = !this.modalWilayah.kabupaten_kode;
            this.prevProvinsiKode = newCode;
            this.modalWilayah.provinsi_kode = newCode;
            const found = this.listProvinsi.find(p => String(p.code) === newCode);
            this.modalWilayah.provinsi_nama = found ? (found.name || found.nama) : '';

            this.modalWilayah.kabupaten_kode = '';
            this.modalWilayah.kabupaten_nama = '';
            this.prevKabupatenKode = '';
            this.modalWilayah.kecamatan_kode = '';
            this.modalWilayah.kecamatan_nama = '';
            this.prevKecamatanKode = '';
            this.modalWilayah.desa_kode = '';
            this.modalWilayah.desa_nama = '';
            this.listKabupaten = [];
            this.listKecamatan = [];
            this.listDesa = [];

            if (newCode) {
                this.fetchKabupaten(newCode, shouldAutoOpen);
            }
        },

        onKabupatenChange(event) {
            if (this.isModalLoading) return;
            const val = this.extractEventValue(event, this.modalWilayah.kabupaten_kode);
            if (val === undefined || val === null) return;
            const newCode = String(val);
            if (newCode === this.prevKabupatenKode) return;

            const shouldAutoOpen = !this.modalWilayah.kecamatan_kode;
            this.prevKabupatenKode = newCode;
            this.modalWilayah.kabupaten_kode = newCode;
            const found = this.listKabupaten.find(k => String(k.code) === newCode);
            this.modalWilayah.kabupaten_nama = found ? (found.name || found.nama) : '';

            this.modalWilayah.kecamatan_kode = '';
            this.modalWilayah.kecamatan_nama = '';
            this.prevKecamatanKode = '';
            this.modalWilayah.desa_kode = '';
            this.modalWilayah.desa_nama = '';
            this.listKecamatan = [];
            this.listDesa = [];

            if (newCode) {
                this.fetchKecamatan(newCode, shouldAutoOpen);
            }
        },

        onKecamatanChange(event) {
            if (this.isModalLoading) return;
            const val = this.extractEventValue(event, this.modalWilayah.kecamatan_kode);
            if (val === undefined || val === null) return;
            const newCode = String(val);
            if (newCode === this.prevKecamatanKode) return;

            const shouldAutoOpen = !this.modalWilayah.desa_kode;
            this.prevKecamatanKode = newCode;
            this.modalWilayah.kecamatan_kode = newCode;
            const found = this.listKecamatan.find(k => String(k.code) === newCode);
            this.modalWilayah.kecamatan_nama = found ? (found.name || found.nama) : '';

            this.modalWilayah.desa_kode = '';
            this.modalWilayah.desa_nama = '';
            this.listDesa = [];

            if (newCode) {
                this.fetchDesa(newCode, shouldAutoOpen);
            }
        },

        onDesaChange(event) {
            if (this.isModalLoading) return;
            const val = this.extractEventValue(event, this.modalWilayah.desa_kode);
            if (val !== undefined && val !== null) {
                this.modalWilayah.desa_kode = String(val);
                const found = this.listDesa.find(d => String(d.code) === String(val));
                this.modalWilayah.desa_nama = found ? (found.name || found.nama) : '';
            }
        },

        applyAlamat() {
            this.alamat.provinsi_kode = this.modalWilayah.provinsi_kode;
            this.alamat.provinsi_nama = this.modalWilayah.provinsi_nama;
            this.alamat.kabupaten_kode = this.modalWilayah.kabupaten_kode;
            this.alamat.kabupaten_nama = this.modalWilayah.kabupaten_nama;
            this.alamat.kecamatan_kode = this.modalWilayah.kecamatan_kode;
            this.alamat.kecamatan_nama = this.modalWilayah.kecamatan_nama;
            this.alamat.desa_kode = this.modalWilayah.desa_kode;
            this.alamat.desa_nama = this.modalWilayah.desa_nama;
            this.alamat.rt = this.modalRt;
            this.alamat.rw = this.modalRw;
            this.alamat.alamat_detail = this.modalAlamatDetail;

            this.isAlamatModalOpen = false;
        }
    };
}

/**
 * Komponen Pemilihan Pasal yang Disangkakan (Pasal Berlapis)
 */
export function pasalPerkaraForm(config = {}) {
    return {
        pasalList: config.pasalList || [],
        selectedPasalIds: (config.selectedPasalIds || []).map(Number),

        addPasal(id) {
            if (!id) return;
            const numId = parseInt(id, 10);
            if (!this.selectedPasalIds.includes(numId)) {
                this.selectedPasalIds.push(numId);
            }
            this.$nextTick(() => {
                const el = document.getElementById('pasal_select');
                if (el) {
                    el.value = '';
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        },

        removePasal(id) {
            const numId = parseInt(id, 10);
            this.selectedPasalIds = this.selectedPasalIds.filter(i => i !== numId);
        },

        getAvailablePasal() {
            return this.pasalList.filter(p => !this.selectedPasalIds.includes(Number(p.id)));
        },

        getSelectedPasalDetails() {
            return this.selectedPasalIds
                .map(id => this.pasalList.find(p => Number(p.id) === Number(id)))
                .filter(Boolean);
        }
    };
}
