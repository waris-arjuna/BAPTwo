# TASKS — BAPTwo

Daftar pekerjaan aktif. Agent wajib memperbarui file ini setelah menyelesaikan task (lihat `AGENTS.md` bagian 8).

**Status:** `[ ]` belum · `[~]` dikerjakan · `[x]` selesai · `[!]` terblokir (tulis alasan)

**Format task:**

```
- [ ] T-XXX — Judul (ref: F-xx)
  - AC: kriteria terima singkat
  - Catatan: opsional
```

Satu task = satu unit kerja kecil yang bisa diuji. Acceptance Criteria utama tetap di `PRD.md`.

---

## Sedang Dikerjakan

- [x] T-070 — In-App PDF Preview Modal Terpadu (ref: D-047)
  - AC: Komponen global `<x-common.pdf-preview-modal />` dipasang di `layouts/app.blade.php`; mendukung iframe preview, loading spinner, fallback informatif, tombol unduh, tombol buka tab baru, dan tombol tutup; link dokumen di `perkara/show.blade.php`, `tahapan/show.blade.php`, `laporan-kejadian/show.blade.php`, `sprindik/show.blade.php`, dan tabel-tabel tahapan mengadopsi atribut `data-pdf-preview`; progressive enhancement berfungsi (klik kiri modal in-app, Ctrl+klik buka tab baru); automated tests lulus 100% dan Pint clean.

## Fondasi

- [x] T-001 — Kerangka `ARCHITECTURE.md`, `TASKS.md`, `DECISIONS.md`; tulis ulang `README.md`; buat `lang/id.json`
- [x] T-002 — Set `.env.example`: `APP_NAME=BAPTwo`, `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`, `DB_DATABASE=baptwo` (ref: D-001, D-004)
- [x] T-003 — Bersihkan RTL & language switcher warisan TailAdmin (ref: D-003)
  - AC: menu Language hilang dari user dropdown; `LocaleController`/route switch dihapus atau dikunci ke `id`; atribut `dir` tetap `ltr`; test lulus.
- [x] T-004 — Bersihkan halaman demo TailAdmin yang tidak dipakai (ecommerce, chart demo, calendar, dsb.) dari route & sidebar
  - AC: route demo dihapus dari `routes/web.php`; file demo views dihapus; sidebar tetap khusus menu BAPTwo; test & pint lulus.
- [x] T-005 — Lengkapi `ARCHITECTURE.md` (skema data, enum, layanan domain)
  - AC: ERD & spesifikasi tabel lengkap; enum domain ditetapkan; alur tahap, penguncian & DPO gate didefinisikan; RBAC, scheduler & strategi wilayah terdokumentasi.
- [x] T-006 — `composer test` Vite manifest safeguard
  - AC: test lulus tanpa harus `npm run build` dulu (`$this->withoutVite()` di `tests/TestCase.php`).
- [x] T-007 — Folder `tailadmin-laravel-main/` (salinan template, tidak di-track git) dikecualikan via `pint.json`
  - AC: folder diabaikan oleh Pint via `pint.json` exclude; `php vendor/bin/pint --test` lulus.
- [x] T-008 — Rapikan 8 file template yang gagal Pint: `app/Helpers/MenuHelper.php`, `DashboardController`, `LocaleController`, `SidebarController`, `SetLocale`, `ecommerce/MonthlyTarget`, `ui/Modal`, `routes/web.php`
  - AC: `php vendor/bin/pint --test` lulus.
- [x] T-017 — Standar toolbar filter & live filter pada `<x-common.table-filter>` (ref: F-39, D-011)
  - AC: teks tombol "Filter" (key `Filter` di `lang/id.json`); select & input tanggal auto-submit via `@change` terdelegasi (termasuk select dari `$slot`); kolom pencarian auto-submit `@input.debounce.500ms`; tombol Filter tetap sebagai fallback tanpa JS; Blade compile & test lulus.
