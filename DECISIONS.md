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
       - Pembersihan listener otomatis saat komponen di-*unmount* via lifecycle hook resmi Alpine `destroy()`.
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

## D-035 — Implementasi Revisi Aplikasi Manajemen Perkara (22 Poin)

- Tanggal: 2026-10-17
- Status: Disepakati & Diimplementasikan (Seluruh 22 Poin Selesai Penuh)
- Konteks: Berdasarkan instruksi pemilik aplikasi untuk menyederhanakan entitas perkara, memperketat validasi dua lapis (UI dan server), merestrukturisasi RBAC, mengoptimalkan tata letak dasbor/tabel, menangani error secara terpadu, dan mengekspor rekap perkara 44 kolom ke Excel.
- Keputusan:
  1. **Kelompok A (Login & Sesi)**:
     - Poin 1: Menghapus tombol "Kembali ke dashboard" pada form login (`signin.blade.php`).
     - Poin 16: Captcha login kedaluwarsa setelah 30 detik (`MathCaptchaService`) dengan visual alert auto-refresh. Session idle timeout diatur ke 30 menit tanpa interaksi dengan countdown dan auto-redirect ke login. Fitur "Remember me" beserta persistent token dihapus total.
  2. **Kelompok B (Role & Hak Akses)**:
     - Poin 7: Master Data dihapus total untuk Pemimpin/Umum (menu hidden, controller 403). Untuk Penyidik, Master Data dibatasi hanya Pasal dan Instansi Hukum. Seluruh Master Data lengkap dikelola oleh Admin.
     - Poin 20: Menu dan akses halaman Progres Perkara diblokir untuk Penyidik (hanya untuk Pemimpin & Umum).
  3. **Kelompok C (Form Perkara)**:
     - Poin 3: Legalitas dan Modus Operandi pada TKP diubah menjadi radio button (2 pilihan).
     - Poin 4: Provinsi Kalimantan Selatan (kode 63) bersifat read-only dan otomatis memuat kabupaten/kota Kalsel secara berjenjang.
     - Poin 8: Pemilihan Pasal pada form Perkara menggunakan komponen yang seragam dengan PPNS Anggota (searchable select dengan tag cards yang dapat dihapus).
     - Poin 9: Jaksa Penuntut diinput ketik manual (teks bebas).
     - Poin 17: NIK tersangka divalidasi tepat 16 digit angka di UI (`pattern="[0-9]{16}"`, input sanitizer) dan server (`digits:16`).
  4. **Kelompok D (Tahapan, Dokumen, SP3 & Skema)**:
     - Poin 2 & 18: Seluruh tahapan wajib diisi dan mewajibkan upload dokumen PDF (maksimal 5 MB) pada saat finalisasi / simpan & lanjut. Pengecualian opsional: P-16, P-17P, P-18P, P-19.
     - Poin 5: Menghapus kolom `alasan` pada SP3, disatukan menjadi satu field teks `keterangan` (konsisten dengan DPO).
     - Poin 11 & 13: Perkara dan Berkas Perkara digabung menjadi 1 entitas; identitas penomoran berkas mengikuti nomor Laporan Kejadian (LK).
     - Poin 14: Penguncian LK dan Sprindik yang sudah dipakai agar tidak dapat dipilih ulang pada berkas lain.
     - Poin 15 & 22: SP3 bersifat terminal. Penerbitan SP3 langsung mengubah `status_proses` menjadi 'SP3' dan `status_formal` menjadi 'tidak_selesai', serta mengunci penambahan atau pelanjutan tahapan berikutnya.
     - Poin 21: Tombol menuju tahap berikutnya disembunyikan apabila perkara telah selesai atau berstatus SP3 / tidak selesai.
     - Penghapusan fitur Delete Perkara: Tidak ada fitur, tombol, atau endpoint hapus Perkara / LK / Sprindik.
  5. **Kelompok E (Dashboard & Tabel)**:
     - Poin 6: Tabel daftar berkas diletakkan di posisi paling bawah pada layout dasbor monitoring.
     - Poin 12: Seluruh tabel daftar mendukung horizontal scroll (`overflow-x-auto` dengan lebar minimum) agar kolom terakhir terlihat penuh.
     - Poin 20: Menambahkan kolom "Tahap Saat Ini (% progress)" dengan bobot progress tetap (0%, 15%, 55%, 85%, 100%) dan status "Tidak Selesai" untuk SP3.
  6. **Kelompok G (Error Handling)**:
     - Poin 19: Global error modal `<x-common.error-modal />` menangani error validasi 422, sesi kedaluwarsa 419, akses ditolak 403, tidak ditemukan 404, server error 500, request timeout, dan offline network tanpa silent failure.
  7. **Kelompok F (Poin 10 - Export Excel)**:
     - Mengimplementasikan ekspor rekap perkara 44 kolom ke berkas `.xlsx` (`App\Exports\PerkaraRekapExport` & `PerkaraExportController`) sesuai format resmi:
       - 1 baris = 1 perkara, diurutkan menurut tanggal LK.
       - Header 4 baris: Baris 1 merge horizontal/vertikal baris 1-3 untuk kolom mandiri, Baris 2 sub-grup proses penyidikan, Baris 3 detail kolom, Baris 4 penomoran kolom 1-44.
       - Tipe sel tanggal Excel asli dengan format `dd-mmm-yy` (contoh `11-Mar-26`), nilai kosong `-`, multi-value dipisah baris baru (`\n`) dengan wrap text aktif.
       - Border tipis pada seluruh sel data, header tebal rata tengah, dan freeze pane di bawah baris 4 (sel A5).
       - Modal interaktif `<x-common.export-excel-modal />` dengan 3 tab (Per Tahun, Per Bulan, dan Rentang Tanggal ala pemesanan tiket pesawat) di halaman `Daftar Perkara` dan `Seluruh Perkara`.
       - Hak akses ketat dibatasi hanya untuk Administrator dan Penyidik (Pemimpin dan Umum diblokir via Gate `export-excel`).
- Konsekuensi: Seluruh 22 poin revisi telah terimplementasi secara lengkap, terintegrasi, dan terverifikasi dengan 100% test pass rate (153 tests).

## D-036 — Pembersihan & Perampingan Repositori (Ponytail Audit Paket A, B, C, D)

- Tanggal: 2026-10-18
- Status: Disepakati & Diimplementasikan Penuh
- Konteks: Audit repositori menyeluruh (`/ponytail-audit`) mengidentifikasi dead code, berkas demo bawaan template TailAdmin, package npm yang tidak terpakai, runtime query overhead (auto-seeding), dan media mockup berukuran besar. Pembersihan disepakati dalam 4 paket bertahap.
- Keputusan:
  1. **Paket A (Zero-Risk & Instant Win)**:
     - Menghapus file orphan `config/excel.php` (381 baris dead config).
     - Menghapus prototype service `app/Services/MockStageService.php` (313 baris).
     - Menghapus demo controller `app/Http/Controllers/SidebarController.php` (190 baris).
     - Membersihkan commented code pada `Modal.php`.
     - Menyederhanakan query `whereNull('status_formal')` di `DashboardService.php`.
     - Menghapus orphan view 0-byte `spdp/edit.blade.php` dan `tahap-1/edit.blade.php`.
     - Menghapus demo console command `inspire` dari `routes/console.php`.
  2. **Paket B (Demo Views & Component Classes)**:
     - Menghapus 52 berkas komponen demo template: 5 tabel demo (`basic-tables-*`), 12 komponen form demo (`form-elements/*`), 6 widget eCommerce demo, dan 3 kartu profil demo beserta PHP class-nya.
  3. **Paket C (Frontend Bundle & NPM Dependencies)**:
     - Memangkas 8 npm package yang tidak digunakan: `@floating-ui/dom`, `@popperjs/core`, `apexcharts`, `fullcalendar`, `jsvectormap`, `prismjs`, `swiper`, `temporal-polyfill`.
     - Menghapus skrip demo chart, calendar, dan map di `resources/js/components/`.
     - Merampingkan `resources/js/app.js` ke 21 baris (ukuran JS bundle turun 89% dari 1.27 MB menjadi 134 kB; waktu build dari 4.8s menjadi 1.22s).
     - Merampingkan `resources/css/app.css` dari 1.326 baris menjadi 715 baris (menghapus duplikasi font/CDN Flaticon dan CSS library pihak ketiga).
  4. **Paket D (Refactoring Backend & Scaffolding Cleanup)**:
     - Menghapus runtime auto-seeding overhead (`ensureBaselinePerkara` dan `ensureBaselineTahapan`) dari `PerkaraService`, `TahapanService`, `DashboardService`, dan controller show methods. Memindahkan fixture pengujian ke `tests/TestCase.php` (`seedBaselineCasesAndStages`).
     - Menghapus legacy RTL & Locale switcher: `LocaleController.php`, `SetLocale.php`, registrasi middleware di `bootstrap/app.php`, route `/locale/{locale}` di `routes/web.php`, serta inline script RTL di `app.blade.php` dan `fullscreen-layout.blade.php`. Mengunci aplikasi pada locale `id` dan `dir="ltr"`.
     - Menghapus 15 class PHP component zero-logic di `app/View/Components/` (`common/*`, `form/DatePicker`, `header/*`, `ui/*`, serta view `youtube-embed.blade.php`), mempertahankan `TableDropdown.php`.
     - Memangkas >8 MB media mockup demo: menghapus folder `product/`, `task/`, `video-thumb/`, `support/`, `ai/`, `cards/`, `carousel/`, `grid-image/`, `chat/`, `logistics/`, `country/`, dan avatar mockup `user-01` s.d. `user-37` (hanya mempertahankan `owner.png`).
- Konsekuensi: Repositori BAPTwo menjadi sangat bersih, ringan, cepat, dan bebas dari sisa template TailAdmin, dengan 100% test lulus (155 tests, 1.614 assertions), lint Pint 100% pass, dan build Vite ultra-cepat (1.22s).

---

## D-037 — Eliminasi View Mock Tahapan, Demo Header Dropdown, dan Dead CSS (Ponytail Full Audit)

- Tanggal: 2026-10-18
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan audit ponytail lanjutan, ditemukan sisa view mock statis tahap awal (`spdp/show`, `tahap-1/show`, `p21/show`) berisikan hardcoded data yang tidak terpakai karena seluruh detail tahapan telah dipusatkan pada `berkas-perkara.tahapan.show`. Selain itu, komponen dropdown notifikasi bawaan template (out-of-scope di PRD), form statis dummy `spdp/create`, route/view `signup` internal, dan sisa CSS RTL/library yang telah dihapus masih tertinggal di repositori.
- Keputusan:
  1. Menghapus 3 berkas view prototype tahapan (`spdp/show.blade.php`, `tahap-1/show.blade.php`, `p21/show.blade.php`, total 1.118 baris). Method `show()` pada `SpdpController`, `Tahap1Controller`, dan `P21Controller` dialihkan redirect langsung ke canonical route `berkas-perkara.tahapan.show` (seragam dengan `Tahap2Controller` dan `PutusanPerkaraController`).
  2. Menghapus komponen demo `<x-header.notification-dropdown />` (194 baris) dan panggilannya di `app-header.blade.php`.
  3. Memangkas 140 baris mock dummy form di `spdp/create.blade.php`, dialihkan menggunakan komponen reusable `_select-berkas` (16 baris) yang seragam dengan seluruh tahapan lainnya.
  4. Mempertahankan route `GET /signup` dan view `signup.blade.php` sebagai halaman panduan resmi bagi pengguna untuk menghubungi Admin Tim IT BPOM Kalsel guna permohonan pembuatan akun.
  5. Menghapus 134 baris CSS usang di `resources/css/app.css` (override ApexCharts, jsVectorMap, Swiper, hover sidebar template, dan varian RTL Flatpickr).
- Konsekuensi: Memangkas bersih 1.542 baris dead code dan 4 file usang. Seluruh 161 automated tests (1.650 assertions) lulus 100%, lint Pint lulus sempurna, dan build bundle CSS semakin ramping.

---

## D-038 — Standarisasi 20 Data Perkara Dummy Realistis (DummyDataSeeder)

- Tanggal: 2026-10-19
- Status: Disepakati & Diimplementasikan
- Konteks: Pengguna membutuhkan data dummy yang sesuai persis dengan struktur skema database mutakhir (D-035 / 22 Poin Revisi) dan tersebar secara representatif sebanyak total 20 perkara beragam untuk keperluan demonstrasi dan pengujian UI. Selain itu, data mock lama di `DatabaseSeeder.php` yang tidak lengkap (hanya 5 perkara tanpa tersangka/berkas/dokumen/tahapan) perlu dibersihkan agar pengujian baseline `AuthTest` tetap murni.
- Keputusan:
  1. Membersihkan `DatabaseSeeder.php` agar hanya memuat master data awal dan 3 akun pengembang (`admin@baptwo.test`, `pimpinan@baptwo.test`, `penyidik@baptwo.test`), menjaga invariant `User::count() === 3` pada pengujian `AuthTest`.
  2. Menyusun ulang `database/seeders/DummyDataSeeder.php` dengan 20 perkara komprehensif (`LK/001/I/2026/BPOM` s.d. `LK/020/III/2026/BPOM`) yang mencakup seluruh variasi alur penanganan perkara:
     - 4 Perkara Pra-SPDP (LK baru terdaftar, terbit Sprindik, draft berkas, dll.)
     - 2 Perkara SPDP (1 SPDP Draft, 1 SPDP Final dengan sub-tahap P-16 dan P-17P)
     - 2 Perkara Tahap 1 (1 Tahap 1 Draft, 1 Tahap 1 Final dengan sub-tahap P-18P & P-19)
     - 2 Perkara P-21 / P-21a (1 P-21 dan 1 P-21a)
     - 2 Perkara Tahap 2 (1 Tahap 2 Draft, 1 Tahap 2 Final berstatus formal Selesai)
     - 2 Perkara Putusan Pengadilan (PN Martapura No. 42/Pid.Sus/2026/PN Mtp dan PN Marabahan No. 18/Pid.Sus/2026/PN Mrh)
     - 2 Perkara DPO (1 DPO berstatus Aktif di Batola, 1 DPO Dicabut di Kotabaru)
     - 2 Perkara Khusus (1 Perkara SP3 di Hulu Sungai Tengah dengan status proses SP3 & formal Tidak Selesai, 1 Perkara Warning SLA > 21 hari di Tanah Bumbu)
  3. Mematuhi seluruh konvensi mutakhir D-035:
     - 1 Perkara = 1 Berkas Perkara dengan `nomor_berkas` identik dengan `nomor_laporan_kejadian`.
     - Seluruh TKP dan Tersangka berada di 13 Kota/Kabupaten Kalimantan Selatan (`provinsi_kode = '63'`).
     - NIK tersangka tepat 16 digit.
     - Radio TKP sesuai spesifikasi (`retailer`/`distributor`, `legal`/`ilegal`, `online`/`offline`).
     - Format SP3 menggunakan `keterangan` (bukan alasan).
     - Seluruh dokumen sampel berukuran aman (< 5 MB) dan tersimpan di storage privat `dokumen/sample-bap.pdf`.
  4. Menyediakan metode pembersihan otomatis `cleanupObsoleteDummyCases()` yang idempoten pada `DummyDataSeeder` agar dapat dijalankan berulang kali tanpa memicu benturan constraint unik nomor surat perintah.
  5. Melengkapi master data dummy masing-masing tepat 20 data:
     - **20 Pasal**: Menjangkau tindak pidana bidang kesehatan (UU 17/2023 Pasal 435–440), pangan (UU 18/2012 Pasal 136–142), perlindungan konsumen (UU 8/1999 Pasal 62), narkotika & psikotropika (UU 35/2009 & UU 5/1997), serta KUHP (Pasal 204 & 55).
     - **20 Instansi Hukum**: Kejati Kalsel, 13 Kejari se-Kalsel, Pengadilan Tinggi Banjarmasin, dan 5 Pengadilan Negeri se-Kalsel.
     - **20 Jaksa Penuntut**: Jaksa Penuntut Umum realistis (lengkap NIP & pangkat/golongan) dari seluruh Kejari dan Kejati di Kalimantan Selatan.
- Konsekuensi: Total perkara dummy tepat 20 kasus, serta Pasal (20), Instansi Hukum (20), dan Jaksa Penuntut (20) terisi lengkap dan realistis. Sebanyak 161 automated test (1.650 assertions) tetap 100% lulus dan linting Pint 100% hijau.

---

## D-039 — Penambahan Kolom Nama Sarana pada Alamat TKP & Standardisasi Alamat Lengkap Ekspor Excel

