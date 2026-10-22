# PRD — BAPTwo

## 1. Ringkasan Produk

BAPTwo adalah aplikasi web internal BPOM Kalimantan Selatan untuk mencatat, memonitor, dan melacak penanganan perkara secara terpusat. Sistem mencatat informasi awal perkara sebelum SPDP, kemudian memonitor progress utama mulai dari SPDP hingga Keputusan Pengadilan. Setiap Berkas Perkara memiliki informasi tersangka, pasal, TKP, PPNS, tahapan, dokumen, histori, status, dan progress yang dapat ditelusuri. BAPTwo dirancang cukup untuk kebutuhan monitoring dan tracking perkara tanpa overengineering.

## 2. Problem Statement

- Informasi perkara dan perkembangan penanganannya perlu tersedia dalam satu sistem terpusat.
- Informasi awal seperti Laporan Kejadian dan Surat Perintah Penyidikan perlu dicatat sebelum progress utama perkara dimulai dari SPDP.
- Informasi pasal, tersangka, TKP, PPNS utama, dan PPNS anggota perlu tersedia sebagai bagian dari data perkara.
- Satu Perkara terhubung langsung dengan satu Tersangka dan satu Berkas Perkara dengan penomoran identik nomor Laporan Kejadian (ref: D-035).
- Setiap Berkas Perkara dapat memiliki pasal berlapis.
- Satu Berkas Perkara dapat memiliki beberapa tahapan proses, tetapi jenis tahapan yang sama tidak boleh muncul lebih dari satu kali.
- Tahapan progress harus mengikuti urutan proses yang ditentukan.
- P-16, P-17P, P-18P, dan P-19 bersifat opsional, tetapi harus dapat dicatat apabila tersedia.
- Data yang belum lengkap perlu dapat disimpan sebagai Draft pada setiap tahapan.
- Tahapan yang sudah dilanjutkan perlu dikunci dan tidak dapat dimundurkan.
- Kondisi DPO dapat terjadi pada tahap mana pun dan perlu dapat dicatat tanpa mengubah tahapan.
- Pimpinan membutuhkan monitoring seluruh perkara dan pekerjaan penyidik.
- Dokumen perkara dan histori perubahan perlu tersedia secara terpusat dan terkontrol.
- Perkara yang belum selesai pada akhir tahun perlu otomatis menjadi Tidak Selesai.
- Data monitoring perlu dapat digunakan untuk kebutuhan pelaporan dan export.

## 3. Target User & Persona

| Persona | Peran | Kebutuhan Utama | Frekuensi Pakai |
|---|---|---|---|
| Penyidik | Menangani dan menginput perkara | Mengelola informasi awal, tersangka, pasal, TKP, PPNS, tahapan, dokumen PDF (maks 5 MB), Draft, DPO, ekspor Excel | Harian |
| Pimpinan | Memantau perkembangan perkara | Melihat seluruh perkara, progress, status, dashboard potensi terlimpah, dan pekerjaan penyidik | Harian/Mingguan |
| Administrator | Mengelola pengguna dan akses | Mengelola user, master data lengkap, soft delete data master/user, dan aktivitas sistem | Sesuai kebutuhan |

## 4. Use Case Utama

### UC-01 — Mencatat Informasi Awal Perkara
**Actor:** Penyidik
**Trigger:** Laporan Kejadian dan Surat Perintah Penyidikan tersedia.
**Result:** Informasi awal perkara, pasal, tersangka, TKP, PPNS utama, dan PPNS anggota tercatat sebelum progress utama dimulai dari SPDP.

### UC-02 — Menjalankan Progress Tahapan Perkara
**Actor:** Penyidik
**Trigger:** Perkara siap masuk progress SPDP.
**Result:** Tahapan dicatat mengikuti urutan workflow, tahapan opsional dapat dilewati, dan tahapan yang sama tidak dapat dimasukkan dua kali.

### UC-03 — Memonitor Progress dan Status Perkara
**Actor:** Penyidik, Pimpinan
**Trigger:** Pengguna membuka dashboard atau detail Berkas Perkara.
**Result:** Pengguna dapat melihat tahapan terakhir, progress, status proses, status monitoring, DPO, dan histori perkara.

