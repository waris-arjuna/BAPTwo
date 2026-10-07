# PRD — BAPTwo

## 1. Ringkasan Produk

BAPTwo adalah aplikasi web internal BPOM Kalimantan Selatan untuk mencatat, memonitor, dan melacak penanganan perkara secara terpusat. Sistem mencatat informasi awal perkara sebelum SPDP, kemudian memonitor progress utama mulai dari SPDP hingga Keputusan Pengadilan. Setiap Berkas Perkara memiliki informasi tersangka, pasal, TKP, PPNS, tahapan, dokumen, histori, status, dan progress yang dapat ditelusuri. BAPTwo dirancang cukup untuk kebutuhan monitoring dan tracking perkara tanpa overengineering.

## 2. Problem Statement

- Informasi perkara dan perkembangan penanganannya perlu tersedia dalam satu sistem terpusat.
- Informasi awal seperti Laporan Kejadian dan Surat Perintah Penyidikan perlu dicatat sebelum progress utama perkara dimulai dari SPDP.
- Informasi pasal, tersangka, TKP, PPNS utama, dan PPNS anggota perlu tersedia sebagai bagian dari data perkara.
- Satu Perkara dapat memiliki lebih dari satu Tersangka dan setiap Tersangka memiliki Berkas Perkara terpisah.
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
| Penyidik | Menangani dan menginput perkara | Mengelola informasi awal, tersangka, pasal, TKP, PPNS, tahapan, dokumen, Draft, DPO, dan monitoring | Harian |
| Pimpinan | Memantau perkembangan perkara | Melihat seluruh perkara, progress, status, dan pekerjaan penyidik | Harian/Mingguan |
| Administrator | Mengelola pengguna dan akses | Mengelola user, role, permission, soft delete, dan aktivitas sistem | Sesuai kebutuhan |

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
| F-02 | Berkas Perkara per Tersangka | Must | Setiap tersangka memiliki berkas terpisah | Jumlah Berkas Perkara mengikuti jumlah tersangka. |
| F-03 | Data Tersangka | Must | Dibutuhkan untuk setiap Berkas Perkara | Data tersangka dapat dicatat dan ditampilkan pada Berkas Perkara. |
| F-04 | Pasal Berlapis | Must | Satu Berkas dapat memiliki beberapa pasal | Beberapa pasal dapat dicatat pada satu Berkas Perkara. |
| F-05 | Laporan Kejadian | Must | Menjadi informasi awal perkara | Laporan Kejadian dapat dicatat dan dikaitkan dengan perkara. |
| F-06 | Surat Perintah Penyidikan | Must | Menjadi informasi awal sebelum SPDP | Surat Perintah Penyidikan dapat dicatat dan dikaitkan dengan perkara. |
| F-07 | Informasi TKP | Must | Menjadi informasi utama perkara | TKP dapat dicatat dan dikaitkan dengan perkara. |
| F-08 | PPNS Utama dan PPNS Anggota | Must | Menunjukkan petugas penanganan perkara | Perkara dapat memiliki PPNS utama dan PPNS anggota. |
| F-09 | Progress Mulai SPDP | Must | Menetapkan titik awal progress | Informasi pra-SPDP tidak masuk perhitungan progress. |
| F-10 | Rangkaian Tahapan Progress | Must | Menjaga konsistensi proses | Sistem mendukung SPDP → P-16 → P-17P → TAHAP I → P-18P/P-19 → P-21/P21a → TAHAP II → Keputusan Pengadilan. |
| F-11 | Satu Tahapan Maksimal Satu Kali | Must | Mencegah duplikasi tahapan | Satu jenis tahapan hanya dapat dicatat satu kali pada satu Berkas Perkara. |
| F-12 | Validasi Urutan Tahapan | Must | Mencegah proses mundur atau lompat sembarangan | Tahapan hanya dapat ditambahkan sesuai urutan workflow. |
| F-13 | Tahapan Opsional | Must | Tidak semua proses tersedia | P-16, P-17P, P-18P, dan P-19 boleh dilewati tetapi dapat diinput apabila tersedia. |
| F-14 | Draft pada Setiap Tahapan | Must | Data belum lengkap tetap dapat disimpan | Setiap tahapan menyediakan aksi Draft tanpa menaikkan tahap. |
| F-15 | Tombol Batal | Must | Membatalkan input | Batal keluar tanpa menyimpan perubahan yang belum disimpan. |
| F-16 | Tombol Draft | Must | Menyimpan data sementara | Draft menyimpan data apa adanya tanpa menaikkan tahap. |
| F-17 | Simpan & Lanjut Tahap Berikutnya | Must | Memfinalkan tahap dan meneruskan workflow | Tahap hanya dapat dilanjutkan jika field wajib lengkap; setelah berhasil tahap terkunci. |
| F-18 | Penguncian Tahapan | Must | Menjaga integritas data | Tahap yang telah lanjut tidak dapat diedit atau dimundurkan oleh Penyidik. |
| F-19 | Tampilan Lihat/Edit | Must | Membedakan data yang dapat diubah | Draft memiliki Lihat + Edit; data yang sudah lanjut hanya Lihat. |
| F-20 | Status Formal Berkas | Must | Menentukan hasil monitoring formal | Status formal hanya Selesai dan Tidak Selesai. |
| F-21 | Label Dalam Proses | Must | Menunjukkan kondisi selama tahun berjalan | Dalam Proses adalah label tampilan, bukan status formal. |
| F-22 | Trigger Selesai | Must | Menentukan penyelesaian berkas secara eksplisit | Berkas menjadi Selesai ketika Tahap II berhasil diselesaikan melalui aksi Simpan & Lanjut sesuai mekanisme tahap. |
| F-23 | Trigger Tidak Selesai | Must | Menentukan kondisi akhir tahun | Berkas yang belum Selesai pada 1 Januari tahun berikutnya otomatis menjadi Tidak Selesai. |
| F-24 | DPO | Must | Menandai tersangka buron | Penyidik dapat mengaktifkan DPO pada tahap mana pun selama berkas masih berjalan. |
| F-25 | Penonaktifan DPO | Must | Memungkinkan proses kembali normal | DPO dapat dinonaktifkan ketika tersangka ditemukan kembali dan tanggal penonaktifan dicatat pada jejak rekam. |
| F-26 | Pembatasan Tahap II Saat DPO Aktif | Must | Mencegah pelimpahan fisik tersangka saat DPO | Saat DPO aktif, tombol "Simpan & Lanjut ke Tahap 2 (Pelimpahan Tersangka & Barang Bukti)" disabled. |
| F-27 | DPO Tidak Menghentikan SLA/Batas Tahun | Must | DPO tidak memberikan pengecualian waktu | DPO tidak menghentikan atau menjeda SLA maupun batas tahun. |
| F-28 | SP3 | Must | Menjadi bukti penutup administratif | SP3 diunggah setelah status Tidak Selesai dan dicatat dalam audit trail; SP3 bukan pemicu status. |
| F-29 | Status Proses Otomatis | Must | Menunjukkan posisi terbaru perkara | Status proses mengikuti tahapan progress terakhir yang telah difinalkan. |
| F-30 | Progress Berbobot | Must | Menunjukkan persentase progress | Progress menggunakan milestone SPDP 15%, TAHAP I 55%, P-21/P21a 85%, dan TAHAP II 100%. |
| F-31 | Case History / Audit Trail | Must | Memungkinkan penelusuran perubahan | Perubahan status, upload dokumen, dan catatan progres dapat ditelusuri. |
| F-32 | Dokumen Perkara Terpusat | Must | Menjaga keterhubungan dokumen | Dokumen terhubung dengan Berkas Perkara dan tahapan terkait. |
| F-33 | Nomor Berkas Manual | Must | Format nomor dapat bervariasi | Nomor Berkas Perkara diinput manual tanpa validasi pola tertentu. |
| F-34 | Catatan/Deskripsi | Must | Menyimpan konteks tambahan | Tahapan dan kondisi khusus dapat memiliki catatan/deskripsi. |
| F-35 | Data DPO | Must | DPO perlu dapat ditelusuri | Data DPO mencakup nomor, tanggal, dan dokumen jika tersedia. |
| F-36 | Data Alamat Terstruktur | Must | Menjaga konsistensi wilayah | Data alamat mendukung pemilihan wilayah secara hierarkis. |
| F-37 | Sumber Data Wilayah | Must | Mengurangi input manual | Data wilayah menggunakan API Kode Wilayah. |
| F-38 | RBAC dan Permission | Must | Mengendalikan akses | Hak akses fitur mengikuti role dan permission. |
| F-39 | Pencarian dan Filter | Must | Memudahkan menemukan perkara | Pengguna dapat mencari dan memfilter Berkas Perkara. |
| F-40 | Dashboard Monitoring Perkara | Must | Menyediakan ringkasan monitoring | Penyidik dan Pimpinan dapat melihat ringkasan progress dan status perkara. |
| F-41 | Dashboard Potensi Terlimpah | Should | Membantu identifikasi perkara belum selesai sebelum akhir tahun | Dashboard menampilkan Berkas Perkara yang belum Selesai untuk ditindaklanjuti. |
| F-42 | Monitoring Pekerjaan Penyidik | Must | Pimpinan perlu melihat pekerjaan penyidik | Pimpinan dapat melihat perkembangan perkara berdasarkan penyidik. |
| F-43 | Akses Seluruh Perkara bagi Penyidik | Must | Monitoring tidak dibatasi pembuat | Penyidik dapat melihat seluruh perkara sesuai permission. |
| F-44 | Soft Delete | Must | Mencegah penghapusan permanen | Seluruh penghapusan menggunakan soft delete dan hard delete tidak tersedia. |
| F-45 | Export Data | Must | Mendukung kebutuhan pelaporan | Data yang berhak diekspor tersedia dalam Excel dan CSV. |

