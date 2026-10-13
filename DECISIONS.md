# DECISIONS — BAPTwo

Log keputusan teknis yang sudah disepakati. `PRD.md` tetap menang untuk aturan bisnis; file ini menang untuk keputusan teknis.

**Format entri:**

```
## D-XXX — Judul singkat
- Tanggal: YYYY-MM-DD
- Status: Diusulkan | Disepakati | Diganti oleh D-YYY
- Konteks: kenapa keputusan dibutuhkan
- Keputusan: apa yang dipilih
- Konsekuensi: dampak & hal yang harus diikuti
```

Jangan menghapus entri lama. Jika berubah, buat entri baru dan tandai entri lama "Diganti oleh D-YYY".

---

## D-001 — Database: MySQL 8

- Tanggal: 2026-10-07
- Status: Disepakati
- Konteks: `AGENTS.md` mencatat database TBD (MySQL/PostgreSQL). `.env.example` sudah `DB_CONNECTION=mysql`. Lingkungan dev memakai Laragon (MySQL bawaan).
- Keputusan: Pakai **MySQL 8**. Nama database dev: `baptwo`.
- Konsekuensi: Migration ditulis kompatibel MySQL. Test memakai konfigurasi di `phpunit.xml` (lihat `ARCHITECTURE.md` bagian Testing).

## D-002 — Lint: Laravel Pint

- Tanggal: 2026-10-07
- Status: Disepakati
- Konteks: Definition of Done mensyaratkan "lint lulus", tetapi perintah lint belum ditetapkan. `laravel/pint` sudah ada di `require-dev`.
- Keputusan: Lint = `php vendor/bin/pint --test`. Perbaikan otomatis = `php vendor/bin/pint`. Preset default Pint (Laravel).
- Konsekuensi: Tidak ada package lint tambahan. Lint JS/CSS belum ditetapkan (tidak ada script npm lint).

## D-003 — RTL & multi-bahasa tidak dipakai

- Tanggal: 2026-10-07
- Status: Disepakati
- Konteks: Template TailAdmin membawa RTL dan language switcher (en/ar/es/de) via `LocaleController`, `SetLocale`, dan menu Language di `user-dropdown.blade.php`. BAPTwo adalah aplikasi internal berbahasa Indonesia.
- Keputusan: BAPTwo **LTR saja**, satu locale `id`. Kode RTL/switcher warisan dibiarkan dorman sampai task pembersihan (lihat `TASKS.md` T-003).
- Konsekuensi: Jangan menambah varian `rtl:`/`ltr:` atau locale baru. Utility logical (`ms-*`, `ps-*`, `start-*`, `text-start`) tetap dianjurkan agar konsisten.

## D-004 — Locale aplikasi `id` dan `lang/id.json`

- Tanggal: 2026-10-07
- Status: Disepakati
- Konteks: Semua label UI wajib bahasa Indonesia; aturan TailAdmin melarang string UI hardcoded tanpa key di `lang/<locale>.json`.
- Keputusan: `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`. Terjemahan di `lang/id.json` dengan key = teks sumber, value = teks Indonesia. Istilah domain (SPDP, P-16, TAHAP I, DPO, SP3) tidak diterjemahkan.
- Konsekuensi: Blade memakai `__('Key')`. Key baru ditambahkan ke `lang/id.json` di task yang sama.

## D-005 — Akun seed per role (khusus dev)

- Tanggal: 2026-10-07
- Status: Disepakati
- Konteks: Developer dan penguji butuh akun untuk tiap role.
- Keputusan: Seeder membuat tiga akun dev dengan password `password`:
  - `admin@baptwo.test` — Administrator
  - `pimpinan@baptwo.test` — Pimpinan
  - `penyidik@baptwo.test` — Penyidik
- Konsekuensi: Seeder akun dev hanya jalan di environment `local`/`testing`. Tidak boleh dipakai di production.

## D-006 — Captcha Pengurangan Internal (SVG Dinamis & One-time Use)

- Tanggal: 2026-10-07
- Status: Disepakati
- Konteks: Form login/signin membutuhkan perlindungan captcha matematika pengurangan tanpa ketergantungan package luar atau ekstensi gambar berat seperti GD.
- Keputusan: Menggunakan `MathCaptchaService` internal dengan formula pengurangan (`A - B = ?`, $A > B$, hasil selalu $> 0$), proteksi visual anti-OCR berbasis filter distorsi lekukan SVG (`feTurbulence` & `feDisplacementMap`), rotasi/skew per karakter, multi-font acak, garis penembus kurva (strike-through), bintik noise, session validasi sekali pakai (pull/flush saat diverifikasi), endpoint reload AJAX `GET /captcha/refresh`, rule validasi `App\Rules\MathCaptcha`, dan komponen Blade `<x-form.math-captcha />`.
- Konsekuensi: Tidak perlu package Composer eksternal; pesan error validasi dilokalisasi dalam Bahasa Indonesia di `lang/id.json`.

