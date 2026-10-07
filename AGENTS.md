# AGENTS.md — TailAdmin Free Laravel

> Free Laravel 12 admin dashboard template · Tailwind CSS v4 · Blade Components · Alpine.js · Vite · ApexCharts · FullCalendar · Swiper · Flatpickr (RTL bawaan template tidak dipakai di BAPTwo)

## Repo Map

```
app/
├── Helpers/                  # MenuHelper and UI utilities
bootstrap/
├── app.php                   # Laravel application configuration & middleware setup
public/
├── images/                   # Static images, user avatars, icons (flag-us.svg, flag-sa.svg)
resources/
├── css/
│   └── app.css               # Tailwind CSS v4 theme (@theme), global utilities & 3rd party overrides
├── js/
│   ├── app.js                # Main JS entry point (Alpine.js, component dynamic imports)
│   ├── bootstrap.js          # Axios & HTTP setup
│   └── components/           # Client-side modules (calendar-init, charts, map)
└── views/
    ├── components/           # Reusable Blade components (<x-ui.*>, <x-form.*>, <x-common.*>, etc.)
    │   ├── common/           # Shared page elements (page-breadcrumb, component-card, table-dropdown)
    │   ├── ecommerce/        # Ecommerce dashboard widgets (metrics, monthly-target, recent-orders)
    │   ├── form/             # Form controls (input, select, date-picker, dropzone)
    │   ├── header/           # Header widgets (user-dropdown, notification-dropdown)
    │   ├── profile/          # User profile cards (personal-info, profile-card, address-card)
    │   ├── tables/           # Table variations (basic-tables-one to five)
    │   └── ui/               # UI primitives (alert, avatar, badge, button, modal)
    ├── layouts/              # Master layouts (app.blade.php, fullscreen-layout.blade.php, sidebar.blade.php, app-header.blade.php)
    └── pages/                # Route view templates (dashboard/, auth/, ui-elements/, form/, tables/, chart/, calender.blade.php)
routes/
├── web.php                   # Web application routes
├── api.php                   # API routes
└── console.php               # Console commands
```

## Stack

- **Laravel 12** with **PHP >= 8.2**.
- **Blade Template Engine** using modular, component-driven architecture (`<x-component-name />`).
- **Tailwind CSS v4** configured with `@tailwindcss/vite` and `@theme` tokens in `resources/css/app.css`.
- **Alpine.js v3** for reactive UI interactions, toggles, dropdowns, and global store management (`Alpine.store('theme')`).
- **Vite 7** with `laravel-vite-plugin` for lightning-fast asset compilation.
- **Third-Party Libraries**: ApexCharts, FullCalendar, Swiper, Flatpickr, jsVectorMap, Leaflet, MapLibre GL, Prism.js.
- Scripts:
  - `composer run dev` — runs 4 processes concurrently: `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail --timeout=0`, `npm run dev`.
  - `npm run dev` / `npm run build` — Vite development and production asset bundling.
  - `composer test` — clears config cache, then runs test suite via Pest PHP.
  - `php vendor/bin/pint --test` — lint (Laravel Pint, check only). `php vendor/bin/pint` — auto-fix.

## Conventions

- **New Page**:
  - Add route in `routes/web.php`.
  - Create Blade view in `resources/views/pages/<category>/<page-name>.blade.php`.
  - Extend the appropriate layout: `@extends('layouts.app')` for dashboard pages or `@extends('layouts.fullscreen-layout')` for auth/error/utility pages.
- **New Reusable Component**:
  - Domain-specific component: `resources/views/components/<feature>/<component-name>.blade.php`.
  - Generic UI primitive: `resources/views/components/ui/<component-name>.blade.php`.
  - Form component: `resources/views/components/form/<component-name>.blade.php`.
  - Shared wrapper: `resources/views/components/common/<component-name>.blade.php`.
  - Always use `@props([...])` to declare default values and expected component properties.
- **Icons**:
  - Drop reusable SVGs in `resources/views/components/svg/<icon-name>.blade.php` and invoke via `<x-svg.icon-name />`.
  - For inline SVG icons in components, always use `fill-current` / `stroke-current` and size with `w-*` / `h-*` tokens.