### Struktur Perkara dan Berkas Perkara

- Satu Perkara dapat memiliki lebih dari satu Tersangka.
- Setiap Tersangka memiliki satu Berkas Perkara terpisah.
- Jumlah Berkas Perkara dalam suatu Perkara mengikuti jumlah Tersangka.
- Setiap Berkas Perkara dapat memiliki pasal berlapis.
- Aturan tahapan, SLA, status, dan penguncian berlaku pada level Berkas Perkara.

### Informasi Pra-SPDP

Informasi awal perkara berada sebelum progress utama:

**Laporan Kejadian → Surat Perintah Penyidikan → Informasi Pasal, Tersangka, TKP, PPNS Utama, PPNS Anggota → SPDP**

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
- Keputusan Pengadilan tetap dicatat setelah TAHAP II tetapi tidak meningkatkan progress di atas 100%.

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
- Selama DPO aktif, tombol **Simpan & Lanjut ke Tahap 2 (Pelimpahan Tersangka & Barang Bukti)** disabled.
- Tahap sebelumnya tetap dapat dikerjakan selama DPO aktif.
- DPO tidak menghentikan atau menjeda SLA maupun batas tahun.

### SP3

- SP3 merupakan dokumen administratif.
- SP3 diunggah setelah status Berkas Perkara menjadi Tidak Selesai.
- SP3 menjadi bukti penutup administratif.
- SP3 masuk jejak rekam/audit trail.
- SP3 **bukan pemicu** perubahan status.

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