- [x] T-018 — Filter tabel AJAX tanpa reload halaman (ref: F-39, D-011)
  - AC: Live filter via Alpine `tableFilter()` component di seluruh 8 tabel (Laporan Kejadian, Sprindik, SPDP, Tahap 1, P-21, Dashboard Progress Perkara, Master Data, Pengaturan); header request `X-Filter-Partial: 1` mengembalikan partial HTML tabel; URL tersinkronisasi via `history.pushState` dan navigasi tombol back/forward browser tetap bekerja; fallback normal GET tanpa JS; test & pint lulus.
- [x] T-019 — Filter tanggal menggunakan Datepicker Flatpickr (ref: F-39, D-011)
  - AC: `<x-common.table-filter>` menggunakan `<x-form.date-picker>` berbasis Flatpickr dengan lokalisasi Bahasa Indonesia (`id`), icon kalender Flaticon, event bubbling `change` terintegrasi langsung dengan live filter tanpa reload, styling light & dark mode rapi, dan reset otomatis Flatpickr instance; test & pint lulus.
- [x] T-020A — Penomoran seragam kolom "No" pada Master Data & Manajemen Pengguna (ref: D-013)
  - AC: Kolom nomor urut `No` (`{{ $loop->iteration }}`) ditambahkan ke tabel Master Data dan Manajemen Pengguna; colspan baris kosong disesuaikan; tampilan konsisten dengan seluruh tabel perkara di BAPTwo; test & pint lulus.

## Auth & RBAC (F-38)

- [x] T-010 — Autentikasi login/logout (auth Laravel bawaan)
  - AC: login memakai email, password, `is_active = true`, & `MathCaptcha`; logout invalidate session; guest redirect ke `/signin`; user dropdown tampil nama, email, & role aktif.
- [x] T-014 — Captcha pengurangan internal (SVG dinamis, filter distorsi lekukan & one-time use) (ref: D-006)
  - AC: soal pengurangan acak ($A > B$, hasil $> 0$); SVG filter distorsi lekukan (`feTurbulence`/`feDisplacementMap`), multi-font acak, rotasi/skew per karakter, noise curves; tombol reload AJAX; validasi session sekali pakai; Blade component `<x-form.math-captcha />`; test & pint lulus.
- [x] T-011 — Role Penyidik/Pimpinan/Administrator + seeder akun dev (ref: D-005, D-008, D-009)
  - AC: migrasi tabel singular `user` (+ `alamat`, `jabatan`, `bidang`, `panggol`), enum `UserRole`, model `User` (`protected $table = 'user'`, `SoftDeletes`), dan seeder 3 akun dev (`admin@baptwo.test`, `pimpinan@baptwo.test`, `penyidik@baptwo.test`).
- [x] T-012 — Policy dasar & uji akses tiap role (PRD bagian Hak Akses)
  - AC: `UserPolicy` & Gate RBAC (`view-cases`, `create-cases`, `edit-draft`, `soft-delete`, `hard-delete` selalu false, `manage-users`); Pimpinan diblokir di route tambah; tahap final terkunci mutlak termasuk untuk Admin.
- [x] T-013 — Manajemen user oleh Administrator (UC-05)
  - AC: `UserController` + `UserService` + `StoreUserRequest` / `UpdateUserRequest` di `/pengaturan` (lihat, cari/filter, tambah, ubah, soft delete, restore, cegah hapus diri sendiri, tanpa hard delete).
- [x] T-015 — Halaman Profil Pengguna (`/profile`) & Proxy Wilayah (ref: F-36, F-37, F-38)
  - AC: (1) Card Foto User + tombol ganti foto profil; (2) Card Informasi Akun + popup "Update Email / Password" dengan syarat min 8 karakter, huruf besar/kecil + angka, simbol, serta input password lama, password baru, & konfirmasi; (3) Card Data Diri (Nama, Alamat, Tempat/Tanggal Lahir, Jenis Kelamin, Status Pernikahan, No.Telp/WA) di mana hanya No.Telp/WA & Alamat yang bisa diedit; (4) Card Informasi Kepegawaian read-only (NIP, Jabatan, Bidang, Pangkat/Golongan format `Pembina (IV/a)`, Hak Akses); validasi & feedback notification lengkap.