## D-007 — Seluruh Dokumen Wajib Format PDF Tunggal (Maks 20 MB)

- Tanggal: 2026-10-07
- Status: Disepakati
- Konteks: Administrasi penanganan perkara tindak pidana BPOM mensyaratkan dokumen legal yang paten, tidak dapat diubah sembarangan, aman dari serangan macro skrip (.doc/.docx), serta seragam untuk pratinjau (preview modal).
- Keputusan: Seluruh berkas yang diunggah ke BAPTwo wajib berformat **PDF** murni (`mimes:pdf`, `application/pdf`). Format selain PDF ditolak di validasi server. Ukuran file maksimal 20 MB.
- Konsekuensi: Form Request Laravel menerapkan rule `['required', 'file', 'mimes:pdf', 'max:20480']`. UI menyediakan viewer PDF seragam. Tabel `dokumen` menyimpan metadata PDF terpusat.

## D-008 — Skema Database Final & Peleburan Entitas Pra-SPDP ke Tabel `perkara`

- Tanggal: 2026-10-07
- Status: Disepakati
- Konteks: Menyempurnakan ERD database agar 100% patuh aturan bisnis `PRD.md`, efisien tanpa overengineering (join berlebih pada data 1-to-1 pra-SPDP), dan kompatibel penuh dengan Laravel 12.
- Keputusan:
  1. Data pra-SPDP (nomor & tanggal Laporan Kejadian serta Surat Perintah Penyidikan/Sprindik) dilebur langsung ke dalam tabel `perkara`.
  2. Kolom `status_formal` pada `berkas_perkara` bertipe `enum('selesai', 'tidak_selesai') NULL DEFAULT NULL` (nilai `NULL` ditampilkan di UI sebagai "Dalam Proses" sesuai PRD F-20, F-21).
  3. Kolom `user_id` pada `audit_log` dibuat `nullable` agar pemicu otomatis cron scheduler 1 Januari 00:00 dapat menulis jejak audit sistem tanpa kegagalan foreign key. Ditambahkan juga `berkas_perkara_id` terindeks untuk query riwayat berkas instan.
  4. Penyimpanan dokumen dipusatkan di tabel `dokumen` yang mendukung dokumen level perkara, berkas, maupun tahapan spesifik.
  5. Semua tabel menggunakan penamaan singular snake_case Bahasa Indonesia tanpa akhiran `'s'` (`alamat`, `user`, `perkara`, `berkas_perkara`, dst.), primary key `id` (`BIGINT UNSIGNED`), foreign key `{nama_tabel}_id`, serta kolom tracking `created_by`, `updated_by`, dan `deleted_at` (`SoftDeletes`).
- Konsekuensi: Model Eloquent mendefinisikan `protected $table = '...'` secara eksplisit sesuai nama tabel singular.

## D-009 — Penyempurnaan Tabel `alamat` (`kodewilayah.web.id`), `user.role`, `instansi_hukum` & `jaksa_penuntut`

