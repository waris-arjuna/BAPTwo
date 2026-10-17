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

## D-016 — Skema Tabel `tahapan_perkara`, Enum `JenisTahap`, dan Validasi Urutan Progres Perkara

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Mengimplementasikan PRD F-10 s.d. F-13 terkait pencatatan tahapan progress pada Berkas Perkara. Dibutuhkan struktur tabel database singular, enum jenis tahapan, pembatasan maksimal 1 kali per jenis tahapan per berkas perkara, fleksibilitas melewati tahapan opsional (P-16, P-17P, P-18P, P-19), serta penguncian permanen pada level domain service.
- Keputusan:
  1. **Tabel Singular `tahapan_perkara`**:
     - Kolom: `id`, `berkas_perkara_id` (FK `berkas_perkara`), `jenis_tahap`, `nomor_surat`, `tanggal_surat`, `catatan`, `status` (`draft`/`final`), `finalized_at`, `finalized_by` (FK `user`), `created_by`, `updated_by`, timestamps, `deleted_at`.
     - Index gabungan: `['berkas_perkara_id', 'jenis_tahap']`.
  2. **Enum `App\Enums\JenisTahap`**:
     - Nilai: `spdp` (15%), `p16` (0%), `p17p` (0%), `tahap_1` (55%), `p18p` (0%), `p19` (0%), `p21` (85%), `tahap_2` (100%), `keputusan_pengadilan` (100%).
     - Flag opsional: `P-16`, `P-17P`, `P-18P`, `P-19` bersifat opsional dan dapat dilewati.
  3. **Domain Service `App\Services\TahapanService`**:
     - Seluruh validasi urutan (`getNextAvailableStages`, `canProceedTo`, `validateStageOrder`) diletakkan di Service, bukan di controller atau Blade.
     - Satu jenis tahapan hanya boleh dicatat maksimal 1 kali per Berkas Perkara.
     - Tahapan yang telah berstatus `final` terkunci permanen (`assertStageNotLocked`) dan melempar `DomainException` jika dicoba untuk diedit atau dimundurkan (termasuk oleh role Administrator).
- Konsekuensi: Integritas data tahapan dan aturan urutan tervalidasi secara kuat di lapisan model dan domain service.

## D-017 — Mekanisme Tiga Tombol Tahapan, Card Referensi, dan Penguncian Permanen (Zero Override)

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Mengimplementasikan PRD F-14 s.d. F-19 mengenai penanganan setiap tahapan perkara. Pengguna membutuhkan fleksibilitas menyimpan draft sementara data belum lengkap, membatalkan input, serta memfinalkan tahap ketika data lengkap sekaligus mengunci tahap tersebut tanpa celah pembatalan/edit.
- Keputusan:
  1. **Tiga Tombol Seragam di Setiap Tahapan**:
     - **Batal**: Link kembali ke halaman berkas perkara tanpa menyimpan perubahan.
     - **Draft** (`action_type = draft`): Menyimpan data apa adanya (nomor/tanggal surat boleh kosong), status tetap `draft`, tidak menaikkan progress berkas, dan dapat diedit kembali oleh Penyidik.
     - **Simpan & Lanjut Tahap Berikutnya** (`action_type = lanjut`): Memvalidasi field wajib lengkap (nomor surat, tanggal surat, dokumen PDF valid jika diunggah). Mengubah status menjadi `final`, mengunci tahap permanen (`finalized_at`, `finalized_by`), serta otomatis menaikkan progress berbobot dan status proses pada `berkas_perkara`.
  2. **Penguncian Permanen (Zero Override)**:
     - Tahapan yang telah berstatus `final` terkunci mutlak di server (`assertStageNotLocked` melempar `DomainException` dan Form Request melempar HTTP 403 Forbidden).
     - Role Administrator pun tidak dapat meng-override atau mengedit tahap yang telah dikunci.
     - Di UI, tahap final hanya menampilkan mode **Lihat Saja (Read-Only)** dengan indikator visual gembok hijau `Terkunci Permanen`.
  3. **Card Referensi Tahapan Sebelumnya**:
     - Ditampilkan di bagian atas formulir tahapan aktif untuk memberikan konteks histori dokumen dan nomor surat tahapan sebelumnya (Laporan Kejadian, Sprindik, dan tahapan progress yang telah final).
  4. **Pusat Dokumen & Jejak Audit**:
     - Dokumen tahapan disimpan di tabel `dokumen` dengan relasi `tahapan_perkara_id` dan `berkas_perkara_id`.
     - Setiap aksi simpan draft dan finalisasi tahap dicatat ke tabel `audit_log` via `AuditTrailService`.
## D-018 — Halaman SPDP Riil, Tab Sub-Tahap Opsional (P-16 & P-17P), Datepicker Flatpickr, dan Modal Seleksi Berkas

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Menu SPDP di sidebar sebelumnya masih menggunakan mock data dari `MockStageService`. Diperlukan integrasi data riil dari tabel `tahapan_perkara`, akomodasi tahapan opsional (`P-16` dan `P-17P`) tanpa memadati sidebar utama, standardisasi filter tanggal dan input tanggal menggunakan Flatpickr datepicker, serta flow tambah SPDP berbasis modal seleksi berkas.
- Keputusan:
  1. **Menu SPDP Riil & Tab Sub-Tahap Opsional**:
     - Route `/spdp` ditangani oleh `SpdpController` yang mengambil data riil dari `tahapan_perkara` berelasi dengan `berkas_perkara`, `perkara`, dan `tersangka`.
     - Sidebar tetap ringkas dengan tahapan utama. Halaman `/spdp` menyediakan tab filter sub-tahap: **Semua**, **SPDP**, **P-16 (Opsional)**, dan **P-17P (Opsional)** lengkap dengan pill counter dinamis.
     - Pergantian tab terintegrasi secara mulus dengan live filter AJAX (`X-Filter-Partial: 1`) dan `history.pushState`.
  2. **Standardisasi Datepicker Flatpickr (D-013)**:
     - Filter tanggal pada tabel `/spdp` menggunakan komponen `<x-form.date-picker>` dengan lokalisasi Bahasa Indonesia (`id`), icon Flaticon `fi-rr-calendar`, auto-dispatch `change`, dan reset Flatpickr otomatis.
     - Input `tanggal_surat` pada formulir tahapan (`resources/views/pages/tahapan/form.blade.php`) menggunakan `<x-form.date-picker>` menggantikan native HTML5 `<input type="date">`.
  3. **Tombol "+ Tambah SPDP" Berbasis Modal Seleksi Berkas**:
     - Mengklik tombol "+ Tambah SPDP" membuka modal Alpine.js yang menampilkan daftar Berkas Perkara yang siap memulai SPDP (`tahap_perkara_status = 'final'` dan belum memiliki SPDP).
     - Memilih berkas langsung mengarahkan ke form input SPDP (`route('berkas-perkara.tahapan.create', [$berkas, 'jenis' => 'spdp'])`).
  4. **Tabel & Aksi Seragam (D-013)**:
     - Kolom tabel seragam: `No`, `Berkas Perkara & Tersangka`, `Tahapan`, `Nomor Surat`, `Tanggal Surat`, `Dokumen`, `Status`, `Aksi`.
     - Penomoran urut pagination: `{{ $loop->iteration + ($spdps->currentPage() - 1) * $spdps->perPage() }}`.
     - Untuk SPDP yang sudah difinalkan, Penyidik diberikan tombol shortcut **Mulai Tahap 1** untuk langsung melanjutkan ke tahapan berikutnya.
- Konsekuensi: Menu SPDP dan tahapan pembuka penyidikan beroperasi 100% menggunakan data database riil, UX lebih cepat dan intuitif, serta tahapan opsional terakomodasi secara bersih dan rapi.