- **Layout Structure**:
  - Dashboard pages: Wrapped in `@extends('layouts.app')` with `<x-common.page-breadcrumb>` at the top and content sections inside `<x-common.component-card>`.
  - Full-width / Auth pages: Wrapped in `@extends('layouts.fullscreen-layout')`.
- **State Management**:
  - Component-level state: Use `x-data="{ ... }"` in Alpine.js.
  - Global theme state: Managed via `Alpine.store('theme')` (handles `light`, `dark`, and system preference synced to `localStorage`).

## RTL Layout Architecture — TIDAK DIPAKAI di BAPTwo

> Keputusan `DECISIONS.md` D-003. BAPTwo adalah aplikasi internal berbahasa Indonesia, **LTR saja**.

- **Status**: fitur RTL dan language switcher (en/ar/es/de) adalah warisan template TailAdmin. Tidak dipakai.
- **Locale**: satu locale, `id`. Terjemahan di `lang/id.json`. Tidak ada multi-bahasa.
- **Kode warisan** (`LocaleController::SUPPORTED_LOCALES`, middleware `SetLocale`, menu Language di `user-dropdown.blade.php`, script `dir` di `<head>` layout):
  - Biarkan sampai task pembersihan di `TASKS.md` dikerjakan.
  - Jangan dikembangkan, jangan ditambah locale/arah baru.
- **Styling**:
  - Utility logical (`ms-*`/`me-*`, `ps-*`/`pe-*`, `start-*`/`end-*`, `text-start`/`text-end`, `rounded-s-*`/`rounded-e-*`) tetap dianjurkan agar konsisten dengan kode template.
  - Jangan menambah varian `rtl:` / `ltr:` baru.
  - Varian `rtl:` yang sudah ada di template tidak perlu dihapus manual; ikut dibersihkan bersama task pembersihan.
- **Off-Canvas Drawers**: kelas `max-xl:rtl:translate-x-full` yang sudah ada jangan dihapus parsial — bisa memicu bug spesifisitas dengan `xl:translate-x-0`.

## Styling Rules

- Tailwind CSS **v4** — configured via `resources/css/app.css` using `@theme`. There is **no** `tailwind.config.js`.
- **Theme Tokens**: Always use predefined theme tokens:
  - Colors: `brand` (`50`–`950`), `gray` (`25`–`950`), `success`, `error`, `warning`, `blue-light`, `orange`, `theme-pink-500`, `theme-purple-500`.
  - Typography: `font-outfit`, `text-theme-xs/sm/xl`, `text-title-sm/md/lg/xl/2xl`.
  - Shadows: `shadow-theme-xs/sm/md/lg/xl`, `shadow-focus-ring`, `shadow-datepicker`, `shadow-tooltip`.
  - Z-Index: `z-1`, `z-9`, `z-99`, `z-999`, … `z-999999`.
- **Dark Mode**:
  - Dark mode is class-driven (`.dark` on `<html>`).
  - Every styled element must include its corresponding `dark:` variant (e.g. `bg-white dark:bg-gray-800 text-gray-800 dark:text-white/90 border-gray-200 dark:border-gray-800`).
- **Reusable Utility Classes**:
  - Check `resources/css/app.css` before writing custom styles (`custom-scrollbar`, `no-scrollbar`, `menu-item-*`, `menu-dropdown-*`, `input-placeholder-*`).
  - Third-party library overrides (ApexCharts, Flatpickr, FullCalendar, Swiper, SimpleBar) are maintained at the bottom of `resources/css/app.css`.
- **No Hardcoded Hex**: Never hardcode hex colors directly in Blade `class=""` attributes. Use Tailwind theme tokens.

## Component & Interactive UI Rules

- **Single Responsibility**: Keep sub-components modular and focused (e.g. `MonthlySalesChart.blade.php`, `RecentOrders.blade.php`).
- **Alpine.js Event Handling**:
  - Do NOT attach redundant `@click` handlers to decorative spans inside `<label :for="...">` elements when `<input x-model="...">` is present, as this causes double-toggle event conflicts.
  - Use `x-cloak` alongside `[x-cloak] { display: none !important; }` to prevent flash of unstyled content during Alpine initialization.
- **Modals & Drawers**:
  - Implement using Alpine `x-data="{ isOpen: false }"` with `@keydown.escape.window="isOpen = false"`, overlay backdrop, and focus management.