### UC-04 — Mengelola Dokumen dan Histori
**Actor:** Penyidik, Pimpinan sesuai hak akses
**Trigger:** Pengguna membutuhkan dokumen atau histori Berkas Perkara.
**Result:** Dokumen dan histori dapat ditelusuri sesuai hak akses.

### UC-05 — Mengelola Pengguna dan Hak Akses
**Actor:** Administrator
**Trigger:** Administrator perlu mengelola pengguna atau hak akses.
**Result:** User, role, permission, soft delete, dan aktivitas sistem dapat dikelola.

## 5. Fitur (MoSCoW)

| ID | Fitur | Prioritas | Alasan | Acceptance Criteria |
|---|---|---|---|---|
| F-01 | Pengelolaan Perkara | Must | Menjadi objek utama sistem | Penyidik dapat membuat dan melihat data perkara sesuai hak akses. |
| F-02 | Penggabungan Perkara & Berkas | Must | Efisiensi & kesederhanaan administrasi perkara | 1 Perkara = 1 Tersangka = 1 Berkas Perkara (ref: D-035). |
| F-03 | Data Tersangka | Must | Dibutuhkan untuk setiap Berkas Perkara | Data tersangka (NIK 16 digit, alamat lengkap 38 provinsi, tanggal lahir) tercatat pada Berkas Perkara. |
| F-04 | Pasal Berlapis | Must | Satu Berkas dapat memiliki beberapa pasal | Beberapa pasal dapat dicatat pada satu Berkas Perkara melalui combo box dinamis. |
| F-05 | Laporan Kejadian | Must | Menjadi informasi awal perkara | Laporan Kejadian dicatat satu kali dan terkunci setelah digunakan pada Sprindik. |
| F-06 | Surat Perintah Penyidikan | Must | Menjadi informasi awal sebelum SPDP | Surat Perintah Penyidikan dicatat satu kali dan terkunci setelah digunakan pada Berkas. |
| F-07 | Informasi TKP | Must | Menjadi informasi utama perkara | TKP (nama sarana, klasifikasi retailer/distributor, legalitas legal/ilegal, modus online/offline, alamat berjenjang) dicatat pada perkara. |
| F-08 | PPNS Utama, Anggota & Penuntut Umum | Must | Menunjukkan petugas penanganan perkara | Perkara memiliki 1 PPNS utama, 0..N PPNS anggota, serta Penuntut Umum (nama instansi dari master Kejaksaan, nama jaksa, NIP, dan pangkat diinput manual). |
| F-09 | Progress Mulai SPDP | Must | Menetapkan titik awal progress | Informasi pra-SPDP tidak masuk perhitungan progress. |
| F-10 | Rangkaian Tahapan Progress | Must | Menjaga konsistensi proses | Sistem mendukung SPDP → P-16 → P-17P → TAHAP I → P-18P/P-19 → P-21/P21a → TAHAP II → Keputusan Pengadilan (terminal penutup). |
| F-11 | Satu Tahapan Maksimal Satu Kali | Must | Mencegah duplikasi tahapan | Satu jenis tahapan hanya dapat dicatat satu kali pada satu Berkas Perkara. |
| F-12 | Validasi Urutan Tahapan | Must | Mencegah proses mundur atau lompat sembarangan | Tahapan hanya dapat ditambahkan sesuai urutan workflow. |
| F-13 | Tahapan Opsional | Must | Tidak semua proses tersedia | P-16, P-17P, P-18P, dan P-19 boleh dilewati tetapi dapat diinput apabila tersedia. |
| F-14 | Draft pada Setiap Tahapan | Must | Data belum lengkap tetap dapat disimpan | Setiap tahapan menyediakan aksi Draft tanpa menaikkan tahap. |
| F-15 | Tombol Batal | Must | Membatalkan input | Batal keluar tanpa menyimpan perubahan yang belum disimpan. |
| F-16 | Tombol Draft | Must | Menyimpan data sementara | Draft menyimpan data apa adanya tanpa menaikkan tahap. |
| F-17 | Simpan & Lanjut Tahap Berikutnya | Must | Memfinalkan tahap dan meneruskan workflow | Tahap hanya dapat dilanjutkan jika field wajib lengkap dan dokumen PDF (maks 5 MB) diunggah; setelah berhasil tahap terkunci. |
| F-18 | Penguncian Tahapan | Must | Menjaga integritas data | Tahap yang telah lanjut tidak dapat diedit atau dimundurkan oleh siapapun termasuk Administrator. |
| F-19 | Tampilan Lihat/Edit | Must | Membedakan data yang dapat diubah | Draft memiliki Lihat + Edit; data yang sudah lanjut hanya Lihat. |
| F-20 | Status Formal Berkas | Must | Menentukan hasil monitoring formal | Status formal hanya Selesai dan Tidak Selesai. |
| F-21 | Label Dalam Proses | Must | Menunjukkan kondisi selama tahun berjalan | Dalam Proses adalah label tampilan, bukan status formal. |
| F-22 | Trigger Selesai | Must | Menentukan penyelesaian berkas secara eksplisit | Berkas menjadi Selesai ketika Tahap II berhasil diselesaikan melalui aksi Simpan & Lanjut sesuai mekanisme tahap. |
| F-23 | Trigger Tidak Selesai | Must | Menentukan kondisi akhir tahun | Berkas yang belum Selesai pada 1 Januari tahun berikutnya otomatis menjadi Tidak Selesai. |
| F-24 | DPO | Must | Menandai tersangka buron | Penyidik dapat mengaktifkan DPO pada tahap mana pun selama berkas masih berjalan. |
| F-25 | Penonaktifan DPO | Must | Memungkinkan proses kembali normal | DPO dapat dinonaktifkan ketika tersangka ditemukan kembali dan tanggal penonaktifan dicatat pada jejak rekam. |
| F-26 | Pembatasan Tahapan Saat DPO Aktif dan SP3 | Must | Mencegah pelanjutan perkara saat perkara terhambat status khusus | Seluruh tombol dan aksi penambahan tahapan berikutnya diblokir di UI dan server saat perkara berstatus DPO aktif atau telah diterbitkan SP3; perbedaannya status DPO dapat dicabut ketika tersangka ditemukan (sehingga tahapan dapat dilanjutkan kembali), sedangkan status SP3 bersifat permanen dan tidak dapat dicabut (perkara berhenti mutlak sebagai Tidak Selesai). |
| F-27 | DPO Tidak Menghentikan SLA/Batas Tahun | Must | DPO tidak memberikan pengecualian waktu | DPO tidak menghentikan atau menjeda SLA maupun batas tahun. |
| F-28 | SP3 Terminal | Must | Menjadi penutup administratif penghentian penyidikan | SP3 diinput dengan nomor surat, tanggal, teks keterangan, dan dokumen PDF (maks 5 MB); boleh diterbitkan saat DPO aktif (otomatis mencabut DPO); langsung memicu status proses 'SP3', status formal 'Tidak Selesai', dan mengunci tahapan permanen (ref: D-030, D-035, D-053). |
| F-29 | Status Proses Otomatis | Must | Menunjukkan posisi terbaru perkara | Status proses mengikuti tahapan progress terakhir yang telah difinalkan, atau SP3. |
| F-30 | Progress Berbobot | Must | Menunjukkan persentase progress | Progress menggunakan milestone SPDP 15%, TAHAP I 55%, P-21/P21a 85%, dan TAHAP II 100%. |
| F-31 | Case History / Audit Trail | Must | Memungkinkan penelusuran perubahan | Perubahan status, upload dokumen, dan catatan progres dapat ditelusuri. |
| F-32 | Dokumen Perkara Terpusat | Must | Menjaga keterhubungan dokumen | Seluruh dokumen wajib format PDF (maksimal 5 MB) dan tersimpan terpusat di private storage. |
| F-33 | Nomor Berkas Identik LK | Must | Konsistensi penomoran berkas perkara | Nomor Berkas Perkara otomatis mengikuti dan identik dengan nomor Laporan Kejadian (ref: D-035). |
| F-34 | Catatan/Deskripsi | Must | Menyimpan konteks tambahan | Tahapan dan kondisi khusus dapat memiliki catatan/deskripsi. |
| F-35 | Data DPO | Must | DPO perlu dapat ditelusuri | Data DPO mencakup nomor, tanggal, dokumen PDF (maks 5 MB), dan keterangan. |
| F-36 | Data Alamat Terstruktur | Must | Menjaga konsistensi wilayah | Data alamat mendukung pemilihan wilayah berjenjang dengan nama sarana TKP. |
| F-37 | Sumber Data Wilayah | Must | Mengurangi input manual | Data wilayah menggunakan proxy API Kode Wilayah dengan fallback JSON offline. |
| F-38 | RBAC dan Permission | Must | Mengendalikan akses | Hak akses fitur mengikuti role: Admin (akses penuh), Penyidik (operasional perkara), dan Pimpinan/Umum (monitoring terbatas: Dashboard, Pencarian, Potensi Terlimpah); Master Data khusus Admin (Penyidik hanya Pasal & Instansi) (ref: D-035, D-053). |
| F-39 | Pencarian dan Filter | Must | Memudahkan menemukan perkara | Pengguna dapat mencari dan memfilter Berkas Perkara secara live via AJAX table filter. |
| F-40 | Dashboard Monitoring Perkara | Must | Menyediakan ringkasan monitoring | Penyidik dan Pimpinan dapat melihat ringkasan progress dan status perkara. |
| F-41 | Dashboard Potensi Terlimpah | Should | Membantu identifikasi perkara belum selesai sebelum akhir tahun | Dashboard menampilkan Berkas Perkara yang belum Selesai untuk ditindaklanjuti. |
| F-42 | Monitoring Pekerjaan Penyidik | Must | Pimpinan perlu melihat pekerjaan penyidik | Pimpinan dapat melihat perkembangan perkara berdasarkan penyidik. |
| F-43 | Akses Seluruh Perkara bagi Pengguna | Must | Monitoring terpusat | Penyidik dan Pimpinan dapat melihat seluruh perkara sesuai permission. |
| F-44 | Perlindungan Berkas & Soft Delete Terbatas | Must | Mencegah penghapusan berkas perkara sembarangan | Data Perkara/LK/Sprindik tidak memiliki fitur hapus; soft delete hanya berlaku untuk Master Data dan Pengguna; hard delete tidak tersedia (ref: D-035). |
| F-45 | Export Rekap Excel 44 Kolom | Must | Mendukung kebutuhan pelaporan resmi | Data diekspor dalam format Excel (.xlsx) 44 kolom terstandarisasi BPOM dengan modal filter 3 tab (Tahun, Bulan, Rentang Tanggal) khusus Admin & Penyidik (ref: D-035, D-039). |
| F-46 | Manajemen Data Petugas & Recovery Data | Must | Fleksibilitas penugasan dan keamanan data | Menu Data Petugas memungkinkan penugasan role non-admin dari daftar user; halaman Sampah berganti nama resmi menjadi Recovery Data (ref: D-053). |
| F-47 | Proteksi Akun Nonaktif & Force Logout | Must | Keamanan autentikasi dan integritas sesi | Akun nonaktif/terhapus ditolak masuk dengan toast dan banner peringatan setelah password valid; sesi aktif diputus otomatis via middleware EnsureUserIsActive (ref: D-053). |

