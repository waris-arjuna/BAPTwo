---
name: baptwo
description: Panduan pengembangan khusus untuk proyek BAPTwo. Gunakan saat mengembangkan, meninjau, merancang, melakukan debugging, atau mengubah fitur, database, workflow, UI, validasi, otorisasi, upload dokumen, dan tracking perkara.
---

# Skill Proyek BAPTwo

## 1. Konteks Proyek

BAPTwo adalah aplikasi internal BPOM Kalimantan Selatan untuk monitoring dan tracking penanganan perkara penyidikan.

### Stack

- Laravel Framework 12.26.4
- TailAdmin
- Tailwind CSS 4.1.12
- Vite 7.0.4
- Alpine.js 3.14.9
- Laravel Boost
- Laravel Boost MCP

Jangan mengganti atau meng-upgrade stack tanpa permintaan pengguna.

---

## 2. Prinsip Pengembangan

1. Utamakan kebutuhan nyata aplikasi BAPTwo.
2. Hindari overengineering.
3. Gunakan desain yang sederhana, jelas, dan mudah dipelihara.
4. Periksa implementasi yang relevan sebelum melakukan perubahan.
5. Jangan melakukan pemeriksaan seluruh project untuk perubahan kecil.
6. Buat perubahan sekecil dan seaman mungkin.
7. Gunakan konvensi Laravel yang sudah ada.
8. Jangan membuat abstraksi, tabel, field, relasi, atau modul baru tanpa kebutuhan yang jelas.
9. Selalu sertakan validasi dan error handling yang baik.
10. Gunakan Laravel Boost MCP jika membutuhkan konteks teknis Laravel.
11. Jangan mengubah business rule yang sudah disepakati tanpa konfirmasi.
12. Prioritaskan keamanan, integritas data, dan maintainability.

---

## 3. Cara Kerja

Sebelum melakukan perubahan:

1. Pahami tujuan dan requirement.
2. Identifikasi bagian project yang benar-benar terdampak.
3. Periksa hanya file dan konteks yang relevan.
4. Gunakan Laravel Boost MCP jika membutuhkan informasi tentang:
   - database
   - migration
   - model
   - relationship
   - route
   - controller
   - Artisan
   - struktur Laravel
5. Tentukan perubahan minimum yang diperlukan.
6. Implementasikan perubahan.
7. Sertakan validasi, authorization, dan error handling yang sesuai.
8. Lakukan pemeriksaan atau testing yang relevan.
9. Laporkan perubahan penting dan hasil verifikasi.

Jangan melakukan full-project scan untuk perubahan kecil.

Untuk perubahan database atau business logic yang berisiko:

- jelaskan dampaknya terlebih dahulu
- jangan menambah struktur tanpa kebutuhan yang jelas
- pertahankan desain sesederhana mungkin
- jangan mengubah workflow yang sudah disepakati tanpa alasan

Jika requirement ambigu dan dapat memengaruhi struktur database atau business workflow, tanyakan terlebih dahulu daripada menebak.

---

## 4. Alur Perkara

Alur utama perkara:

SPDP → Tahap 1 → P-21 → Tahap 2

### Tahapan dan Dokumen

- P16 dan P17 bukan tahapan wajib.
- P18/P19 bersifat opsional.
- P18/P19 hanya dicatat jika memang diberikan atau diterima.
- Jangan mengasumsikan setiap perkara selalu memiliki P18/P19.

### DPO dan SP3

DPO dan SP3 merupakan kondisi yang dapat ditetapkan pada
tersangka melalui informasi perkara.

Pada informasi perkara harus tersedia mekanisme untuk menetapkan
seorang tersangka sebagai DPO atau SP3.

SLA perkara adalah 2 bulan dan dihitung sejak tanggal SPDP keluar.

Jika tersangka ditetapkan sebagai DPO atau SP3 sebelum batas SLA
berakhir, kondisi tersebut masuk ke status `Suspend`.

Jika batas SLA telah terlewati sesuai business rule yang berlaku,
kondisi tersebut dapat berubah menjadi `Case Closed`.

