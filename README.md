# BAPTwo

Aplikasi web internal **BPOM Kalimantan Selatan** untuk mencatat, memonitor, dan melacak penanganan perkara: dari informasi awal pra-SPDP sampai Keputusan Pengadilan.

Setiap Berkas Perkara menyimpan tersangka, pasal, TKP, PPNS, tahapan, dokumen, histori, status, dan progress.

Dibangun di atas template **TailAdmin Free Laravel** (Laravel 12, Tailwind CSS v4, Alpine.js, Vite).

## Dokumen Proyek

| File | Isi |
|---|---|
| [`PRD.md`](PRD.md) | Kebutuhan produk & aturan bisnis (sumber kebenaran domain) |
| [`ARCHITECTURE.md`](ARCHITECTURE.md) | Arsitektur teknis |
| [`TASKS.md`](TASKS.md) | Daftar pekerjaan aktif |
| [`DECISIONS.md`](DECISIONS.md) | Log keputusan teknis |
| [`AGENTS.md`](AGENTS.md) | Aturan untuk AI agent & konvensi kode |

## Kebutuhan

- [Laragon](https://laragon.org/) (Windows) dengan:
  - PHP **8.2+** build **64-bit (x64)**
  - MySQL **8**
  - Composer
  - Node.js **18+** dan npm
- Git

Cek versi dari terminal Laragon:

```bash
php -v
php -r "echo PHP_INT_SIZE === 8 ? '64-bit' : '32-bit', PHP_EOL;"
composer -V
node -v
npm -v
mysql --version
```

`composer install` wajib memakai PHP 64-bit. Paket `maennchen/zipstream-php` (dependensi ekspor Excel) mencatat syarat `php-64bit`, sehingga instalasi dengan PHP 32-bit (`C:\php\php.exe` bawaan Composer Setup) berhenti dengan pesan `Your lock file does not contain a compatible set of packages`. Gunakan build x64 dari Laragon, misalnya `C:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe`, lewat terminal Laragon atau dengan mengatur ulang urutan `PATH`.

## Setup Lokal (Laragon + MySQL)

### 1. Clone ke folder `www` Laragon

```bash
cd C:\laragon\www
git clone https://github.com/waris-arjuna/BAPTwo.git baptwo
cd baptwo
```

### 2. Install dependency

```bash
composer install
npm install
```

### 3. Buat file `.env`

```bash
copy .env.example .env
php artisan key:generate
```

Edit `.env`, sesuaikan nilai berikut:

```env
APP_NAME=BAPTwo
APP_URL=http://baptwo.test

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=baptwo
DB_USERNAME=root
DB_PASSWORD=
```

> Default MySQL Laragon: user `root`, password kosong.

### 4. Buat database

Lewat HeidiSQL (menu Laragon **Database**) buat database `baptwo`, atau dari terminal:

```bash
mysql -u root -e "CREATE DATABASE baptwo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Migrasi & seed

```bash
php artisan migrate --seed
php artisan storage:link
```

### 6. Jalankan

Laragon otomatis membuat virtual host `http://baptwo.test` setelah **Start All** (atau **Menu › Apache/Nginx › Reload**).

Untuk aset frontend dengan hot reload:

```bash
npm run dev
```

Atau jalankan semua proses sekaligus tanpa virtual host Laragon:

```bash
composer run dev
```

Perintah ini menjalankan 4 proses: `php artisan serve` (http://localhost:8000), `php artisan queue:listen`, `php artisan pail` (log), dan `npm run dev` (Vite).

## Akun Dev BAPTwo

Aplikasi menyediakan 3 akun dev bawaan (password default `password`, login dilengkapi verifikasi matematika captcha dinamis):

| Peran (Role) | Email Login | Hak Akses Utama |
|---|---|---|
| **Administrator** | `admin@baptwo.test` | Kelola master data penuh, manajemen pengguna, tambah perkara, edit draft, ekspor Excel |
| **Pimpinan** | `pimpinan@baptwo.test` | Memonitor dashboard, melihat seluruh perkara, pencarian multi-kriteria (read-only) |
| **Penyidik** | `penyidik@baptwo.test` | Tambah Laporan Kejadian/Sprindik, buat perkara, simpan draft, finalisasi tahap, ekspor Excel |

> Catatan Keamanan: Tidak ada peran yang memiliki hak akses *hard delete* (penghapusan fisik). Tahapan yang telah difinalkan terkunci mutlak dan tidak dapat diedit kembali oleh siapapun termasuk Administrator.

### Seeder Data Dummy Komprehensif (20 Kasus)

Untuk mengisi data pengujian realistis yang mencakup seluruh alur penanganan perkara dari Pra-SPDP, SPDP, Tahap 1, P-21, Tahap 2, Putusan Pengadilan, DPO, SP3, dan peringatan SLA di 13 Kabupaten/Kota Kalimantan Selatan:

```bash
php artisan db:seed --class=DummyDataSeeder
```

Seeder ini bersifat idempoten (aman dijalankan berulang kali) dan menghasilkan:
- **20 Perkara** representatif dengan alur tahapan lengkap
- **20 Pasal Pelanggaran** (UU Kesehatan, Pangan, Perlindungan Konsumen, Narkotika, Psikotropika, KUHP)
- **20 Instansi Hukum** (Kejati Kalsel, 13 Kejari, Pengadilan Tinggi, 5 Pengadilan Negeri se-Kalsel)
- **20 Jaksa Penuntut Umum** resmi lengkap NIP dan pangkat/golongan

Reset total database dan tanam data baseline:

```bash
php artisan migrate:fresh --seed
```

## Testing

Test suite otomatis menggunakan [Pest PHP](https://pestphp.com/) dengan 162 skenario pengujian (1.653 assertions) mencakup seluruh alur bisnis, RBAC, dan validasi:

```bash
composer test
```

Perintah `composer test` secara otomatis membersihkan cache config lalu menjalankan test suite lengkap.

Jalankan pengujian tertentu (misal ekspor):

```bash
php artisan test --filter=PerkaraExportTest
```

## Lint (Laravel Pint)

Cek gaya kode tanpa mengubah file:

```bash
php vendor/bin/pint --test
```

Perbaiki otomatis:

```bash
php vendor/bin/pint
```

Task dianggap selesai hanya jika `composer test` (162 tests) dan `php vendor/bin/pint --test` lulus 100%.

## Scheduler

Status **Tidak Selesai** di-set otomatis pada 00:00 WITA tanggal 1 Januari oleh Laravel Scheduler. Di lokal, jalankan manual:

```bash
php artisan schedule:work
```

Di server produksi, daftarkan `php artisan schedule:run` setiap menit (cron / Windows Task Scheduler).

## Build Produksi

```bash
npm run build
composer install --optimize-autoloader --no-dev
php artisan optimize
```

Set `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` sesuai domain.

## Struktur Folder Utama

```
app/            Controller, Model, Policy, Service domain, Exports (Excel 44 kolom)
database/       Migration, seeder (DummyDataSeeder 20 perkara), factory
lang/id.json    Terjemahan label UI (bahasa Indonesia)
resources/      Blade view, CSS (Tailwind v4 @theme), JS (Alpine.js)
routes/         web.php, console.php (scheduler)
tests/          Test Pest (162 automated tests)
```

## Troubleshooting

| Masalah | Solusi |
|---|---|
| `Class not found` | `composer dump-autoload` |
| Perubahan config/route tidak terbaca | `php artisan optimize:clear` |
| Gagal koneksi database | Pastikan MySQL Laragon jalan, database `baptwo` ada, kredensial `.env` benar |
| `baptwo.test` tidak terbuka | Laragon › Menu › Apache/Nginx › Reload; pastikan folder di `C:\laragon\www\baptwo` |
| Aset CSS/JS tidak muncul | Jalankan `npm run dev` atau `npm run build` |
| Ekstensi Zip belum aktif / Ekspor gagal | Aktifkan `extension=zip` di `php.ini` Laragon lalu restart Apache/Nginx |
| Platform check Composer / 64-bit PHP | Gunakan terminal bawaan Laragon (PHP 64-bit), atau pastikan `"platform-check": false` di `composer.json` |

## Lisensi

Aplikasi internal BPOM Kalimantan Selatan. Template UI: [TailAdmin License](https://tailadmin.com/license).
