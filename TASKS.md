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

_(kosong)_

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

- [ ] T-032 — Progress berbobot & status proses otomatis (F-29, F-30)
- [ ] T-033 — Trigger Selesai dari TAHAP II (F-22)
- [ ] T-034 — Scheduler Tidak Selesai 1 Januari 00:00 (F-23)
- [ ] T-035 — SLA 1 bulan kalender + batas tahun (PRD bagian SLA)

## DPO, SP3, Dokumen, Audit

- [ ] T-040 — DPO aktif/nonaktif + gate Tahap II di server & UI (F-24–F-27, F-35)
- [ ] T-041 — Upload SP3 setelah Tidak Selesai (F-28)
- [ ] T-042 — Dokumen terpusat per Berkas & tahap (F-32)
- [ ] T-043 — Audit trail / case history (F-31)
- [ ] T-044 — Soft delete + halaman Sampah untuk pemulihan (F-44)

## Monitoring & Laporan

- [ ] T-050 — Pencarian Perkara (`/pencarian-perkara`) & Seluruh Perkara (`/seluruh-perkara` + card statistik) + struktur menu Role Pimpinan/Umum (F-38, F-39)
- [ ] T-051 — Dashboard monitoring: Tabel Progress Perkara auto-filter (progress terendah & tanggal perkara tertua, maks 5 baris), tombol "Lihat Semua" (`/progress-perkara`), & interaksi double-click ID Perkara untuk modal detail lengkap tanpa kolom aksi (F-40, F-42, F-43)
- [ ] T-052 — Dashboard Potensi Terlimpah (F-41, Should)
- [ ] T-053 — Export Excel & CSV (F-45)
  - Catatan: butuh persetujuan install Laravel Excel.

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