- [x] T-016 — Modul Master Data (6 Submenu) & Integrasi ke Manajemen Pengguna (ref: F-38, D-009)
  - AC: Submenu sidebar Master Data (`Jabatan`, `Bidang`, `Pangkat & Golongan`, `Pasal / UU`, `Instansi Hukum`, `Jaksa Penuntut`); semua role bisa lihat/cari; `Administrator` & `Penyidik` bisa tambah/ubah/soft-delete/pulihkan via modal pop-up; dropdown Jabatan, Bidang, & Pangkat/Golongan terhubung ke modal Tambah/Ubah Pengguna di `/pengaturan`; test & pint lulus.

## Data Perkara (pra-SPDP)

- [x] T-020 — Perkara + Laporan Kejadian + Surat Perintah Penyidikan + Sistem 3 Tombol (Draft vs Simpan & Lanjut) (F-01, F-05, F-06, F-14–F-19, D-007, D-008)
  - AC: Migrasi tabel singular `perkara` (dengan `tahap_laporan_status`, `laporan_locked_at`, `laporan_finalized_by`, `tahap_sprindik_status`, `sprindik_locked_at`, `sprindik_finalized_by`), `dokumen`, `audit_log`; Model Eloquent (`Perkara`, `Dokumen`, `AuditLog`); `PerkaraService` + `AuditTrailService`; `LaporanKejadianController` & `SprindikController` dengan 3 tombol aksi (`Batal`, `Simpan Draft`, `Simpan & Lanjut Tahap Berikutnya`), penguncian permanen tahap yang sudah difinalkan (termasuk untuk Admin), visual indicator `Draft` vs `Final & Terkunci`, dan Card Referensi Tahapan Sebelumnya pada Sprindik; 12 feature tests baru lulus; Pint lulus.
- [x] T-021 — Tersangka; 1 Tersangka = 1 Berkas Perkara (F-02, F-03)
  - AC: 1 Tersangka menghasilkan tepat 1 Berkas Perkara terpisah; tabel singular `tersangka` dan `berkas_perkara` dengan foreign key unik `tersangka_id`; pembuatan dinamis via Alpine.js di UI; relasi Eloquent lengkap; test lulus.
- [x] T-021A — Modal Alamat Tersangka (38 Provinsi Seluruh Indonesia) & Datepicker Tanggal Lahir (F-03, D-008, D-009)
  - AC: Modal interaktif pemilihan alamat tersangka (Provinsi, Kab/Kota, Kecamatan, Desa/Kelurahan via `<x-form.searchable-select>`, RT/RW, Detail Jalan) tersimpan di tabel `alamat` & direlasikan ke `tersangka.alamat_id`; trigger box ringkasan alamat berikon `fi fi-rr-marker`; input tanggal lahir menggunakan `<x-form.date-picker>` dengan batasan `maxDate: today`; alamat lengkap tampil di detail perkara (`show.blade.php`); 7 test feature `AlamatTersangkaTest` lulus; Pint & 162 test suite lulus.
- [x] T-022 — Pasal berlapis per Berkas (F-04)
  - AC: Satu berkas perkara dapat memiliki pasal berlapis (1..N pasal dari master data `pasal`); tabel pivot `berkas_pasal`; multi-select/checkbox pasal di UI form berkas perkara; test lulus.
- [x] T-023 — TKP + PPNS utama/anggota (F-07, F-08)
  - AC: Informasi TKP (klasifikasi retailer/distributor, legalitas legal/ilegal, modus online/offline, alamat berjenjang terintegrasi proxy wilayah API Kode Wilayah) tersimpan di tabel `tkp` & `alamat`; Tim Penyidik PPNS (1 PPNS Utama + 0..N PPNS Anggota) tersimpan di pivot `perkara_ppns` dengan peran; test lulus.
- [x] T-023A — Pemilihan Tim Penyidik PPNS Anggota via combo box dinamis + kartu detail terpilih (F-08, D-020)
  - AC: Dropdown combo box untuk penambahan PPNS Anggota berulang kali; kartu kecil detail penyidik terpilih (Nama, NIP) dengan tombol pembatalan `(x)`; eksklusi otomatis dari combo box untuk PPNS Utama dan anggota yang sudah terpilih; hidden input kompatibel 100% backend; test & pint lulus.
