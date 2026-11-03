# ARCHITECTURE — BAPTwo

Arsitektur teknis BAPTwo. Aturan bisnis merujuk ke `PRD.md`; log keputusan teknis ke `DECISIONS.md`.

---

## 1. Gambaran Umum

- Monolit **Laravel 12** server-rendered (Blade + Alpine.js + Tailwind CSS v4) di atas template TailAdmin yang telah dirampingkan bersih (D-036, D-037).
- Satu database **MySQL 8** di lingkungan Laragon (D-001).
- Single locale **id** (Bahasa Indonesia) dan tata letak **LTR saja** (D-003, D-004).
- Tidak ada API publik. Integrasi eksternal hanya proxy **API Kode Wilayah** (`https://api.kodewilayah.web.id`) dengan fallback JSON offline (PRD F-37, D-015).
- Ekspor resmi pelaporan rekap perkara format template resmi LAPJU Excel (.xlsx) 45 kolom terstandarisasi BPOM (D-035, D-055).

```mermaid
flowchart TD
    U["Pengguna (Browser LTR)"] -->|HTTP / HTTPS| R["Routing Laravel (routes/web.php)"]
    R -->|Middleware Auth & Captcha 30s| C["Controller Layer"]
    C -->|Validasi Input Ketat| FR["Form Request (app/Http/Requests)"]
    C -->|Otorisasi Role-Based| P["Policy & Gate RBAC"]
    C -->|Pusat Logika Domain| S["Domain Service Layer (app/Services)"]
    
    subgraph Services ["Layanan Domain Utama"]
        PS["PerkaraService (Pra-SPDP & Berkas)"]
        TS["TahapanService (Workflow & Locking)"]
        DS["DashboardService (Monitoring, SLA & Analitik)"]
        WS["WilayahService (Cache 30 Hari + Proxy)"]
    end
    
    S --> Services
    PS -->|1 Perkara = 1 Berkas| M["Eloquent Models (app/Models)"]
    TS -->|Kunci Mutlak / DPO Gate / SP3| M
    M --> DB[("MySQL 8 (Tabel Singular)")]
    
    Services -->|Audit Trail Kronologis| AL["Tabel audit_log"]
    SCH["Laravel Scheduler (00:00 1 Jan WITA)"] -->|Tutup Otomatis Tidak Selesai| TS
    
    C -->|Ekspor LAPJU 45 Kolom| EXP["LapjuExport (PhpSpreadsheet + Template)"]
    EXP -->|StreamedResponse .xlsx| U
```

---

## 2. Lapisan & Tanggung Jawab

| Lapisan | Lokasi | Tanggung Jawab |
|---|---|---|
| **Controller** | `app/Http/Controllers` | Menerima HTTP request, memanggil Form Request / Policy / Service, mengembalikan response Blade/JSON/Redirect/Streamed Excel. Bersih dari logika bisnis. |
| **Form Request** | `app/Http/Requests` | Validasi tipe, format, digit (mis. NIK 16 digit), batasan berkas PDF (max 5 MB), dan kelengkapan field input pengguna. |
| **Policy & Gate** | `app/Policies` & `AppServiceProvider` | Otorisasi hak akses berbasis peran (Penyidik, Pimpinan, Administrator) termasuk gate `export-excel` dan pembatasan Master Data. |
| **Domain Service** | `app/Services` | Pusat seluruh aturan bisnis: transisi tahapan progress, kalkulasi bobot (0%, 15%, 55%, 85%, 100%), penguncian permanen, DPO gate, trigger formal, audit trail, serta kalkulasi analitik dashboard zona Makassar (D-056). |
| **Model** | `app/Models` | Definisi tabel singular (`protected $table = '...'`), query scopes (`dalamProses`, `selesai`, `tidakSelesai`, `tahunLk`), relasi Eloquent, accessor (`alamat_lengkap`), casting, dan soft deletes. |
| **Enum** | `app/Enums` | Representasi tipe aman PHP 8.2: `UserRole`, `JenisTahap`, `StatusProses`, `StatusFormal`. |
| **Export Engine** | `app/Exports` | Generator laporan Excel (.xlsx) resmi template LAPJU 45 kolom (A s.d. AS) berbasis PhpSpreadsheet IOFactory, freeze pane A7, nomor urut 1–45, dan penanganan periode kosong. |
| **View & Component** | `resources/views` | Template Blade modular TailAdmin, interaktivitas Alpine.js, icon resmi Flaticon (`fi fi-rr-*`), teks UI via `__()` dan `lang/id.json`. |