## D-019 — Komponen Input File PDF Reusable (<x-form.pdf-upload>) dengan Tombol Pembatalan (x)

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Pengguna membutuhkan tombol pembatalan/penghapusan file terpilih saat memilih dokumen PDF (`file-d00.pdf (x)`) untuk mengantisipasi kesalahan pemilihan file sebelum form disubmit.
- Keputusan:
  1. **Komponen Reusable `<x-form.pdf-upload>`**:
     - Membungkus input file PDF dalam satu komponen terpadu dengan Alpine.js.
     - Menyediakan tombol pembatalan `(x)` (`fi fi-rr-cross-small`) sejajar dengan nama dan ukuran file baru yang terpilih.
     - Mengklik `(x)` mengeksekusi `$refs.fileInput.value = ''` dan mereset status, sehingga input file kembali kosong.
  2. **Penanganan Mode Edit**:
     - Jika dokumen sudah pernah tersimpan di database, membatalkan file baru dengan `(x)` secara otomatis memulihkan visual dokumen lama yang tersimpan di server, menjaga dokumen lama tetap aman saat form di-submit tanpa perubahan.
  3. **Validasi Klien Terpadu**:
     - Membatasi input hanya menerima file berformat PDF (`accept="application/pdf,.pdf"`) dan ukuran maksimal 20 MB (`maxSizeMb="20"`).
  4. **Penerapan Luas**:
     - Diterapkan secara seragam di seluruh formulir: Laporan Kejadian (`create`/`edit`), Sprindik (`create`/`edit`), Tahapan Perkara (`form`), dan SPDP (`create`).
- Konsekuensi: UI konsisten, bebas duplikasi kode Alpine.js, dan kesalahan pemilihan file dapat dibatalkan secara instan tanpa perlu me-reload halaman.

## D-020 — Pemilihan PPNS Anggota via Combo Box Dinamis & Kartu Detail Terpilih

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Pada formulir pembentukan Data Perkara Pra-SPDP (`/perkara/{id}/buat`), daftar Penyidik PPNS Anggota sebelumnya menggunakan grid checkbox yang memanjang ke bawah. Pengguna menginginkan pemilihan berbasis combo box (dropdown select) yang dapat memilih berulang kali, disertai kartu kecil detail penyidik terpilih (Nama, NIP, pangkat) dan tombol hapus `(x)` untuk membatalkan anggota.
- Keputusan:
  1. **Combo Box Dinamis & Penambahan Otomatis**:
     - Checkbox digantikan dengan `<select>` dropdown (combo box).
     - Saat penyidik dipilih dari dropdown, Alpine.js otomatis menambahkan ID ke `selectedAnggotaIds` dan mereset nilai combo box kembali ke placeholder ("+ Pilih dan tambah anggota penyidik...").
  2. **Eksklusi Otomatis PPNS Terpilih & PPNS Utama**:
     - Penyidik yang telah dipilih sebagai PPNS Utama maupun yang sudah masuk ke dalam daftar Anggota otomatis dihilangkan dari opsi combo box (`getAvailableAnggota()`) untuk mencegah duplikasi peran.
     - Jika PPNS Utama diganti ke salah satu penyidik yang sedang menjadi anggota, Alpine.js otomatis mengeluarkan orang tersebut dari daftar anggota.
  3. **Kartu Kecil Detail Anggota Terpilih**:
     - Anggota yang terpilih dirender dalam grid responsif (1–3 kolom) berisi icon profil (`fi fi-rr-user`), Nama Lengkap, NIP, serta tombol hapus `(x)` (`fi fi-rr-cross-small`).
     - Mengklik `(x)` menghapus anggota dari array `selectedAnggotaIds` dan mengembalikan penyidik tersebut ke dalam daftar opsi combo box.
     - Saat belum ada anggota yang dipilih, ditampilkan empty-state dashed yang informatif.
  4. **Kompatibilitas Penuh Backend**:
     - Setiap ID anggota terpilih dirender sebagai `<input type="hidden" name="ppns_anggota_ids[]" :value="id">` via `<template x-for>`, sehingga data dikirimkan 100% kompatibel dengan Form Request `SavePerkaraBerkasRequest` dan relasi pivot database `perkara_ppns` tanpa perubahan backend.
- Konsekuensi: Pengalaman pengguna lebih ringkas dan modern, bebas duplikasi pilihan penyidik, dan data anggota tersimpan secara konsisten.

## D-021 — Larangan Istilah Internal di UI & Standardisasi Catatan Format Penomoran Dokumen

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Teks antarmuka pengguna pada formulir perkara sebelumnya memuat istilah teknis dan nama file developer (seperti "PRD F-33" dan "Aturan domain BAPTwo") yang mengganggu kenyamanan pengguna akhir. Selain itu, pengguna membutuhkan catatan petunjuk format penomoran yang seragam dan elegan di bawah seluruh kolom input nomor dokumen perkara.
- Keputusan:
  1. **Larangan Istilah Developer di UI**:
     - Seluruh nama dokumen atau kode referensi developer (PRD, SRS, F-xx, D-xx, T-xx, "aturan domain", dsb.) dilarang keras muncul di teks antarmuka yang dilihat pengguna akhir. Seluruh UI teks diubah menjadi petunjuk operasional yang ramah dan relevan bagi pengguna akhir.
  2. **Standardisasi Catatan Format Penomoran Dokumen**:
     - Kolom input tetap mempertahankan placeholder contoh penomoran.
     - Di bawah kolom input, ditambahkan catatan seragam berformat `<strong class="font-medium text-gray-700 dark:text-gray-300">Note:</strong> [keterangan format]`.
     - Template catatan:
       - **Nomor Berkas Perkara**: `Note: Format penomoran berkas: BP/[No]/[Bulan Romawi]/[Tahun]/PPNS-BPOM (atau sesuaikan dengan nomor pada dokumen fisik berkas perkara).`
       - **Nomor Laporan Kejadian**: `Note: Format penomoran: LK/[No]/[Bulan Romawi]/[Tahun] (contoh: LK/001/IX/2026).`
       - **Nomor Sprindik**: `Note: Format penomoran: SP/[No]/[Bulan Romawi]/[Tahun] (contoh: SP/001/IX/2026).`
       - **Nomor Surat Tahapan / SPDP**: `Note: Format nomor surat resmi (contoh: B-01/X/2026/PPNS-BPOM).`
- Konsekuensi: Tampilan UI menjadi bersih dan profesional, serta pengguna mendapatkan panduan penomoran yang jelas di setiap formulir input nomor.

## D-022 — Validasi Simpan Draft Mewajibkan Identitas/Nomor Utama & Melarang Draft Kosong

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Pengguna menghendaki bahwa formulir tidak boleh disimpan atau dijadikan draft jika data kosong sama sekali. Formulir draft harus tetap memiliki identitas/nomor utama agar tidak menghasilkan record kosong (ghost rows) di database.
- Keputusan:
  1. **Field Identitas/Nomor Utama Wajib untuk Draft**:
     - **Laporan Kejadian**: `nomor_laporan` wajib terisi (`required`). Tanggal laporan, dokumen, dan deskripsi boleh menyusul (`nullable` saat draft).
     - **Sprindik**: `nomor_sprindik` wajib terisi (`required`). Tanggal sprindik, dokumen, dan deskripsi boleh menyusul (`nullable` saat draft).
     - **Data Perkara Pra-SPDP**: Minimal 1 Tersangka dengan `nama` dan `nomor_berkas` wajib terisi (`required`). TKP, PPNS Utama/Anggota, dan Pasal boleh menyusul (`nullable` saat draft).
     - **Tahapan Perkara (SPDP s.d. Tahap II)**: `nomor_surat` wajib terisi (`required`). Tanggal surat, dokumen, dan catatan boleh menyusul (`nullable` saat draft).
  2. **Pencegahan Record Kosong**:
     - Menekan tombol "Simpan Draft" dengan kolom nomor/identitas utama kosong akan ditolak oleh validasi server dengan pesan error yang jelas dan tidak disimpan ke database.
  3. **Pembeda Draft vs Simpan & Lanjut**:
     - **Draft**: Mewajibkan nomor/identitas utama; field pelengkap lainnya boleh menyusul; tahap TIDAK naik dan TIDAK terkunci.
     - **Simpan & Lanjut**: Mewajibkan seluruh field wajib (nomor, tanggal, kelengkapan berkas, dsb.) lengkap 100%; setelah berhasil tahap naik dan terkunci permanen.
