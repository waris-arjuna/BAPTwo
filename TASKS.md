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

## Data Perkara (pra-SPDP)

- [ ] T-020 — Perkara + Laporan Kejadian + Surat Perintah Penyidikan (F-01, F-05, F-06)
- [ ] T-021 — Tersangka; 1 Tersangka = 1 Berkas Perkara (F-02, F-03)
- [ ] T-022 — Pasal berlapis per Berkas (F-04)
- [ ] T-023 — TKP + PPNS utama/anggota (F-07, F-08)
- [x] T-024 — Alamat hierarkis via API Kode Wilayah (`api.kodewilayah.web.id` + `WilayahService`) (F-36, F-37)
- [ ] T-025 — Nomor Berkas manual tanpa validasi pola (F-33)

## Tahapan & Progress

- [ ] T-030 — Enum tahapan + aturan urutan, maksimal 1x, opsional (F-10–F-13)
- [ ] T-031 — Tiga aksi Batal/Draft/Simpan & Lanjut + penguncian permanen di DB & UI + Card Referensi tahapan sebelumnya + visual indicator Completed/Locked (F-14–F-19)
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
- [x] T-024 — 2026-10-12