- [x] T-023B — Pembersihan Istilah Internal di UI & Standardisasi Catatan Format Penomoran Dokumen (D-021)
  - AC: Seluruh istilah developer (PRD, PRD F-33, Aturan domain) dihapus dari UI; catatan Note format penomoran ditambahkan di bawah kolom input Nomor Berkas, Nomor Laporan Kejadian, Nomor Sprindik, Nomor Surat Tahapan, dan Nomor SPDP; test & pint lulus.
- [x] T-025 — Nomor Berkas manual tanpa validasi pola (F-33)
  - AC: Input `nomor_berkas` diinput manual oleh user tanpa pembatasan regex/format tertentu (bebas karakter format); tersimpan di `berkas_perkara.nomor_berkas`; test lulus.
- [x] T-026 — Menu Navigasi Data Perkara di Sidebar (Daftar Perkara & Progress Perkara) + Pop-up Tambah Perkara (F-01, F-39, F-40, D-014)
  - AC: Menu dropdown "Data Perkara" di Sidebar grup Perkara dengan sub-menu "Daftar Perkara" (`/data-perkara`) dan "Progress Perkara" (`/progress-perkara`); modal pop-up pemilihan Sprindik final; live filter AJAX `<x-common.table-filter>`; 4 feature tests baru lulus; Pint lulus.

## Tahapan & Progress

- [x] T-031D — Integrasi Sub-Tahap (P-16/P-17 ke SPDP & P-18/P-19 ke Tahap 1) dan Migrasi Halaman Tahapan Lanjutan ke Data Riil (`/tahap-1`, `/p21`, `/tahap-2`, `/putusan-perkara`) (D-024)
- [x] T-032 — Progress berbobot & status proses otomatis (F-29, F-30)
- [x] T-033 — Trigger Selesai dari TAHAP II (F-22)
- [x] T-034 — Scheduler Tidak Selesai 1 Januari 00:00 (F-23, D-031)
- [x] T-035 — SLA 1 bulan kalender + batas tahun (PRD bagian SLA, D-031)

## DPO, SP3, Dokumen, Audit

- [x] T-040 — DPO aktif/nonaktif + gate Tahap II di server & UI (F-24–F-27, F-35, D-025)
- [x] T-041 — Upload / Terbitkan SP3 (F-28, D-030)
- [x] T-042 — Dokumen terpusat per Berkas & tahap + Seeding 10 Data Dummy Beragam (F-32)
- [x] T-043 — Audit trail / case history (F-31, D-025)
- [x] T-044 — Soft delete + halaman Sampah untuk pemulihan (F-44, D-032)

## Monitoring & Laporan

- [x] T-050 — Pencarian Perkara (`/pencarian-perkara`) & Seluruh Perkara (`/seluruh-perkara` + card statistik) + struktur menu Role Pimpinan/Umum (F-38, F-39, D-033)
- [x] T-051 — Dashboard monitoring: Tabel Progress Perkara auto-filter (progress terendah & tanggal perkara tertua, maks 5 baris), tombol "Lihat Semua" (`/progress-perkara`), & interaksi double-click ID Perkara untuk modal detail lengkap tanpa kolom aksi (F-40, F-42, F-43)
- [x] T-052 — Dashboard Potensi Terlimpah (F-41, D-034)
- [x] T-053 — Export Excel 44 Kolom (.xlsx) terintegrasi PhpSpreadsheet (F-45, ref: D-035, D-039, D-041)
  - Catatan: diselesaikan menggunakan `phpoffice/phpspreadsheet` murni untuk format 44 kolom, 4 baris header, format tanggal, filter 3 tab, dan akses role Penyidik & Admin.

## Selesai