- Konsekuensi: Integritas database terjaga, bebas dari ghost records/data kosong, sementara fleksibilitas pengerjaan draft bertahap tetap terpenuhi.

## D-023 — Standardisasi Notifikasi Flash Message dengan Timer Auto-Dismiss 10 Detik dan Tombol Tutup (x)

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Notifikasi flash message hasil operasi sistem (berhasil/sukses, gagal/error, peringatan/warning, status/info) sebelumnya masih menggunakan markup ad-hoc per view, tidak memiliki tombol tutup instan `(x)`, dan tidak hilang secara otomatis sehingga menetap di layar tanpa batas waktu. Pengguna menginginkan setiap notifikasi berhasil/gagal dilengkapi tombol `(x)` untuk menutup langsung atau otomatis hilang setelah 10 detik.
- Keputusan:
  1. **Komponen Flash Alert Terpusat (`<x-common.flash-alert>`)**:
     - Diletakkan di master layout `resources/views/layouts/app.blade.php` tepat di atas main content (`@yield('content')`), serta di `signin.blade.php`.
     - Secara otomatis membaca dan merender seluruh session flash message: `success`, `profile_success`, `error`, `warning`, `status`, dan `info`.
     - Menghapus seluruh blok duplikat `@if (session(...))` ad-hoc di 13 view individual untuk menjaga arsitektur DRY.
  2. **Timer Auto-Dismiss 10 Detik & Jeda Saat Hover**:
     - Notifikasi otomatis hilang (fade out & slide up transition) setelah 10 detik (`10000ms`) via Alpine.js `setTimeout(() => this.show = false, 10000)`.
     - Dilengkapi event listener `@mouseenter="clearTimer()"` dan `@mouseleave="startTimer()"` agar timer terjeda saat pengguna mengarahkan mouse ke notifikasi untuk membaca pesan lebih teliti atau mengklik tombol tutup.
  3. **Tombol Tutup Instan `(x)` (Flaticon Wajib D-010)**:
     - Setiap notifikasi dilengkapi tombol dismiss `(x)` menggunakan Flaticon UIcons `<i class="fi fi-rr-cross-small text-lg"></i>` dengan aria-label `Tutup notifikasi`.
     - Mengklik tombol `(x)` membatalkan timer dan menutup notifikasi seketika dengan animasi transisi yang mulus.
  4. **Pembaruan Komponen UI Primitive (`<x-ui.alert>`)**:
     - Komponen `<x-ui.alert>` dimutakhirkan dengan dukungan properti `dismissible` (default `true`), `autoDismiss` (default `false`), `timeout` (default `10000`), token warna Tailwind tema standar (`success`, `error`, `warning`, `brand`), serta icon Flaticon.
## D-024 — Integrasi Sub-Tahap (P-16/P-17 ke SPDP & P-18/P-19 ke Tahap 1) dan Migrasi Halaman Tahapan Lanjutan ke Data Riil

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Agar pengguna tidak dibebani oleh form terpisah yang terlalu banyak, seluruh field sub-tahap penunjukan/permintaan Kejaksaan diintegrasikan langsung ke formulir tahap utamanya masing-masing: P-16 dan P-17 ditempatkan pada formulir SPDP, sedangkan P-18 dan P-19 ditempatkan pada formulir Tahap 1. Selain itu, seluruh halaman tahapan lanjutan di sidebar (`/tahap-1`, `/p21`, `/tahap-2`, `/putusan-perkara`) dimigrasikan dari template mock menjadi controller dan view berbasis data riil database Eloquent.
- Keputusan:
  1. **Integrasi Form Sub-Tahap**:
     - **SPDP (`/berkas-perkara/{berkas}/tahapan/tambah?jenis=spdp` & edit)**: Memiliki data utama SPDP serta 2 section opsional tersemat: P-16 (Penunjukan JPU) dan P-17 (Permintaan Perkembangan).
     - **Tahap 1 (`/berkas-perkara/{berkas}/tahapan/tambah?jenis=tahap_1` & edit)**: Memiliki data utama Tahap I serta 2 section opsional tersemat: P-18 (Berkas Belum Lengkap) dan P-19 (Pengembalian Berkas).
     - Form edit SPDP dan Tahap 1 diizinkan diakses bahkan setelah tahap utama difinalkan untuk melengkapi sub-tahap susulan dari Kejaksaan, dengan data nomor dan tanggal surat utama tetap terkunci permanen (read-only).
  2. **Skema Penyimpanan Ternormalisasi & Audit Trail**:
     - Field sub-tahap disimpan sebagai record `TahapanPerkara` terpisah dengan `jenis_tahap = 'p16'`, `'p17p'`, `'p18p'`, `'p19'`. Dokumen PDF disimpan terpusat di tabel `dokumen`.
     - Setiap penyimpanan/pembaruan sub-tahap dicatat secara otomatis ke dalam `audit_log`.
  3. **Migrasi Halaman Sidebar ke Data Riil**:
     - `Tahap1Controller` (`/tahap-1`): Menampilkan data riil `tahapan_perkara`, pill tabs (Semua, Tahap 1, P-18, P-19), live filter AJAX, modal pemilihan berkas siap Tahap 1, dan shortcut "Lanjut P-21".
     - `P21Controller` (`/p21`): Menampilkan data riil P-21, live filter AJAX, modal pemilihan berkas siap P-21, dan shortcut "Lanjut Tahap 2".
     - `Tahap2Controller` (`/tahap-2`): Menampilkan data riil Tahap 2, pelimpahan berkas, trigger otomatis status formal `selesai` (progress 100%), dan visual indicator DPO.
     - `PutusanPerkaraController` (`/putusan-perkara`): Menampilkan data riil Keputusan Pengadilan untuk berkas perkara yang telah menyelesaikan Tahap II.
  4. **Penegakan DPO Gate pada Tahap 2**:
     - Jika tersangka berstatus DPO (`is_dpo === true`), tombol "Simpan & Lanjut" dinonaktifkan di UI, dan di server `TahapanService@saveStage` melempar `DomainException`. Pengguna hanya diizinkan menyimpan draft hingga status DPO dicabut.
  5. **Standardisasi Komponen UI**:
     - Semua tombol dan icon wajib Flaticon (D-010).
     - Seluruh input PDF menggunakan `<x-form.pdf-upload>` dengan tombol silang `(x)` (D-019).
     - Seluruh filter tanggal menggunakan Flatpickr datepicker dengan lokalisasi `id` (D-012).
     - Format penomoran dilengkapi catatan `Note: ...` di bawah input (D-021).
     - Penomoran `No` seragam (`{{ $loop->iteration }}`) pada tabel (D-013).
- Konsekuensi: Pengguna dapat mengisi tahapan Kejaksaan dengan sangat ringkas dalam satu formulir, integritas data dan status formal 100% terjaga, dan seluruh alur penanganan perkara dari SPDP hingga Putusan Pengadilan berfungsi penuh dengan data riil database.

