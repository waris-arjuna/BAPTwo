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