---

## 3. Model Data & Skema Database

Semua tabel domain wajib menggunakan nama **singular `snake_case` Bahasa Indonesia** (D-008, D-009) dan menyertakan kolom `created_by`, `updated_by`, serta `deleted_at` (`SoftDeletes`).

### 3.1 Relasi Entitas (ERD Mutakhir)

```mermaid
erDiagram
    jabatan ||--o{ user : "jabatan"
    bidang ||--o{ user : "bidang"
    panggol ||--o{ user : "pangkat/golongan"

    alamat ||--o{ user : "domisili user"
    alamat ||--o{ tkp : "lokasi tkp (dengan nama sarana)"
    alamat ||--o{ tersangka : "domisili tersangka"

    tkp ||--|| perkara : "tkp perkara"
    user ||--o{ perkara_ppns : "petugas"
    perkara ||--o{ perkara_ppns : "tim ppns (utama & anggota)"

    perkara ||--|| tersangka : "1 perkara = 1 tersangka"
    perkara ||--|| berkas_perkara : "1 perkara = 1 berkas (nomor LK identik)"
    tersangka ||--|| berkas_perkara : "1 tersangka = 1 berkas (UNIQUE)"

    berkas_perkara ||--o{ berkas_pasal : "pasal berlapis"
    pasal ||--o{ berkas_pasal : "pasal UU"

    instansi_hukum ||--o{ jaksa_penuntut : "menaungi jpu"
    instansi_hukum ||--o{ tahapan_perkara : "kejaksaan / pengadilan"
    jaksa_penuntut ||--o{ tahapan_perkara : "jaksa penuntut"
    berkas_perkara ||--o{ tahapan_perkara : "tahapan progress (maks 1x)"

    berkas_perkara ||--o{ dpo : "riwayat penetapan dpo"
    berkas_perkara ||--o| sp3 : "dokumen sp3 terminal"
    berkas_perkara ||--o{ dokumen : "dokumen tahapan/spdp"
    perkara ||--o{ dokumen : "dokumen lapkej/sprindik"
    berkas_perkara ||--o{ audit_log : "case history terindeks"
```

### 3.2 Spesifikasi Tabel Utama

1. **`alamat`** (D-009, D-015, D-039)
   - `id`: bigint unsigned, PK
   - `nama`: string(150), nullable (nama toko, kios, gudang, atau sarana usaha TKP)
   - `provinsi_kode`, `provinsi_nama`: string(15), string(100), nullable (Kalsel = 63)
   - `kabupaten_kode`, `kabupaten_nama`: string(15), string(100), nullable
   - `kecamatan_kode`, `kecamatan_nama`: string(15), string(100), nullable
   - `desa_kode`, `desa_nama`: string(15), string(100), nullable
   - `rt`, `rw`: string(5), nullable
   - `alamat_detail`: string(255), nullable
   - `created_at`, `updated_at`
   - *Accessor*: `alamat_lengkap` (format gabungan utuh jalan + RT/RW + Desa + Kec + Kab + Prov).

2. **`user`** (D-005, D-008)
   - `id`: bigint unsigned, PK
   - `role`: enum (`administrator`, `pimpinan`, `penyidik`, `umum`), default `penyidik`
   - `jabatan_id`, `bidang_id`, `panggol_id`, `alamat_id`: FK nullable
   - `nama`: string(150), `nip`: string(30) nullable unique, `email`: string(255) unique
   - `password`: string(255)
   - `is_active`: boolean, default true
   - `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`