## D-025 — Manajemen Penetapan & Pencabutan DPO serta Riwayat Penanganan Perkara (Case History Timeline)

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Merujuk PRD F-24 s.d. F-27, F-31, dan F-35 (Tasks T-040 & T-043), pengguna membutuhkan mekanisme manajemen DPO (Daftar Pencarian Orang) yang terpadu pada Detail Perkara (`/perkara/{id}`): Penyidik dan Administrator dapat menetapkan status DPO pada tersangka kapan saja dan mencabutnya ketika tersangka ditemukan kembali. Saat DPO aktif, sistem harus menampilkan indikator visual merah mencolok, card data DPO, dan memblokir pelimpahan Tahap II di UI maupun server. Selain itu, seluruh aktivitas penanganan perkara (tahap, DPO, dokumen) harus terekam di `audit_log` dan ditampilkan sebagai Riwayat Penanganan Perkara (Case History / Timeline) yang elegan dan informatif.
- Keputusan:
  1. **Skema Tabel Singular `dpo` & Relasi Eloquent**:
     - Tabel singular `dpo` memiliki kolom `berkas_perkara_id`, `nomor_surat` (`VARCHAR(150)`), `tanggal_penetapan` (`DATE`), `tanggal_pencabutan` (`DATE`), `status` (`ENUM('aktif', 'nonaktif')`), `keterangan` (`TEXT`), `alasan_pencabutan` (`TEXT`), `dokumen_id` (FK `dokumen`), `created_by`, `updated_by`, `timestamps`, dan `softDeletes`.
     - Model `Dpo` terhubung dengan `BerkasPerkara` (`belongsTo`), `Dokumen` (`belongsTo`), serta `User` (`creator`/`updater`).
     - Kolom `berkas_perkara.is_dpo` disinkronkan secara otomatis: bernilai `true` saat DPO aktif dan `false` saat DPO dicabut.
  2. **Service & Controller Terisolasi (`DpoService` & `DpoController`)**:
     - `DpoService::activateDpo`: Memvalidasi bahwa berkas belum DPO dan belum berstatus formal final, mengunggah dokumen PDF pendukung jika ada, membuat entri `dpo`, mengeset `berkas_perkara.is_dpo = true`, dan mencatat entri `audit_log` (`aksi: aktifkan_dpo`).
     - `DpoService::revokeDpo`: Memvalidasi bahwa berkas sedang DPO, mengupdate entri DPO aktif menjadi status `nonaktif` dengan tanggal dan alasan pencabutan, mengeset `berkas_perkara.is_dpo = false`, dan mencatat entri `audit_log` (`aksi: nonaktifkan_dpo`).
     - Route terproteksi dengan Gate `toggle-dpo` (`isPenyidik() || isAdmin()`), Pimpinan dibatasi hanya dapat melihat (403 Forbidden untuk aksi perubahan).
  3. **Antarmuka Pengguna di Detail Perkara (`resources/views/pages/perkara/show.blade.php`)**:
     - **Visual Indicator & Card Peringatan**: Menampilkan badge merah `DPO Aktif` (`fi fi-rr-shield-exclamation`) pada header berkas perkara dan card peringatan DPO aktif berlatar merah dengan rincian nomor surat, tanggal penetapan, link preview dokumen, keterangan, dan pesan peringatan bahwa pelimpahan ke Tahap II dinonaktifkan.
     - **Riwayat Pencabutan**: Jika DPO pernah dicabut, ditampilkan catatan informatif tanggal dan alasan pencabutan.
     - **Modal Interaktif Alpine.js**: Modal "Tetapkan Status DPO" dan "Cabut Status DPO" dengan Flaticon UIcons (D-010), Flatpickr datepicker (D-012), `<x-form.pdf-upload>` dengan tombol silang `(x)` (D-019), dan catatan format `Note: ...` di bawah nomor surat (D-021).
  4. **Riwayat Penanganan Perkara (Case History / Timeline Audit Trail)**:
     - Menggantikan tampilan mentah audit log lama dengan komponen timeline modern yang terhubung garis vertikal.
     - Icon dan badge warna khas per jenis aktivitas (`aktifkan_dpo` = merah, `nonaktifkan_dpo` = hijau, `finalisasi_*` = brand/indigo, `simpan_draft_*` = kuning, `simpan_sub_tahap` = biru).
     - Label berbahasa Indonesia yang ramah pengguna, nama tersangka terkait, tanggal dan waktu lengkap (`WITA`), pelaksana (nama + role), alamat IP, serta rincian data baru (nomor surat, tanggal, alasan pencabutan, keterangan).
- Konsekuensi: Penegakan aturan bisnis DPO dan transparansi jejak rekam perkara (audit trail) 100% terpenuhi sesuai PRD F-24 s.d. F-27 dan F-31, dengan pengujian feature test yang lulus sempurna.

## D-026 — Larangan Pemilihan Ulang Data Antar-Tahap (Relasi 1-ke-1 Konsumsi Data & Validasi Ketat Server-Side)

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan permintaan pengguna, data dari tahap sebelumnya yang telah digunakan pada suatu tahap berikutnya tidak boleh muncul atau dipilih kembali pada tahap yang sama. Khususnya:
  1. Relasi 1 Laporan Kejadian ke 1 Sprindik: Laporan kejadian yang sudah terhubung ke suatu Sprindik tidak boleh muncul atau dipilih kembali di form Sprindik lain.
  2. Relasi 1 Berkas Perkara ke setiap jenis tahapan: Berkas perkara yang telah memiliki tahapan tertentu (SPDP, Tahap 1, P-21, Tahap 2, atau Putusan Perkara) tidak boleh muncul kembali pada modal pemilihan berkas di tahap yang sama.
  3. Pengguna menginginkan aturan ini ditegakkan di seluruh lapisan sistem (UI, Controller, Form Request, dan Domain Service) agar tidak dapat dibypass melalui manipulasi URL parameter (`?perkara_id=` atau `?berkas_id=`).
- Keputusan:
  1. **Eksklusi Data Terpakai di Query Service**:
     - `PerkaraService::getAvailableForSprindik()`: Hanya mengembalikan perkara yang belum memiliki `nomor_surat_perintah` dan `tahap_sprindik_status`.
     - `PerkaraService::getAvailableForPerkara()`: Menambahkan `whereDoesntHave('tersangkas')` agar perkara yang tersangkanya sudah dibuat tidak muncul kembali di modal.
     - `TahapanService::getAvailableBerkasForSpdp()`, `getAvailableBerkasForTahap1()`, `getAvailableBerkasForP21()`, `getAvailableBerkasForTahap2()`, dan `getAvailableBerkasForPutusan()`: Mengecualikan seluruh Berkas Perkara yang sudah memiliki tahapan terkait (baik status draft maupun final) menggunakan `whereDoesntHave('tahapan', fn($q) => $q->where('jenis_tahap', ...))`.
  2. **Validasi Server-Side pada Form Request**:
     - `StoreSprindikRequest`: Validasi `Rule::exists('perkara', 'id')` memverifikasi bahwa perkara yang dipilih belum memiliki Sprindik. Jika sudah ada, validasi gagal dengan pesan yang ramah pengguna.
     - `StoreTahapanRequest`: Menambahkan closure validation pada `jenis_tahap` yang mengecek `$berkas->hasTahap($enum)` dan memastikan `$enum` ada dalam daftar `$tahapanService->getNextAvailableStages($berkas)`.
  3. **Proteksi Controller terhadap Parameter Query String**:
     - `SprindikController::create`: Jika `?perkara_id=X` diberikan tetapi perkara sudah terhubung ke Sprindik lain, langsung me-redirect kembali ke halaman form dengan flash pesan error.
     - `SpdpController::create`, `Tahap1Controller::create`, `P21Controller::create`, `Tahap2Controller::create`, `PutusanPerkaraController::create`: Jika `?berkas_id=X` diberikan tetapi berkas sudah memiliki tahap tersebut atau belum memenuhi urutan prasyarat (`canProceedTo`), controller me-redirect ke halaman indeks dengan flash error.
     - `TahapanPerkaraController::create`: Memvalidasi `?jenis=X`; jika tidak termasuk dalam `availableStages`, me-redirect ke halaman detail perkara dengan pesan error.
  4. **Proteksi Domain Service**:
     - `PerkaraService::attachSprindik`: Melempar `DomainException` jika perkara sudah memiliki Sprindik.
     - `TahapanService::validateStageOrder`: Melempar `DomainException` jika berkas sudah memiliki tahapan tersebut atau melompati urutan.
  5. **Penyembunyian Tombol Aksi di Antarmuka Pengguna**:
     - Tombol "Mulai Tahap 1" di tabel SPDP (`_table-partial.blade.php`) disembunyikan jika berkas sudah memiliki Tahap 1.
     - Tombol "Lanjut P-21" di tabel Tahap 1 disembunyikan jika berkas sudah memiliki P-21.
     - Tombol "Lanjut Tahap 2" di tabel P-21 disembunyikan jika berkas sudah memiliki Tahap 2.
     - Tombol "Catat Putusan" di tabel Tahap 2 disembunyikan jika berkas sudah memiliki Keputusan Pengadilan.
     - Tombol "Input Tahapan" di detail perkara (`perkara/show.blade.php`) disembunyikan jika seluruh tahapan progress berkas perkara telah selesai.
