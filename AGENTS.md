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
- **Icons (WAJIB Flaticon — ref: D-010)**:
  - **SEMUA** icon di BAPTwo wajib menggunakan icon dari **Flaticon** (`https://www.flaticon.com/` / Flaticon UIcons) menggunakan elemen `<i class="fi fi-rr-... ..."></i>` (atau `fi-sr-*` / `fi-br-*`).
  - Dilarang menggunakan inline `<svg>` manual untuk icon navigasi, tombol, modal, alert, atau halaman UI (pengecualian hanya untuk gambar soal captcha dinamis `MathCaptchaService`).
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
- **Filter Toolbar & Live Filter (D-011, D-012, D-013)**:
  - Semua halaman list wajib memakai `<x-common.table-filter>`; jangan buat toolbar filter ad-hoc per halaman.
  - Filter berjalan server-side via GET + query string; controller trim param lalu `->when()`, `paginate()` + `withQueryString()`.
  - Live filter AJAX: form terikat `tableFilter('table-body-partial')` dengan header `X-Filter-Partial: 1`, swap fragment `#table-body-partial`, sinkronisasi `history.pushState`, dan popstate listener. Tombol Filter tetap sebagai fallback tanpa JS.
  - Filter tanggal memakai komponen `<x-form.date-picker>` Flatpickr dengan lokalisasi Bahasa Indonesia (`id`), icon Flaticon `fi-rr-calendar`, auto-dispatch `change`, dan reset otomatis instance `el._flatpickr.clear()`.
  - Kolom pencarian via `@input.debounce.500ms`, mencakup kolom utama dan status secara cerdas.
- **Tabel Daftar & Penomoran Seragam (D-013)**:
  - Seluruh tabel daftar (Stage Perkara, Dashboard, Master Data, Manajemen Pengguna) wajib menyertakan kolom nomor urut **`No`** (`{{ $loop->iteration }}`) pada kolom pertama (`<th class="w-12 ...">No</th>`).
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
- **PHP >= 8.2**, build **64-bit (x64)** untuk seluruh perintah CLI: Composer, Artisan, Pint, dan Pest memakai PHP x64 Laragon (mis. `php-8.4.25-Win32-vs17-x64`), bukan `C:\php\php.exe` 32-bit. Dependensi `maennchen/zipstream-php` mensyaratkan `php-64bit ^8.3`, jadi PHP 32-bit menggagalkan `composer install` (ref: D-044).
- **Database**: **MySQL 8** (Laragon). Lihat `DECISIONS.md` D-001.
- **Auth**: pakai auth Laravel bawaan + **Policies/Gates** untuk RBAC.
- **Testing**: **Pest PHP** (sudah disetup TailAdmin via `composer test`).
- **Lint**: **Laravel Pint** (sudah terpasang). Cek: `php vendor/bin/pint --test`. Perbaiki: `php vendor/bin/pint`.
- **Scheduler**: pakai Laravel Scheduler untuk trigger "Tidak Selesai" 1 Januari.
- **Wilayah**: konsumsi **API Kode Wilayah** (lihat PRD F-37).
- **Export**: menggunakan library `phpoffice/phpspreadsheet` murni untuk format ekspor Excel 44 kolom (.xlsx). Ekstensi PHP `zip` wajib aktif di `php.ini` (ref: D-035, D-041).
- **Composer Config**: `"platform-check": false` dikonfigurasi di `composer.json` agar runtime Laravel tidak memblokir eksekusi di lingkungan lokal (ref: D-041). Flag ini tidak memengaruhi pemeriksaan syarat paket saat `composer install` (lihat D-044).

---

## 5. Konvensi Laravel untuk BAPTwo

Selain konvensi TailAdmin, ikuti aturan berikut:

- **Domain logic** (progress, status proses, validasi urutan tahap, DPO gate) diletakkan di **Service / Action class**, BUKAN di Controller, BUKAN di Blade, BUKAN di Model langsung.
- **Controller** hanya: terima request → panggil Service → kembalikan response/view.
- **Form Request** untuk semua validasi input user.
- **Policy** untuk setiap model yang punya akses berbasis role (Perkara, BerkasPerkara, Dokumen, User).
- **Nama Tabel Singular (D-008, D-009)**: Semua 18 tabel menggunakan singular `snake_case` Bahasa Indonesia tanpa akhiran `'s'` (`user`, `alamat`, `perkara`, `berkas_perkara`, `tahapan_perkara`, `dokumen`, `audit_log`, dst.). Setiap Model Eloquent WAJIB mendefinisikan `protected $table = '...'`.
- **Migration** harus menyertakan kolom: `created_by`, `updated_by`, `deleted_at` (soft delete) untuk semua tabel domain.
- **Dokumen Wajib PDF (D-007)**: Hanya menerima file PDF (`['required', 'file', 'mimes:pdf', 'max:20480']`), disimpan terpusat di tabel `dokumen` pada storage privat.
- **Event / Observer** untuk menulis ke tabel `audit_log` (`user_id` nullable untuk scheduler, `berkas_perkara_id` terindeks) setiap kali: status berubah, tahap difinalkan, dokumen diunggah, DPO diaktifkan/dinonaktifkan.
- **Nomor Berkas Perkara** = input manual, **tanpa** validasi pola (PRD F-33).
- **Alamat (`api.kodewilayah.web.id`, D-009)** = tabel `alamat` terpusat dengan kolom `*_kode` (`VARCHAR(15)`) + snapshot `*_nama` (`VARCHAR(100)`) via proxy `WilayahService` (cache 30 hari).
- **Icon UI Wajib Flaticon (D-010)** = seluruh icon pada sidebar, header, tombol, form, card, dan modal wajib menggunakan icon dari `https://www.flaticon.com/` (Flaticon UIcons `<i class="fi fi-rr-..."></i>`).

---

## 6. Yang TIDAK Boleh Dilakukan Agent (khusus BAPTwo)

- ❌ Mengedit `PRD.md` tanpa izin eksplisit.
- ❌ Menggunakan icon selain dari `https://www.flaticon.com/` (jangan membuat inline `<svg>` icon sendiri).
- ❌ Menambah hard delete (route, tombol, query). Semua hapus = soft delete.
- ❌ Mengizinkan edit / mundur tahap yang sudah difinalkan — termasuk oleh Administrator.
- ❌ Menjadikan `Dalam Proses` sebagai status formal.
- ❌ Menjadikan DPO sebagai status formal atau penunda SLA.
- ❌ Menjadikan SP3 sebagai pemicu status.
- ❌ Membuat progress / status proses / status formal bisa diinput manual dari UI.
- ❌ Menambah fitur di luar scope v1.0 (lihat PRD bagian 8 & bagian 7 di file ini).
- ❌ Mengubah bobot progress tanpa persetujuan.
- ❌ Menaruh validasi tahapan / DPO gate **hanya di UI**. Harus ada di server.
- ❌ Menampilkan nama file internal, dokumen aturan, atau referensi developer (seperti PRD, PRD F-xx, D-xx, T-xx, dll.) di teks antarmuka (UI) yang dilihat pengguna akhir. Seluruh teks bantuan, label, dan catatan di UI wajib bersih dan berfokus hanya pada petunjuk operasional pengguna.
- ❌ Menggunakan gaya bahasa dan copywriting AI-slop: Dilarang menggunakan tanda em dash (`—`) dalam teks UI/copywriting. Dilarang menggunakan buzzword klise AI (seperti "revolusioner", "canggih", "seamless", "next-gen", dsb.). Gunakan bahasa Indonesia operasional hukum yang natural, baku, dan lugas.
- ❌ Membuat komponen interaktif palsu atau mati (dead controls): Semua tombol, modal, link, tab, dan toggle wajib berfungsi nyata atau dihapus.
- ❌ Menerapkan desain happy path only: Setiap tampilan penyajian data wajib menyertakan 3 state lengkap (data ada, empty state yang informatif, dan error/loading state).
- ❌ Menggunakan visual generik AI-slop: Dilarang gradient ungu-biru / neon tanpa tujuan fungsional, dilarang glassmorphism berlebihan, dilarang border-radius pill pada seluruh elemen, dan dilarang ikon AI generik (sparkle, magic, robot).
- ❌ Mengabaikan standar kontras dan aksesibilitas: Seluruh teks wajib memenuhi standar WCAG AA (kontras rasio minimal 4.5:1 untuk teks normal).
- ❌ Langsung mengeksekusi kode atau perubahan tanpa penyelarasan (/grill-me): Dilarang langsung mengubah kode, skema, atau tampilan sebelum menyamakan pendapat dengan pengguna. Agent WAJIB mengajukan pertanyaan terarah/klarifikasi desain (menggunakan metode /grill-me dan dialog interaktif) untuk menyepakati rencana sebelum eksekusi.
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
3. **Wajib Penyelarasan (/grill-me)**: Sebelum menulis atau mengubah kode/skema/tampilan, ajukan pertanyaan terarah, klarifikasi kebutuhan, dan alternatif solusi kepada pengguna agar satu persepsi. Jangan langsung mengeksekusi sebelum ada kesepakatan eksplisit.
4. Buat rencana implementasi singkat yang telah disetujui pengguna (daftar file yang akan diubah/dibuat).
5. Kerjakan **satu task kecil** yang telah disepakati dulu; jangan langsung banyak.
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