- Tanggal: 2026-10-19
- Status: Disepakati & Diimplementasikan (via interview /grill-me)
- Konteks: Pada ekspor rekap perkara Excel (.xlsx), pengguna menginginkan alamat Tersangka (Kolom L) dan alamat TKP (Kolom P) berupa alamat lengkap yang utuh (Detail Jalan, RT/RW, Desa/Kelurahan, Kecamatan, Kabupaten/Kota, Provinsi). Selain itu, nama TKP (Kolom O) sebelumnya menggunakan fallback hierarki yang rumit (`nama_sarana ?? deskripsi ?? TKP Kabupaten`). Pengguna meminta agar dibuatkan kolom `nama` di tabel `alamat` dan input nama pada formulir TKP, di mana ekspor Excel langsung mengambil nama sarana dari kolom tersebut (atau `-` jika kosong).
- Keputusan:
  1. Menambahkan kolom `nama VARCHAR(150) NULL` pada tabel `alamat` melalui migration `2026_10_19_000001_add_nama_to_alamat_table.php` dan mendaftarkannya pada `$fillable` model `Alamat`.
  2. Menambahkan accessor `getAlamatLengkapAttribute()` pada model `Alamat` yang mengembalikan `$this->formatted()` (menggabungkan detail jalan, RT/RW, desa/kelurahan, kecamatan, kabupaten/kota, dan provinsi).
  3. Menambahkan field input opsional `tkp_nama` pada formulir TKP di `resources/views/pages/perkara/form.blade.php`, menampilkannya di halaman detail `pages/perkara/show.blade.php`, serta mendukungnya pada `SavePerkaraBerkasRequest` dan `PerkaraService::resolveAlamatPayload()`.
  4. Menyederhanakan penulisan sel pada `PerkaraRekapExport.php`:
     - Kolom L (Alamat Tersangka): `$tersangka?->alamat?->alamat_lengkap ?: '-'`
     - Kolom O (Nama TKP): `$tkp?->alamat?->nama ?: '-'`
     - Kolom P (Alamat TKP): `$tkp?->alamat?->alamat_lengkap ?: '-'`
  5. Menyesuaikan seeder `DummyDataSeeder.php` agar 20 data perkara dummy otomatis mengisi nama sarana TKP ke kolom `nama` pada alamat TKP.
- Konsekuensi: Format ekspor Excel Kolom L, O, dan P kini sepenuhnya deterministik, bersih, dan sesuai spesifikasi. Rangkaian test `composer test` (162 tests, 1.653 assertions) dan `php vendor/bin/pint --test` (141 files) 100% lulus.

---

## D-040 — Perbaikan Layout Viewport Zoom 125% & Sinkronisasi Dynamic Offset Sidebar

- Tanggal: 2026-10-19
- Status: Disepakati & Diimplementasikan
- Konteks: Pada zoom browser 125% (lebar efektif ~1520px, tepat di bawah breakpoint `2xl` 1536px), ditemukan sejumlah anomali antarmuka:
  1. Judul halaman dan kolom "No" pada tabel terpotong karena offset kiri wrapper konten tidak sinkron dengan lebar aktual sidebar (terutama saat collapsed).
  2. Muncul strip putih di tepi kanan layar dan scrollbar horizontal karena wrapper layout utama tidak mengisi lebar penuh viewport.
  3. Elemen tabel dan kartu statistik meluap melewati wrapper kontainer.
  4. Ikon di bagian atas sidebar tepat di bawah header terpotong separuh akibat padding/margin offset scroll yang tidak pas.
- Keputusan:
  1. Mengganti offset margin statis (`xl:ml-[290px]`, `2xl:ml-[90px]`) pada layout utama (`app.blade.php`) dengan padding dinamis terikat state Alpine `$store.sidebar` (`isExpanded`, `isHovered`, `isCollapsed`).
  2. Memastikan container halaman menggunakan `w-full max-w-full overflow-x-hidden` pada wrapper terluar tanpa menyentuh tag `body` atau `html`.
  3. Memastikan setiap tabel berada di dalam wrapper `overflow-x-auto` yang mandiri sehingga scrolling horizontal tabel tidak merusak layout global.
  4. Menyelaraskan padding atas pada navigasi sidebar (`sidebar.blade.php`) agar logo dan menu item teratas tidak saling tumpang tindih dengan header.
- Konsekuensi: Layout stabil di seluruh tingkat zoom browser (100%, 110%, 125%, 150%) dan berbagai resolusi layar (390px s.d. 1920px), tanpa scrollbar horizontal global.

---

## D-041 — Penguncian Dependensi PhpSpreadsheet, Ekstensi Zip PHP, dan Konfigurasi `platform-check: false`

- Tanggal: 2026-10-19
- Status: Disepakati & Diimplementasikan
- Konteks: Ekspor rekap perkara 44 kolom (.xlsx) memerlukan library `phpoffice/phpspreadsheet`. Sebelumnya library ini belum tercatat di `composer.json` sehingga hilang saat `composer update` dijalankan, memicu error `Class not found`. Selain itu, ekstensi `zip` di PHP Laragon perlu diaktifkan, dan Composer secara default menyertakan `platform_check` yang menolak PHP 32-bit (akibat dependensi `zipstream-php` yang mencatat `php-64bit`), padahal CLI default sistem operasi pengguna masih mengarah ke `C:\php\php.exe` (32-bit).
- Keputusan:
  1. Mencatat secara permanen dependensi `"phpoffice/phpspreadsheet": "^5.10"` pada `composer.json` dan menguncinya di `composer.lock`.
  2. Mengaktifkan ekstensi `extension=zip` pada konfigurasi `php.ini` Laragon.
  3. Menambahkan pengaturan `"platform-check": false` pada blok `"config"` di `composer.json` agar Composer tidak memblokir eksekusi `php artisan` di lingkungan CLI 32-bit maupun 64-bit lokal.
- Konsekuensi: `composer update` tidak akan menghapus PhpSpreadsheet lagi; seluruh rangkaian 162 tes dan perintah `php artisan` dapat dijalankan dengan lancar di terminal PowerShell standar pengguna maupun di terminal Laragon.
- Koreksi (D-044): `platform-check: false` hanya mematikan pemeriksaan saat autoload, bukan pemeriksaan syarat paket ketika `composer install`. Karena itu penolakan `php-64bit` pada dependensi `zipstream-php` tetap muncul dan baru teratasi dengan memakai PHP CLI 64-bit, bukan dengan flag tersebut.

---

## D-042 — Adopsi Aturan Anti AI-Slop dan Standar Craftsmanship UI/Copywriting

- Tanggal: 2026-10-19
- Status: Disepakati & Diimplementasikan
- Konteks: Untuk menjaga kualitas antarmuka, keaslian konten, dan kenyamanan pengguna internal BPOM, aplikasi BAPTwo harus terhindar dari artefak generik AI (AI slop), seperti copywriting klise dengan buzzword berlebihan, tanda em dash (`—`), tombol/link mati (dead controls), desain yang hanya mendukung *happy path*, gradient ungu-biru generik tanpa tujuan fungsional, dan kebocoran istilah internal teknis pengembang di antarmuka pengguna.
- Keputusan:
  1. Mengadopsi prinsip standar Craftsmanship antislop (C-1 s.d. C-5) ke dalam `AGENTS.md` (bagian 6 & 11) dan memasang blok pointer antislop resmi.
  2. Melarang keras penggunaan tanda baca em dash (`—`) dalam teks UI/copywriting. Menggantinya dengan tanda baca natural (koma, titik, titik dua, atau kurung).
  3. Menolak seluruh buzzword generik AI ("revolusioner", "seamless", "canggih", "next-gen", dsb.) dan menegakkan terminologi hukum operasional yang baku dan lugas.
  4. Mewajibkan kelengkapan fungsional: setiap elemen interaktif wajib berfungsi nyata atau dihilangkan; melarang desain *happy path only* dengan mewajibkan 3 state (data ada, empty state informatif, dan error/loading state).
  5. Menegakkan standar visual dan aksesibilitas: dilarang gradient generik tanpa tujuan, wajib kontras rasio minimal 4.5:1 (WCAG AA), dan wajib icon Flaticon kontekstual.
- Konsekuensi: Seluruh pekerjaan antarmuka, komponen Blade, dan teks copywriting yang dihasilkan agent wajib mematuhi panduan ini. Teks UI tetap alami, profesional, dan bersih dari jargon internal.

---

## D-043 — Kewajiban Penyelarasan Rencana via Grill-Me Sebelum Eksekusi Kode

- Tanggal: 2026-10-19
- Status: Disepakati & Diimplementasikan
- Konteks: Mencegah eksekusi sepihak dan asumsi liar oleh agent AI tanpa konfirmasi pengguna. Pengguna menghendaki agar setiap ada ide, task, atau perubahan yang akan dilakukan, agent tidak langsung mengeksekusi kode, melainkan mewawancarai dan menyamakan persepsi terlebih dahulu menggunakan skill /grill-me.
- Keputusan:
  1. Menetapkan larangan mutlak eksekusi langsung tanpa persetujuan eksplisit pada `AGENTS.md` (bagian 6 & 8).
  2. Sebelum menyentuh kode, skema database, atau tampilan antarmuka, agent WAJIB memaparkan rancangan, mengajukan pertanyaan terarah, dan menyajikan opsi/alternatif keputusan secara interaktif menggunakan skill `/grill-me` (dan tool `ask_question`).
  3. Eksekusi kode hanya boleh dilakukan setelah rencana dan opsi disetujui secara eksplisit oleh pengguna.
- Konsekuensi: Agent selalu memvalidasi arah implementasi terlebih dahulu; tidak ada kode spekulatif atau perubahan mendadak yang tidak disepakati bersama.

---

## D-044 — PHP CLI 64-bit sebagai Prasyarat Build dan Penghapusan `--ignore-platform-reqs` di CI

- Tanggal: 2026-10-19
- Status: Disepakati & Diimplementasikan
- Konteks: Run GitHub Actions untuk workflow `Laravel` gagal di langkah Install Dependencies dengan pesan `Your lock file does not contain a compatible set of packages. Please run composer update.` (exit code 2, 11 detik). Penelusuran: `composer.lock` mengunci `maennchen/zipstream-php` 3.2.2 yang mensyaratkan `php-64bit ^8.3`, sementara workflow saat itu memakai PHP 8.2. Lock tersebut terbentuk di mesin pengembang yang PHP CLI-nya 32-bit (`C:\php\php.exe`, `PHP_INT_SIZE=4`), sehingga resolusi dependensi dijalankan dengan syarat platform diabaikan. Pengaturan `platform-check: false` pada D-041 tidak menutup kasus ini karena flag tersebut hanya memengaruhi pemeriksaan saat autoload, bukan pemeriksaan dependency saat `composer install`.
- Keputusan:
  1. Menetapkan PHP CLI 64-bit (build x64 Laragon, mis. `php-8.4.25-Win32-vs17-x64`) sebagai lingkungan resmi untuk Composer, Artisan, Pint, dan Pest. PHP 32-bit tidak lagi dipakai untuk perintah tersebut.
  2. Menghapus `--ignore-platform-reqs` dari langkah `composer install` di `.github/workflows/laravel.yml` agar CI kembali memverifikasi kecocokan lock dengan platform, sehingga regresi serupa langsung terdeteksi.
  3. `composer.lock` tidak diubah: `maennchen/zipstream-php` 3.2.2 dan `psr/http-message` 2.0 dipertahankan karena terpasang bersih di PHP 8.4 64-bit.
  4. Menambahkan prasyarat PHP 64-bit, perintah pemeriksaan `PHP_INT_SIZE`, dan contoh path build x64 pada `README.md`.
- Konsekuensi: `composer install` polos berjalan tanpa flag di lokal (x64) maupun di runner Ubuntu 64-bit; lock yang tidak lagi bisa dipasang di 64-bit akan langsung menggagalkan CI. Pengguna yang tetap memakai PHP 32-bit menerima pesan error platform dari Composer dan harus beralih ke build x64.

---

## D-045 : Pemisahan Modal Kompleks sebagai Domain Feature Partial

- Tanggal: 2026-10-20
- Status: Disepakati & Diimplementasikan
- Konteks: View formulir perkara (`pages/perkara/form.blade.php`) dan halaman profil (`pages/profile.blade.php`) sebelumnya membengkak hingga ratusan/ribuan baris karena dialog modal (modal pemilihan alamat tersangka bertingkat, modal update password, modal edit data diri) tertanam inline di template utama. Di sisi lain, membuat generic abstraction `<x-ui.modal>` berbasis slot murni berisiko over-engineering dan menimbulkan benturan scope reaktivitas Alpine.js.
- Keputusan:
  1. Modal kompleks dipisahkan menjadi file domain feature partial terdedikasi menggunakan awalan underscore (`_`) langsung pada folder halaman terkait (misal: `resources/views/pages/perkara/_modal-alamat-tersangka.blade.php`, `resources/views/pages/profile/_modal-update-akun.blade.php`, dan `_modal-edit-profil.blade.php`).
  2. Partial modal disisipkan menggunakan `@include('...')` di dalam konteks view parent sehingga tetap terikat rapi dalam lingkup `x-data` dan DOM Alpine.js tanpa overhead generic wrapper.
  3. Mengurangi ukuran template utama secara drastis (form perkara berkurang ~175 baris, profile berkurang ~335 baris) dengan keterbacaan kode yang jauh lebih baik.
- Konsekuensi: Struktur kode lebih modular dan mudah dirawat. Pengembang dapat mengedit dialog modal secara terisolasi tanpa khawatir merusak tata letak formulir utama.

---

## D-046 : Pratinjau Modal Lightbox Foto Profil Pengguna

- Tanggal: 2026-10-20
- Status: Disepakati & Diimplementasikan
- Konteks: Pada halaman Profil Saya (`/profile`), foto profil pengguna sebelumnya hanya berupa elemen gambar avatar pasif tanpa interaktivitas untuk melihat foto beresolusi penuh.
- Keputusan:
  1. Menjadikan avatar foto profil interaktif dengan kursor pointer, efek zoom halus, dan overlay ikon pencarian bertuliskan "Lihat Foto".
  2. Klik pada avatar membuka dialog modal lightbox terdedikasi (`resources/views/pages/profile/_modal-foto-profil.blade.php`) sesuai standar partial D-045.
  3. Modal menampilkan foto profil ukuran besar (340px), nama pengguna berukuran tebal (`text-title-sm sm:text-title-md`), jabatan yang jelas, badge hak akses, bidang, serta rincian pangkat dan golongan pegawai.
  4. Menyertakan penutupan ramah pengguna: tombol Tutup, silang (x), klik backdrop luar, dan tombol Escape keyboard.
- Konsekuensi: Pengguna dapat memeriksa kejelasan foto identitas kepegawaian mereka secara langsung dan nyaman tanpa meninggalkan halaman profil.

---

## D-047 : Arsitektur Modal Pratinjau PDF Terpadu (In-App PDF Viewer)

- Tanggal: 2026-10-20
- Status: Disepakati & Dalam Implementasi
- Konteks: Pemeriksaan berkas legal PDF (Laporan Kejadian, Sprindik, SPDP, Tahapan, DPO, dan SP3) sebelumnya selalu membuka tab peramban baru via `route('dokumen.preview', $doc)`. Hal ini membuat alur kerja penyidik dan pimpinan terpecah karena harus bolak-balik antar-tab peramban saat memverifikasi berkas perkara.
- Keputusan:
  1. Membangun komponen global `<x-common.pdf-preview-modal />` di master layout `resources/views/layouts/app.blade.php` yang mendengarkan event Alpine window `@open-pdf-modal.window`.
  2. Menerapkan pola *progressive enhancement* dengan delegated click listener pada atribut `data-pdf-preview`: klik kiri utama membuka viewer modal in-app yang lega (`max-w-5xl`, `h-[82vh]`), sedangkan Ctrl+Klik/klik kanan tetap mempertahankan perilaku native browser tab baru.
  3. Header modal menyediakan kendali lengkap: nama dokumen, tombol unduh (`dokumen.download`), tombol buka tab baru (`dokumen.preview`), dan tombol tutup.
  4. Menyediakan indikator loading saat iframe memuat dan fallback informatif jika peramban (misal browser perangkat bergerak) membatasi penyematan iframe PDF.
- Konsekuensi: Penyidik dan pimpinan dapat memeriksa keabsahan dokumen perkara secara instan di dalam satu alur kerja terpadu tanpa kehilangan konteks halaman.

---

## D-048 : Upgrade Donut Chart Monitoring Perkara, Largest Remainder Method, 4 Sub-kategori SLA, Filter Periode AJAX, dan Drill-down Terfilter