| Role              | Lihat | Tambah Berkas | Edit Draft | Edit Setelah Lanjut | Soft Delete | Hard Delete |
|-------------------|-------------------|---|---|---|---|---|
| **Penyidik**      | ✅ Semua perkara | ✅ | ✅ | ❌ | ✅ | ❌ |
| **Pimpinan**      | ✅ Semua perkara | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Administrator** | ✅ Semua perkara + data pengguna | ✅ | ✅ | ❌ | ✅ | ❌ |

Aturan tambahan:

- Penyidik dapat melihat semua perkara sesuai permission.
- Pimpinan memiliki akses lihat dan pencarian seluruh perkara.
- Administrator dapat mengelola pengguna dan permission.
- **Hard delete tidak tersedia untuk role mana pun.**
- Seluruh penghapusan menggunakan **soft delete** dan data yang dihapus tetap dapat dipulihkan melalui Sampah.

## 6. Non-Functional Requirements

| Kategori | Requirement | Target |
|---|---|---|
| Performance | Dashboard dan monitoring responsif | ≤ 3 detik untuk mayoritas request normal |
| Performance | Pencarian dan filter responsif | Hasil ≤ 3 detik pada kondisi normal |
| Security | Akses fitur dibatasi role dan permission | 100% akses tanpa permission ditolak |
| Security | Dokumen hanya dapat diakses pengguna berwenang | 100% |
| Security | Tahapan yang sudah dilanjutkan tidak dapat diubah oleh Penyidik | 100% |
| Security | Audit trail tersedia untuk aktivitas penting | 100% aktivitas yang ditetapkan tercatat |
| Security | Hard delete tidak tersedia | 100% penghapusan menggunakan soft delete |
| Usability | Batal, Draft, dan Simpan & Lanjut mudah dibedakan | 100% tahapan memiliki ketiga aksi |
| Usability | Tahapan, progress, status proses, dan status monitoring mudah dibedakan | Informasi dapat dipahami dalam satu tampilan |
| Usability | DPO aktif terlihat jelas | Status DPO dan blokir Tahap II terlihat |
| Reliability | Urutan tahapan tetap konsisten | Tidak ada duplikasi atau perpindahan ilegal |
| Reliability | Progress mengikuti bobot milestone | 100% |
| Reliability | Status proses mengikuti tahapan terakhir | 100% |
| Reliability | Status Selesai/Tidak Selesai mengikuti trigger yang ditentukan | 100% |
| Reliability | Trigger 1 Januari berjalan otomatis | 100% berkas yang memenuhi kondisi berubah menjadi Tidak Selesai |
| Reliability | DPO tidak menjeda SLA/batas tahun | Tidak ada pengecualian waktu akibat DPO |
| Compliance | Data hanya tersedia bagi pengguna berwenang | Tidak ada akses publik |
| Compliance | Penghapusan data dapat dipulihkan | Seluruh penghapusan menggunakan soft delete |