- [x] T-001 — 2026-10-07
- [x] T-002 — 2026-10-07
- [x] T-003 — 2026-10-07
- [x] T-004 — 2026-10-12
- [x] T-005 — 2026-10-12
- [x] T-006 — 2026-10-12
- [x] T-007 — 2026-10-12
- [x] T-008 — 2026-10-07
- [x] T-010 — 2026-10-12
- [x] T-011 — 2026-10-12
- [x] T-012 — 2026-10-12
- [x] T-013 — 2026-10-12
- [x] T-014 — 2026-10-07
- [x] T-015 — 2026-10-12
- [x] T-016 — 2026-10-13
- [x] T-017 — 2026-10-13
- [x] T-018 — 2026-10-13
- [x] T-019 — 2026-10-13
- [x] T-020A — 2026-10-13
- [x] T-020 — 2026-10-13
- [x] T-021 — 2026-10-13
- [x] T-022 — 2026-10-13
- [x] T-023 — 2026-10-13
- [x] T-023A — 2026-10-14 Pemilihan Tim Penyidik PPNS Anggota via combo box dinamis + kartu detail terpilih (D-020)
- [x] T-023B — 2026-10-14 Pembersihan Istilah Internal di UI & Standardisasi Catatan Format Penomoran Dokumen (D-021)
- [x] T-024 — 2026-10-12
- [x] T-025 — 2026-10-13
- [x] T-026 — 2026-10-13
- [x] T-027 — 2026-10-13 Sistem Wilayah Hibrida: penanganan SSL cURL API `api.kodewilayah.web.id` dan fallback JSON lokal lengkap 38 Provinsi & 514 Kabupaten/Kota (D-015, F-37)
- [x] T-030 — 2026-10-14 Enum tahapan + aturan urutan, maksimal 1x, opsional (F-10–F-13)
- [x] T-031 — 2026-10-14 Tiga aksi Batal/Draft/Simpan & Lanjut + penguncian permanen di DB & UI + Card Referensi tahapan sebelumnya + visual indicator Completed/Locked (F-14–F-19)
- [x] T-031A — 2026-10-14 Halaman SPDP Riil + Sub-Tahap Opsional (P-16, P-17P) + Flatpickr Datepicker + Modal Berkas Siap SPDP (F-10, F-11, F-39, D-018)
- [x] T-031B — 2026-10-14 Validasi Simpan Draft Mewajibkan Nomor/Identitas Utama & Melarang Draft Kosong (D-022)
- [x] T-031C — 2026-10-14 Standardisasi Notifikasi Flash Message: Timer Auto-Dismiss 10 Detik + Tombol Tutup (x) (D-023)
- [x] T-031D — 2026-10-14 Integrasi Sub-Tahap (P-16/P-17 ke SPDP & P-18/P-19 ke Tahap 1) dan Migrasi Halaman Tahapan Lanjutan ke Data Riil (D-024)
- [x] T-031E — 2026-10-14 Larangan Pemilihan Ulang Data Antar-Tahap (Relasi 1-ke-1 Konsumsi Data & Validasi Ketat Server-Side) (D-026)
- [x] T-032 — 2026-10-14 Progress berbobot & status proses otomatis (F-29, F-30)
- [x] T-033 — 2026-10-14 Trigger Selesai dari TAHAP II (F-22)
- [x] T-040 — 2026-10-14 DPO aktif/nonaktif + gate Tahap II di server & UI (F-24–F-27, F-35, D-025)
- [x] T-041 — 2026-10-14 Penerbitan SP3 (Surat Perintah Penghentian Penyidikan) Manual pada Berkas Perkara + Modal Upload PDF + Penguncian Status Tidak Selesai + Audit Trail (F-28, D-030)
- [x] T-042 — 2026-10-14 Seeding 10 Data Dummy Beragam untuk Seluruh Alur & Tahapan Kasus BAPTwo di 10 Wilayah Kalsel (F-32)
- [x] T-043 — 2026-10-14 Audit trail / case history kronologis dengan timeline modern di Detail Perkara (F-31, D-025)
- [x] T-051 — 2026-10-14 Dashboard monitoring riil: Tabel 5 berkas prioritas + Donut Chart + 5 Card Statistik + Tombol "Lihat Semua" + Modal Quick-View read-only (F-40, F-42, F-43, D-027)
- [x] T-034 — 2026-10-17 Scheduler Tidak Selesai Otomatis 1 Januari 00:00 (F-23, D-031)
- [x] T-035 — 2026-10-17 SLA 1 bulan kalender + batas tahun mutlak + visual indicator badge (PRD bagian SLA, D-031)
- [x] T-044 — 2026-10-17 Halaman Sampah (Trash) & Pemulihan Data Soft Delete Tanpa Hard Delete (PRD F-44, D-032)
- [x] T-050 — 2026-10-17 Halaman Seluruh Perkara (dengan 5 Card Statistik), Hub Pencarian Cepat Multi-Kriteria, dan Pembaruan Struktur Navigasi Menu untuk Role Pimpinan & Umum (PRD F-38, F-39, D-033)
- [x] T-052 — 2026-10-17 Dashboard Monitoring Potensi Terlimpah & Peringatan Dini Batas Waktu Akhir Tahun (PRD F-41, D-034)
- [x] T-060 — 2026-10-17 Paket Revisi Manajemen Perkara 22 Poin Lengkap (Kelompok A, B, C, D, E, F, G; Seluruh Poin 1–22 Selesai Penuh) (ref: D-035)
  - [x] Kelompok A (Poin 1, 16): Hapus tombol kembali ke dashboard, Captcha TTL 30 detik + visual alert, Inaktivitas sesi 30 menit auto-redirect, Hapus Remember Me.
  - [x] Kelompok B (Poin 7, 20): RBAC Master Data (Pemimpin diblokir, Penyidik hanya Pasal & Instansi Hukum, Admin penuh); Blokir menu & akses Progress Perkara bagi Penyidik.
  - [x] Kelompok C (Poin 3, 4, 8, 9, 17): Radio button Legalitas & Modus Operandi; Provinsi Kalsel read-only; Pasal combobox identik PPNS Anggota; Jaksa input bebas; NIK tepat 16 digit.
  - [x] Kelompok D (Poin 2, 5, 11, 13, 14, 15, 18, 21, 22): Dokumen PDF wajib & max 5 MB; Penggabungan 1 Perkara = 1 Berkas Perkara dengan nomor LK; Hapus hapus perkara; Penguncian satu kali pakai LK & Sprindik; SP3 terminal teks manual & status Tidak Selesai; Tombol tahap berikutnya disembunyikan jika tuntas/SP3.
  - [x] Kelompok E (Poin 6, 12, 20): Tabel dashboard di paling bawah; Horizontal scroll penuh tabel; Kolom "Tahap Saat Ini (% progress)".
  - [x] Kelompok G (Poin 19): Modal & alert penanganan error terpadu tanpa silent fail (422, 419, 403, 404, 500, timeout, offline).
  - [x] Kelompok F (Poin 10): Ekspor Rekap Perkara 44 Kolom (.xlsx) terurut tanggal LK, 4 baris header, sel tanggal Excel dd-mmm-yy, nilai kosong -, wrap text multi-value, border, freeze pane A5, modal 3 tab (Tahun, Bulan, Rentang Tanggal tiket pesawat), hak akses khusus Admin & Penyidik.