### Struktur Perkara dan Berkas Perkara

- 1 Perkara = 1 Tersangka = 1 Berkas Perkara.
- Nomor Berkas Perkara otomatis identik dengan nomor Laporan Kejadian (LK).
- Setiap Berkas Perkara dapat memiliki pasal berlapis.
- Aturan tahapan, SLA, status, dan penguncian berlaku pada level Berkas Perkara.

### Informasi Pra-SPDP

Informasi awal perkara berada sebelum progress utama:

**Laporan Kejadian → Surat Perintah Penyidikan → Informasi Pasal, Tersangka, TKP, PPNS Utama, PPNS Anggota, Penuntut Umum → SPDP**

Informasi pra-SPDP tidak masuk perhitungan progress.

### Progress Utama

Progress utama dimulai dari:

**SPDP → P-16 → P-17P → TAHAP I → P-18P/P-19 → P-21/P21a → TAHAP II → Keputusan Pengadilan**

Aturan:

1. Satu Berkas Perkara dapat memiliki banyak tahapan.
2. Tahapan yang sama tidak boleh dicatat dua kali.
3. Tahapan harus mengikuti urutan.
4. P-16, P-17P, P-18P, dan P-19 bersifat opsional.
5. Tahapan opsional dapat dilewati.
6. P-21/P21a dan TAHAP II adalah dua tahapan berbeda.
7. Setelah tahapan dilanjutkan, tahapan tersebut terkunci.
8. Tidak ada perpindahan mundur ke tahap sebelumnya.
9. Keputusan Pengadilan merupakan tahapan penutup terminal. Setelah dicatat, tidak ada lagi tahapan berikutnya yang dapat ditambahkan (ref: D-053).