---

## 11. Aturan Anti AI-Slop & Standar Craftsmanship (ref: D-042)

Semua implementasi UI, copywriting, dan logika di BAPTwo wajib memenuhi standar antislop dan craftsmanship berikut:

1. **Copywriting & Bahasa Indonesia yang Alami**:
   - Dilarang keras menggunakan em dash (`—`) di teks UI pengguna. Gunakan koma (`,`), titik (`.`), titik dua (`:`), atau tanda kurung `()`.
   - Dilarang kata-kata buzzword marketing generik AI ("Revolusioner", "Canggih", "Seamless", "Next-Gen", "Powerful", "Ultimate", dsb.).
   - Gunakan istilah hukum dan operasional resmi BPOM/KUHAP ("Tahap Final & Terkunci", "Pelimpahan Tersangka & Barang Bukti", "Penetapan DPO", "Penghentian Penyidikan/SP3").
   - Bersih 100% dari kebocoran istilah teknis internal developer (`PRD`, `F-xx`, `D-xx`, `T-xx`) pada teks antarmuka yang dilihat pengguna.

2. **Kelengkapan Fungsional & Kontrol Nyata**:
   - Setiap elemen interaktif (tombol, dropdown, modal, tab, link) wajib memiliki fungsi aksi nyata. Dilarang membuat tombol atau link mati (dead controls) yang tidak melakukan apa-apa.
   - Jika aksi belum didukung dalam scope v1.0, hapus elemen tersebut dari tampilan daripada membiarkannya mati.

3. **Ketahanan UI (Resilience & 3 State Wajib)**:
   - Dilarang mendesain *happy path only*. Setiap tampilan data wajib memiliki:
     - **Empty state**: Tampilan ramah dan informatif saat belum ada data (bukan sekadar tabel kosong atau putih polos).
     - **Loading state**: Indikator saat data sedang diambil (AJAX partial / filter).
     - **Error state**: Penanganan kegagalan yang jelas (modal / alert kesalahan validasi yang dapat ditindaklanjuti).

4. **Visual & Styling Proporsional**:
   - Dilarang menggunakan gradient ungu-biru, neon, atau glow berlebihan yang mencirikan default AI slop.
   - Pertahankan tema warna resmi TailAdmin & BPOM (`brand`, `gray`, `success`, `error`, `warning`).
   - Dilarang membuat semua komponen berbentuk pil (*pill-shaped*). Variasi radius sudut harus konsisten dengan hierarki desain.
   - Seluruh ikon wajib menggunakan Flaticon UIcons kontekstual (`fi fi-rr-...`). Dilarang ikon AI generik (sparkle, magic, robot, diamond).

5. **Aksesibilitas & Responsif (WCAG AA)**:
   - Kontras warna teks wajib memenuhi standar rasio minimal **4.5:1** untuk teks normal dan **3:1** untuk teks besar (18px+).
   - Seluruh modal wajib dapat ditutup dengan tombol `Escape` dan memiliki backdrop focus trap.
   - Responsif pada tampilan seluler (tanpa overflow horizontal, target ketukan minimal 44px).

<!-- antislop:start -->
## antislop
For UI, copy, people, mobile layout, or code comments work, read `antislop.md` (core) and then the skill for the task:
- UI / visual: `skills/antislop-ui/SKILL.md`
- Copy & text: `skills/antislop-copywriting/SKILL.md`
- People: `skills/antislop-human/SKILL.md`
- Mobile / responsive: `skills/antislop-layoutmobile/SKILL.md`
- Code comments: `skills/antislop-code/SKILL.md`
Before starting, follow the core's "Two Usage Modes" section in strict order: explicit session instruction first, then global preference, then ask. A session instruction always wins. For a resolved mode, say `antislop active: <mode> (session override).` or `antislop active: <mode> (global preference).` once before presenting findings or making edits, using the actual mode and source. Acknowledging the user's request without naming the source does not replace this notice.
Only an explicit choice of antislop during or after selects a session mode. A request to review, audit, or avoid file edits does not select a mode; read the global preference in that case. Another skill's mode does not select antislop's mode.
If the mode is unresolved, ask during/after and end the response; wait for the answer before any UI review, planning, or concept. For read-only tasks, put the active-mode notice only at the start of the final answer, never in progress messages. For editing tasks, announce before the first edit and omit it from the final answer.
To update antislop later: download `antislop.md` again, or run `npx antislop-ai --update` if it was installed as skill folders.
<!-- antislop:end -->