- [x] T-061 — 2026-10-19 Standarisasi 20 Data Dummy Perkara & Master Data Realistis (ref: D-038)
  - AC: `DatabaseSeeder.php` bersih dari mock perkara lama agar baseline user tetap 3; `DummyDataSeeder.php` menghasilkan tepat 20 perkara beragam (Pra-SPDP s.d. Putusan Pengadilan, DPO Aktif & Dicabut, SP3 terminal, Warning SLA) di seluruh 13 Kabupaten/Kota Kalsel; 1 Perkara = 1 Berkas Perkara dengan nomor LK identik; NIK 16 digit; format SP3 keterangan; seluruh dokumen sampel PDF valid; master data (Pasal: 20, Instansi Hukum: 20, Jaksa Penuntut: 20); pembersihan idempoten bebas benturan constraint unik; `composer test` (161 tests, 1.650 assertions) & `php vendor/bin/pint --test` 100% lulus.
- [x] T-062 — 2026-10-19 Kolom Nama Sarana Alamat TKP & Standardisasi Alamat Lengkap Ekspor Excel (ref: D-039)
  - AC: Kolom `nama` (VARCHAR 150 nullable) ditambahkan pada tabel `alamat`; accessor `alamat_lengkap` pada model `Alamat` mengembalikan format utuh; input `tkp_nama` ditambahkan pada form & detail perkara; ekspor Excel Kolom L (Alamat Tersangka) & P (Alamat TKP) memuat alamat lengkap; Kolom O (Nama TKP) mengambil dari `alamat.nama` (fallback `-`); seeder dummy 20 perkara terisi nama TKP; `composer test` (162 tests) & `php vendor/bin/pint --test` 100% lulus.