3. **`tkp`** (Tempat Kejadian Perkara — D-035 Poin 3)
   - `id`: bigint unsigned, PK
   - `alamat_id`: FK -> `alamat.id`
   - `klasifikasi`: enum (`retailer`, `distributor`)
   - `legalitas`: enum (`legal`, `ilegal`) — via radio button UI
   - `modus`: enum (`online`, `offline`) — via radio button UI
   - `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`

4. **`perkara`** (Informasi Pra-SPDP — D-035 Poin 5)
   - `id`: bigint unsigned, PK
   - `nomor_laporan_kejadian`: string(100), unique
   - `tanggal_laporan_kejadian`: date
   - `tahap_laporan_status`: enum (`draft`, `final`)
   - `nomor_surat_perintah`: string(100), unique nullable
   - `tanggal_surat_perintah`: date nullable
   - `tahap_sprindik_status`: enum (`draft`, `final`) nullable
   - `tahap_perkara_status`: enum (`draft`, `final`) nullable
   - `tkp_id`: FK -> `tkp.id` nullable
   - `penuntut_instansi_id`: FK -> `instansi_hukum.id` nullable (Kejaksaan)
   - `penuntut_nama`: string(150), nullable
   - `penuntut_nip`: string(30), nullable
   - `penuntut_pangkat`: string(100), nullable
   - `deskripsi`: text, nullable
   - `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`

