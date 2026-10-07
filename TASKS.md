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
- [ ] T-002 — Set `.env.example`: `APP_NAME=BAPTwo`, `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`, `DB_DATABASE=baptwo` (ref: D-001, D-004)
- [ ] T-003 — Bersihkan RTL & language switcher warisan TailAdmin (ref: D-003)
  - AC: menu Language hilang dari user dropdown; `LocaleController`/route switch dihapus atau dikunci ke `id`; atribut `dir` tetap `ltr`; test lulus.
- [ ] T-004 — Bersihkan halaman demo TailAdmin yang tidak dipakai (ecommerce, chart demo, calendar, dsb.) dari route & sidebar
  - Catatan: konfirmasi daftar halaman ke user sebelum menghapus.
- [ ] T-005 — Lengkapi `ARCHITECTURE.md` (skema data, enum, layanan domain)
- [ ] T-006 — `composer test` gagal: `Tests\Feature\ExampleTest` dapat 500 karena `ViteManifestNotFoundException` (`public/build/manifest.json` tidak ada)
  - AC: test lulus tanpa harus `npm run build` dulu (mis. `$this->withoutVite()` di `tests/TestCase.php`).
- [ ] T-007 — Folder `tailadmin-laravel-main/` (salinan template, tidak di-track git) ikut di-scan Pint
  - AC: folder dihapus atau dikecualikan via `pint.json`. Konfirmasi ke user sebelum menghapus.
- [ ] T-008 — Rapikan 8 file template yang gagal Pint: `app/Helpers/MenuHelper.php`, `DashboardController`, `LocaleController`, `SidebarController`, `SetLocale`, `ecommerce/MonthlyTarget`, `ui/Modal`, `routes/web.php`
  - AC: `php vendor/bin/pint --test` lulus.

## Auth & RBAC (F-38)

- [ ] T-010 — Autentikasi login/logout (auth Laravel bawaan)
- [ ] T-011 — Role Penyidik/Pimpinan/Administrator + seeder akun dev (ref: D-005)
- [ ] T-012 — Policy dasar & uji akses tiap role (PRD bagian Hak Akses)
- [ ] T-013 — Manajemen user oleh Administrator (UC-05)

## Data Perkara (pra-SPDP)

- [ ] T-020 — Perkara + Laporan Kejadian + Surat Perintah Penyidikan (F-01, F-05, F-06)
- [ ] T-021 — Tersangka; 1 Tersangka = 1 Berkas Perkara (F-02, F-03)
- [ ] T-022 — Pasal berlapis per Berkas (F-04)
- [ ] T-023 — TKP + PPNS utama/anggota (F-07, F-08)
- [ ] T-024 — Alamat hierarkis via API Kode Wilayah (F-36, F-37)
- [ ] T-025 — Nomor Berkas manual tanpa validasi pola (F-33)

## Tahapan & Progress

- [ ] T-030 — Enum tahapan + aturan urutan, maksimal 1x, opsional (F-10–F-13)
- [ ] T-031 — Tiga aksi Batal/Draft/Simpan & Lanjut + penguncian (F-14–F-19)
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

- [ ] T-050 — Pencarian & filter Berkas (F-39)
- [ ] T-051 — Dashboard monitoring (F-40, F-42, F-43)
- [ ] T-052 — Dashboard Potensi Terlimpah (F-41, Should)
- [ ] T-053 — Export Excel & CSV (F-45)
  - Catatan: butuh persetujuan install Laravel Excel.

## Selesai

- [x] T-001 — 2026-10-07