- Tanggal: 2026-10-20
- Status: Disepakati & Diimplementasikan
- Konteks: Kartu dashboard "Perkara Tahun Berjalan" sebelumnya menggunakan teks statis, catatan kaki menyebutkan kategori "Belum Dimulai" yang membingungkan, perhitungan persentase berisiko tidak mencapai 100% akibat pembulatan integer, warna merah pada "Tidak Selesai" memberi konotasi error pada penghentian sah (SP3), dan belum memiliki pecahan SLA, filter periode reaktif, maupun navigasi drill-down.
- Keputusan:
  1. **Agregasi Server-side Tunggal & Largest Remainder Method (Hare-Niemeyer)**:
     - `DashboardService::getCaseStatusData(?int $tahun, ?int $bulan)` mengagregasi data status formal (`Selesai`, `Dalam Proses`, `Tidak Selesai`) dalam satu query SQL efisien dengan `CASE WHEN`, menggunakan acuan tanggal Laporan Kejadian (LK).
     - Persentase dihitung dengan Largest Remainder Method (`calculatePercentages`), menjamin total selalu tepat 100% tanpa sisa pembulatan desimal.
     - Kategori "Belum Dimulai" dihapus dari catatan dan tooltip karena seluruh perkara aktif berstatus "Dalam Proses".
  2. **Indikator Aksesibilitas & Non-Warna**:
     - Warna "Tidak Selesai" (termasuk SP3) dialihkan ke warna abu-abu netral (`stroke-gray-400 dark:stroke-gray-500`) karena SP3 adalah tahap akhir yang sah, bukan kesalahan.
     - Setiap baris legend dan sub-kategori dilengkapi icon Flaticon non-warna untuk mendukung aksesibilitas pengguna buta warna.
     - Catatan kaki panjang digantikan oleh tombol info (ⓘ) dengan tooltip popover di header kartu.
     - Tanggal pembaruan dinamis ("Diperbarui: DD MMM YYYY, HH:mm WITA") menggantikan teks statis.
     - Dilengkapi empty state ("Belum ada perkara pada periode ini"), loading skeleton, `aria-label`, dan teks tersembunyi untuk screen reader.
  3. **Pecahan 4 Sub-Kategori SLA pada "Dalam Proses"**:
     - Ditampilkan 4 sub-baris kecil:
       a. Belum Ada SPDP (Pra-SPDP / `sla_deadline IS NULL`)
       b. Dalam SLA (> 7 hari)
       c. Mendekati Batas (≤ 7 hari / konstanta `AMBANG_MENDEKATI_SLA_HARI = 7`)
       d. Melewati SLA (`sla_deadline < today`)
     - Jumlah keempat sub-baris dijamin selalu persis sama dengan jumlah total "Dalam Proses".
  4. **Interaktivitas, Filter Periode AJAX & Drill-Down**:
     - Dropdown Tahun dan Bulan di header kartu; perubahan periode memicu pembaruan AJAX ke endpoint `Route::get('/dashboard/case-status')` tanpa reload halaman penuh.
     - Dilengkapi indikator pembanding terhadap tahun sebelumnya ("▲ X perkara dibanding {tahun - 1}").
     - Hover pada segmen chart menampilkan tooltip dinamis di tengah donut dan `<title>` browser.
     - Klik pada segmen donut, baris legend, atau sub-baris SLA langsung mengarahkan ke `/seluruh-perkara` terfilter (query string: `tahun`, `bulan`, `status`, `sla`) yang dapat diakses oleh seluruh peran (Penyidik, Pimpinan, Administrator) tanpa hambatan permission.
- Konsekuensi: Dashboard monitoring menyajikan proporsi penanganan perkara secara akurat, informatif, dan interaktif dengan aksesibilitas tinggi dan beban query database yang sangat ringan.

---

## D-049 : Standarisasi Tipografi Lokal (Inter Variable & JetBrains Mono 500) dan Angka Tabular (tabular-nums)

- Tanggal: 2026-10-20
- Status: Disepakati & Diimplementasikan
- Konteks: Aplikasi sebelumnya memuat font Google Fonts Outfit melalui CDN eksternal `@import url('https://fonts.googleapis.com/css2?family=Outfit...')`. Hal ini membuat font rentan gagal dimuat di lingkungan internal/offline tanpa akses internet. Selain itu, angka-angka statistik dan nomor perkara belum memiliki pemisahan tipografi yang jelas dan berisiko pergeseran lebar kolom (*layout shift*).
- Keputusan:
  1. **Instalasi Lokal via NPM (Offline-Ready 100%)**:
     - Menginstal paket `@fontsource-variable/inter` dan `@fontsource/jetbrains-mono` secara lokal melalui npm.
     - Mengimpor `@fontsource-variable/inter` dan `@fontsource/jetbrains-mono/500.css` langsung di `resources/css/app.css` agar diproses dan di-bundle oleh Vite ke folder aset lokal (`public/build/assets/`).
     - Menghapus seluruh import CDN Google Fonts Outfit lama agar tidak terjadi duplikasi unduhan font.
  2. **Konfigurasi Tailwind CSS v4 Theme**:
     - Mendeklarasikan `--font-sans: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;` di blok `@theme` untuk seluruh teks UI, heading, form, tabel, dan tombol.
     - Mendeklarasikan `--font-mono: 'JetBrains Mono', ui-monospace, monospace;` di blok `@theme` khusus untuk kode, nomor identitas perkara, dan NIK.
     - Menerapkan `font-sans antialiased` pada `body` di CSS dan master layout (`layouts/app.blade.php`, `layouts/fullscreen-layout.blade.php`).
  3. **Penegasan Angka Tabular (`tabular-nums`) & Monospace Nomor Perkara**:
     - Menerapkan utilitas `tabular-nums` pada seluruh angka metrik statistik dashboard, persentase progress, perbandingan tahun, kolom `No` tabel, dan tanggal agar lebar angka sejajar rapi.
     - Menerapkan `font-mono` (bobot 500) pada seluruh nomor perkara legal (Nomor LK, Nomor Sprindik, Nomor Berkas, Nomor SPDP, Nomor Tahapan, Nomor Putusan, dan NIK).
  4. **Preservasi Kontras Bobot Huruf**:
     - Tidak ada penggunaan font berbobot tipis (`font-light` / `font-thin`) pada background gelap. Seluruh teks isi memakai bobot 400, label/header memakai 500-600, dan judul/angka besar memakai 600-700.
- Konsekuensi: Aplikasi sepenuhnya independen dari koneksi internet untuk aset font, tampilan teks UI menjadi lebih tajam, keterbacaan nomor perkara meningkat drastis, dan tata letak angka tidak lagi bergeser saat nilai berubah.

---

## D-050: Sistem Notifikasi Pop-up (Toast) Terpadu Auto-Dismiss 3 Detik Berbasis CSS Animation

- Tanggal: 2026-10-21
- Status: Disepakati & Diimplementasikan
- Konteks: Aplikasi sebelumnya menggunakan modal pop-up tengah layar yang menghalangi konten saat terjadi error atau login sukses (dengan timer 10 detik), modal kesalahan input pada halaman login, alert bawaan browser (`window.alert`) pada ekspor modal, serta komponen `x-common.flash-alert` yang kurang informatif dan statis. Pengguna membutuhkan notifikasi pop-up (toast) yang otomatis hilang setelah 3 detik, informatif, menarik, tidak rentan throttling latar belakang browser, serta ramah aksesibilitas dan aman dari serangan XSS.
- Keputusan:
  1. **Komponen Reusable `<x-common.toast>` & Alpine.js Manager (`resources/js/components/toast.js`)**:
     - Wadah toast ditempatkan di bawah header (`fixed top-20 right-4 sm:right-6 left-4 sm:left-auto sm:w-[380px] z-999999`) pada layout dashboard berautentikasi dan `top-5` pada layout fullscreen, memastikan kartu toast tidak tertutup oleh header (tinggi 64px `z-99999`) serta tidak menutupi tombol tema atau widget kalender.
     - Penumpukan rapi dibatasi maksimal 4 toast terlihat bersamaan dengan pesan terbaru di posisi teratas. Ketika toast ke-5 masuk, toast tertua langsung dihapus secara otomatis.
     - Dilengkapi mekanisme pencegahan duplikasi pesan identik (tipe, judul, dan isi yang sama) dalam rentang 1,5 detik (1500 ms).
     - Menyediakan fungsi JavaScript global `window.toast({ type, title, message, duration })` serta helper cepat `window.toast.success`, `window.toast.error`, `window.toast.warning`, dan `window.toast.info`.
  2. **Timing & Progress Bar Berbasis CSS Animation (@keyframes)**:
     - Menghindari penggunaan interval JavaScript 25ms agar timer tidak melenceng saat tab browser berada di latar belakang dan tidak memicu re-render Alpine per tick.
     - Progress bar menyusut dari 100% ke 0% menggunakan CSS animation `@keyframes toast-shrink` dengan inline style `animation-duration: ...ms; animation-timing-function: linear; animation-fill-mode: forwards;`.
     - Jeda (pause) dan lanjut (resume) otomatis ditangani melalui CSS murni: kelas `group-hover:[animation-play-state:paused]` dan `group-focus-within:[animation-play-state:paused]`, sehingga animasi berhenti seketika saat pointer mouse melintas atau tombol keyboard memfokuskan toast, dan melanjutkan sisa waktu saat keduanya dilepas.
     - Pada perangkat layar sentuh (touch), ketukan pada badan kartu toast memicu toggle status pause. Tombol tutup (X) selalu mengeksekusi penutupan instan.
     - Penutupan otomatis dieksekusi saat event DOM `@animationend` terpanggil pada elemen progress bar.
  3. **Kontrak Pesan Controller (String & Array)**:
     - Komponen Blade mendukung format string (dengan fallback judul default per tipe: "Berhasil", "Gagal", "Perhatian", "Informasi") maupun format array `['title' => '...', 'message' => '...']`.
     - Seluruh Controller backend distandarisasi menggunakan format array dengan copywriting operasional hukum yang spesifik (judul = aksi + hasil, isi = identitas perkara/nomor surat + tindak lanjut).
     - Pesan login sukses menyapa nama pengguna secara personal: "Selamat datang kembali di BAPTwo, {nama}."
  4. **Pembersihan Kode Mati & Penanganan Khusus Sesi Habis**:
     - Komponen modal error lama `error-modal.blade.php` dan `flash-alert.blade.php` dihapus bersih dari proyek.
     - Peringatan sesi berakhir (`?session_expired=1`) ditampilkan sebagai banner inline yang stabil di halaman login tanpa modal dan tanpa toast 3 detik, agar pengguna tidak kehilangan informasi.
     - Panggilan `window.alert` native pada `export-excel-modal.blade.php` digantikan dengan `window.toast.warning(...)`.
  5. **Keamanan XSS & Aksesibilitas**:
     - Serialisasi payload data awal menggunakan `@js()` dan rendering teks judul/pesan menggunakan direktif Alpine `x-text` (dilarang `x-html`).
     - Aksesibilitas menggunakan `role="alert"` untuk error dan warning, serta `role="status"` untuk success dan info, tanpa konflik atribut `aria-live`.
- Konsekuensi: Seluruh notifikasi aplikasi kini terpadu dalam satu sistem toast modern, konsisten dengan tema kartu gelap BAPTwo, performa rendering optimal tanpa membebani browser, dan aman dari kerentanan XSS.

---

## D-050 : Standar Keamanan, Performa Bebas N+1, dan Efisiensi Animasi GPU Compositor

- Tanggal: 2026-10-21
- Status: Disepakati
- Konteks: Memperkuat postur keamanan aplikasi penanganan perkara internal BPOM Kalsel, menjamin kecepatan muat halaman (LCP ≤ 2.5 detik, CLS ≤ 0.1, INP ≤ 200 ms) tanpa query N+1, serta menetapkan arsitektur animasi GPU compositor yang konsisten dan ramah aksesibilitas.
- Keputusan:
  1. **Keamanan (Otorisasi Record, Sanitasi Formula Excel, Proteksi Brute-force & Error Generik)**:
     - Seluruh akses dokumen (unduh & pratinjau PDF) wajib memeriksa otorisasi record pengguna untuk mencegah kerentanan IDOR.
     - Ekspor data Excel wajib menetralisasi karakter formula (`=`, `+`, `-`, `@`) pada teks dengan tanda petik tunggal (`'`).
     - Percobaan login dibatasi rate limiter (5 kali per menit per email dan IP) dengan pesan kesalahan generik ("Email atau kata sandi salah") untuk mencegah user enumeration.
     - Header keamanan HTTP diterapkan: `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-Frame-Options: SAMEORIGIN`.
     - Catat audit trail untuk aksi sensitif termasuk ekspor perkara dan kegagalan login.
  2. **Performa (Pencegahan N+1, Agregasi Tunggal, Paginasi Penuh, & Pemuatan Aset Ramping)**:
     - Mengaktifkan `Model::preventLazyLoading(! app()->isProduction())` di `AppServiceProvider` untuk menangkap potensi N+1 sejak tahap pengembangan.
     - Mengubah pemuatan seluruh tabel master data menjadi terpaginasi atau ter-cache daripada `->get()` tanpa batas.
     - Angka ringkasan dan statistik dashboard dihitung melalui query agregat tunggal terikat parameter binding, didukung cache TTL singkat.
     - Ikon Flaticon UIcons CDN dirampingkan hanya memuat paket `uicons-regular-rounded.css` (`fi-rr`), menghapus paket solid dan bold yang tidak terpakai.
     - Seluruh elemen gambar wajib menyertakan atribut `width` dan `height` eksplisit guna mencegah pergeseran tata letak (*Cumulative Layout Shift*).
  3. **Animasi (GPU Compositor Only, Token Theme, & Reduced Motion)**:
     - Seluruh animasi UI dibatasi hanya pada properti `transform` dan `opacity`. Melarang animasi properti layout (`width`, `height`, `margin`, `padding`, `top`, `left`, `box-shadow`).
     - Progress bar diwajibkan menggunakan `transform: scaleX()` dengan `transform-origin: left`.
     - Melarang penggunaan kelas `transition-all` secara global, mewajibkan deklarasi properti transisi spesifik.
     - Menetapkan token durasi dan kurva easing konsisten pada Tailwind v4 `@theme` (`cubic-bezier(0.22, 1, 0.36, 1)` untuk in, `cubic-bezier(0.4, 0, 1, 1)` untuk out).
     - Menegakkan `prefers-reduced-motion: reduce` secara global di CSS utama dan menyematkan `scrollbar-gutter: stable` pada elemen akar `html`.
- Konsekuensi: Aplikasi BAPTwo memiliki fondasi keamanan hukum yang kokoh, performa tangguh bebas lag atau kebocoran memori, dan tampilan visual yang halus tanpa membebani GPU atau CPU klien.

---

## D-051 : Penanganan Kompatibilitas ZipStream, Eager Loading Bertingkat, Dropdown Alamat, Istilah Pasal, dan Timer Toast pada Reduced-Motion

- Tanggal: 2026-10-21
- Status: Disepakati
- Konteks: Menyelesaikan 6 kendala operasional dan fungsional pada BAPTwo yang mencakup lazy loading violation, kegagalan ekspor file XLSX biner, susunan layout tabel tersangka/berkas, bug event input alamat dinamis, inkonsistensi label pasal di antarmuka, serta toast notifikasi yang menutup terlalu cepat di lingkungan dengan fitur reduced-motion aktif.
- Keputusan:
  1. **Eager Loading Berkas & Tahapan (Poin 1)**:
     - Meng-eager load relasi `tahapan` dan `tersangkas.berkasPerkara.sp3` pada `PerkaraService::getProgressPerkaraList()` serta memanfaatkan relasi ter-load pada method `BerkasPerkara::hasSp3()`, memastikan ketaatan pada `Model::preventLazyLoading(true)` di seluruh alur.
  2. **Kompatibilitas Ekspor Biner XLSX & ZipStream (Poin 2)**:
     - Melakukan pembersihan output buffer (`ob_end_clean()`) sebelum streaming file Excel serta menambahkan casting tipe integer eksplisit `(int) hexdec(...)` pada kalkulasi CRC32 di `vendor/maennchen/zipstream-php/src/File.php` agar mencegah `TypeError` float-to-int pada arsitektur PHP 32-bit.
  3. **Tata Letak Vertikal Tersangka & Berkas (Poin 3)**:
     - Menyusun nama tersangka dan nomor berkas perkara secara vertikal (`flex flex-col gap-0.5`), menampilkan nomor berkas perkara di baris bawah dengan badge abu-abu dan indentasi `ps-4`.
  4. **Penanganan Event Dropdown Wilayah Alamat (Poin 4)**:
     - Memperbaiki parsing event pada `searchable-select` untuk memprioritaskan `event.detail` di samping `event.target.value`, mencegah reset otomatis nilai provinsi saat memilih opsi, dan menonaktifkan pembukaan paksa dropdown kabupaten (`autoOpen = false`).
  5. **Standardisasi Istilah Antarmuka Pasal (Poin 5)**:
     - Mengubah semua teks tampilan pengguna dari "Pasal Berlapis" menjadi "Pasal yang Disangkakan" (pada form, detail, header) atau "Pasal" (pada tabel ringkas), tanpa mengubah nama kolom database atau struktur internal.
  6. **Pengecualian Timer Toast pada Reduced-Motion (Poin 6)**:
     - Menandai progress bar toast dengan atribut `data-toast-progress` dan mengecualikannya dari selektor override `0.01ms` pada `@media (prefers-reduced-motion: reduce)` di `resources/css/app.css`.
     - Memperbaiki listener `@animationend` pada komponen toast agar memvalidasi `animationName === 'toast-shrink'` dan elapsed time untuk mencegah penutupan prematur.