## 7. Success Metric

| Metric | Baseline | Target | Cara Ukur |
|---|---|---|---|
| Pengguna dapat mengetahui posisi perkara melalui aplikasi | Belum terukur | ≥ 80% pengguna target | Survei/evaluasi |
| Berkas memiliki tahapan terakhir yang jelas | Belum terukur | 100% berkas aktif | Audit data |
| Status proses sesuai tahapan terakhir | Belum terukur | 100% | Pengujian fungsional |
| Progress sesuai bobot milestone | Belum terukur | 100% | Pengujian fungsional |
| Tidak ada duplikasi tahapan | Belum terukur | 100% | Pengujian dan audit data |
| Tahapan final tidak dapat diedit/dimundurkan oleh Penyidik | Belum terukur | 100% | Pengujian fungsional |
| Setiap tahapan memiliki Draft | Belum terukur | 100% | Pengujian fungsional |
| Berkas yang selesai mengikuti trigger Selesai | Belum terukur | 100% | Pengujian skenario |
| Berkas yang belum selesai pada 1 Januari menjadi Tidak Selesai | Belum terukur | 100% | Pengujian pergantian tahun |
| DPO aktif memblokir pelanjutan Tahap II | Belum terukur | 100% | Pengujian skenario DPO |
| DPO nonaktif memungkinkan proses berjalan kembali | Belum terukur | 100% | Pengujian skenario |
| Histori perubahan penting tersedia | Belum terukur | 100% | Audit trail |
| Akses tanpa permission ditolak | Belum terukur | 100% | Pengujian kontrol akses |
| Hard delete tidak tersedia | Belum terukur | 100% | Pengujian fungsional |
| Pimpinan menggunakan dashboard untuk monitoring | Belum terukur | ≥ 80% | Analisis penggunaan/survei |
| Berkas berpotensi Tidak Selesai dapat diidentifikasi sebelum akhir tahun | Belum terukur | 100% | Pengujian dashboard |
| Data dapat diekspor | Belum terukur | 100% | Pengujian Excel dan CSV |

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
- Pengeditan tahapan yang telah dikunci oleh Penyidik.
- Perpindahan mundur ke tahap sebelumnya.
- Pemindahan Berkas Perkara ke tahun berikutnya hanya karena belum selesai.
- Hard delete data.
- Detail implementasi teknis dan arsitektur aplikasi. Detail teknis mengacu pada `architecture.md` (TBD).

---

Seluruh aturan utama mengenai struktur Perkara/Berkas Perkara, tahapan, progress, Draft, penguncian, status, DPO, SP3, SLA, batas tahun, hak akses, dan soft delete telah ditetapkan sebagai acuan kerja.