- Konsekuensi: Integritas data terjamin 100%, duplikasi tahapan atau sprindik tidak dapat terjadi baik sengaja maupun tidak sengaja, dan antarmuka pengguna hanya menyajikan opsi yang valid dan relevan.

## D-027 — Dashboard Monitoring Riil & Quick-View Detail Perkara (Tanpa Kolom Aksi)

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Halaman Beranda (`/`) sebelumnya menggunakan data tiruan (`MockStageService`). Sesuai PRD F-40, F-42, F-43, dan task T-051, dashboard monitoring utama wajib beralih ke data riil database tanpa mengubah tata letak, gaya visual, atau hierarki komponen TailAdmin yang sudah disepakati. Selain itu, baris tabel progress pada dashboard harus dapat di-double-click (serta melalui menu opsi) untuk memunculkan modal pratinjau cepat (quick-view) detail lengkap tanpa tombol aksi pengeditan/penghapusan.
- Keputusan:
  1. **DashboardService (`app/Services/DashboardService.php`)**:
     - `getStatistics(?int $tahun)`: Menghitung 5 metrik card riil dari tabel `perkara` dan `berkas_perkara` (Total Perkara, Berkas Berjalan, Menunggu Verifikasi/Tahap Awal, Selesai, dan Rata-rata Hari Selesai).
     - `getCaseStatusData(?int $tahun)`: Menghitung total dan persentase status formal (`Selesai`, `Dalam Proses`, `Tidak Selesai`) untuk Donut Chart.
     - `getStageAverageDays(?int $tahun)`: Menghitung rata-rata hari penyelesaian antar-tahap (SPDP -> Tahap 1, Tahap 1 -> P-21, P-21 -> Tahap 2).
     - `getPriorityProgressPerkara(Request $request, int $limit = 5)`: Mengambil maksimal 5 berkas perkara prioritas teratas dengan urutan `progress_persen ASC` dan tanggal SPDP / perkara tertua (`tanggal ASC`), mendukung filter pencarian dan status, serta AJAX live-filter (`X-Filter-Partial: 1`).
  2. **DashboardController & Routes**:
     - Mengubah route `/` dari closure mock menjadi `Route::get('/', [DashboardController::class, 'index'])->name('dashboard')`.
     - Mendukung query parameter `?tahun=YYYY` dengan filter dropdown tahun yang reaktif.
  3. **Preservasi Tampilan Visual 100%**:
     - Array data yang dihasilkan `DashboardService` mempertahankan struktur keys template (`nomor`, `tersangka`, `pasal`, `tahap`, `progress`, `status`, `spdp`, `tahap1`, `p21`, `tahap2`, `estimasi`) sehingga 5 Card Statistik, Donut Chart SVG, Rata-rata Hari per Tahap, dan Timeline Progress SVG di tabel tetap tampil presisi tanpa perubahan layout.
  4. **Interaksi Quick-View & Tombol "Lihat Semua"**:
     - Header card tabel progress menyertakan tombol "Lihat Semua" (`fi fi-rr-arrow-right`) menuju `/progress-perkara`.
     - Setiap baris tabel mendukung double-click (`@dblclick="openQuickView(perkara)"`) dan opsi menu dropdown "Ringkasan Cepat" (`fi fi-rr-eye`).
     - Modal Quick-View menampilkan rincian berkas (Nomor Berkas, NIK, Sprindik, PPNS Utama & Anggota, Pasal Berlapis, persentase progress bar, dan tabel riwayat tahapan tercatat) dalam mode read-only (tanpa tombol aksi edit/hapus), dengan tombol "Tutup" dan "Buka Detail Penuh".
- Konsekuensi: Dashboard monitoring terhubung penuh dengan data riil database, operasional pemantauan berkas perkara menjadi sangat efisien dan informatif, serta integritas tampilan dashboard TailAdmin tetap terjaga 100%.

## D-028 — Komponen Dropdown Aksi Tabel Global dengan Portal Teleport ke Body & Auto-Flip

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Pada seluruh tabel di aplikasi (Data Perkara, Laporan Kejadian, Sprindik, Progress Perkara, Dashboard Monitoring), tombol "Aksi" pada baris terbawah sebelumnya memicu dropdown menu yang terpotong oleh pembungkus overflow tabel (`overflow-x-auto` / `overflow-hidden`), yang memunculkan scrollbar vertikal yang tidak diinginkan. Perbaikan harus dilakukan secara global melalui satu komponen reusable, tanpa mengubah tinggi kontainer atau padding bawah tabel.
- Keputusan:
  1. **Komponen Reusable `<x-common.table-dropdown>` (`resources/views/components/common/table-dropdown.blade.php`)**:
     - **Portal ke Body**: Menu dropdown dirender menggunakan `<template x-teleport="body">` sehingga node DOM menu berada langsung di bawah `<body>`. Hal ini membebaskannya 100% dari batasan `overflow` atau `stacking context` kontainer tabel.
     - **Posisi Fixed Mengambang**: Posisi dihitung secara dinamis dari `getBoundingClientRect()` tombol trigger `$refs.button` dengan koordinat `fixed`, sehingga posisi selalu presisi dan tidak bergeser saat scrolling.
     - **Auto-Flip Vertikal**: Jika ruang vertikal di bawah tombol tidak mencukupi (`spaceBelow < menuHeight + 12px`) dan ruang di atas lebih besar, dropdown secara otomatis terbuka ke atas (`openUp = true`).
     - **Penutupan Otomatis (Auto-Close)**:
       - Klik di luar area dropdown (`@click.outside` dengan pengecualian elemen tombol trigger).
       - Klik pada item aksi di dalam menu.
       - Scrolling halaman atau kontainer tabel (`window.addEventListener('scroll', ..., true)` pada fase *capture* yang menangkap scroll di elemen `div.overflow-x-auto`).
       - Penekanan tombol `Esc` (`@keydown.escape.window`).
       - Perubahan ukuran jendela browser (`resize`).
       - Pembersihan listener otomatis saat komponen di-*unmount* via `this.$cleanup(...)`.
     - **Preservasi Visual & Desain**:
       - Tampilan visual tombol "Aksi", border, sudut membulat, bayangan (`shadow-theme-lg`), mode gelap (`dark:`), dan icon panah berotasi (`fi fi-rr-angle-small-down`) 100% identik dengan gaya TailAdmin.
  2. **Migrasi Seluruh Tabel**:
     - Mengganti seluruh dropdown ad-hoc di:
       - `resources/views/pages/laporan-kejadian/_table-partial.blade.php`
       - `resources/views/pages/sprindik/_table-partial.blade.php`
       - `resources/views/pages/data-perkara/_table-partial.blade.php`
       - `resources/views/components/dashboard/_progress-partial.blade.php`
       - `resources/views/pages/progress-perkara/_table-partial.blade.php`
     - Mendukung aksi modal konfirmasi hapus (soft delete) melalui preserve scope Alpine.
  3. **Pengujian**:
     - Dibuat `tests/Feature/TableDropdownTest.php` untuk memvalidasi rendering komponen, keberadaan direktif `x-teleport="body"`, auto-flip calculation, scroll listener, serta rendering dropdown di seluruh halaman tabel terkait.
- Konsekuensi: Dropdown pada baris tabel mana pun (termasuk baris terakhir) tampil utuh, tidak pernah terpotong, dan tidak pernah memicu scrollbar vertikal pada kontainer tabel.