DPO/SP3 tidak boleh diasumsikan sebagai `Case Closed` secara
langsung.

Perhitungan SLA harus menggunakan tanggal SPDP sebagai titik awal.

Logic otomatis perubahan `Suspend` → `Case Closed` harus mengikuti
aturan SLA yang telah dikonfirmasi.

DPO/SP3 dapat berkaitan dengan perkara pada:

- SPDP
- Tahap 1
- P-21
- Tahap 2

Jika terdapat lebih dari satu tersangka dalam satu perkara,
jangan mengasumsikan bahwa status salah satu tersangka otomatis
menentukan status seluruh perkara sampai business rule tersebut
dikonfirmasi.

## 5. Scope Aplikasi

Prioritas utama BAPTwo:

- CRUD
- RBAC / authorization
- Dashboard Monitoring
- Upload Dokumen
- Tracking Perkara
- Riwayat atau audit yang memang diperlukan

Jangan menambahkan modul atau fitur hanya karena secara teknis memungkinkan.

### ML / Data Science

ML dan Data Science saat ini berada di luar scope.

Jangan membuat:

- model ML
- endpoint ML
- pipeline Data Science
- prediction logic
- arsitektur ML

kecuali pengguna secara eksplisit meminta atau menyatakan bahwa tahap tersebut sudah dimulai.

---

## 6. Urutan Pengembangan Frontend dan Backend

Gunakan urutan pengembangan berdasarkan jenis pekerjaan, bukan aturan yang kaku.

### Untuk Fitur Fungsional

Untuk fitur seperti CRUD, tracking perkara, upload dokumen, RBAC, dan workflow:

1. Pahami requirement dan business rule.
2. Identifikasi data yang dibutuhkan.
3. Periksa database, migration, model, dan relationship yang relevan.
4. Implementasikan backend:
   - validation
   - authorization
   - business logic
   - controller
   - service jika memang diperlukan
   - file handling jika diperlukan
5. Implementasikan frontend:
   - form
   - table
   - detail
   - status
   - feedback
   - error state
6. Integrasikan frontend dengan backend.
7. Lakukan testing dan validasi.

Backend merupakan sumber kebenaran untuk:

- validation
- authorization
- business rules
- integritas data
- keamanan

Validasi frontend hanya membantu UX dan tidak menggantikan validasi backend.

### Untuk UI / Prototype

Jika pekerjaan hanya berfokus pada desain atau prototype UI:

1. Pahami requirement UI.
2. Gunakan TailAdmin dan komponen yang sudah tersedia.
3. Gunakan dummy/mock data jika backend belum tersedia.
4. Fokus pada:
   - layout
   - usability
   - responsive behavior
   - loading state
   - empty state
   - error state
5. Integrasikan backend setelah rancangan UI siap atau ketika data nyata dibutuhkan.

Jangan membuat backend hanya karena sebuah pekerjaan frontend belum membutuhkan data nyata.

### Untuk Perubahan Backend yang Sudah Ada

Jika backend sudah tersedia:

1. Periksa route, controller, model, validation, authorization, dan response yang relevan.
2. Jangan membuat ulang backend jika implementasi yang ada masih dapat digunakan.
3. Sesuaikan frontend dengan kontrak backend yang sudah ada.

### Prinsip Utama

Frontend bertanggung jawab terhadap pengalaman pengguna.

Backend bertanggung jawab terhadap:

- validation
- authorization
- business rules
- integritas data
- keamanan

---

## 7. Validasi dan Error Handling

Setiap operasi perubahan data harus mempertimbangkan:

- validasi input
- validasi tipe dan format data
- validasi relasi
- validasi aturan bisnis
- authorization
- validasi file
- penanganan record yang tidak ditemukan
- transaksi database jika diperlukan
- kegagalan penyimpanan file
- kegagalan database
- pesan error yang aman untuk pengguna

Gunakan fitur Laravel jika sesuai, seperti:

- Form Request
- Policy / Gate
- Transaction
- Validation
- Eloquent Relationship

Jangan menampilkan detail exception internal kepada pengguna.

