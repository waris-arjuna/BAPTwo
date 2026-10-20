# Panduan Rollback dan Pemulihan Versi Git BAPTwo

Dokumen ini adalah panduan praktis untuk membatalkan (*rollback*) atau mengembalikan kode ke versi sebelumnya jika terjadi kesalahan atau ketidaksesuaian pada pembaruan aplikasi.

---

## 1. Titik Riwayat Saat Ini

- **Commit Pembaruan Terakhir**: `ba51452` (`feat(dashboard): upgrade kartu donut chart perkara tahun berjalan (D-048)`)
- **Commit Sebelum Pembaruan (Titik Aman)**: `e319557` (`feat(dokumen): tambahkan modal pratinjau PDF in-app`)
- **Branch Cadangan Lokal**: `backup-sebelum-donut-upgrade` (berada tepat di commit `e319557`)

---

## 2. Tiga Pilihan Cara Rollback

Pilih salah satu cara di bawah ini sesuai kebutuhan Anda:

### Opsi A: Menggunakan `git revert` (Paling Direkomendasikan & Standar GitHub)

Cara ini membuat commit baru yang isinya secara otomatis membatalkan perubahan terakhir tanpa menghapus riwayat sebelumnya. Sangat aman dan tidak berisiko konflik bagi pengembang lain.

**Langkah:**
1. Buka terminal di folder proyek (`c:\laragon\www\baptwo`).
2. Jalankan perintah berikut:
   ```bash
   git revert HEAD --no-edit
   git push origin main
   ```
3. Selesai. Seluruh perubahan pada commit terakhir otomatis terbatalkan dan GitHub langsung sinkron.

---

### Opsi B: Mengembalikan `main` Persis ke Branch Cadangan (`git reset`)

Gunakan cara ini jika Anda ingin branch `main` mundur sepenuhnya ke kondisi sebelum pembaruan kartu donut chart dibuat.

**Langkah:**
1. Buka terminal di folder proyek.
2. Jalankan perintah reset ke branch cadangan:
   ```bash
   git reset --hard backup-sebelum-donut-upgrade
   ```
3. Sinkronkan ke GitHub (memerlukan opsi force):
   ```bash
   git push origin main --force
   ```
4. Selesai. Posisi branch `main` di komputer lokal dan di GitHub kembali persis ke commit `e319557`.

---

### Opsi C: Hanya Mengintip atau Menguji Versi Lama (Tanpa Mengubah Apapun)

Gunakan cara ini jika Anda hanya ingin menjalankan dan mencoba versi lama sementara waktu tanpa membatalkan commit di `main`.

**Langkah:**
1. Pindah ke branch cadangan:
   ```bash
   git checkout backup-sebelum-donut-upgrade
   ```
2. Jalankan aplikasi atau tes seperti biasa untuk memeriksa.
3. Setelah selesai memeriksa, kembalikan ke versi terbaru kapan saja:
   ```bash
   git checkout main
   ```

---

## 3. Cara Praktis: Minta AI Antigravity yang Mengerjakan

Jika Anda tidak ingin menjalankan perintah terminal sendiri, Anda cukup mengetikkan pesan di chat ini:

> *"Tolong rollback ke commit sebelum upgrade donut chart"*
> atau
> *"Tolong kembalikan kode ke branch backup-sebelum-donut-upgrade dan push ke github"*

AI akan langsung mengeksekusi langkah rollback, menjalankan pengujian otomatis, dan memperbarui GitHub untuk Anda.

---

## 4. Perintah Berguna untuk Memeriksa Status

- Melihat 5 commit terakhir:
  ```bash
  git log --oneline -n 5
  ```
- Melihat daftar branch yang tersedia:
  ```bash
  git branch
  ```
- Memeriksa status file yang berubah:
  ```bash
  git status
  ```
- Menjalankan seluruh pengujian otomatis:
  ```bash
  php artisan test
  ```