## D-029 — Komponen Searchable Select Reusable (Select2-Like) Berbasis Alpine.js & Tailwind CSS v4

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Pengguna menghendaki fitur pencarian (search) pada input yang menggunakan combobox atau select seperti layaknya Select2 pada Bootstrap untuk mempermudah pemilihan data pada daftar opsi yang panjang (seperti Laporan Kejadian, Penyidik PPNS, Wilayah Provinsi/Kabupaten/Kecamatan/Desa, Tahapan Perkara, Jabatan, Bidang, Pangkat/Golongan, dan Instansi Kejaksaan).
- Keputusan:
  1. **Komponen Reusable `<x-form.searchable-select>` (`resources/views/components/form/searchable-select.blade.php`)**:
     - **Arsitektur Native 100%**: Dibangun murni menggunakan **Alpine.js v3** dan **Tailwind CSS v4** bawaan tanpa menambah library/dependensi eksternal (menjaga performa tetap optimal dan bundle JS tetap ringan).
     - **Desain & Dark Mode Seragam**: Menggunakan kelas Tailwind yang identik dengan input TailAdmin (`h-11`, `rounded-lg`, `border-gray-300`, `dark:bg-gray-900`, `dark:border-gray-700`, `focus:border-brand-500`) serta icon Flaticon UIcons (`fi fi-rr-search`, `fi fi-rr-angle-small-down`, `fi fi-rr-cross-small`, `fi fi-rr-check`).
  2. **Fitur Pengalaman Pengguna (UX) ala Select2**:
     - **Live Search**: Kolom input pencarian tersemat di bagian atas dropdown yang langsung fokus otomatis saat dropdown dibuka (`$nextTick(() => $refs.searchInput.focus())`).
     - **Navigasi Keyboard**: Mendukung navigasi panah atas (`ArrowUp`), panah bawah (`ArrowDown`), Enter untuk memilih, dan `Escape` untuk menutup dropdown.
     - **Tombol Reset / Clearable**: Tombol silang `(x)` untuk mengosongkan nilai terpilih secara instan tanpa membuka dropdown.
     - **Auto-Flip Vertikal**: Panel dropdown otomatis mendeteksi batas bawah layar; jika ruang di bawah kurang dari 260px, dropdown terbuka ke atas.
     - **Indikator Item Aktif**: Item yang terpilih diberi aksen warna brand (`bg-brand-50 text-brand-700`) beserta icon centang `fi fi-rr-check`.
     - **Status Kosong**: Menampilkan pesan `"Data tidak ditemukan"` jika query pencarian tidak menghasilkan opsi yang cocok.
  3. **Bridge Sinkronisasi Dua Arah (`x-modelable`) & Kompatibilitas Form**:
     - Mempertahankan elemen `<select>` native di balik layar (`class="sr-only"`) sehingga pengiriman form standar Laravel (`POST`/`PUT`), validasi Form Request, nilai lama (`old(...)`), dan atribut `required` bekerja tanpa perlu memodifikasi controller backend.
     - Dilengkapi `MutationObserver` untuk mendeteksi penambahan opsi dinamis dari template Alpine `<template x-for>` atau asynchronous fetch (seperti pada cascading wilayah Provinsi -> Kabupaten -> Kecamatan -> Desa).
     - Menggunakan `x-modelable="selectedValue"` untuk mendukung *two-way binding* penuh dengan Alpine `x-model` pada komponen induk.
  4. **Penerapan Menyeluruh di Seluruh Aplikasi**:
     - **Form Pembuatan Sprindik** (`resources/views/pages/sprindik/create.blade.php`): Pilihan Laporan Kejadian yang sudah final (`perkara_id`).
     - **Form Pembuatan SPDP** (`resources/views/pages/spdp/create.blade.php`): Pilihan Sprindik yang tersedia.
     - **Form Data Perkara** (`resources/views/pages/perkara/form.blade.php`): Pilihan PPNS Utama, PPNS Anggota, serta Wilayah TKP (Provinsi, Kabupaten, Kecamatan, Desa).
     - **Form Tahapan Perkara** (`resources/views/pages/tahapan/form.blade.php`): Pilihan Jenis Tahap lanjutan.
     - **Form Profil Pengguna** (`resources/views/pages/profile.blade.php`): Wilayah Tempat Tinggal (Provinsi, Kabupaten, Kecamatan, Desa).
     - **Form Pengaturan Pengguna** (`resources/views/pages/pengaturan/index.blade.php`): Pilihan Jabatan, Bidang, dan Pangkat/Golongan.
     - **Form Master Data** (`resources/views/pages/master-data/index.blade.php`): Pilihan Asal Kejaksaan pada Jaksa Penuntut.
  5. **Pengujian Komprehensif**:
     - Dibuat `tests/Feature/SearchableSelectTest.php` untuk memvalidasi rendering dengan props array, slot `<option>`, binding nilai default/old, status disabled/clearable, serta integrasi pada halaman form.
- Konsekuensi: Seluruh input select/combobox di BAPTwo kini memiliki fitur pencarian interaktif yang responsif, modern, dan sangat nyaman digunakan untuk data berjumlah besar, dengan 100% keselarasan desain dan tanpa menambah dependensi pihak ketiga.

## D-030 — Tombol & Penerbitan SP3 Manual pada Berkas Perkara, Penguncian Status Tidak Selesai, dan Penyimpanan Dokumen PDF Legal

- Tanggal: 2026-10-14
- Status: Disepakati & Diimplementasikan
- Konteks: Pengguna menghendaki fitur tombol **SP3 (Surat Perintah Penghentian Penyidikan)** yang dapat diakses langsung pada kartu Berkas Perkara mirip dengan tombol DPO, guna mengakomodasi penghentian penyidikan perkara atas alasan hukum yang sah (tidak cukup bukti, bukan tindak pidana, demi hukum, atau restorative justice).
- Keputusan:
  1. **Tombol "Terbitkan SP3" pada Kartu Berkas Perkara**:
     - Disematkan pada kartu Berkas Perkara di halaman detail perkara (`resources/views/pages/perkara/show.blade.php`), berdampingan dengan tombol DPO.
     - Hanya dapat diakses oleh role yang memiliki otorisasi (`Penyidik` dan `Administrator` via permission `create-cases`); role `Pimpinan` dibatasi read-only.
     - Tombol otomatis tersembunyi jika berkas perkara sudah pernah diterbitkan SP3, atau jika status formal perkara telah selesai / tidak selesai.
  2. **Modal Interaktif Penerbitan SP3**:
     - Menggunakan Alpine.js (`isSp3ModalOpen`) dengan backdrop blur dan transisi halus.
     - Input field lengkap:
       - `nomor_surat` (text, required): Nomor surat SP3 resmi.
       - `tanggal_sp3` (date picker Flatpickr, default hari ini).
       - `alasan` (select dropdown berstandar Pasal 109 ayat 2 KUHAP & Restorative Justice).
       - `dokumen` (PDF upload murni maks 20 MB via `<x-form.pdf-upload>`, required per aturan D-007).
       - `keterangan` (textarea, opsional).
  3. **Penetapan Status Formal & Penguncian Tahapan**:
     - Penerbitan SP3 diproses oleh `App\Services\Sp3Service`:
       - Menyimpan dokumen fisik PDF ke disk lokal privat dan mencatatnya ke tabel `dokumen` dengan kategori `'sp3'`.
       - Menyimpan entitas ke tabel `sp3`.
       - Otomatis mengubah `berkas_perkara.status_formal = 'tidak_selesai'`.
       - Menulis jejak audit ke tabel `audit_log` dengan aksi `'terbitkan_sp3'`.
     - `App\Services\TahapanService::getNextAvailableStages()` dikonfigurasi untuk memblokir penambahan tahapan lanjutan pada berkas yang berstatus `tidak_selesai`.
  4. **Badge Status Visual & Tombol Lihat Dokumen**:
     - Pada Berkas Perkara yang telah diterbitkan SP3, ditampilkan badge merah `SP3 Terbit (Tidak Selesai)` (`fi-rr-ban`) dan tombol shortcut `Lihat SP3` (`fi-rr-document`) yang langsung membuka dokumen PDF resmi di tab baru (`route('dokumen.preview')`).