### Bobot Progress

| No. | Tahapan | Bobot Kumulatif | Kontribusi |
|---|---|--:|--:|
| 1 | SPDP | 15% | 15% |
| 2 | TAHAP I | 55% | 40% |
| 3 | P-21/P21a | 85% | 30% |
| 4 | TAHAP II | 100% | 15% |

Aturan:

- Pra-SPDP → **0%**
- SPDP → **15%**
- TAHAP I → **55%**
- P-21/P21a → **85%**
- TAHAP II → **100%**
- P-16, P-17P, P-18P, dan P-19 tidak memiliki bobot tersendiri.
- Keputusan Pengadilan dicatat setelah TAHAP II sebagai dokumentasi resmi vonis hakim inkrah dari pihak penyidik tanpa meningkatkan progress di atas 100%. Setelah Keputusan Pengadilan dicatat, seluruh tahapan perkara terkunci permanen dan tidak ada lagi tahapan yang dapat ditambahkan (ref: D-053).

### Status Proses

Status proses mengikuti **tahapan progress terakhir yang telah difinalkan**.

| Kondisi | Status Proses |
|---|---|
| Belum ada tahapan progress yang difinalkan | **Baru** |
| Tahapan terakhir SPDP | **SPDP** |
| Tahapan terakhir P-16 | **P-16** |
| Tahapan terakhir P-17P | **P-17P** |
| Tahapan terakhir TAHAP I | **TAHAP I** |
| Tahapan terakhir P-18P | **P-18P** |
| Tahapan terakhir P-19 | **P-19** |
| Tahapan terakhir P-21/P21a | **P-21/P21a** |
| Tahapan terakhir TAHAP II | **TAHAP II** |
| Tahapan terakhir Keputusan Pengadilan | **Keputusan Pengadilan** |