- Konsekuensi: Halaman dan fitur aplikasi berjalan stabil, andal di berbagai konfigurasi sistem operasi/browser (termasuk mode reduced-motion), dan menyajikan teks antarmuka yang bersih serta operasional.

---

## D-052 : Perhitungan Data Riil Rata-rata Hari Kerja Penyelesaian Tahapan Dashboard BAPTwo

- Tanggal: 2026-10-21
- Status: Disepakati
- Konteks: Menggantikan data tiruan (hardcoded dummy) pada method `DashboardService::getStageAverageDays()` dengan perhitungan analitik data riil database untuk kartu "Rata-rata Penyelesaian per Tahap" pada Dashboard BAPTwo.
- Keputusan:
  1. **Acuan Durasi Antar-Tahap**:
     - Mengukur selisih hari antar-tahap berurutan berdasarkan `tanggal_surat` resmi dokumen:
       - **SPDP**: `tanggal_surat_perintah` (Sprindik) ke `tanggal_surat` SPDP.
       - **TAHAP I**: `tanggal_surat` SPDP ke `tanggal_surat` TAHAP I.
       - **P-21**: `tanggal_surat` TAHAP I ke `tanggal_surat` P-21/P21a.
       - **TAHAP II**: `tanggal_surat` P-21 ke `tanggal_surat` TAHAP II.
  2. **Satuan Hari Kerja (Business Days)**:
     - Menggunakan utilitas Carbon `diffInWeekdays` untuk mengecualikan hari Sabtu dan Minggu, sesuai dengan keterangan resmi kartu dashboard.
  3. **Penyaringan Periode & Penanganan Kosong**:
     - Mengikuti filter tahun (`$tahun`) yang dipilih pengguna di dashboard.
     - Jika sebuah tahap belum memiliki sampel berkas di tahun terkait, disajikan teks representatif `- Hari` dan sub-label `Belum ada data` tanpa memicu kesalahan kalkulasi pembagian nol.
  4. **Performa & Invalidation Cache**:
     - Eager loading terkontrol (`perkara:id,tanggal_surat_perintah` dan `tahapan`) untuk memproses kalkulasi dalam memori tanpa query N+1.
     - Hasil perhitungan di-cache dengan key `dashboard_stage_averages_{tahun}` selama 120 detik, di-invalidasi otomatis oleh method `DashboardService::clearCache()` setiap kali terjadi mutasi pada model `Perkara`, `BerkasPerkara`, atau `TahapanPerkara`.
- Konsekuensi: Kartu analitik dashboard menyajikan data riil operasional penanganan perkara BPOM Kalsel yang akurat, berkinerja tinggi, dan responsif terhadap filter tahun.

---

## D-053 : Standardisasi 9 Kebijakan Operasional: Keamanan Akun, Penuntut Umum Terintegrasi, Tata Kelola Menu & Data Petugas, Penguncian Terminal Putusan, dan DPO Gate Menyeluruh

- Tanggal: 2026-10-21
- Status: Disepakati
- Konteks: Menetapkan konsensus domain dan spesifikasi teknis untuk 9 area operasional aplikasi BAPTwo mencakup autentikasi akun nonaktif/terhapus, integrasi data Penuntut Umum dan Pengadilan, restrukturisasi menu per peran, penguncian perkara selesai dan DPO gate, tabel Daftar Perkara 8 kolom, serta tautan navigasi referensi tahapan.
- Keputusan:
  1. **Keamanan Autentikasi Akun Nonaktif & Terhapus**:
     - Pengguna yang berstatus `is_active = false` atau terhapus secara soft delete (`deleted_at IS NOT NULL`) dilarang masuk sistem.
     - Demi mencegah user enumeration (D-050), pesan spesifik "Akun Anda sedang dinonaktifkan atau telah dihapus" hanya ditampilkan jika kombinasi email dan password terbukti valid di database. Jika password salah, tetap menampilkan pesan generik "Email atau kata sandi salah".
     - Notifikasi penolakan masuk disajikan dalam bentuk toast error 3 detik dan banner peringatan inline merah pada formulir masuk.
     - Setiap percobaan masuk akun nonaktif tetap dicatat ke dalam `audit_log` dengan aksi `login_inactive` dan tunduk pada rate limiter login serta captcha.
  2. **Pemutusan Sesi Pengguna Aktif (Force Logout Middleware)**:
     - Dibuatkan middleware `EnsureUserIsActive` pada grup web berautentikasi. Jika akun pengguna yang sedang login dinonaktifkan atau dihapus oleh Administrator, request berikutnya langsung memutus sesi (logout) dan mengarahkan ke halaman masuk dengan flash alert penonaktifan.
  3. **Penuntut Umum & Pengadilan Terintegrasi Master Data**:
     - Pada form Tambah Perkara, input Nama Instansi Kejaksaan diambil dari tabel `instansi_hukum` dengan jenis `'kejaksaan'`, sedangkan Nama Jaksa, NIP Jaksa, dan Pangkat Jaksa diinput manual.
     - Data Penuntut Umum disimpan pada tabel `perkara` (`penuntut_instansi_id`, `penuntut_nama`, `penuntut_nip`, `penuntut_pangkat`) dan ditampilkan dalam Card Informasi Penuntut Umum pada detail perkara dan tahapan jika terisi.
     - Pada form Putusan Perkara (Keputusan Pengadilan), field "Nama Pengadilan" diambil dari tabel `instansi_hukum` dengan jenis `'pengadilan'`.
  4. **Penguncian Terminal Keputusan Pengadilan**:
     - Tahap 2 (Pelimpahan Tersangka & Barang Bukti) menyelesaikan penyidikan administratif (100% dan status formal Selesai).
     - Keputusan Pengadilan dicatat setelah Tahap 2 sebagai dokumentasi resmi vonis hakim inkrah dari pihak penyidik tanpa menambah persentase di atas 100%.
     - Setelah Keputusan Pengadilan dicatat, seluruh tahapan perkara terkunci permanen dan tidak ada lagi tahapan apa pun yang dapat ditambahkan (semua tombol aksi selanjutnya dihilangkan dan divalidasi ketat di sisi server).
  5. **Pemblokiran Tahapan oleh DPO Aktif dan SP3 (DPO vs SP3)**:
     - Baik status DPO aktif maupun SP3 sama-sama menghambat jalannya perkara dan memblokir SELURUH penambahan tahapan berikutnya di tahap mana pun (SPDP, Tahap 1, P-21, Tahap 2, Putusan). Tombol tahapan berikutnya dinonaktifkan/disembunyikan di UI dan divalidasi ketat (422) di server.
     - Perbedaan mendasar: Status DPO bersifat sementara dan dapat dicabut kembali saat tersangka ditemukan (sehingga pemblokiran tahapan dicabut dan berkas dapat dilanjutkan kembali), sedangkan status SP3 bersifat terminal/permanen dan tidak dapat dicabut (perkara berhenti mutlak sebagai 'Tidak Selesai' dan 'SP3').
     - SP3 diperbolehkan diterbitkan saat tersangka berstatus DPO aktif. Penerbitan SP3 otomatis mencabut status DPO berkas dan menetapkan perkara berstatus formal "Tidak Selesai" serta status proses "SP3".
  6. **Penetapan Peran "Umum" sebagai Pimpinan Berfitur Terbatas**:
     - Role `'umum'` adalah role untuk pimpinan/pemimpin instansi. Dinamakan "umum" karena fiturnya dibatasi hanya untuk monitoring: Dashboard, Pencarian Perkara, dan Potensi Terlimpah (read-only), tanpa menu Perkara operasional, Master Data, maupun Pengaturan.
  7. **Restrukturisasi Menu & Submenu Pengaturan**:
     - Menu Pimpinan: Menu Utama (Dashboard, Pencarian Perkara, Potensi Terlimpah).
     - Menu Penyidik: Menu Utama (Dashboard, Data Perkara [Daftar Perkara, Potensi Terlimpah]), Perkara (7 tahapan), Sistem (Master Data [Pasal & Instansi], Data Petugas).
     - Menu Administrator: Menu Utama (Dashboard, Data Perkara), Perkara (7 tahapan), Sistem (Master Data lengkap, Pengaturan [User, Petugas, Umum], Recovery Data).
     - Halaman Sampah berganti nama tampilan resmi menjadi "Recovery Data" di seluruh antarmuka.
  8. **Modul Data Petugas & Standarisasi Tabel Daftar Perkara**:
     - Halaman "Data Petugas" menampilkan tabel petugas non-admin dengan fitur tambah penugasan (memilih user dari tabel user dan memilih role selain admin: penyidik atau umum).
     - Tabel Daftar Perkara distandarisasi menjadi 8 kolom: No, Perkara (No LK & tanggal vertikal), TKP (kota & sarana vertikal), PPNS (nama & NIP vertikal), Tersangka (nama & alias), Pasal (maksimal 2 + tooltip daftar lengkap), Status (status perkara dan tahapan terakhir beserta angka persentase tanpa bar), dan Aksi.
  9. **Tautan Navigasi Tahapan Sebelumnya**:
     - Nomor surat pada Card Referensi Tahapan Sebelumnya di halaman tahapan dijadikan tautan link aktif menuju halaman tahapan terkait jika peran pengguna memiliki hak akses melihatnya.
- Konsekuensi: Kebijakan bisnis, alur penguncian perkara, tata kelola akun, dan navigasi antarmuka selaras 100% dengan kebutuhan operasional BPOM Kalsel.
















## D-054 : Seeder 100 Data Perkara Berbasis Rencana untuk Pengujian Manual

- Tanggal: 2026-10-21
- Status: Disepakati
- Konteks: Kebutuhan data uji manual yang lebih kaya untuk menguji filter, paginasi, dashboard, DPO, SP3, dan batas tahun. Seeder sebelumnya berisi 20 perkara yang seluruhnya ditulis manual sehingga penambahan data baru memakan banyak baris dan rawan duplikasi.
- Keputusan:
  1. **Rencana Data Terpusat**: 100 perkara disusun dari tabel distribusi bucket tahapan, katalog 18 jenis perkara, katalog 17 wilayah, dan deret nama/jalan/usaha sebagai sumber variasi. Nomor surat, tanggal, dan NIK dibangkitkan deterministik dari tanggal dokumen dan indeks urutan.
  2. **Penomoran per Berkas**: Nomor surat tahapan dibangkitkan di `createBerkas()` agar setiap berkas perkara memiliki nomor unik sendiri, termasuk pada perkara dengan dua atau tiga tersangka.
  3. **Struktur Helper Terpisah**: Kasus perkara dibuat oleh `createCase()` lalu setiap tersangka menjadi satu berkas melalui `createBerkas()`, sehingga aturan 1 tersangka = 1 berkas perkara tetap terjaga dan berlaku untuk semua data.
  4. **Idempotensi**: Seeder tetap membersihkan data dummy lama (`LK/%`) dengan eager loading `berkasPerkaras`, `tersangkas`, dan `tkp` agar tidak melanggar `Model::preventLazyLoading()`, lalu menanam ulang data dengan `updateOrCreate`.
  5. **Konsistensi Domain**: Progress, status proses, dan status formal tidak pernah diisi manual. Semuanya dihitung dari rantai tahapan yang sudah difinalkan, dengan pengecualian `tidak_selesai` untuk berkas SPDP tahun lalu dan SP3.
- Konsekuensi: Data uji manual mencakup seluruh tahapan, status, dan kombinasi DPO/SP3, tetap dapat dijalankan berulang tanpa duplikasi, dan mudah diperluas dengan menambah baris distribusi.

---

## D-055 : Penggantian Format Ekspor Excel Menggunakan Template LAPJU 45 Kolom

- Tanggal: 2026-10-22
- Status: Disepakati & Diimplementasikan
- Konteks: Menggantikan layout ekspor rekap perkara 44 kolom lama dengan format template resmi LAPJU (`template_lapju.xlsx`, 45 kolom: A s.d. AS) pada seluruh 4 metode pengambilan data (per tahun, per bulan, semua data, dan rentang tanggal) tanpa mengubah tombol, modal, maupun alur filter yang sudah ada.
- Keputusan:
  1. **Pemuatan Template Resmi (`resources/templates/template_lapju.xlsx`)**:
     - Template dimuat langsung via PhpSpreadsheet `IOFactory::load()` untuk menjaga 100% konsistensi jenis font (Arial 11), warna header (`FFF1F5F9`), warna baris nomor (`FFE2E8F0`), merge sel bertingkat, tinggi baris, dan lebar kolom.
  2. **Struktur Header & Keterangan Periode**:
     - Baris 1: A1:AS1 judul "PROSES PENYIDIKAN / HISTORY LAPJU" bold rata tengah.
     - Baris 2: Keterangan dinamis A2:AS2 tanpa border rata kiri: `Periode: <label periode>  |  Tanggal Export: <tanggal hari ini>`.
     - Baris 6: Mengoreksi kesalahan penomoran template asli (AQ6 kosong) menjadi urut 1–45 (AQ=43, AR=44, AS=45).
     - Freeze pane dipasang di A7 (di bawah baris nomor kolom).
  3. **Aturan Penyajian Data**:
     - 1 Perkara = 1 baris. Data dimulai dari Baris 7 terurut tanggal LK terbaru ke terlama (`descending`), id menurun.
     - Nilai ganda (multi-tersangka berurutan konsisten I–N, multi-pasal, multi-PPNS, multi-putaran P-18/P-19) disajikan bernomor `1. ...\n2. ...` dengan *wrap text* aktif. Nilai tunggal disajikan tanpa nomor.
     - Tanggal tunggal ditulis sebagai tanggal Excel asli dengan format tampilan `dd/mm/yyyy`. Tanggal ganda ditulis sebagai string bernomor dengan format `dd/mm/yyyy`.
     - Seluruh sel teks diisi sebagai string eksplisit (`DataType::TYPE_STRING`) dan dinetralisir dari potensi *formula injection* (`=`, `+`, `@`, `-`).
     - Kolom 44 (AR): Menampilkan amar putusan (`amar_putusan`) dari tahapan Keputusan Pengadilan.
     - Kolom 45 (AS): Menampilkan catatan kendala operasional riil (keterangan SP3, keterangan DPO, petunjuk P-18/P-19; '-' jika lancar tanpa hambatan). Tidak menyertakan informasi SLA.
  4. **Penanganan Periode Kosong & Penamaan Berkas**:
     - Jika periode yang dipilih tidak memiliki perkara, berkas tidak diunduh. Controller membatalkan unduhan dan sistem menampilkan notifikasi pop-up BAPTwo (toast 3 detik): *"Tidak ada data pada periode ini"*.
     - Modal ekspor menerapkan async pre-check sebelum submit form.
     - Format penamaan berkas terstandarisasi: `LAPJU_<periode>_<yyyy-mm-dd>.xlsx`.
- Konsekuensi: Format ekspor Excel memenuhi standar pelaporan LAPJU BPOM Kalsel yang presisi, informatif, aman dari injeksi formula, dan teruji lulus pada 4 metode pengambilan data.

---

## D-056 : Penyelarasan Logika dan Standardisasi Perhitungan Dashboard BAPTwo