- Konsekuensi: Penyidik dan Administrator memiliki mekanisme resmi yang jelas, terdokumentasi, dan akuntabel untuk menghentikan penyidikan perkara dengan berkas PDF sah dan jejak audit lengkap.

## D-031 — Mekanisme Batas Waktu SLA 1 Bulan Kalender, Batas Tahun Mutlak, dan Scheduler Otomatis 1 Januari 00:00 (PRD F-23)

- Tanggal: 2026-10-17
- Status: Disepakati & Diimplementasikan
- Konteks: Mengimplementasikan PRD F-23 dan aturan domain batas waktu penyidikan (SLA). SLA Berkas Perkara adalah 1 bulan kalender secara total dari SPDP sampai Selesai. Berkas hanya boleh dikerjakan dalam tahun yang sama dengan tahun terbitnya SPDP, di mana batas tahun mutlak selalu didahulukan daripada SLA 1 bulan. Tepat 1 Januari 00:00, berkas yang belum mencapai status Selesai otomatis ditetapkan menjadi Tidak Selesai tanpa intervensi manusia.
- Keputusan:
  1. **Batas Tahun Mutlak Mendahului SLA 1 Bulan**:
     - Pada `App\Services\TahapanService::saveStage()` dan model `BerkasPerkara`:
       - SLA dasar: `tanggal_spdp->copy()->addMonth()`.
       - Batas tahun: `tanggal_spdp->copy()->endOfYear()`.
       - Tanggal jatuh tempo efektif (`effective_deadline`): jika SLA 1 bulan melewati 31 Desember tahun tersebut, maka batas akhir mutlak ditetapkan pada 31 Desember tahun yang sama.
  2. **Evaluasi Status & Badge Visual SLA**:
     - Ditambahkan atribut pada `BerkasPerkara`: `effective_deadline`, `sisa_hari_sla`, `sla_status`, dan `sla_badge`.
     - Kategori status: `Pra-SPDP`, `SLA Berjalan`, `Mendekati Batas SLA` (sisa <= 7 hari), `Melebihi SLA` (overdue), `Selesai Tepat Waktu`, `Selesai Melebihi SLA`, dan `Tidak Selesai`.
     - Status DPO aktif tidak menjeda atau menghentikan perhitungan SLA maupun batas tahun (PRD bagian DPO).
     - Badge visual ditampilkan pada kartu Berkas Perkara di halaman detail perkara (`perkara.show`) dan tabel progress perkara (`progress-perkara`).
  3. **Scheduler Otomatis 1 Januari 00:00 (`perkara:close-unfinished`)**:
     - Command artisan: `App\Console\Commands\CloseUnfinishedCasesCommand` terdaftar di `routes/console.php` via `Schedule::command('perkara:close-unfinished')->yearlyOn(1, 1, '00:00')`.
     - Mencari seluruh Berkas Perkara yang belum memiliki status formal (`status_formal IS NULL`) dari tahun sebelumnya atau tahun yang ditutup.
     - Mengubah status formal menjadi `'tidak_selesai'`.
     - Mencatat jejak audit ke tabel `audit_log` dengan `user_id = null` (aksi sistem scheduler), aksi `'sistem_tidak_selesai'`, dan keterangan batas tahun berakhir.
  4. **Pengujian Komprehensif**:
     - `tests/Feature/CloseUnfinishedCasesTest.php`: memvalidasi penutupan otomatis, preservasi berkas selesai, pencegahan dampak pada berkas tahun berjalan, opsi `--dry-run`, dan pencatatan audit trail sistem.
     - `tests/Feature/SlaAndBatasTahunTest.php`: memvalidasi batas SLA 1 bulan kalender, batas tahun mutlak akhir Desember, evaluasi sisa hari, pemetaan badge status, serta independensi terhadap toggle DPO.
- Konsekuensi: Integritas batas waktu perkara dan transisi status formal akhir tahun berjalan 100% otomatis, akurat, taat aturan hukum dan PRD, serta transparan bagi Penyidik dan Pimpinan.

## D-032 — Halaman Sampah (Trash) dan Pemulihan Data Soft Delete Tanpa Hard Delete (PRD F-44)

- Tanggal: 2026-10-17
- Status: Disepakati & Diimplementasikan
- Konteks: Mengimplementasikan PRD F-44 dan prinsip integritas data BAPTwo, di mana seluruh penghapusan data bersifat **Soft Delete** (`deleted_at`), dan fitur **Hard Delete ditiadakan sama sekali** untuk semua peran tanpa pengecualian. Data yang dihapus (Laporan Kejadian/Perkara, Berkas Perkara, dan Pengguna) harus dapat ditinjau dan dipulihkan kembali (*restore*) ke posisi semula oleh peran yang berwenang.
- Keputusan:
  1. **Menu Navigasi & Halaman Sampah (`/sampah`)**:
     - Ditambahkan ke grup navigasi *Sistem* pada sidebar (`App\Helpers\MenuHelper::getSystemItems`) menggunakan ikon Flaticon `trash` (`fi fi-rr-trash`).
     - Tampilan ber-tab 3 kategori:
       - Tab `perkara`: Menampilkan data Laporan Kejadian & Perkara yang di-soft-delete beserta dokumen laporan dan pembuatnya.
       - Tab `berkas`: Menampilkan Berkas Perkara & Tersangka yang di-soft-delete beserta relasi perkara induk.
       - Tab `user`: Menampilkan Akun Pengguna yang di-soft-delete beserta relasi jabatan dan role.
     - Setiap tab memiliki badge counter dinamis yang menghitung jumlah item terhapus secara *real-time*.
  2. **Zero Hard-Delete Enforced**:
     - Halaman Sampah tidak menyediakan tombol, modal, link, maupun route untuk *Hard Delete* (hapus permanen).
     - Data yang berada di Sampah hanya dapat dipulihkan (*Restore*) atau dibiarkan sebagai riwayat terhapus yang permanen dan aman.
     - Banner edukasi disematkan di bagian atas halaman untuk menegaskan bahwa data tidak dimusnahkan secara fisik demi menjaga akuntabilitas penanganan perkara.
  3. **Otorisasi & Pemulihan Bertingkat (RBAC)**:
     - Seluruh pengguna terautentikasi dapat melihat (*read-only*) isi Sampah untuk transparansi.
     - Aksi pemulihan Perkara & Berkas Perkara dilindungi gate `can:soft-delete` (hanya `Penyidik` dan `Administrator`); role `Pimpinan` dibatasi read-only (HTTP 403 jika mencoba memulihkan).
     - Aksi pemulihan Pengguna dilindungi gate `can:manage-users` (khusus `Administrator`).
     - Pemulihan Perkara (`PerkaraService::restorePerkara`) secara otomatis memulihkan dokumen lampiran, tersangka, dan berkas perkara terkait dalam transaksi DB.
     - Pemulihan Berkas (`PerkaraService::restoreBerkas`) secara otomatis memulihkan tersangka terkait.
  4. **Audit Trail & Filter Terpadu**:
     - Setiap pemulihan mencatat jejak audit ke tabel `audit_log` (`restore_perkara`, `restore_berkas_perkara`, `restore_user`).
     - Dilengkapi `<x-common.table-filter>` terpadu dengan pencarian cerdas (`nomor_berkas`, `tersangka`, `perkara`, `nip`, `email`, dll.), filter rentang tanggal `deleted_at`, serta dukungan live swap partial AJAX (`X-Filter-Partial: 1`).
  5. **Pengujian Komprehensif (`tests/Feature/SampahTest.php`)**:
     - Validasi akses multi-role (Penyidik, Admin, Pimpinan).
     - Validasi pemulihan Perkara dan Berkas Perkara oleh Penyidik beserta audit log.
     - Validasi penolakan 403 Forbidden bagi role Pimpinan saat mencoba memulihkan data.
     - Validasi pemulihan Pengguna khusus Administrator dan penolakan bagi Penyidik.
     - Validasi respons partial tabel AJAX.
- Konsekuensi: BAPTwo memiliki sistem pemulihan data yang aman, transparan, terintegrasi audit trail, serta patuh mutlak terhadap larangan *Hard Delete*.