Aturan:

- Status proses diperbarui otomatis setelah tahapan difinalkan.
- Status proses tidak dipilih bebas untuk menggantikan tahapan.
- Informasi pra-SPDP tidak menjadi status proses progress.
- DPO tidak menggantikan status proses.
- SP3 tidak menggantikan histori tahapan.

### Status Berkas Perkara

Status formal Berkas Perkara terdiri dari:

- **Selesai**
- **Tidak Selesai**

`Dalam Proses` merupakan label tampilan sementara, bukan status formal.

#### Trigger Selesai

Berkas menjadi **Selesai** ketika Penyidik berhasil melakukan **Simpan & Lanjut Tahap Berikutnya** dari TAHAP II sesuai mekanisme penyelesaian tahap, dengan field wajib tahap tersebut telah lengkap.

Perubahan ke Selesai bukan tombol khusus terpisah; status diisi otomatis oleh sistem setelah aksi tahap berhasil.

#### Trigger Tidak Selesai

Berkas otomatis menjadi **Tidak Selesai** tepat pada **00:00 tanggal 1 Januari tahun berikutnya**, apabila pada saat itu berkas belum mencapai status **Selesai**. Trigger berjalan otomatis berdasarkan tanggal sistem dan tidak membutuhkan aksi manusia.

Kedua trigger bersifat saling eksklusif:

- Jika Selesai tercapai lebih dulu → trigger Tidak Selesai tidak berlaku.
- Jika Selesai belum tercapai sampai tahun berganti → berkas menjadi Tidak Selesai otomatis.

### DPO

DPO merupakan **toggle/penanda manual**, bukan status formal Berkas Perkara.

Aturan:

- DPO dapat diaktifkan Penyidik selama berkas masih berjalan.
- DPO dapat diaktifkan di tahap mana pun ketika Penyidik mengetahui tersangka kabur.
- DPO tidak mengubah tahapan.
- DPO tidak otomatis mengunci Berkas Perkara.
- DPO tidak menjadi status formal Selesai atau Tidak Selesai.
- Data DPO terdiri dari:
  - Nomor surat/dokumen DPO;
  - Tanggal;
  - Dokumen, jika ada.