- Tanggal: 2026-10-26
- Status: Disepakati & Diimplementasikan
- Konteks: Menyelaraskan seluruh kalkulasi statistik, kartu analitik, Donut Chart, dan tabel prioritas di `app/Services/DashboardService.php` agar konsisten, menghilangkan redundansi definisi antar-komponen, dan mematuhi zona waktu operasional BPOM Kalimantan Selatan.
- Keputusan:
  1. **Satu Definisi Status Formal (`scopeDalamProses`, `scopeSelesai`, `scopeTidakSelesai`)**:
     - Ditambahkan konstanta dan query scope resmi pada model `BerkasPerkara`: `scopeDalamProses` (`status_formal IS NULL OR status_formal = 'dalam_proses'`), `scopeSelesai` (`status_formal = 'selesai'`), dan `scopeTidakSelesai` (`status_formal = 'tidak_selesai'`).
     - Seluruh query kartu statistik, Donut Chart, dan tabel prioritas menggunakan sumber scope dan konstanta yang seragam.
  2. **Kategori "Selesai" Murni dari `status_formal`**:
     - Klausul `OR progress_persen >= 100` dihapus dari kartu statistik Selesai dan dari `calculateAverageCompletionDays`. Berkas ber-progress 100% yang belum difinalkan tetap berstatus "Dalam Proses". Kartu "Selesai" dan "Perkara Berjalan" kini 100% sinkron dengan Donut Chart.
  3. **Satu Acuan Tahun: Tanggal Laporan Kejadian (LK)**:
     - Dibuat query scope `scopeTahunLk($tahun, ?$bulan = null)` pada model `BerkasPerkara` via relasi `perkara`.
     - Seluruh acuan tahun yang sebelumnya menggunakan `OR tanggal_spdp` / `tanggal_surat_perintah` digantikan murni oleh `tanggal_laporan_kejadian`.
     - Judul kartu pertama diubah dari "Perkara Aktif" menjadi "Total Perkara" (deskripsi: "Perkara pada tahun X") karena mencakup seluruh perkara di tahun LK tersebut.
  4. **Penyaringan Kartu "Menunggu Verifikasi Penyidik"**:
     - Kedua kondisi (memiliki tahapan berstatus `draft` aktif maupun berada pada progress awal) dibatasi tegas dengan `scopeDalamProses()`, sehingga riwayat draft pada berkas yang telah Selesai maupun SP3 tidak terhitung.
     - Ambang progress awal dipindahkan ke konstanta publik `DashboardService::AMBANG_PROGRESS_AWAL_PERSEN = 15;` (bobot tahap awal SPDP).
  5. **Tabel Prioritas Progress Perkara**:
     - Default tanpa filter status hanya menampilkan berkas `scopeDalamProses()` (menyembunyikan berkas Selesai dan Tidak Selesai/SP3). Filter manual status tetap berfungsi jika dipilih.
     - Urutan prioritas: `sla_deadline` ASC dengan NULL di paling belakang secara portabel (`orderByRaw('sla_deadline IS NULL')`), lalu `progress_persen` ASC, lalu `created_at` ASC.
  6. **Rata-rata Waktu Penyelesaian SPDP s.d. Tahap II**:
     - Eager loading `tahapan` hanya memuat tahapan berstatus `final` untuk SPDP dan Tahap II.
     - Data anomali (tanggal Tahap II lebih awal dari SPDP) otomatis diabaikan (`continue`).
  7. **Standardisasi Zona Waktu WITA (`Asia/Makassar`)**:
     - Ditambahkan konstanta `DashboardService::ZONA_WAKTU = 'Asia/Makassar'`.
     - Seluruh pemanggilan `date('Y')`, `Carbon::today()`, dan `Carbon::now()` digantikan oleh instance Carbon berzona waktu Makassar.
     - Evaluasi SLA dan perbandingan tanggal menggunakan format string `Y-m-d` via `DATE(sla_deadline)`.
  8. **Optimalisasi `getAvailableYears`**:
     - Menggantikan penarikan seluruh data tanggal ke memori PHP dengan kueri database `DISTINCT YEAR(tanggal_laporan_kejadian)` (MySQL) atau `DISTINCT strftime('%Y', tanggal_laporan_kejadian)` (SQLite), ditambah tahun berjalan zona Makassar, terurut menurun.
- Konsekuensi: Seluruh data statistik dashboard konsisten, akurat, bebas anomali tanggal/status, berkinerja tinggi, dan terverifikasi penuh oleh 231 tes otomatis Pest (2162 assertions) serta standar Laravel Pint.

---

## D-057 : Widget Feed Aktivitas Penyidikan Terkini dan Restrukturisasi Grid Dashboard BAPTwo

- Tanggal: 2026-10-26
- Status: Disepakati & Diimplementasikan
- Konteks: Dashboard memerlukan pemantauan riwayat dan perkembangan penyidikan perkara secara real-time (Audit Log) serta tata letak grid yang lebih seimbang antara Donut Status Perkara, Aktivitas Terkini, dan Rata-rata Penyelesaian per Tahap.
- Keputusan:
  1. **Pemusatan Metadata Aksi Audit Log**:
     - Konstanta `MILESTONE_ACTIONS`, method `isMilestone()`, accessor `action_meta`, serta query scope `scopeMilestone()` dan `scopeTerkini()` dipusatkan pada model `AuditLog`.
     - Seluruh metadata (label resmi Bahasa Indonesia operasional hukum, icon Flaticon `fi-rr-*`, badge Tailwind, icon background) terkelola dalam method statis `AuditLog::actionMeta()`.
  2. **Data Provider Terpadu**:
     - Method `DashboardService::getRecentActivities(?int $limit = 6)` memuat feed terkini (`milestones` dan `all`) dengan batasan limit 6 per kategori.
     - Eager loading relasi `['user', 'perkara', 'berkasPerkara.tersangka']` diterapkan secara eksplisit untuk mencegah masalah query N+1.
     - Dioper ke view dashboard melalui `DashboardController::index()` dalam variabel `$recentActivities`.
  3. **Komponen Blade `<x-dashboard.recent-activities>`**:
     - Menggunakan toggle filter client-side instan berbasis Alpine.js (`x-data="{ tab: 'milestones' }"`) antara "Milestone Penting" dan "Semua Aktivitas".
     - Menyediakan 3 state lengkap: state ada data, empty state informatif berikon Flaticon `fi-rr-clipboard-list-check`, dan fallback state.
     - Setiap item aktivitas menampilkan label aksi badge, waktu relatif (`diffForHumans()`), badge nomor perkara/LK yang dapat diklik langsung ke detail perkara (`route('perkara.show', $id)`), nama tersangka, dan nama petugas/penyidik yang mengeksekusi.
     - Bebas dari tanda em dash (`—`) dan buzzword generik AI-slop sesuai aturan D-042.
  4. **Restrukturisasi Grid Dashboard**:
     - Baris 1: Grid 12 kolom seimbang berdampingan (`lg:col-span-6` Donut Status Perkara + `lg:col-span-6` Aktivitas Penyidikan Terkini).
     - Baris 2: Kartu Rata-rata Penyelesaian per Tahap (`x-dashboard.average-stage`) full-width.
     - Baris 3: Tabel Prioritas Progress Perkara (`x-dashboard.progress-perkara`) di posisi paling bawah.
  5. **Refactor Detail Perkara (`show.blade.php`)**:
     - Array lokal `$actionMeta` 80 baris pada `resources/views/pages/perkara/show.blade.php` dihapus dan digantikan oleh panggilan properti terpusat `$log->action_meta`.
- Konsekuensi: Tampilan dashboard BAPTwo lebih informatif, visual seimbang dan proporsional, data audit log tersaji real-time, bebas dari redundansi kode, serta terverifikasi 100% lulus 239 tes otomatis Pest PHP dan linter Laravel Pint.

---

## D-058 : Refactor Visual dan Desain Kartu Perkara Tahun Berjalan (Donut 6 Segmen & Layout 2 Kolom)

- Tanggal: 2026-10-26
- Status: Disepakati & Diimplementasikan
- Konteks: Kartu "Perkara Tahun Berjalan" pada Dashboard BAPTwo memerlukan perampingan layout visual (tinggi berkurang ~50%), hierarki visual yang lebih tegas, donut dinamis 6 segmen berbasis pure SVG tanpa library eksternal, dan legenda dot 10px berwarna senada segmen donut.
- Keputusan:
  1. **Layout 2 Kolom Responsif dengan Container Query**:
     - Elemen pembungkus kartu menggunakan `@container` pada `case-status.blade.php`.
     - Tata letak body menggunakan grid 2 kolom `grid grid-cols-1 @[600px]:grid-cols-[150px_1fr] gap-6 items-center`.
     - Skeleton loading diselaraskan dengan tata letak grid 2 kolom baru (lingkaran 136px di kiri dan 3 placeholder baris di kanan).
  2. **Donut Dinamis 6 Segmen Berbasis Pure SVG**:
     - Lingkaran dengan radius `r=54` (keliling `C ≈ 339.292`) dan stroke 16px di dalam viewBox `0 0 140 140`.
     - 6 kategori data dipetakan proporsional: Selesai (`stroke-success-500`), Belum Ada SPDP (`stroke-brand-400`), Dalam SLA (`stroke-teal-500`), Mendekati Batas (`stroke-amber-500`), Melewati SLA (`stroke-error-500`), dan Tidak Selesai/SP3 (`stroke-gray-400 dark:stroke-gray-600`).
     - Segmen bernilai 0 otomatis diabaikan dari rendering SVG. Celah 2px antar segmen dan panjang busur minimum 4px diterapkan jika terdapat lebih dari 1 segmen aktif.
     - Di tengah donut: Angka Total Perkara (26px, `text-2xl font-semibold tabular-nums`) dan label `Total Perkara` (11px, `text-[11px]`).
     - Tooltip melayang Alpine.js (`x-data="{ hovered: null }"`) muncul di atas donut saat hover/focus keyboard.
  3. **Teks Perbandingan Tahun Lalu Netral**:
     - Ditampilkan di bawah donut dengan teks netral 12px (`text-xs text-gray-500 dark:text-gray-400 tabular-nums`).
     - Format: `▲ {diff} (+{pct}%) vs {prevYear}` atau `▼ {diff} (-{pct}%) vs {prevYear}` atau `0 vs {prevYear}` tanpa menggunakan tanda em dash (`—`) per aturan D-042. Jika tahun lalu bernilai 0, persen disembunyikan otomatis tanpa error pembagian nol.
  4. **Daftar Status Kolom Kanan dengan Legenda Dot 10px**:
     - Dot 10px bulat (`h-2.5 w-2.5 rounded-full`) sebagai penanda warna legenda yang identik dengan segmen donut.
     - Panel surface level 1 "Dalam Proses" (`bg-gray-50/70 dark:bg-white/[0.02] border border-gray-100 dark:border-gray-800 rounded-xl p-3`) memuat 4 sub-baris SLA.
     - Penanganan nilai 0: Sub-baris bernilai 0 menggunakan dot outline (`border border-gray-300 dark:border-gray-600 bg-transparent`) dan teks/angka muted (`text-gray-400 dark:text-gray-500 font-normal`).
     - Sub-baris "Melewati SLA" hanya menampilkan badge pill bahaya (`bg-error-50 text-error-600 font-bold`) jika nilainya > 0.
     - Seluruh segmen donut dan baris status mempertahankan tautan drill-down interaktif ke rute `seluruh-perkara.index` terfilter.
- Konsekuensi: Tampilan kartu "Perkara Tahun Berjalan" menjadi jauh lebih kompak, proporsional, estetik, dan informatif, selaras dengan kartu feed aktivitas di sebelahnya, serta terverifikasi 100% lulus 241 tes otomatis Pest PHP dan linter Laravel Pint.

---

## D-059 : Tren Perkara Masuk & Selesai (ApexCharts) dan Tata Letak Grid Dashboard Simetris

- Tanggal: 2026-10-26
- Status: Disepakati & Diimplementasikan
- Konteks: Dashboard BAPTwo memerlukan visualisasi tren perkara bulanan (Januari s.d. Desember) yang membandingkan perkara masuk (berdasarkan tanggal Laporan Kejadian) dan berkas yang berhasil diselesaikan (berdasarkan tanggal surat Tahap II berkas berstatus formal `selesai`) dalam satu tahun anggaran, serta penataan grid dashboard yang simetris dan proporsional.
- Keputusan:
  1. **Instalasi Pustaka ApexCharts**:
     - Pustaka `apexcharts` dipasang resmi via `npm install apexcharts --save` dan dibundle melalui Vite.
     - Modul JavaScript modular dibuat di `resources/js/components/monthly-cases-chart.js` dengan fungsi inisialisasi `initMonthlyCasesChart` dan Alpine data component `monthlyCasesChart`.
     - Warna chart diselaraskan dengan Tailwind theme tokens: Perkara Masuk (Biru Brand `#465fff`) dan Perkara Selesai (Hijau Sukses `#12b76a`).
     - Listener custom event `'theme-changed'` disematkan untuk memperbarui warna teks sumbu, garis grid, dan tooltip saat toggle mode gelap diklik secara real-time.
  2. **Data Provider Tren Bulanan (`DashboardService::getMonthlyCasesTrends`)**:
     - Mengagregasi data 12 bulan (indeks 0..11 untuk Jan s.d. Des) dengan query yang kompatibel baik untuk MySQL di production maupun SQLite di testing environment.
     - Perkara Masuk dihitung dari `Perkara` berdasarkan `tanggal_laporan_kejadian` pada tahun anggaran terpilih.
     - Perkara Selesai dihitung dari `TahapanPerkara` berjenis `TAHAP_2` berstatus `final` pada tahun anggaran terpilih dari berkas perkara berstatus formal `selesai`.
     - Mengembalikan data `labels`, `incoming`, `completed`, `totalIncoming`, `totalCompleted`, dan `clearanceRate` (persentase rasio penyelesaian perkara).
     - Di-cache selama 120 detik (`dashboard_monthly_cases_{tahun}`) dan di-invalidasi otomatis pada mutasi data via `DashboardService::clearCache()`.
  3. **Komponen Blade `<x-dashboard.monthly-cases-chart>`**:
     - Card container standar TailAdmin dengan layout `@container` dan border yang selaras dengan kartu Donut Chart di sebelahnya.
     - Header kartu memuat judul, subjudul tahun anggaran, legenda dot 10px berwarna senada batang grafik, serta badge rasio penyelesaian tahunan.
     - Body kartu memuat kontainer ApexCharts dengan fallback pesan informatif jika belum ada aktivitas pada tahun anggaran terpilih.
  4. **Tata Letak Grid Dashboard Simetris**:
     - **Baris 1**: 5 Kartu Statistik Ringkasan (`<x-dashboard.statistics />`).
     - **Baris 2 (Visual Charts)**: Grid 12 kolom simetris (`lg:grid-cols-12 lg:gap-6`):
       - `lg:col-span-6`: Donut Status Perkara (`<x-dashboard.case-status />`).
       - `lg:col-span-6`: Tren Perkara Masuk & Selesai (`<x-dashboard.monthly-cases-chart />`).
     - **Baris 3 (Operasional & Aktivitas)**: Grid 12 kolom simetris (`lg:grid-cols-12 lg:gap-6`):
       - `lg:col-span-6`: Aktivitas Penyidikan Terkini (`<x-dashboard.recent-activities />`).
       - `lg:col-span-6`: Rata-rata Penyelesaian per Tahap (`<x-dashboard.average-stage />`).
     - **Baris 4 (Tabel Detail Utama)**: Tabel Prioritas Progress Perkara (`<x-dashboard.progress-perkara />`) full width di posisi paling bawah.
- Konsekuensi: Tampilan dashboard monitoring perkara menjadi sangat seimbang secara visual dan fungsional, memberikan gambaran produktivitas penyidikan tahunan secara visual, serta terverifikasi 100% lulus 244 tes otomatis Pest PHP dan linter Laravel Pint.

---

## D-060 : Standarisasi 1.000 Data Dummy Perkara, Perluasan Master Data, dan Penegakan Murni 1 Tersangka 1 Berkas

- Tanggal: 2026-10-26
- Status: Disepakati & Diimplementasikan (via interview /grill-me)
- Konteks: Pengguna menghendaki peningkatan volume data uji seeder hingga tepat 1.000 data perkara yang sangat bervariasi untuk menguji performa analitik, monitoring dashboard, pencarian multi-kriteria, dan ekspor LAPJU. Selain itu, pengguna menginstruksikan penghapusan skenario multi-tersangka (2 atau 3 tersangka) agar murni memenuhi aturan D-035 (1 Perkara = 1 Tersangka = 1 Berkas Perkara dengan nomor berkas identik dengan nomor LK), serta memperluas seluruh master data (Pasal, Jaksa, Instansi, Panggol, Jabatan, dan Penyidik PPNS).
- Keputusan:
  1. **Penegakan Murni 1 Perkara = 1 Tersangka = 1 Berkas Perkara**:
     - Seluruh logika pembentukan multi-tersangka dihapus sepenuhnya dari `DummyDataSeeder.php` (`jumlah_tersangka = 1` untuk seluruh 1.000 perkara).
     - Kolom `nomor_berkas` selalu identik 100% dengan `nomor_laporan_kejadian` (tanpa suffix `/T2` atau `/T3`).
  2. **Perluasan Master Data Menyeluruh**:
     - **38 Pasal/UU**: Menjangkau tindak pidana bidang kesehatan (UU 17/2023 Pasal 435–441), pangan (UU 18/2012 Pasal 136–146), perlindungan konsumen (UU 8/1999 Pasal 62), narkotika (UU 35/2009 Pasal 114 & 129), psikotropika (UU 5/1997 Pasal 60 & 62), dan KUHP (Pasal 204, 55, 56, 378).
     - **45 Jaksa Penuntut Umum**: Tersebar di Kejati Kalsel dan 13 Kejari se-Kalsel lengkap dengan NIP dan kepangkatan realistis.
     - **20 Akun Penyidik PPNS**: 20 pengguna PPNS aktif (`penyidik@baptwo.test` s.d. `tri.ppns@baptwo.test`) dengan variasi jabatan fungsional, bidang, dan golongan.
     - **9 Pangkat & Golongan**: Mencakup II/c s.d. IV/c.
     - **9 Jabatan & 5 Bidang**: Mencakup jabatan fungsional pengawas farmasi makanan dan bidang teknis operasional.
  3. **Distribusi Tepat 1.000 Perkara & Rentang 3 Tahun (2024–2026)**:
     - 650 Perkara (65%) pada tahun berjalan 2026: tersebar di seluruh tahapan aktif (Pra-SPDP s.d. Tahap 2, Selesai 2026, SP3 2026, DPO aktif & nonaktif, peringatan SLA).
     - 200 Perkara (20%) pada tahun 2025: terbagi antara status `selesai` dan `tidak_selesai` (lewat batas tahun 31 Desember atau SP3).
     - 150 Perkara (15%) pada tahun 2024: terbagi antara status `selesai` dan `tidak_selesai`.
     - 840 Berkas Perkara dan Tersangka (160 perkara berada di tahap Pra-SPDP LK/Sprindik).
  4. **Pembersihan Idempoten & Kinerja Tinggi**:
     - Pembersihan data lama menggunakan perulangan while-chunk terpadu pada `cleanupObsoleteDummyCases()` untuk menghapus ribuan record relasi tanpa memory exhaustion.
     - Menggunakan `DB::disableQueryLog()` dan transaksi database berkala (commit per 50 perkara) sehingga 1.000 perkara beserta puluhan ribu relasi (~5.000 dokumen, ~2.700 tahapan, ~5.000 audit log) tertanam stabil dalam waktu ~1 menit.