- Tanggal: 2026-10-12
- Status: Disepakati
- Konteks: Penyesuaian skema database terhadap format response API `https://api.kodewilayah.web.id` (`{ code, name }`), penyederhanaan RBAC tanpa tabel role/permission terpisah, serta perbaikan duplikasi data instansi pada tabel lokasi/penuntut umum.
- Keputusan:
  1. **Tabel `alamat`**: Menyimpan `provinsi_kode`, `kabupaten_kode`, `kecamatan_kode`, `desa_kode` (`VARCHAR(15)`) beserta snapshot `*_nama` (`VARCHAR(100)`), `rt`, `rw`, dan `alamat_detail`. Pengambilan data menggunakan backend proxy `WilayahService` dengan cache 30 hari, di mana server mengisi `*_nama` otomatis berdasarkan `*_kode` saat disimpan. Tabel `alamat` direlasikan ke `tkp`, `tersangka`, dan `user`.
  2. **Role pada `user`**: Tabel `role`, `permission`, dan `role_permission` dihapus; diganti kolom `role ENUM('administrator', 'pimpinan', 'penyidik')` langsung di tabel `user`. Tabel master kepegawaian (`jabatan`, `bidang`, `panggol`) tetap dipisah.
  3. **Tabel `instansi_hukum` & `jaksa_penuntut`**: Menggantikan `lokasi` dan `penuntut_umum`.
     - `instansi_hukum`: `id`, `jenis` (`ENUM('pengadilan', 'kejaksaan')`), `nama` (`VARCHAR(150)`), `wilayah` (`VARCHAR(100)`), `created_at`, `updated_at`.
     - `jaksa_penuntut`: `id`, `instansi_hukum_id` (FK ke `instansi_hukum`), `nama_jaksa` (`VARCHAR(225)`), `created_at`, `updated_at`.
  4. **Tabel `master_tahapan` & `tkp`**: `master_tahapan` tetap dipertahankan (10 tahapan baku). `tkp` tetap menggunakan `ENUM` (`retailer`/`distributor`, `legal`/`ilegal`, `online`/`offline`).
- Konsekuensi: Jumlah total tabel menjadi 18 tabel singular yang bersih, bebas duplikasi data instansi, dan 100% kompatibel dengan `api.kodewilayah.web.id`.

## D-010 — Standarisasi Seluruh Icon Menggunakan Flaticon (`https://www.flaticon.com/`)

- Tanggal: 2026-10-13
- Status: Disepakati
- Konteks: Menjaga konsistensi visual seluruh antarmuka BAPTwo dan menggantikan berbagai inline SVG bawaan template TailAdmin.
- Keputusan: Seluruh icon yang ada maupun yang akan ditambahkan selanjutnya di aplikasi BAPTwo **wajib** menggunakan icon dari **Flaticon** (`https://www.flaticon.com/` — Flaticon UIcons `fi fi-rr-*`, `fi-sr-*`, `fi-br-*`). Pengecualian hanya untuk gambar soal matematika `MathCaptchaService` yang dirender sebagai gambar SVG terdistorsi.
- Konsekuensi: Stylesheet Flaticon UIcons dimuat secara global di layout utama (`app.blade.php` & `fullscreen-layout.blade.php`) dan `resources/css/app.css`. `MenuHelper::getIconSvg()` serta seluruh komponen/halaman Blade menggunakan tag `<i class="fi ..."></i>`.

## D-011 — Standar Filter & Pencarian

- Tanggal: 2026-10-13
- Status: Disepakati
- Konteks: PRD F-39 mewajibkan pencarian & filter Berkas Perkara; NFR mensyaratkan respons filter ≤ 3 detik. Pola filter bercampur: server-side di `UserController`/`MasterDataController` vs `array_filter()` di Blade untuk halaman mock pra-SPDP. Perlu satu acuan tunggal.
- Keputusan:
  1. **Filter server-side** via query string GET; controller trim param (`$request->string(...)->trim()->toString()`) lalu `->when()` di query builder, `paginate()` + `withQueryString()`. Wajib tanpa package baru.
  2. **Satu komponen UI**: `<x-common.table-filter>` dipakai semua halaman list; filter tambahan lewat `$slot`; reset muncul hanya saat filter aktif; icon Flaticon; label via `__()`.
  3. **Konvensi param** snake_case konsisten: `search`, `status` (status proses), `status_formal`, `tahun`, `penyidik_id`, `dpo`, `tanggal_dari`/`tanggal_sampai`, `sort`/`direction`.
  4. **Aturan domain**: opsi status proses di-generate dari enum/model (bukan hardcode/huruf bebas); "Dalam Proses" di filter berarti `status_formal IS NULL` (label, bukan enum — D-008); DPO adalah filter terpisah, bukan bagian dropdown status; filter menghormati soft delete; hasil tetap patuh RBAC.
  5. **Performa**: indeks kolom sering difilter (`nomor_berkas`, `tanggal`, `status_proses`, `status_formal`, `penyidik_id`); `LIKE %...%` cukup untuk skala internal; empty state + tombol Reset selalu terlihat.
  6. `array_filter()` di Blade pada halaman mock (spdp, laporan-kejadian, tahap-1, p21, sprindik) hanya transisi; pindah ke pola server-side begitu data terkoneksi database.
  7. **Auto-submit (live filter)**: filter berjalan tanpa klik tombol atau tekan Enter. Select & input tanggal memakai `@change` terdelegasi di `<form>` (`@change="if ($event.target.name !== 'search') $el.requestSubmit()"`) sehingga select tambahan dari `$slot` ikut terpicu. Kolom pencarian memakai `@input.debounce.500ms` agar tidak submit tiap ketikan. Tombol Filter tetap dipertahankan sebagai fallback tanpa JS. Submit GET otomatis membuang `page`, sehingga hasil selalu kembali ke halaman 1.
  8. **Label**: teks tombol memakai key `Filter`. Di `lang/id.json` key `Filter` bernilai "Filter" (tidak diterjemahkan menjadi "Saring").