## D-033 — Halaman Seluruh Perkara, Hub Pencarian Multi-Kriteria, dan Pembaruan Struktur Menu Role Pimpinan & Umum (PRD F-38, F-39, F-43)

- Tanggal: 2026-10-17
- Status: Disepakati & Diimplementasikan
- Konteks: Mengimplementasikan PRD F-38, F-39, F-43 untuk menyediakan sarana monitoring perkara komprehensif bagi Pimpinan dan Penyidik. Pimpinan memerlukan akses cepat melihat seluruh perkara aktif beserta ringkasan statusnya dan mencari perkara berdasarkan multi-kriteria secara instan (target respons <= 3 detik sesuai Non-Functional Requirement).
- Keputusan:
  1. **Pembaruan Navigasi Menu Sidebar (`App\Helpers\MenuHelper`)**:
     - Menambahkan dua menu utama ke dalam grup *Menu Utama*:
       - **Seluruh Perkara** (`/seluruh-perkara`) menggunakan ikon Flaticon `layers` (`fi fi-rr-layers`).
       - **Pencarian Perkara** (`/pencarian-perkara`) menggunakan ikon Flaticon `search` (`fi fi-rr-search`).
     - Seluruh peran terautentikasi (Penyidik, Pimpinan, dan Administrator) memiliki akses ke kedua halaman ini (read-only bagi Pimpinan, dengan aksi detail bagi Penyidik/Admin).
  2. **Halaman Seluruh Perkara (`/seluruh-perkara`)**:
     - Ditampilkan 5 Card Ringkasan Statistik Metrik di bagian atas:
       1. *Total Perkara*: Jumlah seluruh Laporan Kejadian & Sprindik terdaftar.
       2. *Dalam Proses*: Berkas perkara yang sedang berjalan (belum selesai/tidak selesai).
       3. *Berkas Selesai*: Berkas yang telah tuntas di Tahap 2 / Putusan Pengadilan.
       4. *Tidak Selesai*: Berkas yang dihentikan via SP3 atau ditutup otomatis oleh scheduler 1 Januari.
       5. *DPO Aktif*: Jumlah berkas/tersangka yang sedang dalam status buron aktif.
     - Tabel overview komprehensif dengan penomoran urut (`No`), nomor Sprindik & LK, TKP & Wilayah, PPNS Utama, Daftar Tersangka & Berkas, Status Berkas & SLA badge, serta `<x-common.table-dropdown>` aksi menuju detail perkara.
     - Terintegrasi `<x-common.table-filter>` dengan dukungan live swap partial AJAX (`X-Filter-Partial: 1`).
  3. **Hub Pencarian Cepat Multi-Kriteria (`/pencarian-perkara`)**:
     - Kotak input pencarian cerdas (`search` / `q`) yang mencakup multi-tabel secara simultan: nomor berkas, nama tersangka, NIK, alias, nomor LK, nomor sprindik, deskripsi kasus, klasifikasi TKP, pasal yang disangkakan, dan nama/NIP PPNS.
     - Shortcut preset chips: *Semua*, *Sedang Berjalan*, *Sudah Selesai*, dan *DPO Aktif*.
     - Filter dimensi lanjutan:
       - Dropdown Tahapan Proses (SPDP s.d. Putusan Pengadilan).
       - Dropdown Status Formal (Dalam Proses, Selesai, Tidak Selesai).
       - Dropdown Status DPO (Semua, DPO Aktif, Bukan DPO).
       - Dropdown Penyidik PPNS Penanggung Jawab.
       - Rentang tanggal terpadu Flatpickr (`tanggal_dari` & `tanggal_sampai`).
     - Tabel hasil pencarian dengan badge indikator total temuan, progress bar, SLA badge, dan tautan langsung ke Detail Perkara.
     - Mendukung live filter partial AJAX `#pencarian-table-partial`.
  4. **Pengujian Komprehensif (`tests/Feature/SeluruhDanPencarianPerkaraTest.php`)**:
     - 9 skenario uji mencakup akses multi-role (Penyidik, Pimpinan, Administrator), validasi 5 kartu statistik, pencarian multi-kriteria NIK/deskripsi/pasal/PPNS, filter status proses dan status formal, preset chips, respons AJAX partial, dan integritas menu navigasi Flaticon.
- Konsekuensi: Pimpinan dan Penyidik kini memiliki akses ringkas, cepat, dan terpusat untuk memonitor seluruh perkara dan mencari berkas perkara secara fleksibel tanpa hambatan operasional.

## D-034 — Dashboard Monitoring Potensi Terlimpah & Peringatan Dini Batas Waktu Akhir Tahun (PRD F-41)

- Tanggal: 2026-10-17
- Status: Disepakati & Diimplementasikan
- Konteks: Mengimplementasikan PRD F-41 mengenai dashboard khusus untuk mengidentifikasi Berkas Perkara yang masih belum Selesai menjelang akhir tahun atau mendekati batas SLA 1 bulan kalender. Tujuannya adalah menyediakan *early warning system* bagi Penyidik dan Pimpinan guna memprioritaskan berkas yang siap dilimpahkan ke Tahap II sebelum terkena penetapan otomatis *Tidak Selesai* pada 1 Januari 00:00.
- Keputusan:
  1. **Submenu & Routing Navigasi (`/potensi-terlimpah`)**:
     - Ditambahkan sebagai submenu di bawah grup menu *Data Perkara* (`App\Helpers\MenuHelper::getPerkaraItems`) bersama *Daftar Perkara* dan *Progress Perkara*.
     - Route `GET /potensi-terlimpah` (`potensi-terlimpah.index`) dapat diakses oleh seluruh peran terautentikasi (Penyidik, Pimpinan, Administrator).
  2. **Banner Edukasi & 5 Kartu Metrik Ringkasan Potensi Terlimpah**:
     - Banner amber peringatan dini di bagian atas menjelaskan konsekuensi otomatis batas 31 Desember 23:59 menuju 1 Januari 00:00.
     - 5 Kartu Ringkasan Metrik:
       1. *Berkas Berjalan*: Total berkas perkara aktif yang belum mencapai status formal selesai/tidak selesai.
       2. *Siap Tahap II (&ge;55%)*: Berkas yang telah mencapai Tahap I (55%) atau P-21 (85%) yang paling siap dilimpahkan.
       3. *Mendekati Batas (&le;7 Hari)*: Berkas dengan sisa waktu SLA 1 - 7 hari menuju deadline.
       4. *Melebihi SLA*: Berkas yang telah melampaui SLA 1 bulan kalender atau akhir tahun (sisa hari < 0).
       5. *Terkendala DPO*: Berkas dengan status DPO aktif yang memerlukan penangkapan tersangka sebelum dapat lanjut ke Tahap II.
  3. **Tabel Cerdas dengan Rekomendasi Tindakan Operasional**:
     - Menampilkan kolom urut `No`, nomor berkas & perkara, tersangka & NIK, PPNS Utama, bar progress, sisa hari SLA (dengan badge dinamis merah/amber/biru), rekomendasi tindakan operasional per kondisi berkas (contoh: *P-21 Lengkap: Segera lakukan Pelimpahan Tahap II*, *Terkendala DPO: Tangkap tersangka untuk membuka Tahap II*, dll.), dan tombol aksi shortcut ke detail perkara dan lanjut tahapan.
     - Terintegrasi penuh dengan toolbar `<x-common.table-filter>` dan partial swap AJAX `#potensi-terlimpah-table-partial` (`X-Filter-Partial: 1`).
  4. **Pengujian Komprehensif (`tests/Feature/PotensiTerlimpahTest.php`)**:
     - Memvalidasi akses multi-role, eksklusi berkas yang sudah selesai, filter kategori urgensi (siap tahap 2, DPO, mendekati batas, kritis), respons AJAX partial, dan keberadaan submenu di MenuHelper.
- Konsekuensi: Penyidik dan Pimpinan memiliki instrumen kendali mutu penanganan perkara menjelang penutupan tahun yang terukur, akuntabel, dan transparan.