- Konsekuensi: Database pengujian BAPTwo memiliki 1.000 data perkara yang realistis, komprehensif, terstruktur rapi 1:1:1, dan terverifikasi 100% lulus oleh 244 tes otomatis Pest PHP dan linter Laravel Pint.

---

## D-061 : Optimasi Bulk Range Styling PhpSpreadsheet pada Ekspor Rekap LAPJU 45 Kolom

- Tanggal: 2026-10-26
- Status: Disepakati & Diimplementasikan
- Konteks: Pada pengujian ekspor LAPJU dengan volume 1.000 data perkara, terjadi fatal error `Symfony\Component\ErrorHandler\Error\FatalError: Maximum execution time of 30 seconds exceeded` di `vendor\phpoffice\phpspreadsheet\src\PhpSpreadsheet\Style\Border.php:212`. Hasil diagnosis membuktikan bahwa query database berjalan sangat cepat (~0,49 detik), namun pemanggilan styling per sel dan per baris (`getStyle()` sebanyak 45.000 kali dan kalkulasi hash border ratusan ribu kali) memakan waktu hingga ~33 detik dan menembus limit 30 detik.
- Keputusan:
  1. **Bulk Range Styling Satu Kali (`applyBulkStyles`)**:
     - Mengeliminasi pemanggilan border dan vertikal per baris di dalam `populateRow()`.
     - Mengeliminasi pemanggilan `getStyle()` individual di dalam `setTextCell()` dan `setDateCell()`.
     - Menerapkan border tipis, vertical center, dan wrap text sekaligus pada seluruh rentang data `A7:AS{$lastRow}` setelah loop data selesai.
  2. **Format Kolom Massal**:
     - Kolom tanggal (`C`, `E`, `U`, `W`, `Y`, `AA`, `AC`, `AE`, `AG`, `AI`, `AL`, `AN`) diformat massal dengan `dd/mm/yyyy` dan horizontal center per rentang kolom `{$col}7:{$col}{$lastRow}`.
     - Kolom teks dengan posisi tengah (`A`, `J`, `K`, `N`, `Q`, `R`, `S`) diformat massal horizontal center per rentang kolom.
     - Baris yang memiliki multi-tersangka dikembalikan ke rata kiri pada kolom J, K, N secara presisi.
  3. **Pencegahan Timeout Runtime**:
     - Ditambahkan `@set_time_limit(120);` pada awal metode `download()` sebagai lapisan pertahanan ganda (*defense-in-depth*).
  4. **Hasil Benchmark**:
     - Waktu generasi dan penyimpanan spreadsheet untuk 1.000 data perkara lengkap berkurang drastis dari **~33,88 detik** menjadi **~3,68 detik** (percepatan ~9x lipat, aman jauh di bawah ambang batas 30 detik).
- Konsekuensi: Ekspor file LAPJU 45 kolom beroperasi dengan performa tinggi, aman dari timeout, dan 100% kompatibel dengan seluruh suite tes otomatis (244 tes lulus).

---

## D-062 : Pembersihan Awalan Alamat Tersangka dan Redundansi Nama Sarana pada Kolom Alamat Ekspor LAPJU

- Tanggal: 2026-10-26
- Status: Disepakati & Diimplementasikan
- Konteks: Pada file ekspor Excel LAPJU 45 kolom, ditemukan bahwa Kolom L (Alamat Tersangka) selalu memuat frasa awalan "Tempat tinggal tersangka di ", dan Kolom P (Alamat Sarana/TKP) mengulang penulisan nama toko/sarana usaha di awal alamat padahal Kolom O sudah disediakan secara khusus untuk Nama Sarana/TKP.
- Keputusan:
  1. **Sanitasi Kolom L (Alamat Tersangka) pada `PerkaraRekapExport`**:
     - Ditambahkan metode `sanitizeAlamatTersangka(?string $alamat)` yang menghapus frasa awalan `Tempat tinggal tersangka di ` (case-insensitive, termasuk variasi dengan tanda titik dua atau pemisah) dan melakukan trim karakter pemisah berlebih.
  2. **Sanitasi Kolom P (Alamat Sarana/TKP) pada `PerkaraRekapExport`**:
     - Ditambahkan metode `sanitizeAlamatTkp(?string $alamat, ?string $namaTkp)` yang secara dinamis memeriksa nama sarana dari Kolom O (`$tkp?->alamat?->nama`). Jika nama tersebut muncul di awal string alamat, nama dan karakter pemisahnya (koma, tanda hubung, titik dua) dihapus sehingga Kolom P murni hanya memuat alamat fisik (jalan, nomor, RT/RW, kelurahan, kecamatan, dsb.).
  3. **Penyempurnaan Seeder `DummyDataSeeder.php`**:
     - Seeder alamat tersangka kini menghasilkan nama jalan dan nomor acak realistis murni (`Jl. {Jalan} No. {Nomor}`) tanpa awalan string operasional.
     - Seeder alamat TKP memisahkan nama sarana ke kolom `nama` dan hanya menyimpan detail jalan/lokasi pada kolom `alamat_detail`.
- Konsekuensi: File ekspor Excel LAPJU menjadi lebih bersih, baku, tidak redundan antar-kolom, serta terverifikasi 100% lulus 245 tes otomatis Pest PHP dan linter Laravel Pint.

---

## D-063 : Standarisasi Modal Pilih Berkas, Modal Loading Ekspor Excel, dan Sinkronisasi Akses Perkara Berbasis Role & Status Terpadu (Revisi Batch 27 Okt 2026)

- Tanggal: 2026-10-27
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan hasil evaluasi UI/UX dan alur kerja operasional:
  1. Komponen modal pilih berkas di 5 tahapan lanjutan (SPDP, Tahap I, P-21, Tahap II, Putusan) terduplikasi dengan layout kaku (tinggi statis) serta belum memiliki fitur pencarian/filter saat daftar berkas panjang.
  2. Modal ekspor Excel tidak menampilkan feedback visual saat proses kompilasi berlangsung, dan tidak memberikan peringatan jelas saat data periode kosong.
  3. Terjadi kebingungan navigasi bagi Admin dan Penyidik yang mengakses menu "Seluruh Perkara" dan "Progress Perkara" padahal alur kerja mereka berpusat di "Data Perkara", serta status SP3 dan DPO aktif belum memiliki prioritas hirarki status formal yang baku di tabel induk perkara.
  4. Halaman Data Perkara belum menyajikan card ringkasan informasi perkara dan filter PPNS Utama untuk Administrator.
- Keputusan:
  1. **Komponen Bersama `<x-tahapan.select-berkas-modal>`**:
     - Diterapkan seragam pada 5 tahapan lanjutan (SPDP, Tahap I, P-21, Tahap II, Putusan).
     - Tinggi modal dinamis mengikuti jumlah berkas (1 s.d. 7 baris) dan mengaktifkan scrollbar vertikal halus (`max-h-[34rem] overflow-y-auto`) jika > 7 berkas.
     - Dilengkapi input pencarian live client-side Alpine.js (`searchQuery`) dengan fallback pesan "Data tidak ditemukan".
  2. **Modal Feedback Unduhan Excel Terpadu**:
     - Controller `PerkaraExportController` mendeteksi header `X-Export-Fetch` dan mengembalikan respons JSON 422 jika data periode kosong, serta mengirim header `X-Export-Filename` saat berhasil.
     - Komponen modal menampilkan dialog loading "Mengunduh data..." tanpa tombol tutup/klik luar selama fetch blob berlangsung.
     - Jika periode kosong, modal loading ditutup, modal filter periode dibuka kembali secara otomatis, dan toast peringatan auto-dismiss 3 detik dimunculkan.
  3. **Pengalihan Akses Menu & Prioritas Status Formal**:
     - Admin & Penyidik yang mengakses `/seluruh-perkara` dan `/progress-perkara` dialihkan otomatis (302) ke `/data-perkara` dengan query string tetap terbawa. Pimpinan dan Umum tetap mengakses halaman monitoring masing-masing.
     - Penerbitan SP3 pada berkas perkara yang berstatus DPO Aktif otomatis mengisi alasan pencabutan DPO dengan "Ditutup karena SP3".
     - Hirarki prioritas evaluasi status formal perkara di `Perkara::getStatusFormalAttribute()` ditetapkan: `SP3 > Selesai > DPO Aktif > Dalam Proses`.
  4. **Penyempurnaan Halaman Data Perkara**:
     - Menambahkan 5 card ringkasan informasi non-clickable di atas tabel (`Total Perkara`, `Dalam Proses`, `DPO Aktif`, `Selesai`, `Tidak Selesai / SP3`) dengan format angka tabular dan suffix "X perkara".
     - Kolom `Status & SLA` terpadu menggabungkan badge status formal, teks informatif `Tahap: {Nama} ({Persen}%)`, dan badge SLA.
     - Menambahkan dropdown filter `PPNS Utama` (khusus Admin), `Status Formal Perkara`, dan `Tahapan Terakhir`.
- Konsekuensi: Seluruh alur antarmuka konsisten sesuai role, pengalaman ekspor lebih responsif dan informatif, serta seluruh suite pengujian otomatis lulus 100% (251 tes Pest) dan Laravel Pint bersih.

---

## D-064 : Standarisasi Batas Ukuran Dokumen PDF Maksimal 5 MB & Format Note Terpadu

- Tanggal: 2026-10-27
- Status: Disepakati & Diimplementasikan (Menyesuaikan D-007)
- Konteks: Pada implementasi awal (D-007), batas ukuran dokumen PDF ditetapkan 20 MB. Dalam evaluasi alur kerja dan optimasi penyimpanan, seluruh Form Request telah dibatasi maksimal 5 MB (`max:5120`), namun teks petunjuk (*helperText*) di beberapa view Blade masih menampilkan residu "Maksimal 20 MB" dan belum memiliki format pesan note terpadu saat menampilkan dokumen pada form edit.
- Keputusan:
  1. **Batas Ukuran Dokumen 5 MB Konsisten**:
     - Seluruh input berkas PDF di BAPTwo (Laporan Kejadian, Sprindik, Tahapan & Sub-Tahapan, Modal DPO, dan Modal SP3) wajib berukuran maksimal 5 MB (`max:5120`).
     - Pesan validasi server diseragamkan dengan pesan ramah pengguna: `"Ukuran file dokumen tidak boleh melebihi 5 MB."`.
  2. **Format Note Terpadu pada `<x-form.pdf-upload>`**:
     - Form Tambah / Baru (belum ada berkas): `"Format file: PDF maks. 5 MB"`.
     - Form Edit (sudah ada berkas sebelumnya): `"Dokumen saat ini: [nama_file]. Biarkan kosong jika tidak ingin mengubah. Format file: PDF maks. 5 MB"` serta tombol modal pratinjau `"Lihat file saat ini"`.
     - Komponen Blade `<x-form.pdf-upload>` menghasilkan teks note ini secara otomatis dan dinamis berdasarkan ada/tidaknya properti `:existingFile`.
  3. **Pembersihan Residu 20 MB**:
     - Seluruh prop `helperText` manual yang menyebutkan "20 MB" di form Laporan Kejadian, Sprindik, Tahapan (5 sub-dokumen), dan modal detail perkara dihapus dan diselaraskan ke komponen terpadu.
- Konsekuensi: Pengalaman pengguna seragam, pesan validasi ramah dan konsisten, serta seluruh 255 pengujian otomatis Pest PHP dan linter Laravel Pint lulus 100%.

---

## D-065 : Penghapusan Master Data Jaksa Penuntut, Penyederhanaan Input Penuntut Umum, dan Combobox Pengadilan

- Tanggal: 2026-10-27
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan hasil evaluasi UI/UX dan alur operasional:
  1. Master Data Jaksa Penuntut tidak lagi diperlukan karena data Jaksa Penuntut Umum (JPU) kini diinput manual per perkara sesuai surat penunjukan dari Kejaksaan.
  2. Input data Penuntut Umum pada form Perkara perlu disederhanakan: pilihan Instansi Kejaksaan dijadikan combobox dengan pencarian, Nama dan NIP Jaksa diinput manual, dan field Pangkat Jaksa dihapus karena tidak esensial.
  3. Pilihan Nama Pengadilan pada form Keputusan Pengadilan (Putusan Perkara) masih berupa `<select>` biasa yang menyulitkan ketika daftar instansi pengadilan bertambah banyak, sehingga perlu diubah menjadi searchable combobox.
- Keputusan:
  1. **Penghapusan Master Data Jaksa Penuntut**:
     - Menghapus submenu `Jaksa Penuntut` dari menu Master Data Administrator (`MenuHelper.php`).
     - Menghapus tipe `'jaksa-penuntut'` dari `MasterDataService.php`, `MasterDataController.php`, `SaveMasterDataRequest.php`, serta view `pages/master-data/index.blade.php` dan `_table-partial.blade.php`.
     - Akses langsung ke URL `/master-data/jaksa-penuntut` otomatis mengembalikan `404 Not Found`.
     - Tabel database `jaksa_penuntut` tidak di-drop agar mencegah kehilangan data (*accidental data loss prevention*).
  2. **Penyederhanaan Input & Card Penuntut Umum**:
     - Form Perkara menggunakan `<x-form.searchable-select>` untuk Instansi Kejaksaan dengan search bar (`"Ketik nama kejaksaan..."`).
     - Input Nama Jaksa (`penuntut_umum_nama`) dan NIP Jaksa (`penuntut_umum_nip`) dipertahankan secara manual.
     - Field Pangkat Jaksa (`penuntut_umum_pangkat`) dihapus dari form, `SavePerkaraBerkasRequest.php`, `PerkaraService.php`, dan `card-penuntut-umum.blade.php`.
  3. **Combobox Nama Pengadilan pada Putusan Perkara**:
     - Pada form Keputusan Pengadilan (`pages/tahapan/form.blade.php`), dropdown `pengadilan_instansi_id` diubah menjadi `<x-form.searchable-select>` dengan search bar (`"Ketik nama pengadilan..."`), dengan tetap mempertahankan field nomor surat, tanggal surat, amar putusan, upload dokumen PDF maks 5 MB, dan catatan.
- Konsekuensi: Form perkara lebih ringkas, navigasi dan input pencarian instansi lebih cepat, dan seluruh 255 pengujian otomatis Pest PHP serta Laravel Pint lulus 100%.

---

## D-066 : Peningkatan Ukuran Logo pada Sidebar Aktif dan Mode Terlipat (Collapsed)