5. **`tersangka`** (D-035 Poin 8, 17)
   - `id`: bigint unsigned, PK
   - `perkara_id`: FK -> `perkara.id`, ON DELETE CASCADE
   - `nama`: string(150), `alias`: string(150) nullable, `bin_binti`: string(150) nullable
   - `nik`: string(20) nullable (wajib tepat 16 digit pada finalisasi)
   - `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `agama`, `pekerjaan`, `kewarganegaraan` (default 'WNI')
   - `alamat_id`: FK -> `alamat.id` nullable
   - `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`

6. **`berkas_perkara`** (Tracking, SLA & Progress - D-035 Poin 5, 11, D-058)
   - `id`: bigint unsigned, PK
   - `perkara_id`: FK -> `perkara.id`, ON DELETE CASCADE
   - `tersangka_id`: FK -> `tersangka.id`, UNIQUE (1 tersangka = 1 berkas)
   - `nomor_berkas`: string(150) - **Wajib identik dengan nomor Laporan Kejadian**
   - `tanggal_spdp`: date, nullable (titik awal proses berkas perkara & tahun batas)
   - `status_formal`: enum (`selesai`, `tidak_selesai`), nullable (`null` = label "Dalam Proses")
   - `status_proses`: string(50), default 'Baru' (mengikuti tahapan final terakhir, atau 'SP3')
   - `progress_persen`: unsigned tinyint, default 0 (0, 15, 55, 85, 100)
   - `is_dpo`: boolean, default false
   - `sla_deadline`: date, nullable (1 bulan kalender per tahapan aktif atau batas 31 Desember)
   - `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`

7. **`dokumen`** (Sentralisasi File Wajib PDF — D-007, D-035 Poin 18)
   - `id`: bigint unsigned, PK
   - `perkara_id`, `berkas_perkara_id`, `tahapan_perkara_id`: FK nullable
   - `kategori`: enum (`lapkej`, `sprindik`, `spdp`, `tahapan`, `dpo`, `sp3`, `lainnya`)
   - `nama_dokumen`: string(150), `nama_file_asli`: string(255), `file_path`: string(500)
   - `file_size`: bigint unsigned (maksimum **5 MB** / 5.120 kB pada upload privat)
   - `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`
   - *Viewer Terpadu (D-047)*: Dilengkapi komponen global `<x-common.pdf-preview-modal />` dengan delegated click listener untuk pratinjau instan in-app tanpa meninggalkan konteks halaman.

8. **`sp3`** (Dokumen Penghentian Penyidikan — D-035 Poin 15, 22)
   - `id`: bigint unsigned, PK
   - `berkas_perkara_id`: FK -> `berkas_perkara.id`, UNIQUE
   - `nomor_surat`: string(150)
   - `tanggal`: date
   - `keterangan`: text (input manual uraian pertimbangan penghentian)
   - `dokumen_id`: FK -> `dokumen.id` (file PDF resmi)
   - `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`
   - *Status Terminal (SP3)*: Penerbitan SP3 langsung mengubah `status_proses = 'SP3'` dan `status_formal = 'tidak_selesai'`, serta mengunci berkas dan seluruh tahapan secara permanen di UI dan server (tidak dapat dicabut). Berbeda dengan status DPO yang dapat dicabut, SP3 menghentikan perkara secara mutlak.

---

## 4. Alur Tahapan & State Machine

```mermaid
stateDiagram-v2
    [*] --> Pra_SPDP : Pendaftaran LK & Sprindik (0%)
    Pra_SPDP --> SPDP : Simpan & Lanjut SPDP (15%)
    
    SPDP --> P_16 : Sub-tahap Opsional
    SPDP --> P_17P : Sub-tahap Opsional
    SPDP --> TAHAP_I : Simpan & Lanjut Tahap 1 (55%)
    P_16 --> TAHAP_I
    P_17P --> TAHAP_I
    
    TAHAP_I --> P_18P : Sub-tahap Opsional
    TAHAP_I --> P_19 : Sub-tahap Opsional
    TAHAP_I --> P_21 : Simpan & Lanjut P-21 (85%)
    P_18P --> P_21
    P_19 --> P_21
    
    state DPO_Fork <<choice>>
    P_21 --> DPO_Fork
    DPO_Fork --> DPO_Aktif : Jika DPO Aktif (Seluruh Tahap Lanjutan Diblokir)
    DPO_Fork --> TAHAP_II : Jika Bebas DPO (100% + Status Selesai)
    DPO_Aktif --> TAHAP_II : Setelah Status DPO Dicabut
    DPO_Aktif --> SP3_Terminal : Penerbitan SP3 (Otomatis Cabut DPO)
    
    TAHAP_II --> Keputusan_Pengadilan : Pencatatan Vonis / Inkrah (100%)
    Keputusan_Pengadilan --> [*] : Kunci Mutlak Seluruh Tahapan (Terminal)
    
    SPDP --> SP3_Terminal : Penerbitan SP3 Administratif
    TAHAP_I --> SP3_Terminal
    P_21 --> SP3_Terminal
    SP3_Terminal --> [*] : Status Proses SP3 & Formal Tidak Selesai