- Konsekuensi: Tidak ada toolbar filter ad-hoc per halaman. Filter logic di Service/scope model sesuai aturan AGENTS.md. Perubahan perilaku auto-submit cukup di `<x-common.table-filter>`, tidak boleh diduplikasi per halaman.

## D-012 — Live Filtering AJAX Tanpa Reload Halaman (Partial Swap + pushState)

- Tanggal: 2026-10-13
- Status: Disepakati
- Konteks: Pengguna membutuhkan filter tabel realtime responsif tanpa reload halaman browser pada seluruh 8 tabel daftar (Laporan Kejadian, Sprindik, SPDP, Tahap 1, P-21, Dashboard Progress Perkara, Master Data, dan Manajemen Pengguna). State URL harus tetap dapat dibagikan (shareable) dan tombol back/forward browser harus tetap berfungsi.
- Keputusan:
  1. **Alpine Component**: Menggunakan komponen `tableFilter(targetSelector)` di `resources/js/components/table-filter.js` tanpa ketergantungan library luar (zero new package).
  2. **Header Request**: Request AJAX mengirimkan header `X-Filter-Partial: 1` dan `X-Requested-With: XMLHttpRequest`.
  3. **Fragment Partial**: Controller dan route mengembalikan snippet Blade terpisah (`_table-partial.blade.php`) yang hanya merender tabel dan pagination, dibungkus kontainer `#table-body-partial`.
  4. **History PushState & Popstate**: Setiap kali filter berubah, `window.history.pushState` memperbarui query string di address bar. Event listener `window.onpopstate` menangani navigasi browser back/forward dengan memanggil ulang partial sesuai state URL.
  5. **Cakupan Pencarian Status**: Input pencarian teks (`search`) secara cerdas mencocokkan kata status (seperti "aktif", "nonaktif", "selesai", "dalam proses", dll.) selain nama/nomor, sehingga pencarian kata status memunculkan data yang tepat.
  6. **Progressive Enhancement**: Jika JavaScript dinonaktifkan, form filter tetap mengirim GET request reguler dan memuat halaman penuh secara normal.
- Konsekuensi: Seluruh tabel daftar di BAPTwo wajib membungkus tabel dan pagination di dalam `<div id="table-body-partial">@include('..._table-partial')</div>` dan memisahkan partial view terkait.

## D-013 — Standarisasi Datepicker Flatpickr & Kolom Nomor Urut Seragam ("No")

- Tanggal: 2026-10-13
- Status: Disepakati
- Konteks: Filter tanggal pada toolbar tabel sebelumnya menggunakan native HTML5 `<input type="date">` yang inkonsisten antar peramban dan tidak seragam dengan desain TailAdmin. Selain itu, tabel Master Data dan Pengguna belum memiliki kolom nomor urut (`No`), berbeda dengan tabel tahapan perkara lainnya.
- Keputusan:
  1. **Datepicker Flatpickr**: Filter tanggal pada `<x-common.table-filter>` distandarkan menggunakan komponen `<x-form.date-picker>` berbasis Flatpickr bawaan template TailAdmin, dengan lokalisasi Bahasa Indonesia (`id`), icon kalender Flaticon (`<i class="fi fi-rr-calendar"></i>`), format standar `Y-m-d`, styling terpadu (light & dark mode), dan auto-dispatch event native `change` (`bubbles: true`) agar live filtering AJAX terpicu otomatis saat tanggal dipilih.
  2. **Reset Datepicker**: Fungsi `resetFilter()` di `table-filter.js` mendeteksi `element._flatpickr` dan mengeksekusi `.clear()` untuk mengosongkan tanggal di UI secara reaktif.
  3. **Penomoran Seragam ("No")**: Seluruh tabel di BAPTwo (termasuk seluruh 6 submenu Master Data dan Manajemen Pengguna) wajib menyertakan kolom penomoran urut tampilan **`No`** (`{{ $loop->iteration }}`) pada kolom pertama (`<th class="w-12 ...">No</th>`) demi konsistensi visual dan standar kebutuhan administrasi instansi pemerintah.