- Tanggal: 2026-10-27
- Status: Disepakati & Diimplementasikan (via proses klarifikasi /grill-me)
- Konteks: Logo aplikasi BAPTwo pada sidebar sebelumnya berukuran kecil (`h-8` atau 32 px) sehingga terlihat kurang jelas di dalam container header sidebar yang memiliki tinggi `h-16` (64 px) dan lebar sidebar `w-[290px]`. Pengguna menginginkan logo pada sidebar saat aktif (terbuka) diperbesar agar lebih jelas dan tegas.
- Keputusan:
  1. **Ukuran Logo Sidebar Saat Aktif (Terbuka/Hovered/Mobile Open)**:
     - Ditingkatkan dari `h-8` (32 px) menjadi `h-12` (48 px) pada kedua varian tema (light mode `/images/logo/logo.svg` dan dark mode `/images/logo/logo-dark.svg`).
     - Atribut dimensi numerik disesuaikan secara proporsional sesuai aspek rasio asli SVG (~1.148 : 1) menjadi `width="55" height="48"` untuk mencegah layout shift (CLS).
     - Posisi perataan logo pada container header sidebar diatur menjadi `justify-center` secara konsisten (baik saat aktif maupun saat terlipat), sehingga logo selalu berada tepat di tengah (*center aligned*) header sidebar.
     - Tinggi container header sidebar tetap dipertahankan `h-16` (64 px) agar garis pembatas bawah (*border bottom*) tetap presisi dan sejajar lurus dengan app header navbar atas.
  2. **Ukuran Logo Sidebar Saat Terlipat (Collapsed / Mini Sidebar)**:
     - Logo ikon (`/images/logo/logo-icon.svg`) disesuaikan dari `h-8` (32 px) menjadi `h-9` (36 px) dengan atribut numerik `width="41" height="36"`, sehingga tampil lebih menonjol dan proporsional di dalam sidebar mini selebar 90 px.
  3. **Penyesuaian Safeguard Pengujian Otomatis**:
     - `tests/Feature/PerformanceOptimizationTest.php` diperbarui untuk memvalidasi atribut dimensi logo baru `width="55" height="48"`.
- Konsekuensi: Logo BAPTwo tampil jauh lebih jelas, tegas, dan berwibawa di sidebar tanpa mengganggu keselarasan vertikal tata letak antarmuka. Seluruh 255 pengujian otomatis Pest PHP dan linter Laravel Pint lulus 100%.

---

## D-067 : Standarisasi Istilah Tenggang Waktu, Pembesaran Card SPDP & Batas Waktu, serta Pengurutan Riwayat Tahapan Progress Berpanah

- Tanggal: 2026-10-27
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan hasil peninjauan operasional hukum dan antarmuka BAPTwo:
  1. Istilah akronim "SLA" (*Service Level Agreement*) disesuaikan menjadi istilah hukum operasional baku berbahasa Indonesia yaitu **"Tenggang Waktu"** pada seluruh teks antarmuka (UI) pengguna (badge status, kartu dashboard, filter tabel, dan halaman pengaturan).
  2. Komponen informasi "Tanggal Terbit SPDP & Batas Waktu Penyelesaian" pada daftar tersangka di halaman Detail Perkara (`resources/views/pages/perkara/show.blade.php`) sebelumnya berukuran kecil dan kurang menonjol, sehingga perlu diperbesar dengan container luas, ikon di dalam kotak, dan tipografi tanggal yang lebih tegas.
  3. Komponen "Riwayat Tahapan Progress" pada berkas perkara tersangka sebelumnya hanya menampilkan badge tahap sesuai urutan pembuatan di database tanpa pengurutan alur resmi dan tanpa tanda panah penghubung.
- Keputusan:
  1. **Penggantian Istilah UI "SLA" menjadi "Tenggang Waktu"**:
     - `BerkasPerkara::getSlaBadgeAttribute()`: label `'Selesai Melebihi Tenggang Waktu'`, `'Melebihi Tenggang Waktu'`, `'Mendekati Batas Tenggang Waktu'`, dan `'Tenggang Waktu Berjalan'`.
     - `Perkara::getSlaBadgeInfo()`: label `'Tenggang Waktu belum berjalan'`.
     - Dashboard Donut & Panel SLA (`_case-status-content.blade.php`): label `'Dalam Tenggang Waktu'`, `'Dalam Tenggang Waktu (> 7 hari)'`, dan `'Melewati Tenggang Waktu'`.
     - Header kolom tabel perkara (`data-perkara`, `seluruh-perkara`, `pencarian-perkara`): `Status & Tenggang Waktu`.
     - Potensi Terlimpah & Pengaturan Umum: seluruh teks "SLA" diganti menjadi "Tenggang Waktu".
     - Kolom database (`sla_deadline`) dan parameter query string (`sla`) tetap dipertahankan agar tidak merusak kompatibilitas data.
  2. **Pembesaran Card SPDP & Batas Waktu Penyelesaian**:
     - Card menggunakan kontainer `p-4 sm:p-5`, ikon di dalam kotak `h-10 w-10 rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400`, tanggal tebal `text-base font-bold tabular-nums`, keterangan `(Tenggang Waktu 1 Bulan Kalender)`, dan badge status `px-3.5 py-1.5 text-sm font-semibold`.
  3. **Pengurutan Riwayat Tahapan Progress Berpanah**:
     - Menambahkan accessor `getOrderedTahapanAttribute()` di model `BerkasPerkara` yang mengurutkan tahapan berdasarkan `JenisTahap::urutan()`.
     - Bagian "Riwayat Tahapan Progress" di halaman Detail Perkara dan tabel dashboard menggunakan `ordered_tahapan` dan menyisipkan ikon panah `<i class="fi fi-rr-arrow-small-right"></i>` di antara setiap tahapan (`@if (! $loop->last)`).
- Konsekuensi: Tampilan operasional hukum lebih baku, informasi batas waktu sangat mudah dibaca, alur tahapan progress tersaji kronologis dan runtut, serta seluruh 256 tes otomatis Pest PHP dan linter Laravel Pint 100% lulus.

---

## D-068 : Perbaikan Pemanggilan Nilai Tanggal pada Seluruh Form Draft (`<x-form.date-picker>`) dan Eksklusivitas Penyidik PPNS Utama pada Penyidik Anggota

- Tanggal: 2026-10-27
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan temuan operasional pengguna:
  1. Ketika pengguna menginput data tanggal pada form tahapan/perkara dan menyimpannya sebagai **Draft**, saat form dibuka kembali untuk dilanjutkan/diedit, kolom tanggal tidak terisi oleh nilai tanggal yang sudah disimpan sebelumnya. Investigasi kode menunjukkan:
     - Komponen `resources/views/components/form/date-picker.blade.php` hanya mendeklarasikan prop `defaultDate` tanpa `value`, sedangkan form Laporan Kejadian (`create`/`edit`) dan Sprindik (`create`/`edit`) mengoper `:value="..."` sehingga atribut `value` bocor ke `<div>` pembungkus sementara `<input>` tetap kosong dan Flatpickr menerima `defaultDate: null`.
     - Elemen `<div x-data>` pada `date-picker.blade.php` mendefinisikan `init()` di dalam objek `x-data` sekaligus atribut `x-init="init()"`, yang memicu eksekusi ganda pada Alpine.js v3.
     - Method `TahapanService::saveSubStage()` sebelumnya mengabaikan penyimpanan draft sub-tahap (`P-16`, `P-17P`, `P-18P`, `P-19`) jika nomor surat belum diisi meskipun tanggal surat sudah diisi.
  2. Pada form Data Perkara (`resources/views/pages/perkara/form.blade.php`), penyidik yang sudah dipilih sebagai **PPNS Utama** masih muncul dan dapat dipilih kembali di daftar **Penyidik Anggota**. Investigasi menunjukkan bahwa `<x-form.searchable-select>` memancarkan dua event `change` berurutan (`nativeSelect.dispatchEvent(new Event('change'))` lalu `this.$dispatch('change', option.value)`). Karena listener `@change="onUtamaChange($event.target.value)"` membaca `$event.target.value` dari elemen `<div>` pembungkus pada event kedua, nilai `selectedUtamaId` langsung tertimpa menjadi `undefined` (`null`), sehingga fungsi `getAvailableAnggota()` gagal menyaring PPNS Utama.
- Keputusan:
  1. **Standarisasi Prop Tanggal & Inisialisasi `<x-form.date-picker>`**:
     - Menambahkan prop `'value' => null` pada `resources/views/components/form/date-picker.blade.php`, menormalisasi nilai tanggal ke format `Y-m-d` melalui `$resolvedDate = $defaultDate ?? $value`, menghapus pemanggilan ganda `x-init="init()"`, dan merender `value="{{ $resolvedDate }}"` secara konsisten pada `<input>`.
     - Menyeragamkan pemanggilan `<x-form.date-picker>` di `laporan-kejadian/create.blade.php`, `laporan-kejadian/edit.blade.php`, `sprindik/create.blade.php`, `sprindik/edit.blade.php`, `perkara/form.blade.php`, dan `tahapan/form.blade.php` agar memanggil format `Y-m-d` dari data model yang tersimpan saat edit draft.
     - Memperbarui `TahapanService::saveSubStage()` agar tetap menyimpan draft sub-tahap apabila tanggal surat (`{$prefix}_tanggal_surat`) atau berkas dokumen diisi meskipun nomor surat masih kosong pada mode Draft.
  2. **Eksklusivitas Penyidik PPNS Utama vs Penyidik Anggota (UI & Server)**:
     - Memperbaiki handler `onUtamaChange($event)` dan `addAnggota($event)` pada `resources/views/pages/perkara/form.blade.php` dengan helper `extractId(eventOrVal)` yang mengekstrak ID penyidik secara akurat baik dari `CustomEvent.detail` maupun `HTMLSelectElement.value` tanpa mereset `selectedUtamaId` menjadi `null`.
     - Jika penyidik yang sudah ada di daftar Penyidik Anggota kemudian dipilih menjadi PPNS Utama, penyidik tersebut otomatis dihapus dari daftar Penyidik Anggota (`removeAnggota(this.selectedUtamaId)`).
     - Memperbaiki pengumpulan `$existingPpnsIds` di `PerkaraBerkasController::edit()` menggunakan relasi `$perkara->ppnsUtama->pluck('id')->all()`.
     - Menambahkan validasi server-side di `SavePerkaraBerkasRequest` yang menolak ID penyidik pada `ppns_anggota_ids.*` apabila sama dengan `ppns_utama_id`.
- Konsekuensi: Seluruh input tanggal pada mode Draft di semua form (Laporan Kejadian, Sprindik, Data Perkara/Tersangka, SPDP, P-16, P-17P, Tahap I, P-18P, P-19, P-21, Tahap II, dan Keputusan Pengadilan) terpanggil kembali dengan akurat saat diedit, serta PPNS Utama dijamin tidak dapat dipilih ganda sebagai Penyidik Anggota baik di UI maupun di server.

---

## D-069 : Modal Pop-up Lihat Detail SP3 pada Halaman Detail Perkara

- Tanggal: 2026-10-27
- Status: Disepakati & Diimplementasikan
- Konteks: Sebelumnya, tombol "Lihat SP3" pada kartu tersangka/berkas perkara di halaman Detail Perkara (`resources/views/pages/perkara/show.blade.php`) langsung membuka berkas PDF SP3 tanpa menampilkan rincian metadata SP3 (Nomor SP3, Tanggal SP3, Penyidik Pembuat SP3, serta Alasan & Catatan Penghentian Penyidikan).
- Keputusan:
  1. **Pengubahan Tombol "Lihat SP3" Menjadi Pemicu Pop-up Detail SP3**:
     - Tombol `Lihat SP3` pada daftar berkas perkara di `resources/views/pages/perkara/show.blade.php` diubah dari tautan langsung dokumen menjadi tombol `<button type="button" @click="openViewSp3Modal(...)">` yang membuka modal pop-up Detail SP3 (`resources/views/pages/perkara/_modal-detail-sp3.blade.php`).
  2. **5 Informasi Utama di Dalam Pop-up Detail SP3**:
     - **Nomor SP3**: Menampilkan `$berkas->sp3->nomor_surat` dengan tipografi `font-mono`.
     - **Tanggal SP3**: Menampilkan `$berkas->sp3->tanggal_sp3` dalam format `d-m-Y` (`tabular-nums`).
     - **Penyidik yang Membuat SP3**: Menampilkan nama dan NIP akun penerbit SP3 (`$berkas->sp3->creator`, format `Nama (NIP: ...)`) dengan fallback otomatis ke Penyidik PPNS Utama perkara (`$perkara->ppnsUtama->first()`) apabila kosong.
     - **Lihat File SP3**: Menampilkan informasi nama berkas PDF SP3 beserta tombol **Lihat File SP3** yang terhubung dengan In-App PDF Preview Modal (`data-pdf-preview`, `data-pdf-title`, `data-pdf-download`). Jika berkas dokumen tidak tersedia, menampilkan fallback informatif `"Dokumen file SP3 tidak tersedia"`.
     - **Alasan & Catatan Penghentian Penyidikan**: Menampilkan isi `$berkas->sp3->keterangan` secara utuh (`whitespace-pre-line`).
  3. **Eager Loading & Kompatibilitas Modal Bertumpuk**:
     - Menambahkan eager loading relasi `'tersangkas.berkasPerkara.sp3.creator'` pada `PerkaraBerkasController::show()` untuk mencegah `LazyLoadingViolationException`.
     - Menggunakan backdrop klik terpisah (`@click="closeViewSp3Modal()"`) dan `@click.stop` pada dialog modal Detail SP3 (`z-99999`) agar ketika pengguna membuka modal pratinjau PDF (`z-999999`) di atasnya, klik di dalam modal PDF tidak menutup modal Detail SP3 di bawahnya.
- Konsekuensi: Pengguna dari seluruh role dapat meninjau rincian surat penghentian penyidikan (SP3) beserta alasan penghentiannya secara cepat dan terstruktur sebelum atau sembari membuka dokumen fisik PDF SP3.

---

## D-070 : Perbaikan Keamanan, Otorisasi, Logika Domain, Performa, dan UI (Audit Review 28 Okt 2026)

- Tanggal: 2026-10-28
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan hasil audit menyeluruh kode sistem, ditemukan beberapa potensi celah otorisasi, anomali logika domain tahapan, query memori inefisien, ketidakkonsistenan zona waktu, dan residu inline SVG.
- Keputusan:
  1. **Keamanan & Otorisasi**:
     - Route `/data-petugas` (GET & POST) diproteksi khusus untuk Administrator dan Penyidik; role Pimpinan dan Umum ditolak (403 Forbidden). Penyidik diizinkan mengubah role/status petugas non-admin (role target: `penyidik`, `pimpinan`, `umum`), dan dilarang mengubah akun dirinya sendiri.
     - Halaman `/recovery-data` dan rute `/sampah` diproteksi middleware `can:manage-users` dan `Gate::authorize('manage-users')` (khusus Administrator).
     - Form request master data `SaveMasterDataRequest` meneruskan parameter route `$type` ke Gate `manage-master-data`, dan Gate menolak `$type === null` untuk non-admin.
     - Form request `StoreDpoRequest` menggunakan otorisasi gate `toggle-dpo`.
  2. **Logika Domain & Validasi**:
     - `UpdateTahapanRequest` mewajibkan upload file PDF (`required`) jika aksi adalah `'lanjut'` (bukan draft), tahapan bukan sub-tahap opsional, dan tahapan belum memiliki dokumen tersimpan di database.
     - `TahapanPerkaraController::update` membungkus pemanggilan simpan tahapan dengan `try/catch (\DomainException $e)` dan mengembalikan flash error toast yang informatif.
     - `TahapanService::saveSubStage` menyimpan sub-tahapan sebagai `'draft'` (`finalized_at = null`) jika tahapan induk adalah draft atau nomor/tanggal sub-tahap belum lengkap. Status `'final'` hanya diberikan jika induk difinalkan dan sub-tahap lengkap, atau jika sub-tahap telah final sebelumnya.
     - Seluruh 10 toast di `PerkaraBerkasController`, `LaporanKejadianController`, dan `SprindikController` menggunakan properti model yang benar: `$perkara->nomor_laporan_kejadian`.
     - `SprindikController::show` menghapus forging data palsu `'SP/001/IX/2026'` dan mengembalikan `abort_if(..., 404)` jika Sprindik belum pernah dibuat untuk perkara tersebut.
     - Filter `sla` pada `PerkaraService::getDataPerkaraList` diselaraskan dengan `SeluruhPerkaraController` (`belum_spdp`, `dalam_sla`, `mendekati_sla`, `lewat_sla`) berbasis `sla_deadline` dan zona waktu `DashboardService::ZONA_WAKTU`.
     - Filter `status_formal` pada `PerkaraService::getProgressPerkaraList` menggunakan scope resmi `BerkasPerkara` (`dalamProses()`, `selesai()`, `tidakSelesai()`).
  3. **Performa, Zona Waktu & UI**:
     - `PotensiTerlimpahController::index` mengeliminasi hidrasi koleksi in-memory `$activeWithSpdp` dan beralih ke kueri SQL langsung berbasis kolom `sla_deadline` dan `Carbon::today(DashboardService::ZONA_WAKTU)`.
     - `BerkasPerkara::getSisaHariSlaAttribute()` dan `SeluruhPerkaraController` distandarisasi menggunakan `Carbon::now(DashboardService::ZONA_WAKTU)` dan `Carbon::today(DashboardService::ZONA_WAKTU)`.
     - `export-excel-modal.blade.php` menggunakan `DashboardService::getAvailableYears()` untuk kueri distinct tahun database, membetulkan typo toast `'Siap Didownload'`, dan mengganti 2 spinner inline `<svg>` dengan ikon Flaticon `<i class="fi fi-rr-spinner animate-spin ..."></i>`.