- **Charts & Dataviz**:
  - Initialize ApexCharts instances in client-side scripts inside `resources/js/components/` or Alpine `x-init` hooks.
  - Synchronize chart theme colors dynamically on the `'theme-changed'` custom window event.

## Don'ts

- Don't install new Composer packages or NPM dependencies without asking the user.
- Don't add new `rtl:` / `ltr:` variants or RTL logic — BAPTwo is LTR-only (see "RTL Layout Architecture"). Prefer logical utilities (`ms-*`, `me-*`, `ps-*`, `pe-*`, `start-*`, `end-*`, `text-start`, `text-end`) for consistency with existing template code.
- Don't create a `tailwind.config.js` file — Tailwind v4 configuration belongs in `resources/css/app.css`.
- Don't hardcode user-facing strings in Blade files without adding corresponding keys to `lang/<locale>.json`.
- Don't write inline CSS styles (`style="..."`) when Tailwind theme tokens and utility classes are available.

---

# ============================================================
# PROYEK: BAPTwo — BPOM Kalimantan Selatan
# Bagian ini KHUSUS domain BAPTwo. Aturan TailAdmin di atas tetap berlaku.
# ============================================================

## 1. Konteks Proyek

**BAPTwo** adalah aplikasi web internal BPOM Kalimantan Selatan untuk mencatat, memonitor, dan melacak penanganan perkara (pra-SPDP s.d. Keputusan Pengadilan). Proyek ini dibangun **di atas template TailAdmin Free Laravel** — konvensi styling, komponen, dan struktur folder TailAdmin tetap dipakai. **RTL tidak dipakai** (lihat bagian "RTL Layout Architecture" di atas dan `DECISIONS.md` D-003).

**Bahasa UI: Indonesia.** Jangan pakai bahasa Inggris untuk label yang dilihat pengguna. Locale aplikasi = `id` (`APP_LOCALE=id`), terjemahan di `lang/id.json`. Aturan ini menimpa kalimat "UI copy remains in English" milik TailAdmin.

---

## 2. Dokumen Wajib Dibaca (urutan baca)

Sebelum menulis kode, agent WAJIB membaca:

1. `PRD.md` — sumber kebenaran domain & aturan bisnis BAPTwo.
2. `ARCHITECTURE.md` — arsitektur teknis (TBD di PRD, diisi menyusul).
3. `TASKS.md` — daftar pekerjaan aktif.
4. `DECISIONS.md` — log keputusan yang sudah disepakati.
5. `README.md` — cara setup & menjalankan proyek.

Jika terjadi konflik: **`PRD.md` menang untuk aturan bisnis**, **`DECISIONS.md` menang untuk keputusan teknis**, **TailAdmin `AGENTS.md` menang untuk styling/komponen**.

---

## 3. Ringkasan Domain (JANGAN DILANGGAR)

Aturan berikut sudah final di `PRD.md`. Jangan ubah tanpa persetujuan manusia.

### Struktur
- 1 **Perkara** bisa punya banyak **Tersangka**.
- 1 **Tersangka** = 1 **Berkas Perkara** (terpisah).
- 1 **Berkas Perkara** bisa punya **pasal berlapis**.
- Aturan tahapan, SLA, status, dan penguncian berlaku di **level Berkas Perkara**, bukan level Perkara.

### Dua Fase
- **Pra-SPDP** (tidak masuk progress): Laporan Kejadian → Surat Perintah Penyidikan → Pasal/Tersangka/TKP/PPNS → SPDP.
- **Progress utama** (masuk progress): SPDP → P-16 → P-17P → TAHAP I → P-18P/P-19 → P-21/P21a → TAHAP II → Keputusan Pengadilan.

### Tahapan
- Satu jenis tahapan **maksimal 1 kali** per Berkas Perkara.
- Tahapan **harus urut**; tidak boleh lompat sembarangan.
- **Opsional**: P-16, P-17P, P-18P, P-19.
- Tahap yang sudah **difinalkan** → terkunci, tidak bisa diedit/dimundurkan.
- **Administrator pun tidak boleh override penguncian tahap.**

### Tiga Tombol Setiap Tahap
1. **Batal** — keluar tanpa simpan.
2. **Draft** — simpan apa adanya, TIDAK menaikkan tahap, masih bisa diedit.
3. **Simpan & Lanjut Tahap Berikutnya** — hanya jika field wajib lengkap; setelah berhasil → tahap naik & terkunci.

