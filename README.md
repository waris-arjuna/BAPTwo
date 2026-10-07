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
  - PHP **8.2+**
  - MySQL **8**
  - Composer
  - Node.js **18+** dan npm
- Git

Cek versi dari terminal Laragon:

```bash
php -v
composer -V
node -v
npm -v
mysql --version
```

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

## Akun Seed per Role

> **Belum tersedia.** Seeder akun role dikerjakan di `TASKS.md` T-011 (keputusan D-005). Saat ini seeder hanya membuat `test@example.com`.

Rencana akun dev (password `password`, hanya untuk environment `local`/`testing`):

| Role | Email | Hak akses ringkas |
|---|---|---|
| Administrator | `admin@baptwo.test` | Kelola user & permission, lihat semua, tambah berkas, edit draft, soft delete |
| Pimpinan | `pimpinan@baptwo.test` | Lihat & cari semua perkara. Tidak bisa tambah/edit/hapus |
| Penyidik | `penyidik@baptwo.test` | Lihat semua, tambah berkas, edit draft, soft delete |

Tidak ada role yang bisa hard delete atau mengedit tahap yang sudah difinalkan.

Reset database + seed ulang (**menghapus semua data lokal**):

```bash
php artisan migrate:fresh --seed
```

## Testing

Test memakai [Pest PHP](https://pestphp.com/):

```bash
composer test
```

`composer test` membersihkan cache config lalu menjalankan `php artisan test`.

Jalankan test tertentu:

```bash
php artisan test --filter=NamaTest
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

Task dianggap selesai hanya jika `composer test` dan `php vendor/bin/pint --test` lulus.

## Scheduler

Status **Tidak Selesai** di-set otomatis pada 00:00 tanggal 1 Januari oleh Laravel Scheduler. Di lokal, jalankan manual:

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
app/            Controller, Model, Policy, Service domain
database/       Migration, seeder, factory
lang/id.json    Terjemahan label UI (bahasa Indonesia)
resources/      Blade view, CSS (Tailwind v4 @theme), JS (Alpine.js)
routes/         web.php, console.php (scheduler)
tests/          Test Pest
```

## Troubleshooting

| Masalah | Solusi |
|---|---|
| `Class not found` | `composer dump-autoload` |
| Perubahan config/route tidak terbaca | `php artisan optimize:clear` |
| Gagal koneksi database | Pastikan MySQL Laragon jalan, database `baptwo` ada, kredensial `.env` benar |
| `baptwo.test` tidak terbuka | Laragon › Menu › Apache/Nginx › Reload; pastikan folder di `C:\laragon\www\baptwo` |
| Aset CSS/JS tidak muncul | Jalankan `npm run dev` atau `npm run build` |

## Lisensi

Aplikasi internal BPOM Kalimantan Selatan. Template UI: [TailAdmin License](https://tailadmin.com/license).