- [x] T-063 — 2026-10-19 Perbaikan Bug Layout Zoom 125% & Dynamic Offset Sidebar (ref: D-040)
  - AC: Judul halaman dan kolom NO tidak tertutup sidebar; tidak ada strip putih di tepi kanan; container konten menggunakan dynamic padding terikat `$store.sidebar`; tabel berada di dalam `overflow-x-auto` mandiri tanpa overflow halaman global (`document.documentElement.scrollWidth <= document.documentElement.clientWidth`); logo dan ikon atas sidebar terselaraskan rapi.
- [x] T-064 — 2026-10-19 Pendaftaran Dependensi PhpSpreadsheet & Konfigurasi Platform Composer (ref: D-041)
  - AC: `phpoffice/phpspreadsheet` (^5.10) terdaftar permanen di `composer.json` & `composer.lock`; ekstensi `zip` diaktifkan di `php.ini` Laragon; `"platform-check": false` ditambahkan agar `php artisan test` dan perintah CLI dapat dieksekusi di seluruh terminal tanpa benturan arsitektur 32/64-bit; 162 automated test lulus & linting Pint 100% passed.
- [x] T-065 — 2026-10-19 Pengesahan Aturan Anti AI-Slop & Standar Craftsmanship UI/Copywriting (ref: D-042)
  - AC: Aturan eksplisit anti AI-slop dimasukkan ke `AGENTS.md` (bagian 6 & 11) dan `DECISIONS.md` (D-042); melarang em dash (`—`) di teks UI pengguna, melarang AI buzzwords, melarang dead controls, melarang desain happy path only (wajib 3 state), melarang visual generik AI slop, dan menegakkan kontras rasio WCAG AA min 4.5:1; pointer resmi `<!-- antislop:start -->` terpasang; tes dan linter 100% lulus.
- [x] T-066 — 2026-10-19 Kewajiban Penyelarasan Rencana via Grill-Me Sebelum Eksekusi Kode (ref: D-043)
  - AC: Larangan mutlak eksekusi kode langsung tanpa penyelarasan ditambahkan ke `AGENTS.md` (bagian 6 & 8); aturan D-043 dicatat di `DECISIONS.md`; agent wajib mewawancarai pengguna dan menyamakan persepsi via `/grill-me` (dan tool `ask_question`) sebelum menyentuh kode/skema/UI; dokumentasi terupdate rapi.
- [x] T-067 — 2026-10-19 Sinkronisasi PRD.md dengan Implementasi Mutakhir D-035 (ref: D-035)
  - AC: Seluruh selisih aturan di `PRD.md` (1 Perkara = 1 Berkas nomor LK, SP3 terminal picu status Tidak Selesai, peniadaan fitur hapus perkara, RBAC master data & progress perkara, PDF 5 MB, ekspor Excel 44 kolom) disinkronkan resmi atas persetujuan pengguna; status dokumen 100% konsisten.
- [x] T-068 — 2026-10-20 Pemisahan Modal Kompleks sebagai Domain Feature Partial (ref: D-045)
  - AC: Modal Alamat Tersangka diekstrak ke `resources/views/pages/perkara/_modal-alamat-tersangka.blade.php`; Modal Update Akun & Modal Edit Data Diri diekstrak ke `resources/views/pages/profile/_modal-update-akun.blade.php` & `_modal-edit-profil.blade.php`; parent view memanggil via `@include('...')`; ukuran baris template utama berkurang signifikan (form perkara berkurang 175 baris, profile berkurang 338 baris); seluruh fungsionalitas dan reaktivitas Alpine.js tetap utuh; `composer test` (163 tests, 1.654 assertions) dan `php vendor/bin/pint --test` 100% lulus.