```

### 4.1 Progress Berbobot Kumulatif
- **Pra-SPDP**: 0%
- **SPDP**: 15% (titik awal proses berkas perkara & perhitungan SLA 1 bulan kalender per tahap aktif)
- **TAHAP I**: 55% (+40%)
- **P-21 / P-21a**: 85% (+30%)
- **TAHAP II**: 100% (+15%) ➔ Pemicu otomatis status formal **Selesai**.
- **Keputusan Pengadilan**: Tetap 100% (pencatatan nomor & amar putusan pengadilan inkrah).
- *Sub-tahap opsional*: P-16, P-17P, P-18P, P-19 dicatat terintegrasi tanpa menambah persentase progress mandiri.

### 4.2 Tiga Aksi Setiap Tahap
1. **Batal**: Keluar halaman tanpa mengubah data.
2. **Draft**: Simpan formulir tanpa menaikkan progress, tanpa mengunci tahap.
3. **Simpan & Lanjut**: Wajib mengisi field pokok, mengunci tahap permanen (`locked_at`), menaikkan status proses dan persentase progress.

---

## 5. Hak Akses & Keamanan (RBAC)

| Modul / Aksi | Penyidik | Pimpinan | Administrator |
|---|:---:|:---:|:---:|
| Lihat Seluruh Perkara & Detail Berkas | ✅ | ✅ | ✅ |
| Tambah Laporan Kejadian & Sprindik | ✅ | ❌ | ✅ |
| Buat & Edit Draft Berkas Perkara | ✅ | ❌ | ✅ |
| Finalisasi Tahap (Simpan & Lanjut) | ✅ | ❌ | ❌ (Hanya Penyidik pemegang berkas) |
| Edit Tahap yang Sudah Final | ❌ (Terkunci Mutlak) | ❌ (Terkunci Mutlak) | ❌ (Terkunci Mutlak) |
| Aktivasi & Pencabutan DPO | ✅ | ❌ | ✅ |
| Terbitkan Dokumen SP3 | ✅ | ❌ | ✅ |
| Ekspor Excel 44 Kolom (.xlsx) | ✅ | ❌ | ✅ |
| Akses Menu Progress Perkara | ❌ (Dialihkan ke Seluruh Perkara) | ✅ | ✅ |
| Master Data (Pasal & Instansi) | ✅ (Lihat Saja) | ❌ (403 Forbidden) | ✅ (Penuh) |
| Master Data (Jabatan, Bidang, Panggol) | ❌ (403 Forbidden) | ❌ (403 Forbidden) | ✅ (Penuh) |
| Manajemen Pengguna & Reset Password | ❌ | ❌ | ✅ |
| Soft Delete Perkara / Berkas | ✅ | ❌ | ✅ |
| Hard Delete (Penghapusan Fisik) | ❌ (Dilarang Mutlak) | ❌ (Dilarang Mutlak) | ❌ (Dilarang Mutlak) |

---

## 6. Fitur Pelaporan & Ekspor Excel Template LAPJU 45 Kolom (D-055)

- **Engine**: Menggunakan `PhpOffice\PhpSpreadsheet` via `App\Exports\LapjuExport` dengan memuat template resmi `resources/templates/template_lapju.xlsx`.
- **Spesifikasi Format Resmi**:
  - Format huruf seragam **Arial 11** dan layout resmi 45 kolom (Kolom A s.d. AS).
  - **Header Template Resmi**: Baris 1 judul utama "PROSES PENYIDIKAN / HISTORY LAPJU", Baris 2 keterangan periode & tanggal ekspor dinamis, Baris 3–5 pengelompokan tahapan, Baris 6 penomoran urut `1` s.d. `45`.
  - **Freeze Pane**: Terpasang di sel **`A7`** (data perkara dimulai dari baris 7).
  - **Penyajian Data**: 1 Perkara = 1 baris terurut tanggal LK terbaru (`desc`). Nilai ganda disajikan bernomor `1. ...\n2. ...` dengan *wrap text* aktif.
  - **Keamanan Formula**: Dinetralisir dari risiko *formula injection* (`=`, `+`, `-`, `@`) menggunakan format teks eksplisit.
  - **Kolom Keterangan Khusus**: Kolom 44 (AR) memuat amar putusan pengadilan; Kolom 45 (AS) memuat kendala operasional riil (SP3/DPO/P18/P19).
  - **Proteksi Periode Kosong**: Jika periode tidak memiliki data, file tidak diunduh dan sistem menampilkan pop-up notifikasi toast (3s) "Tidak ada data pada periode ini".

---

## 7. Keamanan Sesi & Otentikasi

- **Captcha Dinamis (Internal)**: Operasi pengurangan acak matematika ($A > B$) yang di-render dalam SVG dinamis dengan distorsi lekukan, multi-font, rotasi/skew, dan kurva noise. Memiliki masa berlaku (**TTL**) ketat **30 detik** disertai peringatan visual sebelum kedaluwarsa.
- **Inaktivitas Sesi 30 Menit**: Middleware `SessionTimeout` memantau aktivitas pengguna; jika tidak ada aksi selama 30 menit, sesi ditutup otomatis dan pengguna dialihkan ke halaman login dengan notifikasi peringatan.
- **Eliminasi "Remember Me"**: Untuk menjamin keamanan data penegakan hukum di fasilitas publik/kantor, fitur checkbox persistensi sesi jangka panjang dinonaktifkan.
- **Proteksi Akun Nonaktif & Terhapus**: Pengguna dengan status `is_active = false` atau `deleted_at` terisi ditolak masuk sistem dengan notifikasi toast dan banner peringatan inline setelah kata sandi lolos verifikasi (mencegah enumerasi akun).
- **Force Logout Sesi Dinonaktifkan**: Middleware `EnsureUserIsActive` memeriksa status akun pada setiap HTTP request berautentikasi; jika akun dinonaktifkan atau dihapus saat sedang login, sesi langsung diputus seketika dan diarahkan ke halaman masuk (ref: D-053).

---

## 8. Scheduler & Pergantian Tahun

- **Command**: `php artisan perkara:close-unfinished` (`CloseUnfinishedCasesCommand`).
- **Jadwal Eksekusi**: Tepat tanggal `1 Januari` pukul `00:00:00` WITA (`Asia/Makassar`).
- **Aturan**: Seluruh berkas perkara yang terdaftar di tahun sebelumnya dan belum berstatus `Selesai` otomatis ditutup permanen dengan status formal **Tidak Selesai**. Audit trail dicatat dengan aktor sistem (`user_id = null`).

---

## 9. Testing & Quality Assurance

- Rangkaian pengujian diuji dengan **Pest PHP** (`composer test`) dengan capaian:
  - **231 Automated Feature & Unit Tests** (2.162 assertions) lulus 100%.
  - Zero-warning styling via **Laravel Pint** (`php vendor/bin/pint --test`) pada seluruh file PHP.
- **Seeder Idempoten Komprehensif** (`database/seeders/DummyDataSeeder.php`):
  - 100 Perkara dummy komprehensif mencakup 17 pola tahapan (Pra-SPDP s.d. Putusan Pengadilan, DPO Aktif, DPO Dicabut, SP3 terminal, dan peringatan SLA) di 13 Kabupaten/Kota Kalimantan Selatan (ref: D-053).
  - 20 Pasal UU (Kesehatan, Pangan, Perlindungan Konsumen, Narkotika, Psikotropika, KUHP).
  - 20 Instansi Hukum (Kejati Kalsel, 13 Kejari, Pengadilan Tinggi, 5 Pengadilan Negeri se-Kalsel).
  - 20 Jaksa Penuntut Umum resmi lengkap NIP dan pangkat/golongan.

---

## 10. Arsitektur Dashboard Monitoring & Analitik Perkara (D-048, D-052, D-056, D-057, D-058, D-059)

- **Zona Waktu Operasional Tunggal**: Seluruh tanggal operasional dan evaluasi sisa hari SLA dikunci pada zona waktu **WITA (`Asia/Makassar`)** melalui konstanta `DashboardService::ZONA_WAKTU`.
- **5 Kartu Statistik Utama (`getStatistics`)**:
  1. *Total Perkara*: Total perkara tahun berjalan dihitung murni dari `tanggal_laporan_kejadian` (mencakup seluruh status).
  2. *Perkara Berjalan*: Dihitung dari `scopeDalamProses()` (`status_formal IS NULL OR status_formal = 'dalam_proses'`).
  3. *Menunggu Verifikasi Penyidik*: Dibatasi `scopeDalamProses()` dan kondisi memiliki tahapan draft aktif ATAU progress awal (`progress_persen IS NULL OR progress_persen <= AMBANG_PROGRESS_AWAL_PERSEN` [15% bobot SPDP]). Riwayat draft berkas selesai/SP3 otomatis diabaikan.
  4. *Selesai*: Dihitung murni dari `scopeSelesai()` (`status_formal = 'selesai'`) tanpa fallback progress.
  5. *Rata-rata Waktu Penyelesaian*: Durasi hari kalender dari `tanggal_surat` SPDP s.d. Tahap II dengan eager loading tahapan final, mengabaikan anomali data tanggal terbalik.
- **Donut Chart Perkara Tahun Berjalan (`getCaseStatusData`)**:
  - Native SVG chart tanpa library berat dengan radius `r=54` dan layout 2 kolom responsif `@container`.
  - Persentase bilangan bulat dihitung menggunakan algoritma **Largest Remainder Method (Hare-Niemeyer)** untuk menjamin total akumulasi selalu tepat 100%.
  - 4 Sub-kategori SLA pada Dalam Proses: *Belum Ada SPDP*, *Dalam SLA (> 7 hari)*, *Mendekati Batas (≤ 7 hari)*, dan *Melewati SLA (< hari ini)* via `DATE(sla_deadline)`.
  - Filter tahun berjalan global di atas statistik dan filter bulan pada kartu Donut Chart yang memuat data secara live via AJAX partial.
- **Grafik Tren Perkara Masuk & Selesai (`getMonthlyCasesTrends`)**:
  - Visualisasi grafik batang bertingkat berbasis pustaka `ApexCharts` yang membandingkan Perkara Masuk (`tanggal_laporan_kejadian`) dan Perkara Selesai (`tanggal_surat` Tahap II final berkas selesai) selama 12 bulan (Januari s.d. Desember).
  - Menyediakan metrik total tahunan dan rasio penyelesaian perkara (`clearanceRate`).
  - Dinamis mengikuti pergantian tema gelap / terang via event `theme-changed`.
- **Rata-rata Hari Kerja Antar-Tahap (`getStageAverageDays`)**:
  - Mengukur durasi hari kerja riil (`diffInWeekdays`, mengecualikan Sabtu/Minggu) antar-tahap berurutan: Sprindik ➔ SPDP, SPDP ➔ Tahap 1, Tahap 1 ➔ P-21, P-21 ➔ Tahap 2.
- **Feed Aktivitas Penyidikan Terkini (`getRecentActivities`)**:
  - Menampilkan riwayat aksi audit log dengan toggle tab instan "Milestone Penting" vs "Semua Aktivitas".
- **Tabel Prioritas Progress Perkara (`getPriorityProgressPerkara`)**:
  - Default query tanpa filter status hanya menampilkan berkas dalam proses (`scopeDalamProses()`), menyembunyikan berkas Selesai dan Tidak Selesai (SP3).
  - Urutan prioritas portabel: `sla_deadline` ASC (dengan NULL di paling belakang via `orderByRaw('sla_deadline IS NULL')`), lalu `progress_persen` ASC, lalu `created_at` ASC.
- **Susunan Grid Dashboard Simetris**:
  - Baris 1: 5 Kartu Statistik Utama.
  - Baris 2: Visual Charts (`lg:col-span-6` Donut Status Perkara & `lg:col-span-6` Tren Bulanan ApexCharts).
  - Baris 3: Operasional & Aktivitas (`lg:col-span-6` Aktivitas Terkini & `lg:col-span-6` Rata-rata Tahap).
  - Baris 4: Tabel Prioritas Progress Perkara (Full Width).
- **Strategi Caching & Invalidasi**:
  - Seluruh data analitik di-cache selama 120 detik (`dashboard_stats_{tahun}`, `dashboard_stage_averages_{tahun}`, `dashboard_monthly_cases_{tahun}`) dan di-invalidasi otomatis oleh event mutasi data melalui `DashboardService::clearCache()`.
  - 20 Pasal UU (Kesehatan, Pangan, Perlindungan Konsumen, Narkotika, Psikotropika, KUHP).
  - 20 Instansi Hukum (Kejati Kalsel, 13 Kejari, Pengadilan Tinggi, 5 Pengadilan Negeri se-Kalsel).
  - 20 Jaksa Penuntut Umum resmi lengkap NIP dan pangkat/golongan.