Error handling harus memberikan feedback yang jelas kepada pengguna tanpa membocorkan informasi sensitif atau detail internal aplikasi.

---

## 8. Database

Jangan otomatis menambahkan kolom berikut ke semua tabel:

- `deleted_at`
- `created_by`
- `updated_by`

Tentukan berdasarkan kebutuhan nyata masing-masing entity.

### Timestamp

Gunakan:

- `created_at`
- `updated_at`

jika timestamp memang relevan untuk entity tersebut.

### Soft Delete

Gunakan `deleted_at` hanya jika entity memang membutuhkan mekanisme soft delete.

Jangan menggunakan soft delete secara otomatis pada seluruh tabel.

### Audit Log

Jika audit log diperlukan, prioritaskan mekanisme terpusat seperti:

- model events
- observers
- mekanisme audit khusus

Hindari menulis audit log secara manual di setiap controller jika mekanisme terpusat lebih sesuai.

### Struktur Data Perkara

Saat menentukan apakah tahapan perkara perlu berada dalam satu tabel atau dipisahkan ke beberapa tabel:

- pertimbangkan kebutuhan bisnis nyata
- pertimbangkan dokumen yang bersifat opsional
- pertimbangkan kebutuhan tracking
- pertimbangkan query dan reporting
- pertimbangkan maintainability
- hindari pemisahan tabel hanya demi teori normalisasi

Pilih desain yang paling sederhana yang tetap mampu memenuhi workflow BAPTwo.

---

## 9. UI dan Frontend

Gunakan TailAdmin sebagai dasar UI BAPTwo.

Prioritaskan penggunaan kembali komponen yang sudah tersedia:

- layout
- sidebar
- navigation
- form
- table
- alert
- modal
- card
- pagination

Gunakan Tailwind CSS sesuai pola yang sudah digunakan project.

Jangan menambahkan Bootstrap hanya untuk menyelesaikan satu halaman jika Tailwind/TailAdmin sudah mencukupi.

Dashboard dan halaman monitoring harus memprioritaskan informasi yang berguna untuk operasional, seperti:

- status perkara
- tahapan perkara
- progress penanganan
- filter
- jumlah perkara
- dokumen terkait
- informasi tracking

Hindari widget dekoratif yang tidak memiliki nilai operasional.

---

## 10. File dan Dokumen

Dokumen perkara merupakan data penting.

Setiap upload harus:

- memvalidasi tipe file
- memvalidasi ukuran file
- menggunakan storage path yang terkontrol
- menggunakan nama file penyimpanan yang aman
- tidak mempercayai nama file pengguna sebagai identifier storage
- mencegah overwrite yang tidak disengaja
- memiliki aturan penggantian dan penghapusan yang jelas
- mempertahankan auditability jika diperlukan

Operasi file dan database harus dipertimbangkan secara konsisten agar tidak meninggalkan data yang tidak sinkron.

---

## 11. RBAC dan Authorization

Authorization harus dilakukan di sisi server.

Jangan hanya mengandalkan:

- hidden button
- hidden menu
- pengecekan di Blade
- pembatasan UI

Operasi sensitif harus tetap dilindungi, termasuk:

- create
- view
- update
- delete
- download

Gunakan mekanisme Laravel seperti Policy atau Gate jika sesuai.

Hindari menyebarkan pengecekan role secara manual di banyak controller atau Blade.

---

## 12. Aturan Umum

Saat mengimplementasikan fitur:

- jangan mengubah stack tanpa permintaan
- jangan mengubah business rule tanpa konfirmasi
- jangan menambah kompleksitas tanpa kebutuhan
- jangan melakukan full-project scan untuk perubahan kecil
- gunakan konteks yang relevan saja
- prioritaskan keamanan dan integritas data
- prioritaskan maintainability
- selalu pertimbangkan validasi dan error handling
- gunakan komponen dan pola project yang sudah ada
- jika ada ambiguity yang berdampak pada database atau workflow, tanyakan terlebih dahulu

Tujuan utama adalah membangun BAPTwo yang stabil, aman, mudah dipelihara, dan sesuai kebutuhan nyata pengguna tanpa overengineering.