- DPO dapat dinonaktifkan ketika tersangka ditemukan kembali.
- Tanggal penonaktifan dicatat dalam jejak rekam.
- Setelah DPO dinonaktifkan, berkas dapat dilanjutkan normal.
- Selama DPO aktif, seluruh tombol dan aksi penambahan tahapan berikutnya diblokir di UI dan server (SPDP, Tahap 1, P-21, Tahap 2, Putusan). Berkas tidak dapat dilanjutkan sampai status DPO dicabut (ref: D-053).
- Pengecualian: SP3 tetap diperbolehkan diterbitkan saat DPO aktif. Penerbitan SP3 otomatis mencabut status DPO dan menghentikan perkara menjadi Tidak Selesai (ref: D-053).
- Draft pada tahapan yang sedang berjalan tetap dapat disimpan.
- DPO tidak menghentikan atau menjeda SLA maupun batas tahun.

### SP3 (Surat Perintah Penghentian Penyidikan)

- SP3 merupakan dokumen administratif penutup penghentian penyidikan yang bersifat terminal (ref: D-030, D-035).
- SP3 diterbitkan secara manual dengan input nomor surat, tanggal, teks keterangan ringkas, dan dokumen PDF (maksimal 5 MB).
- Penerbitan SP3 langsung memicu perubahan status proses menjadi **SP3** dan status formal menjadi **Tidak Selesai** (D-035 Poin 15 & 22).
- Berkas yang telah diterbitkan SP3 dikunci secara permanen dari penambahan atau pelanjutan tahapan berikutnya di UI dan server (tombol tahap disembunyikan dan aksi diblokir).
- Penerbitan SP3 dicatat dalam jejak rekam/audit trail.

> **Perbandingan Pemblokiran Tahapan (DPO vs SP3)**:
> Baik status DPO aktif maupun SP3 sama-sama menghambat kelanjutan perkara dan memblokir seluruh tombol serta aksi penambahan tahapan berikutnya di antarmuka (UI) maupun validasi server. Perbedaan mendasarnya terletak pada pencabutan status:
> 1. **DPO dapat dicabut** sewaktu-waktu saat tersangka berhasil ditemukan atau menyerahkan diri, sehingga pemblokiran dicabut dan tahapan perkara dapat dilanjutkan kembali seperti biasa.
> 2. **SP3 tidak dapat dicabut** karena merupakan keputusan penghentian penyidikan yang bersifat final/permanen (terminal), sehingga perkara berhenti mutlak sebagai Tidak Selesai tanpa ada kemungkinan penambahan tahapan baru.

### Draft dan Penguncian

Pada **setiap tahapan**, Penyidik memiliki tiga tombol:

1. **Batal** — keluar tanpa menyimpan.
2. **Draft** — menyimpan data apa adanya, termasuk data yang belum lengkap, tanpa menaikkan tahap.
3. **Simpan & Lanjut Tahap Berikutnya** — hanya berhasil jika seluruh field wajib lengkap; setelah berhasil tahap naik dan data tahap tersebut terkunci.

Aturan:

- Selama Draft, data dapat diedit.
- Draft tidak otomatis menaikkan tahap.
- Setelah tahap lanjut, Penyidik tidak dapat mengedit tahap tersebut.
- Penyidik tidak dapat mundur ke tahap sebelumnya.
- Berdasarkan keputusan terakhir di chat, penguncian juga **tidak dapat di-override oleh Administrator**.

### Batas Waktu (SLA) dan Tahun

- SLA Berkas Perkara adalah **1 bulan kalender secara total**, dihitung dari tanggal SPDP diterbitkan sampai berkas berstatus Selesai.
- SLA bukan hari kerja.
- SLA bukan satu bulan untuk setiap tahap.
- Berkas hanya boleh dikerjakan dalam tahun yang sama dengan tahun SPDP diterbitkan.
- **Batas tahun bersifat mutlak dan selalu didahulukan daripada SLA 1 bulan.**
- Jika SLA jatuh pada tahun berikutnya, pergantian tahun tetap berlaku lebih dahulu.
- Tidak ada pengecualian untuk SPDP yang diterbitkan mendekati akhir tahun.
- DPO tidak menghentikan atau menjeda SLA maupun batas tahun.

### Dashboard Potensi Terlimpah

Menjelang akhir tahun, sistem menyediakan tampilan monitoring untuk mengidentifikasi Berkas Perkara yang masih belum Selesai.