- Konsekuensi: Seluruh 14 poin temuan terselesaikan, integritas otorisasi terjamin, alur tahapan draft aman dari bypass dokumen, performa kueri optimal tanpa memory leak, dan seluruh test suite lulus 100%.

---

## D-071 : Konsolidasi Role Pimpinan Menjadi Role Umum (3 Role Resmi: Administrator, Penyidik, Umum)

- Tanggal: 2026-10-28
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan permintaan pihak penyidikan, akun pimpinan menggunakan role **`Umum`** (`umum`). Sebelumnya di sistem terdapat dua opsi role dengan hak akses dan menu navigasi yang 100% identik yaitu `Pimpinan` / `Pemimpin` (`pimpinan`) dan `Umum` (`umum`), sehingga kemunculan dua nama role berbeda untuk fungsi yang sama di dropdown Manajemen Pengguna (`/pengaturan`) dan Data Petugas (`/data-petugas`) membingungkan pengguna.
- Keputusan:
  1. **Konsolidasi `App\Enums\UserRole` Menjadi 3 Role Resmi**:
     - Enum `UserRole` disederhanakan menjadi tepat 3 case:
       - `ADMINISTRATOR = 'administrator'` (`Administrator`)
       - `PENYIDIK = 'penyidik'` (`Penyidik`)
       - `UMUM = 'umum'` (`Umum`)
     - Untuk menjaga kompatibilitas mundur terhadap kode dan pengujian otomatis, disediakan konstanta alias `public const PIMPINAN = self::UMUM;` pada `UserRole`, normalisasi otomatis `setAttribute('role', 'pimpinan')` ke `UserRole::UMUM` pada model `User`, serta method `$user->isPimpinan()` yang mengarah ke `$this->role === UserRole::UMUM`.
  2. **Migrasi Database & Seeder**:
     - Migrasi `2026_10_28_000001_consolidate_pimpinan_role_to_umum_on_user_table.php` mengonversi seluruh akun yang sebelumnya ber-role `'pimpinan'` menjadi `'umum'` dan memperbarui definisi kolom `user.role` menjadi `ENUM('administrator', 'penyidik', 'umum')`.
     - Akun dev seeder `pimpinan@baptwo.test` di `DatabaseSeeder.php` tetap mempertahankan emailnya untuk kemudahan pengujian dev, dengan role `UserRole::UMUM` (`'umum'`).
  3. **Penyeragaman Label dan Pilihan Role di Seluruh UI**:
     - Halaman Data Petugas (`/data-petugas`) hanya menampilkan pilihan role non-admin **`Penyidik`** dan **`Umum`** pada filter maupun modal penugasan (menghapus opsi `Pimpinan` dan menyederhanakan label `Umum (Pimpinan Terbatas)` menjadi `Umum`).
     - Halaman Manajemen Pengguna (`/pengaturan`) otomatis hanya menampilkan 3 role (`Administrator`, `Penyidik`, `Umum`) dari `UserRole::cases()` dan subjudul halaman disesuaikan menjadi `"Kelola akun Penyidik, Umum, dan Administrator BAPTwo (Soft Delete aktif)."`.
- Konsekuensi: Struktur role BAPTwo menjadi bersih, tunggal, dan sesuai permintaan operasional penyidikan (3 role: Administrator, Penyidik, Umum) tanpa memutus kompatibilitas test suite yang sudah ada.

---

## D-072 : Pengurutan Tabel Tahapan & Perkara Berdasarkan Waktu Input Terbaru ke Sistem

- Tanggal: 2026-10-28
- Status: Disepakati & Diimplementasikan
- Konteks: Sebelumnya, tabel daftar pada halaman Laporan Kejadian (`/laporan-kejadian`), Sprindik (`/sprindik`), SPDP (`/spdp`), Tahap 1 (`/tahap-1`), P-21 (`/p21`), Tahap 2 (`/tahap-2`), dan Putusan Perkara (`/putusan-perkara`) diurutkan berdasarkan kolom tanggal surat (`tanggal_laporan_kejadian`, `tanggal_surat_perintah`, `tanggal_surat` DESC). Akibatnya, ketika pengguna baru saja menginput data yang memiliki tanggal surat lebih lampau atau menyimpan Draft tanpa tanggal surat (`NULL`), data yang baru saja diinput tersebut tidak muncul pada baris paling atas (nomor 1).
- Keputusan:
  1. **Pengurutan Tabel di `PerkaraService` (`app/Services/PerkaraService.php`)**:
     - `getLaporanKejadianList()` dan `getAvailableForSprindik()` diurutkan menggunakan `orderByDesc('created_at')->orderByDesc('id')`.
     - `getSprindikList()`, `getDataPerkaraList()`, dan `getAvailableForPerkara()` (yang disimpan dengan memperbarui baris pada tabel `perkara`) diurutkan menggunakan `orderByDesc('updated_at')->orderByDesc('created_at')->orderByDesc('id')`.
  2. **Pengurutan Tabel Tahapan di `TahapanService` (`app/Services/TahapanService.php`)**:
     - `paginateTahapanQuery()` (melayani halaman SPDP, Tahap 1, P-21, Tahap 2, dan Putusan Perkara) serta `queryAvailableBerkas()` dan `getAvailableBerkasForSpdp()` diurutkan menggunakan `orderByDesc('created_at')->orderByDesc('id')`.
- Konsekuensi: Setiap data yang baru saja diinput atau disimpan oleh petugas di seluruh 8 halaman daftar perkara dan tahapan selalu tampil langsung di baris pertama (nomor 1) terlepas dari tanggal surat yang dipilih pada form.

---

## D-073 : Fleksibilitas DPO Seluruh Tahapan Pra-Putusan, Proteksi Tahapan Induk Final Sub-Tahap, dan Pembersihan Dead Code

- Tanggal: 2026-10-28
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan hasil peninjauan operasional hukum dan review kode:
  1. Status DPO dapat terjadi pada tahap mana pun (mulai Pra-SPDP, SPDP, Tahap 1, P-21, Tahap 2, hingga sebelum Putusan Pengadilan final tercatat) sesuai aturan `PRD.md` Bagian F-24 s.d. F-27. Validasi sebelumnya memblokir penetapan DPO pada berkas yang kolom `status_formal`-nya non-null (misal saat Tahap 2).
  2. Saat SPDP atau Tahap 1 yang telah final dibuka kembali untuk melengkapi sub-tahap (P-16/P-17P atau P-18P/P-19), data tahapan induk harus terlindungi dari penimpaan catatan, penambahan file dokumen, atau pencatatan ulang log finalisasi tahapan induk (PRD F-18).
  3. Scheduler tahunan 1 Januari (`perkara:close-unfinished`) perlu menggunakan scope `dalamProses()` dan mengunci zona waktu WITA (`Asia/Makassar`).
  4. Filter periode tahun/bulan di `PerkaraService::getDataPerkaraList()` diselaraskan dengan Donut Chart agar berbasis `tanggal_laporan_kejadian`.
  5. Pemulihan berkas dan perkara dari Sampah (`/recovery-data`) diseragamkan hanya untuk Administrator (`can:manage-users`).
  6. Dead code yang tidak terpakai dibersihkan: `deleteLaporanKejadian()`, `removeSprindik()`, alias `berkasPerkara()`, dan controller wrapper satu baris `PengaturanUmumController` yang digantikan oleh `Route::view()`.
- Keputusan:
  1. `DpoService::activateDpo()` mengizinkan penetapan DPO pada berkas perkara di setiap tahapan, dan hanya menolak jika berkas telah diterbitkan SP3, telah berstatus 'tidak_selesai', atau telah memiliki Keputusan Pengadilan berstatus final.
  2. `TahapanService::saveStage()` menambahkan pengecekan `$existingTahap !== null && $existingTahap->isFinal()`. Jika tahapan induk sudah final, sistem hanya mengeksekusi `saveSubStage()` tanpa menyentuh record tahapan induk.
  3. `CloseUnfinishedCasesCommand` menggunakan `dalamProses()` dan `Carbon::now(DashboardService::ZONA_WAKTU)`. Scheduler di `routes/console.php` diatur `->timezone(DashboardService::ZONA_WAKTU)`.
  4. `PerkaraService::getDataPerkaraList()` menyelaraskan filter tahun dan bulan ke `tanggal_laporan_kejadian`.
  5. Route dan controller pemulihan Sampah (`restorePerkara` dan `restoreBerkas`) diproteksi `can:manage-users`.
  6. Mengeliminasi `deleteLaporanKejadian`, `removeSprindik`, alias `berkasPerkara()`, dan `PengaturanUmumController` (digantikan `Route::view()`).
- Konsekuensi: Operasional DPO fleksibel sesuai dinamika lapangan hukum pidana hingga sebelum vonis pengadilan, integritas data tahapan final terjamin aman, akurasi scheduler batas tahun dan filter drill-down teruji presisi, serta seluruh 277 test suite Pest PHP dan linter Laravel Pint lulus 100%.

---

## D-074 : Halaman Log Aktivitas & Audit Sistem Terpusat pada Sub-menu Pengaturan Administrator

- Tanggal: 2026-10-28
- Status: Disepakati (Rencana Implementasi Siap Dieksekusi)
- Konteks: Sebelumnya, catatan `audit_log` hanya ditampilkan secara terpisah pada halaman Detail Perkara (`resources/views/pages/perkara/show.blade.php`) dan 6 aktivitas penyidikan terkini di Dashboard (`<x-dashboard.recent-activities>`). Sementara itu, sistem juga mencatat aktivitas lintas-perkara dan keamanan seperti percobaan login gagal (`login_failed`), percobaan login akun nonaktif (`login_inactive`), ekspor Excel LAPJU (`export_excel`), dan pengaturan petugas (`atur_petugas`). Administrator memerlukan satu halaman terpusat untuk memantau seluruh rekam jejak audit dan keamanan sistem sesuai `PRD.md` (UC-05 & F-31).
- Keputusan:
  1. **Penempatan Sub-menu di Bawah Pengaturan (Khusus Administrator)**:
     - Menambahkan sub-menu **"Log Aktivitas"** (`/pengaturan/log-aktivitas`, rute `pengaturan.log-aktivitas`) di dalam menu dropdown **Pengaturan** pada `MenuHelper::getSystemItems()` (bersama *User*, *Petugas*, dan *Umum*).
     - Rute diproteksi di dalam grup middleware `can:manage-users` dan `Gate::authorize('manage-users')` di `AuditLogController@index`. Log bersifat mutlak *read-only* (tidak dapat diubah atau dihapus).
  2. **Kelengkapan Pencatatan Audit Manajemen Pengguna & Kategori Aktivitas**:
     - Melengkapi `UserService` (`app/Services/UserService.php`) agar mencatat aksi `create_user`, `update_user`, `delete_user`, dan `restore_user` ke `AuditTrailService` tanpa pernah menyertakan nilai `password` atau hash kata sandi di `data_lama` maupun `data_baru`.
     - Menambahkan konstanta kategori aktivitas (`CATEGORY_PENYIDIKAN`, `CATEGORY_KEAMANAN`, `CATEGORY_ADMINISTRASI`), daftar aksi `SECURITY_ACTIONS` dan `ADMIN_ACTIONS`, method `categoryOptions()`, query scope `scopeCategory()`, serta melengkapi kamus `AuditLog::actionMeta()` untuk seluruh aksi keamanan dan administrasi.
  3. **Kartu Ringkasan, Live Filter Multi-Kriteria & Modal Detail Perubahan Data**:
     - Menampilkan 4 kartu ringkasan di atas tabel: *Total Aktivitas Tercatat*, *Aktivitas Hari Ini*, *Peringatan Keamanan & Login*, dan *Administrasi & Ekspor Data*.
     - Menyediakan toolbar `<x-common.table-filter>` dengan dukungan live filter AJAX (`X-Filter-Partial: 1`) yang mencakup filter Kategori Aktivitas (`kategori`), Pelaksana (`pelaksana`, termasuk opsi `sistem` untuk aksi otomatis/tanpa user), Rentang Tanggal (`tanggal_dari`, `tanggal_sampai`), dan Pencarian (`search` untuk aksi, IP address, nama/NIP/email petugas, atau nomor perkara).
     - Menyediakan tombol **Detail** pada setiap baris yang membuka **Modal Pop-up Detail Log** (`_modal-detail-log.blade.php`) berisi informasi waktu (WITA), pelaksana, IP address (`font-mono`), tautan ke Detail Perkara terkait, serta komparasi berdampingan **Data Sebelumnya (`data_lama`)** vs **Data Tercatat / Baru (`data_baru`)**.
- Konsekuensi: Administrator memiliki visibilitas penuh dan terpusat atas seluruh aktivitas penyidikan, administrasi pengguna, ekspor data, serta peringatan keamanan login di BAPTwo dengan standar performa bebas N+1 (`withTrashed()`) dan akuntabilitas penuh.

---

## D-075 : Penataan Ulang Layout Dashboard BAPTwo (Opsi A: Fokus Operasional & Proporsi Asimetris)

- Tanggal: 2026-10-28
- Status: Disepakati & Diimplementasikan
- Konteks: Berdasarkan evaluasi antarmuka (`antislop-ui`) dan sesi penyelarasan bersama pengguna pada Dashboard BAPTwo (`resources/views/pages/dashboard/ecommerce.blade.php`), ditemukan 4 area yang perlu dirapikan:
  1. Kartu statistik di Baris 1 (`statistics.blade.php`) terlalu tinggi karena padding `p-5`, jarak `mt-3`, dan tambahan baris teks ke-4 (`Maks. 30 Hari Kalender`) pada kartu ke-5.
  2. Proporsi `6 : 6` di Baris 2 membuat grafik batang 12 bulan (`monthly-cases-chart.blade.php`) beserta 3 badge ringkasannya terasa sempit, sementara kartu Donut (`case-status.blade.php`) memiliki ruang kosong berlebih.
  3. Tabel operasional `Progress Perkara (5 Prioritas Utama)` (`progress-perkara.blade.php`) berada di paling bawah (Baris 4) setelah komponen aktivitas yang panjang, sehingga penyidik/pimpinan harus scroll jauh untuk melihat daftar berkas prioritas.
  4. Baris `recent-activities.blade.php` (6 item besar) dan `average-stage.blade.php` (4 baris tahap dengan sub-teks berulang *"Rata-rata waktu penyelesaian"*) memiliki tinggi vertikal yang timpang.
- Keputusan:
  1. **Susunan Baris & Proporsi Kolom Asimetris (`resources/views/pages/dashboard/ecommerce.blade.php`)**:
     - **Baris 1 (5 Kolom)**: `<x-dashboard.statistics>` dengan padding kompak `p-4`, gap `gap-3.5`, ikon `h-9 w-9`, dan penggabungan teks `"Maks. 30 Hari Kalender"` ke baris deskripsi kartu ke-5.
     - **Baris 2 (Grid 12 Kolom · Rasio `5 : 7`)**: Kiri `lg:col-span-5` (`<x-dashboard.case-status>`) dan Kanan `lg:col-span-7` (`<x-dashboard.monthly-cases-chart>`).
     - **Baris 3 (Lebar Penuh · 12 Kolom)**: `<x-dashboard.progress-perkara>` dinaikkan dari Baris 4 ke Baris 3 agar daftar 5 berkas prioritas langsung terlihat setelah grafik utama.
     - **Baris 4 (Grid 12 Kolom · Rasio `7 : 5`)**: Kiri `lg:col-span-7` (`<x-dashboard.recent-activities>`) dan Kanan `lg:col-span-5` (`<x-dashboard.average-stage>`).
  2. **Pemadatan Feed Aktivitas & Penyeragaman Kartu Rata-rata Tahap**:
     - `DashboardController::index()` dan fallback `recent-activities.blade.php` menampilkan **4 item** aktivitas terkini (`getRecentActivities(4)`) dengan padding item kompak (`p-3`, `space-y-2.5`, ikon `h-9 w-9`).
     - `average-stage.blade.php` menggunakan `flex h-full flex-col justify-between`, menyeragamkan judul header menjadi `text-base font-semibold`, serta menghapus sub-teks berulang *"Rata-rata waktu penyelesaian"* di setiap baris tahap agar tingginya sejajar dengan kartu aktivitas di sebelahnya.
- Konsekuensi: Tampilan Dashboard BAPTwo lebih ringkas, proporsional, bebas timpang tinggi, dan menempatkan tabel berkas prioritas lebih dekat ke pandangan utama pengguna tanpa memutus kompatibilitas pengujian yang sudah ada.