- [x] T-069 — 2026-10-20 Pratinjau Modal Lightbox Foto Profil (ref: D-045)
  - AC: Avatar foto profil di halaman profil interaktif dengan kursor pointer, efek zoom halus, dan overlay indikator "Lihat Foto"; klik avatar membuka modal lightbox `_modal-foto-profil.blade.php`; modal menampilkan foto resolusi penuh, nama lengkap, jabatan/bidang, tombol silang (x), dan tombol Tutup; `ProfileTest` ditambahkan dan 164 tes otomatis lulus 100%.
- [x] T-071 — 2026-10-20 Upgrade Donut Chart Perkara Tahun Berjalan: Largest Remainder, 4 Pecahan SLA, Filter Periode AJAX, dan Drill-Down Terfilter (ref: D-048)
  - AC: Donut chart SVG native tanpa library tambahan; kategori legend (Selesai, Dalam Proses, Tidak Selesai) konsisten; "Belum Dimulai" dihapus dari catatan; info button (ⓘ) popover di header menggantikan catatan kaki panjang; warna "Tidak Selesai" abu-abu netral (SP3 bukan error); icon non-warna di setiap legend; subjudul dinamis "Diperbarui: {waktu}"; persentase Largest Remainder menjamin total tepat 100%; empty state & loading skeleton; aria-label & screen reader text; tooltip hover segmen donut; drill-down klik segmen dan baris legend ke `/seluruh-perkara` terfilter; dropdown filter Tahun & Bulan di header kartu memuat data via AJAX tanpa reload halaman penuh; indikator perbandingan tahun sebelumnya; 4 sub-baris SLA pada "Dalam Proses" (Belum Ada SPDP, Dalam SLA > 7 hari, Mendekati Batas ≤ 7 hari, Melewati SLA) dengan total persis sama dengan "Dalam Proses" dan drill-down terfilter; `DashboardCaseStatusTest` dibuat dan 172 test suite lulus 100% serta Pint clean.
- [x] T-072 — 2026-10-20 Standarisasi Tipografi Lokal (Inter Variable & JetBrains Mono 500) dan Angka Tabular (tabular-nums) (ref: D-049)
  - AC: Paket `@fontsource-variable/inter` dan `@fontsource/jetbrains-mono` terpasang lokal via npm (tanpa CDN); Google Fonts Outfit dihapus; Tailwind v4 `@theme` mendeklarasikan `--font-sans` dan `--font-mono`; body menerapkan `font-sans antialiased`; `tabular-nums` diterapkan pada angka metrik, persentase, komparasi, dan kolom No tabel; `font-mono` diterapkan pada nomor LK, Sprindik, Berkas, SPDP, Tahapan, Putusan, dan NIK; `npm run build` lulus, 172 test otomatis lulus, dan Pint clean.
- [x] T-073 — 2026-10-21 Sistem Notifikasi Pop-up (Toast) Terpadu Auto-Dismiss 3 Detik Berbasis CSS Animation & Pembersihan Kode Mati (ref: D-050)
  - AC: Komponen `x-common.toast` terpasang di master layouts (`app.blade.php`, `fullscreen-layout.blade.php`); modul JS Alpine `toastManager` dan helper global `window.toast` (success, error, warning, info); durasi 3 detik dikendalikan CSS animation `@keyframes toast-shrink` (tahan throttling latar belakang browser & zero 25ms timer); pause & resume otomatis via CSS `group-hover` dan `group-focus-within`; dukungan jeda ketuk pada perangkat sentuh; dismiss instan via tombol X dan `@animationend`; kapasitas maksimal 4 toast dengan auto-drop tertua saat ke-5; de-duplikasi pesan identik 1,5 detik; kontrak flash session string dan array; serialisasi `@js()` dan render `x-text` (XSS-safe); banner inline sesi berakhir (`?session_expired=1`) di halaman login; kode mati (`error-modal.blade.php`, `flash-alert.blade.php`, modal login 10s lama) dihapus bersih; `ToastNotificationTest` dibuat dan 179 test suite lulus 100% serta Pint clean.