- Konsekuensi: Tampilan tabel di seluruh aplikasi BAPTwo 100% seragam, memiliki nomor urut, dan picker tanggal terintegrasi penuh dalam Bahasa Indonesia.

## D-014 — Navigasi Menu Data Perkara di Sidebar dengan Sub-Menu Daftar Perkara dan Progress Perkara

- Tanggal: 2026-10-13
- Status: Disepakati
- Konteks: Sebelumnya akses ke detail Perkara (`/perkara/{id}`) hanya dapat dijangkau dari menu tahapan Sprindik (`/sprindik`) melalui tombol aksi "Buat Perkara" / "Lihat Perkara". Pengguna membutuhkan menu dedicated agar seluruh data Perkara induk dan Berkas Perkara dapat dilihat langsung kapan saja tanpa harus membuka Sprindik terlebih dahulu, sekaligus menyatukan pemantauan progress perkara ke dalam grup Perkara.
- Keputusan:
  1. Menghilangkan grup menu terpisah "Monitoring" pada Sidebar untuk merapikan hierarki navigasi.
  2. Menambahkan menu dropdown **Data Perkara** (`fi-rr-folders`) di posisi paling atas kelompok **Perkara** pada `app/Helpers/MenuHelper.php`, dengan 2 sub-menu:
     - **Daftar Perkara** (`/data-perkara`): Menampilkan tabel seluruh perkara induk (1 baris = 1 perkara), informasi TKP, PPNS Utama, ringkasan nama tersangka & nomor berkas perkara, status (`Draft Perkara` / `Perkara Terbentuk`), dan aksi detail/edit. Serta dilengkapi tombol `+ Tambah Perkara` berupa pop-up modal memilih Sprindik yang sudah final namun belum dibuatkan perkara.
     - **Progress Perkara** (`/progress-perkara`): Menampilkan tabel monitoring seluruh berkas perkara, persentase progress berbobot (0%, 15%, 55%, 85%, 100%), status proses, status formal, dan badge DPO.
  3. Kedua sub-menu mendukung live filter AJAX (`X-Filter-Partial: 1`), URL pushState, datepicker Flatpickr, dan penomoran seragam (`No`).
- Konsekuensi: Navigasi aplikasi lebih terstruktur, efisien, dan memisahkan dengan jelas antara data induk perkara dengan tahapan administratif berurutan (Sprindik s.d. Putusan Perkara).

## D-015 — Standarisasi Sistem Wilayah Hibrida (Live API `api.kodewilayah.web.id` + Offline JSON Fallback)

- Tanggal: 2026-10-13
- Status: Disepakati & Diimplementasikan
- Konteks: Pada lingkungan lokal/development (Windows/Laragon), pemanggilan `https://api.kodewilayah.web.id` gagal karena error SSL cURL (`unable to get local issuer certificate`), memicu *fallback* hardcoded yang sebelumnya hanya menyediakan data Kabupaten/Kecamatan/Desa untuk Kalimantan Selatan. Akibatnya, provinsi lain tidak menampilkan anak-anak wilayahnya.
- Keputusan:
  1. **HTTP Client Toleran SSL**: `WilayahService::fetchFromApi()` menggunakan `Http::timeout(8)->acceptJson()->withoutVerifying()`, timeout ditingkatkan menjadi 8 detik, serta mencatat log warning saat terjadi kendala koneksi alih-alih menelan galat secara senyap.
  2. **Offline Master Data Fallback**: Sediakan berkas JSON master data lokal di `resources/data/wilayah/provinces.json` (38 Provinsi se-Indonesia) dan `resources/data/wilayah/regencies.json` (514 Kabupaten/Kota se-Indonesia).
  3. **Mekanisme Hibrida**: Jika koneksi API eksternal berhasil, data ditarik secara live dan di-cache selama 30 hari. Jika API eksternal gagal atau server beroperasi tanpa internet, service otomatis beralih membaca berkas JSON lokal sehingga seluruh 38 provinsi di Indonesia dijamin selalu memiliki daftar Kabupaten/Kota yang valid dan nyata.
- Konsekuensi: Form TKP Perkara dan Form Profil Pengguna dapat memilih alamat di seluruh 38 provinsi di Indonesia secara mulus tanpa kegagalan dropdown berjenjang, baik saat online maupun offline.