Tujuannya adalah membantu Penyidik dan Pimpinan mengidentifikasi berkas yang berpotensi berakhir sebagai Tidak Selesai sebelum trigger otomatis 1 Januari berjalan.

### Case History / Audit Trail

Histori ditampilkan pada halaman detail Berkas Perkara, di bawah kartu informasi tahapan perkara.

Histori mencatat aktivitas penting seperti:

- perubahan status;
- unggah dokumen;
- catatan progres;
- pengguna;
- tanggal;
- timestamp.

### Hak Akses

| Role | Lihat | Tambah Berkas | Edit Draft | Edit Setelah Lanjut | Soft Delete (Master/User) | Hapus Perkara | Ekspor Excel 44 Kolom |
|---|---|---|---|---|---|---|---|
| **Penyidik** | ✅ Semua perkara | ✅ | ✅ | ❌ | ✅ (Pasal & Instansi) | ❌ | ✅ |
| **Pimpinan** | ✅ Semua perkara | ❌ | ❌ | ❌ | ❌ (Diblokir) | ❌ | ❌ |
| **Administrator** | ✅ Semua perkara + data pengguna | ✅ | ✅ | ❌ | ✅ (Penuh) | ❌ | ✅ |

Aturan tambahan:

- Penyidik dapat melihat semua perkara, mengelola Pra-SPDP dan Tahapan, namun akses menu dan halaman Progress Perkara diblokir (khusus Pimpinan/Umum, ref: D-035 Kelompok B).
- Pimpinan / Umum: Role "umum" adalah role untuk pimpinan/pemimpin dengan fitur terbatas: Dashboard, Pencarian Perkara, dan Potensi Terlimpah (read-only); akses menu Perkara, Master Data, dan Pengaturan diblokir (ref: D-035, D-053).
- Administrator dapat mengelola pengguna, hak akses, dan master data secara penuh.
- **Data Perkara, Laporan Kejadian, dan Sprindik tidak dapat dihapus** oleh siapapun demi integritas catatan penanganan hukum (ref: D-035 Kelompok D).
- **Hard delete tidak tersedia untuk role mana pun.**
- Penghapusan menggunakan **soft delete** hanya berlaku untuk data Master Data dan Manajemen Pengguna yang dapat dipulihkan melalui halaman Recovery Data (sebelumnya Sampah).
- **Keamanan Akun & Sesi**: Pengguna dengan status `is_active = false` atau terhapus (`deleted_at` terisi) ditolak masuk sistem dengan toast dan banner peringatan setelah password valid (mencegah enumerasi akun); sesi aktif pengguna yang dinonaktifkan di tengah operasional diputus seketika via middleware `EnsureUserIsActive` (ref: D-053).
- **Data Petugas**: Modul Data Petugas (dibawah Pengaturan) memungkinkan penugasan peran non-admin (Penyidik, Pimpinan/Umum) dari daftar user yang ada di sistem (ref: D-053).

## 6. Non-Functional Requirements

| Kategori | Requirement | Target |
|---|---|---|
| Performance | Dashboard dan monitoring responsif | ≤ 3 detik untuk mayoritas request normal |
| Performance | Pencarian dan filter responsif | Hasil ≤ 3 detik via live AJAX filter |
| Security | Akses fitur dibatasi role dan permission | 100% akses tanpa permission ditolak (403 Forbidden) |
| Security | Dokumen perkara wajib format PDF (maksimal 5 MB) | 100% format non-PDF atau > 5 MB ditolak server |
| Security | Tahapan yang sudah dilanjutkan tidak dapat diubah oleh siapapun | 100% terkunci mutlak termasuk bagi Administrator |
| Security | Audit trail tersedia untuk aktivitas penting | 100% aktivitas yang ditetapkan tercatat terindeks |
| Security | Perlindungan data perkara & larangan hard delete | 100% perkara terlindungi dari penghapusan; hard delete tidak tersedia |
| Usability | Batal, Draft, dan Simpan & Lanjut mudah dibedakan | 100% tahapan memiliki ketiga aksi |
| Usability | Tahapan, progress, status proses, dan status monitoring mudah dibedakan | Informasi dapat dipahami dalam satu tampilan |
| Usability | DPO aktif terlihat jelas | Status DPO dan blokir Tahap II terlihat di UI & server |
| Reliability | Urutan tahapan tetap konsisten | Tidak ada duplikasi atau perpindahan ilegal |
| Reliability | Progress mengikuti bobot milestone | 100% |
| Reliability | Status proses mengikuti tahapan terakhir atau SP3 | 100% |
| Reliability | Status Selesai/Tidak Selesai mengikuti trigger yang ditentukan | 100% |
| Reliability | Trigger 1 Januari berjalan otomatis (00:00 WITA) | 100% berkas yang memenuhi kondisi berubah menjadi Tidak Selesai |
| Reliability | DPO tidak menjeda SLA/batas tahun | Tidak ada pengecualian waktu akibat DPO |
| Compliance | Data hanya tersedia bagi pengguna berwenang | Tidak ada akses publik |
| Compliance | Penghapusan master data/user dapat dipulihkan | 100% pemulihan melalui Sampah (soft delete) |