### Progress Berbobot (final)
- Pra-SPDP = 0%
- SPDP = 15%
- TAHAP I = 55%
- P-21/P21a = 85%
- TAHAP II = 100%
- P-16, P-17P, P-18P, P-19 = tidak punya bobot sendiri.
- Keputusan Pengadilan dicatat setelah TAHAP II tapi **tidak menaikkan di atas 100%**.

### Status Proses
Mengikuti **tahapan progress terakhir yang telah difinalkan**. Nilai enum: `Baru`, `SPDP`, `P-16`, `P-17P`, `TAHAP I`, `P-18P`, `P-19`, `P-21/P21a`, `TAHAP II`, `Keputusan Pengadilan`. **Tidak dipilih bebas oleh user.**

### Status Formal Berkas
Hanya dua: **Selesai** dan **Tidak Selesai**.
- `Dalam Proses` = label tampilan, BUKAN status formal.
- **Selesai** dipicu oleh aksi "Simpan & Lanjut" dari TAHAP II (bukan tombol terpisah).
- **Tidak Selesai** dipicu otomatis pada 00:00 tanggal 1 Januari tahun berikutnya jika belum Selesai (scheduler).
- Kedua trigger saling eksklusif.

### DPO
- Toggle manual, **bukan status formal**.
- Bisa diaktifkan di tahap mana pun selama berkas berjalan.
- Tidak mengubah tahapan, tidak mengunci berkas, tidak menjeda SLA.
- Saat DPO aktif → tombol **"Simpan & Lanjut ke Tahap 2 (Pelimpahan Tersangka & Barang Bukti)"** disabled (di UI **dan** di server).
- Bisa dinonaktifkan; tanggal penonaktifan dicatat di audit trail.

### SP3
- Dokumen administratif, diunggah **setelah** status Tidak Selesai.
- Bukan pemicu perubahan status.
- Masuk audit trail.

### SLA & Batas Tahun
- SLA = **1 bulan kalender total**, dari tanggal SPDP s.d. Selesai. Bukan hari kerja, bukan per tahap.
- **Batas tahun mutlak**: berkas hanya boleh dikerjakan di tahun yang sama dengan tahun SPDP.
- Batas tahun selalu menang atas SLA.
- DPO tidak menjeda SLA maupun batas tahun.

### Hak Akses (RBAC)
- **Penyidik**: lihat semua, tambah berkas, edit draft, soft delete. TIDAK bisa edit setelah lanjut. TIDAK bisa hard delete.
- **Pimpinan**: lihat semua + pencarian. Tidak tambah, tidak edit, tidak delete.
- **Administrator**: kelola user & permission, lihat semua, tambah berkas, edit draft, soft delete. TIDAK bisa edit setelah lanjut. TIDAK bisa hard delete.
- **Hard delete tidak tersedia untuk role mana pun.**

---

## 4. Stack Tambahan (di luar TailAdmin)

- **Laravel 12** (sudah dari TailAdmin).
- **PHP >= 8.2**.
- **Database**: **MySQL 8** (Laragon). Lihat `DECISIONS.md` D-001.
- **Auth**: pakai auth Laravel bawaan + **Policies/Gates** untuk RBAC.
- **Testing**: **Pest PHP** (sudah disetup TailAdmin via `composer test`).
- **Lint**: **Laravel Pint** (sudah terpasang). Cek: `php vendor/bin/pint --test`. Perbaiki: `php vendor/bin/pint`.
- **Scheduler**: pakai Laravel Scheduler untuk trigger "Tidak Selesai" 1 Januari.
- **Wilayah**: konsumsi **API Kode Wilayah** (lihat PRD F-37).
- **Export**: gunakan package Laravel Excel (butuh approval sebelum install).

---

## 5. Konvensi Laravel untuk BAPTwo

Selain konvensi TailAdmin, ikuti aturan berikut:

- **Domain logic** (progress, status proses, validasi urutan tahap, DPO gate) diletakkan di **Service / Action class**, BUKAN di Controller, BUKAN di Blade, BUKAN di Model langsung.
- **Controller** hanya: terima request → panggil Service → kembalikan response/view.
- **Form Request** untuk semua validasi input user.
- **Policy** untuk setiap model yang punya akses berbasis role (Perkara, BerkasPerkara, Dokumen, User).
- **Migration** harus menyertakan kolom: `created_by`, `updated_by`, `deleted_at` (soft delete) untuk semua tabel domain.
- **Event / Observer** untuk menulis audit trail setiap kali: status berubah, tahap difinalkan, dokumen diunggah, DPO diaktifkan/dinonaktifkan.
- **Nomor Berkas Perkara** = input manual, **tanpa** validasi pola (PRD F-33).
- **Alamat** = struktur hierarkis (provinsi → kab/kota → kecamatan → desa) dari API Kode Wilayah (PRD F-36, F-37).

---

## 6. Yang TIDAK Boleh Dilakukan Agent (khusus BAPTwo)

- ❌ Mengedit `PRD.md` tanpa izin eksplisit.
- ❌ Menambah hard delete (route, tombol, query). Semua hapus = soft delete.
- ❌ Mengizinkan edit / mundur tahap yang sudah difinalkan — termasuk oleh Administrator.
- ❌ Menjadikan `Dalam Proses` sebagai status formal.
- ❌ Menjadikan DPO sebagai status formal atau penunda SLA.
- ❌ Menjadikan SP3 sebagai pemicu status.
- ❌ Membuat progress / status proses / status formal bisa diinput manual dari UI.
- ❌ Menambah fitur di luar scope v1.0 (lihat PRD bagian 8 & bagian 7 di file ini).
- ❌ Mengubah bobot progress tanpa persetujuan.
- ❌ Menaruh validasi tahapan / DPO gate **hanya di UI**. Harus ada di server.
- ❌ Install package Composer/NPM baru tanpa bertanya (aturan TailAdmin tetap berlaku).

---

## 7. Out of Scope v1.0 (JANGAN dikerjakan)

Merujuk PRD bagian 8:
- Aplikasi mobile/native
- Notifikasi WhatsApp
- Integrasi sistem internal/eksternal BPOM lain
- Integrasi e-Court
- Otomatisasi keputusan hukum
- Prediksi hasil perkara, AI/ML/Data Science
- Fitur publik / akses masyarakat
- Pengeditan tahap yang sudah dikunci
- Mundur ke tahap sebelumnya
- Pindah Berkas Perkara ke tahun berikutnya karena belum selesai
- Hard delete

---

## 8. Alur Kerja Agent (setiap sesi)

1. Baca `PRD.md`, `ARCHITECTURE.md`, `TASKS.md`, `DECISIONS.md`, `README.md`, dan file ini.
2. Ringkas tujuan sesi + task yang akan dikerjakan.
3. Jika task menyentuh aturan domain di bagian 3 → konfirmasi interpretasi dulu sebelum coding.
4. Buat rencana implementasi singkat (file yang akan diubah/dibuat).
5. Kerjakan **satu task kecil** dulu; jangan langsung banyak.
6. Setelah selesai: jalankan `composer test` + `php vendor/bin/pint --test`.
7. Laporkan dengan format di bagian 10.
8. Update `TASKS.md` (centang/geser status) dan `DECISIONS.md` jika ada keputusan teknis baru.

---

## 9. Definisi Selesai (Definition of Done)

Sebuah task dianggap selesai jika:

- Acceptance Criteria di `PRD.md` / `TASKS.md` terpenuhi.
- Tidak melanggar aturan domain di bagian 3.
- `composer test` lulus.
- Lint lulus: `php vendor/bin/pint --test` tanpa error (perbaiki otomatis dengan `php vendor/bin/pint`).
- Audit trail ditulis untuk aksi yang relevan (perubahan status, finalisasi tahap, upload dokumen, DPO on/off).
- Soft delete digunakan untuk semua penghapusan.
- Tidak ada string UI hardcoded (gunakan `lang/id.json` sesuai aturan TailAdmin).
- Dokumentasi terkait diupdate jika perilaku berubah.

---

## 10. Format Laporan Setelah Selesai Kerja

Gunakan format ini:
Ringkasan
<1–3 baris>

Perubahan
<file>: <apa & kenapa>

Cara Uji
<langkah>
<langkah>
Catatan / Keputusan
<opsional — tulis juga ke DECISIONS.md jika penting>