## 7. Success Metric

| Metric | Baseline | Target | Cara Ukur |
|---|---|---|---|
| Pengguna dapat mengetahui posisi perkara melalui aplikasi | Belum terukur | ≥ 80% pengguna target | Survei/evaluasi |
| Berkas memiliki tahapan terakhir yang jelas | Belum terukur | 100% berkas aktif | Audit data |
| Status proses sesuai tahapan terakhir atau SP3 | Belum terukur | 100% | Pengujian fungsional |
| Progress sesuai bobot milestone | Belum terukur | 100% | Pengujian fungsional |
| Tidak ada duplikasi tahapan | Belum terukur | 100% | Pengujian dan audit data |
| Tahapan final tidak dapat diedit/dimundurkan | Belum terukur | 100% | Pengujian fungsional |
| Setiap tahapan memiliki Draft | Belum terukur | 100% | Pengujian fungsional |
| Berkas yang selesai mengikuti trigger Selesai | Belum terukur | 100% | Pengujian skenario |
| Berkas yang belum selesai pada 1 Januari menjadi Tidak Selesai | Belum terukur | 100% | Pengujian pergantian tahun |
| DPO aktif memblokir pelanjutan Tahap II | Belum terukur | 100% | Pengujian skenario DPO |
| DPO nonaktif memungkinkan proses berjalan kembali | Belum terukur | 100% | Pengujian skenario |
| Histori perubahan penting tersedia | Belum terukur | 100% | Audit trail |
| Akses tanpa permission ditolak | Belum terukur | 100% | Pengujian kontrol akses |
| Data perkara terlindungi dari penghapusan & hard delete tidak tersedia | Belum terukur | 100% | Pengujian fungsional |
| Pimpinan menggunakan dashboard untuk monitoring | Belum terukur | ≥ 80% | Analisis penggunaan/survei |
| Berkas berpotensi Tidak Selesai dapat diidentifikasi sebelum akhir tahun | Belum terukur | 100% | Pengujian dashboard |
| Data rekap perkara dapat diekspor ke Excel 44 kolom | Belum terukur | 100% | Pengujian dokumen Excel (.xlsx) |

## 8. Out of Scope (v1.0)

- Aplikasi mobile/native.
- Notifikasi otomatis melalui WhatsApp.
- Integrasi dengan sistem internal BPOM lainnya.
- Integrasi dengan sistem eksternal atau lembaga lain.
- Integrasi e-Court.
- Otomatisasi keputusan hukum.
- Prediksi hasil perkara.
- Rekomendasi berbasis AI/ML.
- Fitur Machine Learning, Artificial Intelligence, dan Data Science.
- Fitur publik atau akses masyarakat umum.
- Pengeditan tahapan yang telah dikunci oleh siapapun.
- Perpindahan mundur ke tahap sebelumnya.
- Pemindahan Berkas Perkara ke tahun berikutnya hanya karena belum selesai.
- Penghapusan data perkara / Laporan Kejadian / Sprindik.
- Hard delete data.
- Detail implementasi teknis dan arsitektur aplikasi (mengacu pada `ARCHITECTURE.md`).

---

Seluruh aturan utama mengenai struktur Perkara/Berkas Perkara, tahapan, progress, Draft, penguncian, status, DPO, SP3, SLA, batas tahun, hak akses, dan soft delete telah ditetapkan sebagai acuan kerja.