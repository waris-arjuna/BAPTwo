<?php

namespace Database\Seeders;

use App\Enums\JenisTahap;
use App\Enums\UserRole;
use App\Models\Alamat;
use App\Models\AuditLog;
use App\Models\BerkasPerkara;
use App\Models\Bidang;
use App\Models\Dokumen;
use App\Models\Dpo;
use App\Models\InstansiHukum;
use App\Models\Jabatan;
use App\Models\JaksaPenuntut;
use App\Models\Panggol;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\Sp3;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\Tkp;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DummyDataSeeder extends Seeder
{
    private const BULAN_ROMAWI = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
        5 => 'V',
        6 => 'VI',
        7 => 'VII',
        8 => 'VIII',
        9 => 'IX',
        10 => 'X',
        11 => 'XI',
        12 => 'XII',
    ];

    private const NAMA_DEPAN_LAKI = [
        'Ahmad', 'Muhammad', 'Budi', 'Hendra', 'Rizky', 'Faisal', 'Irwan', 'Dedi',
        'Bambang', 'Rudi', 'Johan', 'Arif', 'Wahyu', 'Ilham', 'Bagus', 'Eko',
        'Suryanto', 'Asep', 'Rahmat', 'Gusti', 'Mursyid', 'Hafiz', 'Ridwan', 'Aditya',
        'Yusuf', 'Zainal', 'Iwan', 'Darmawan', 'Fahri', 'Hidayat', 'Syaiful', 'Naufal',
        'Reza', 'Taufik', 'Anwar', 'Danu', 'Fauzan', 'Syamsul', 'Agus', 'Hasan',
        'Lukman', 'Firman', 'Kurnia', 'Mansyur', 'Farhan', 'Bayu', 'Gilang', 'Satria',
    ];

    private const NAMA_DEPAN_PEREMPUAN = [
        'Siti', 'Dewi', 'Nurul', 'Maya', 'Anisa', 'Linda', 'Tri', 'Rina',
        'Fitri', 'Yuni', 'Ayu', 'Diah', 'Ratna', 'Novita', 'Sri', 'Endang',
        'Lestari', 'Mira', 'Hasna', 'Sabrina', 'Wulan', 'Indah', 'Rahmi', 'Vina',
        'Desi', 'Eka', 'Salma', 'Putri', 'Fatimah', 'Khadijah', 'Zahra', 'Aulia',
        'Nabila', 'Rizka', 'Kartika', 'Dina', 'Mega', 'Sari', 'Utami', 'Rini',
    ];

    private const NAMA_BELAKANG = [
        'Wijaya', 'Saputra', 'Hidayat', 'Pratama', 'Kurniawan', 'Setiawan', 'Maulana',
        'Hakim', 'Santosa', 'Gunawan', 'Rahmawati', 'Lestari', 'Anggraeni', 'Wibowo',
        'Sujatmiko', 'Ramadhan', 'Prasetyo', 'Sutrisno', 'Firmansyah', 'Siregar',
        'Nasution', 'Mahfud', 'Anshari', 'Rusli', 'Syahrani', 'Mardianto',
        'Kusuma', 'Halim', 'Pahlevi', 'Bachri', 'Ismail', 'Rahman', 'Sofyan',
        'Mubarak', 'Wardana', 'Saputri', 'Maryam', 'Salsabila', 'Ningsih', 'Pratiwi',
        'Wahyuni', 'Utami', 'Damayanti', 'Handayani', 'Marlina', 'Permata', 'Sari',
    ];

    private const JALAN = [
        'A. Yani', 'Pangeran Antasari', 'Veteran', 'Brigjend H. Hasan Basri',
        'Kemakmuran', 'Murakata', 'Ir. P.H.M. Noor', 'Basuki Rahmat',
        'Sekumpul Raya', 'Putri Cipta Sari', 'Jenderal Sudirman', 'Perdagangan',
        'Lambung Mangkurat', 'Trikora', 'Sultan Adam', 'Belitung Darat',
        'Kuripan', 'Pangeran Samudera', 'Achmad Yani Km. 34', 'Gerilya',
        'Gubernur Soebarjo', 'Sungai Jingah', 'Kayu Tangi', 'Kramat Jaya',
        'Hasanuddin HM', 'Sutoyo S.', 'D.I. Panjaitan', 'Ulin Barat',
    ];

    private const NAMA_USAHA = [
        'Sumber Rejeki', 'Berkah Jaya', 'Ayu Cantik', 'Sehat Alami', 'Bugar Sehat',
        'Cahaya Farma', 'Mekar Sari', 'Tunas Baru', 'Sinar Abadi', 'Aneka Jaya',
        'Mitra Sehat', 'Karya Mandiri', 'Rasa Murni', 'Bina Usaha', 'Surya Kencana',
        'Harapan Baru', 'Sumber Makmur', 'Amanah Farma', 'Duta Niaga', 'Panca Jaya',
        'Nusa Indah', 'Sari Wangi', 'Cinta Bunda', 'Bintang Timur', 'Sentosa Abadi',
        'Makmur Sejahtera', 'Barokah Herbal', 'Pelita Farma', 'Dunia Sehat', 'Kurnia Alam',
    ];

    private const PEKERJAAN_UMUM = [
        'Wiraswasta', 'Karyawan Swasta', 'Pedagang', 'Kurir Ekspedisi', 'Petani',
        'Buruh Harian', 'Ibu Rumah Tangga', 'Pemilik Ruko', 'Pemilik Toko Online',
        'Pengemudi Ojek Daring', 'Pemilik Warung', 'Karyawan Toko', 'Sales Kosmetik',
        'Distributor Mandiri', 'Pengelola Gudang', 'Pengecer Jamu', 'Pengrajin Makanan',
    ];

    private const VONIS = [
        '2 tahun penjara dan denda Rp 150.000.000 subsider 4 bulan kurungan',
        '1 tahun 6 bulan penjara dan denda Rp 100.000.000 subsider 2 bulan kurungan',
        '3 tahun penjara dan denda Rp 250.000.000 subsider 6 bulan kurungan',
        '10 bulan penjara dan denda Rp 50.000.000 subsider 1 bulan kurungan',
        '4 tahun penjara dan denda Rp 500.000.000 subsider 6 bulan kurungan',
        '1 tahun penjara dengan masa percobaan 2 tahun dan denda Rp 20.000.000',
        '2 tahun 6 bulan penjara dan denda Rp 200.000.000 subsider 3 bulan kurungan',
        '1 tahun penjara dan denda Rp 75.000.000 subsider 2 bulan kurungan',
        '5 tahun penjara dan denda Rp 1.000.000.000 subsider 6 bulan kurungan',
        '8 bulan penjara dan denda Rp 30.000.000 subsider 1 bulan kurungan',
    ];

    private const ALASAN_SP3 = [
        'Tidak diperoleh bukti yang cukup sesuai ketentuan Pasal 109 ayat (2) KUHAP.',
        'Peristiwa yang dilaporkan bukan merupakan tindak pidana berdasarkan hasil gelar perkara.',
        'Penghentian penyidikan demi hukum karena nebis in idem dan kedaluwarsa penuntutan.',
        'Penyelesaian perkara melalui keadilan restoratif (Restorative Justice) dan pemulihan kerugian.',
        'Tersangka meninggal dunia sehingga penuntutan gugur demi hukum sesuai Pasal 77 KUHP.',
    ];

    private const AGAMA = [
        0 => 'islam',
        1 => 'islam',
        2 => 'islam',
        3 => 'islam',
        4 => 'islam',
        5 => 'islam',
        6 => 'kristen',
        7 => 'katolik',
        8 => 'hindu',
        9 => 'buddha',
    ];

    /** @var array<string, int> */
    private array $urutanNomor = [];

    private int $urutanTersangka = 0;

    /**
     * Jalankan seeder 500 data perkara dummy yang beragam untuk seluruh alur BAPTwo periode 2025-2026.
     */
    public function run(): void
    {
        // 1. Pastikan sample PDF privat tersedia
        $this->ensureSamplePdf();

        // 2. Bersihkan data dummy lama yang sudah usang agar jumlah data tetap tepat 500
        $this->cleanupObsoleteDummyCases();

        // 3. Siapkan Master Data Lengkap (Jabatan, Bidang, Panggol, 38 Pasal, 45 Jaksa, Instansi)
        $master = $this->seedMasterData();

        // 4. Siapkan Pengguna PPNS (20 Penyidik) serta Administrator & Pimpinan
        $users = $this->seedUsers($master);
        $this->call(AdminPimpinanSeeder::class);

        // 5. Tanam 500 Perkara dengan variasi alur, tahun 2025–2026, tahapan, DPO, SP3, dan murni 1 Tersangka 1 Berkas
        $this->seedPerkaraCases($master, $users);
    }

    /**
     * Membuat berkas PDF contoh di storage privat agar preview & download dokumen berfungsi.
     */
    private function ensureSamplePdf(): string
    {
        $path = 'dokumen/sample-bap.pdf';
        if (! Storage::disk('local')->exists($path)) {
            $pdfContent = "%PDF-1.4\n"
                ."1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
                ."2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
                ."3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >> endobj\n"
                ."4 0 obj << /Length 60 >> stream\n"
                ."BT /F1 16 Tf 72 710 Td (Dokumen Resmi Balai Besar POM di Banjarmasin) Tj ET\n"
                ."endstream endobj\n"
                ."xref\n0 5\n"
                ."0000000000 65535 f \n"
                ."0000000009 00000 n \n"
                ."0000000058 00000 n \n"
                ."0000000115 00000 n \n"
                ."0000000206 00000 n \n"
                ."trailer << /Size 5 /Root 1 0 R >>\n"
                ."startxref\n315\n%%EOF";

            Storage::disk('local')->put($path, $pdfContent);
        }

        return $path;
    }

    /**
     * Membersihkan nomor perkara dummy lama secara efisien per batch agar idempoten.
     */
    private function cleanupObsoleteDummyCases(): void
    {
        while (true) {
            $cases = Perkara::withTrashed()
                ->where('nomor_laporan_kejadian', 'like', 'LK/%')
                ->take(100)
                ->get();

            if ($cases->isEmpty()) {
                break;
            }

            $caseIds = $cases->pluck('id')->all();
            $tkpIds = $cases->pluck('tkp_id')->filter()->all();
            $tkpAlamatIds = Tkp::withTrashed()->whereIn('id', $tkpIds)->pluck('alamat_id')->filter()->all();

            $berkas = BerkasPerkara::withTrashed()->whereIn('perkara_id', $caseIds)->get();
            $berkasIds = $berkas->pluck('id')->all();

            $tersangkas = Tersangka::withTrashed()->whereIn('perkara_id', $caseIds)->get();
            $tersangkaIds = $tersangkas->pluck('id')->all();
            $tersangkaAlamatIds = $tersangkas->pluck('alamat_id')->filter()->all();

            AuditLog::whereIn('perkara_id', $caseIds)->orWhereIn('berkas_perkara_id', $berkasIds)->delete();
            TahapanPerkara::withTrashed()->whereIn('berkas_perkara_id', $berkasIds)->forceDelete();
            Dpo::withTrashed()->whereIn('berkas_perkara_id', $berkasIds)->forceDelete();
            Sp3::withTrashed()->whereIn('berkas_perkara_id', $berkasIds)->forceDelete();
            Dokumen::withTrashed()->where(function ($q) use ($caseIds, $berkasIds) {
                $q->whereIn('perkara_id', $caseIds)->orWhereIn('berkas_perkara_id', $berkasIds);
            })->forceDelete();

            DB::table('berkas_pasal')->whereIn('berkas_perkara_id', $berkasIds)->delete();
            DB::table('perkara_ppns')->whereIn('perkara_id', $caseIds)->delete();

            BerkasPerkara::withTrashed()->whereIn('id', $berkasIds)->forceDelete();
            Tersangka::withTrashed()->whereIn('id', $tersangkaIds)->forceDelete();
            if (! empty($tersangkaAlamatIds)) {
                Alamat::whereIn('id', $tersangkaAlamatIds)->delete();
            }

            Perkara::withTrashed()->whereIn('id', $caseIds)->forceDelete();
            if (! empty($tkpIds)) {
                Tkp::withTrashed()->whereIn('id', $tkpIds)->forceDelete();
            }
            if (! empty($tkpAlamatIds)) {
                Alamat::whereIn('id', $tkpAlamatIds)->delete();
            }
        }

        DashboardService::clearCache();
    }

    /**
     * Master data lengkap: Jabatan, Bidang, Panggol, 38 Pasal UU, Instansi Hukum, 45 Jaksa.
     *
     * @return array<string, mixed>
     */
    private function seedMasterData(): array
    {
        // 1. Jabatan
        $jabatanList = [
            'ppns' => Jabatan::firstOrCreate(['nama_jabatan' => 'Penyidik Pegawai Negeri Sipil (PPNS)']),
            'pfm_pertama' => Jabatan::firstOrCreate(['nama_jabatan' => 'Pengawas Farmasi dan Makanan Ahli Pertama']),
            'pfm_muda' => Jabatan::firstOrCreate(['nama_jabatan' => 'Pengawas Farmasi dan Makanan Ahli Muda']),
            'pfm_madya' => Jabatan::firstOrCreate(['nama_jabatan' => 'Pengawas Farmasi dan Makanan Ahli Madya']),
            'pfm_utama' => Jabatan::firstOrCreate(['nama_jabatan' => 'Pengawas Farmasi dan Makanan Ahli Utama']),
            'analis' => Jabatan::firstOrCreate(['nama_jabatan' => 'Analis Intelijen Penindakan']),
            'prakom' => Jabatan::firstOrCreate(['nama_jabatan' => 'Pranata Komputer Ahli Pertama']),
            'kepala' => Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Balai Besar POM']),
            'tu' => Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Bagian Tata Usaha']),
        ];

        // 2. Bidang
        $bidangList = [
            'penindakan' => Bidang::firstOrCreate(['nama_bidang' => 'Bidang Penindakan']),
            'pemeriksaan' => Bidang::firstOrCreate(['nama_bidang' => 'Bidang Pemeriksaan']),
            'pengujian' => Bidang::firstOrCreate(['nama_bidang' => 'Bidang Pengujian']),
            'infokom' => Bidang::firstOrCreate(['nama_bidang' => 'Bidang Informasi dan Komunikasi']),
            'tu' => Bidang::firstOrCreate(['nama_bidang' => 'Bagian Tata Usaha']),
        ];

        // 3. Pangkat & Golongan
        $panggolList = [
            'ii_c' => Panggol::firstOrCreate(['pangkat' => 'Pengatur', 'golongan' => 'II/c']),
            'ii_d' => Panggol::firstOrCreate(['pangkat' => 'Pengatur Tk. I', 'golongan' => 'II/d']),
            'iii_a' => Panggol::firstOrCreate(['pangkat' => 'Penata Muda', 'golongan' => 'III/a']),
            'iii_b' => Panggol::firstOrCreate(['pangkat' => 'Penata Muda Tk. I', 'golongan' => 'III/b']),
            'iii_c' => Panggol::firstOrCreate(['pangkat' => 'Penata', 'golongan' => 'III/c']),
            'iii_d' => Panggol::firstOrCreate(['pangkat' => 'Penata Tk. I', 'golongan' => 'III/d']),
            'iv_a' => Panggol::firstOrCreate(['pangkat' => 'Pembina', 'golongan' => 'IV/a']),
            'iv_b' => Panggol::firstOrCreate(['pangkat' => 'Pembina Tk. I', 'golongan' => 'IV/b']),
            'iv_c' => Panggol::firstOrCreate(['pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c']),
        ];

        // 4. Master 38 Pasal / UU Lengkap
        $pasalRaw = [
            // UU Kesehatan 17/2023
            'pasal_kesehatan_1' => ['UU Kesehatan', 'Pasal 435 jo. Pasal 138 ayat (2) dan ayat (3)', '17', '2023', 'Memproduksi atau mengedarkan Sediaan Farmasi dan/atau Alat Kesehatan yang tidak memenuhi standar.'],
            'pasal_kesehatan_2' => ['UU Kesehatan', 'Pasal 436 ayat (2) jo. Pasal 145 ayat (1)', '17', '2023', 'Melakukan praktik kefarmasian tanpa memiliki keahlian dan kewenangan resmi.'],
            'pasal_kesehatan_3' => ['UU Kesehatan', 'Pasal 436 ayat (1)', '17', '2023', 'Memproduksi sediaan farmasi dan/atau alat kesehatan tanpa memiliki keahlian dan kewenangan.'],
            'pasal_kesehatan_4' => ['UU Kesehatan', 'Pasal 437 ayat (1)', '17', '2023', 'Memproduksi atau mengedarkan sediaan farmasi dan/atau alat kesehatan palsu.'],
            'pasal_kesehatan_5' => ['UU Kesehatan', 'Pasal 438 ayat (1)', '17', '2023', 'Memproduksi atau mengedarkan kosmetik yang mengandung bahan berbahaya atau dilarang.'],
            'pasal_kesehatan_6' => ['UU Kesehatan', 'Pasal 439 ayat (1)', '17', '2023', 'Memperjualbelikan obat keras daftar G secara bebas tanpa resep dokter atau izin resmi.'],
            'pasal_kesehatan_7' => ['UU Kesehatan', 'Pasal 440 ayat (1)', '17', '2023', 'Mengedarkan obat bahan alam (obat tradisional) yang mengandung bahan kimia obat (BKO).'],
            'pasal_kesehatan_8' => ['UU Kesehatan', 'Pasal 437 ayat (2)', '17', '2023', 'Menyimpan atau membawa sediaan farmasi dan/atau alat kesehatan palsu untuk diedarkan.'],
            'pasal_kesehatan_9' => ['UU Kesehatan', 'Pasal 438 ayat (2)', '17', '2023', 'Mengedarkan kosmetik tanpa izin edar notifikasi resmi dari Badan Pengawas Obat dan Makanan.'],
            'pasal_kesehatan_10' => ['UU Kesehatan', 'Pasal 439 ayat (2)', '17', '2023', 'Penyimpanan dan penimbunan obat keras daftar G dalam jumlah besar tanpa izin pedagang besar farmasi.'],
            'pasal_kesehatan_11' => ['UU Kesehatan', 'Pasal 440 ayat (2)', '17', '2023', 'Memproduksi obat bahan alam secara tidak higienis dan tanpa sertifikasi CPOTB bertahap.'],
            'pasal_kesehatan_12' => ['UU Kesehatan', 'Pasal 441 ayat (1)', '17', '2023', 'Penyaluran bahan baku obat yang tidak memenuhi spesifikasi mutu Farmakope Indonesia.'],

            // UU Pangan 18/2012
            'pasal_pangan_1' => ['UU Pangan', 'Pasal 142 jo. Pasal 91 ayat (1)', '18', '2012', 'Pelaku Usaha Pangan yang dengan sengaja tidak memiliki izin edar terhadap setiap Pangan Olahan yang diperdagangkan.'],
            'pasal_pangan_2' => ['UU Pangan', 'Pasal 136 jo. Pasal 75 ayat (1)', '18', '2012', 'Pelaku Usaha yang menggunakan bahan tambahan pangan yang dilarang atau melampaui ambang batas maksimal.'],
            'pasal_pangan_3' => ['UU Pangan', 'Pasal 138 jo. Pasal 80 ayat (1)', '18', '2012', 'Pelaku Usaha yang memperdagangkan pangan tercemar, busuk, berbau, atau mengandung bahan beracun.'],
            'pasal_pangan_4' => ['UU Pangan', 'Pasal 140 jo. Pasal 86 ayat (2)', '18', '2012', 'Memproduksi atau mengedarkan pangan olahan dengan label atau iklan palsu dan menyesatkan.'],
            'pasal_pangan_5' => ['UU Pangan', 'Pasal 141 jo. Pasal 89', '18', '2012', 'Mengedarkan pangan olahan dengan kemasan yang membahayakan kesehatan manusia.'],
            'pasal_pangan_6' => ['UU Pangan', 'Pasal 137 ayat (1)', '18', '2012', 'Memproduksi pangan dari bahan baku hasil rekayasa genetik tanpa persetujuan keamanan pangan.'],
            'pasal_pangan_7' => ['UU Pangan', 'Pasal 139 jo. Pasal 84 ayat (1)', '18', '2012', 'Membuka kemasan akhir pangan olahan untuk dikemas kembali dan diperdagangkan tanpa izin.'],
            'pasal_pangan_8' => ['UU Pangan', 'Pasal 143 jo. Pasal 99', '18', '2012', 'Mengedarkan pangan tercemar cemaran biologi atau kimia melampaui ambang batas batas maksimal.'],
            'pasal_pangan_9' => ['UU Pangan', 'Pasal 144', '18', '2012', 'Memperdagangkan pangan kedaluwarsa dengan sengaja menghapus atau mengganti label tanggal kedaluwarsa.'],
            'pasal_pangan_10' => ['UU Pangan', 'Pasal 145', '18', '2012', 'Menolak penarikan pangan olahan berbahaya yang telah diperintahkan oleh otoritas pengawas pangan.'],
            'pasal_pangan_11' => ['UU Pangan', 'Pasal 146 ayat (1)', '18', '2012', 'Mengedarkan pangan yang mengakibatkan kerugian berat pada kesehatan dan keselamatan manusia.'],

            // UU Perlindungan Konsumen 8/1999
            'pasal_konsumen_1' => ['UU Perlindungan Konsumen', 'Pasal 62 ayat (1) jo. Pasal 8 ayat (1) huruf a', '8', '1999', 'Memproduksi dan/atau memperdagangkan barang yang tidak memenuhi atau tidak sesuai dengan standar mutu.'],
            'pasal_konsumen_2' => ['UU Perlindungan Konsumen', 'Pasal 62 ayat (1) jo. Pasal 8 ayat (1) huruf d', '8', '1999', 'Memperdagangkan barang yang tidak sesuai dengan kondisi, jaminan, keistimewaan, atau kemanjuran yang dijanjikan.'],
            'pasal_konsumen_3' => ['UU Perlindungan Konsumen', 'Pasal 62 ayat (1) jo. Pasal 8 ayat (1) huruf i', '8', '1999', 'Memperdagangkan barang yang tidak mencantumkan tanggal kedaluwarsa atau jangka waktu penggunaan yang paling baik.'],
            'pasal_konsumen_4' => ['UU Perlindungan Konsumen', 'Pasal 62 ayat (2) jo. Pasal 9 ayat (1)', '8', '1999', 'Menawarkan atau mempromosikan barang secara tidak benar, salah, atau menyesatkan mengenai asal dan mutu barang.'],
            'pasal_konsumen_5' => ['UU Perlindungan Konsumen', 'Pasal 62 ayat (3) jo. Pasal 18', '8', '1999', 'Pencantuman klausula baku pada dokumen atau perjanjian yang merugikan hak-hak konsumen.'],

            // UU Narkotika 35/2009
            'pasal_narkotika_1' => ['UU Narkotika', 'Pasal 114 ayat (1)', '35', '2009', 'Menawarkan untuk dijual, menjual, membeli, menerima, menjadi perantara dalam jual beli Narkotika Golongan I.'],
            'pasal_narkotika_2' => ['UU Narkotika', 'Pasal 129 huruf a', '35', '2009', 'Memproduksi, mengimpor, mengekspor, atau menyalurkan Prekursor Farmasi untuk pembuatan Narkotika secara melawan hukum.'],
            'pasal_narkotika_3' => ['UU Narkotika', 'Pasal 129 huruf b', '35', '2009', 'Mengimpor, mengekspor, menyalurkan, atau menyerahkan Prekursor Farmasi tanpa izin dari Menteri Kesehatan.'],

            // UU Psikotropika 5/1997
            'pasal_psikotropika_1' => ['UU Psikotropika', 'Pasal 60 ayat (1) huruf c jo. Pasal 14 ayat (1)', '5', '1997', 'Mengedarkan psikotropika tanpa izin edar resmi atau tidak memenuhi standar mutu dan keamanan.'],
            'pasal_psikotropika_2' => ['UU Psikotropika', 'Pasal 62', '5', '1997', 'Secara tanpa hak memiliki, menyimpan, dan/atau membawa psikotropika golongan tertentu.'],

            // KUHP UU 1/1946
            'pasal_kuhp_1' => ['KUHP', 'Pasal 204 ayat (1)', '1', '1946', 'Menjual, menawarkan, menerimakan atau membagi-bagikan barang yang diketahuinya membahayakan nyawa atau kesehatan orang.'],
            'pasal_kuhp_2' => ['KUHP', 'Pasal 55 ayat (1) ke-1', '1', '1946', 'Turut serta melakukan, menyuruh melakukan, atau membujuk melakukan suatu perbuatan tindak pidana.'],
            'pasal_kuhp_3' => ['KUHP', 'Pasal 56 ke-1', '1', '1946', 'Dengan sengaja memberi bantuan pada waktu kejahatan dilakukan.'],
            'pasal_kuhp_4' => ['KUHP', 'Pasal 204 ayat (2)', '1', '1946', 'Menjual barang berbahaya yang mengakibatkan orang mati atau luka berat.'],
            'pasal_kuhp_5' => ['KUHP', 'Pasal 378', '1', '1946', 'Dengan maksud menguntungkan diri sendiri secara melawan hukum dengan tipu muslihat atau perkataan bohong.'],
        ];

        $pasalList = [];
        foreach ($pasalRaw as $key => [$nama, $pasal, $nomor, $tahun, $isi]) {
            $pasalList[$key] = Pasal::firstOrCreate(
                ['nama_pasal' => $nama, 'pasal' => $pasal],
                [
                    'nomor' => $nomor,
                    'tahun' => $tahun,
                    'isi' => $isi,
                    'status' => 'aktif',
                ]
            );
        }

        // 5. Instansi Hukum Lengkap (Kejati, 13 Kejari, Pengadilan Tinggi, 12 PN)
        $instansiRaw = [
            'kejati' => ['kejaksaan', 'Kejaksaan Tinggi Kalimantan Selatan', 'Kalimantan Selatan'],
            'kejari_bjm' => ['kejaksaan', 'Kejaksaan Negeri Banjarmasin', 'Kota Banjarmasin'],
            'kejari_bjb' => ['kejaksaan', 'Kejaksaan Negeri Banjarbaru', 'Kota Banjarbaru'],
            'kejari_banjar' => ['kejaksaan', 'Kejaksaan Negeri Banjar', 'Kabupaten Banjar'],
            'kejari_batola' => ['kejaksaan', 'Kejaksaan Negeri Barito Kuala', 'Kabupaten Barito Kuala'],
            'kejari_tanahlaut' => ['kejaksaan', 'Kejaksaan Negeri Tanah Laut', 'Kabupaten Tanah Laut'],
            'kejari_tapin' => ['kejaksaan', 'Kejaksaan Negeri Tapin', 'Kabupaten Tapin'],
            'kejari_hst' => ['kejaksaan', 'Kejaksaan Negeri Hulu Sungai Tengah', 'Kabupaten Hulu Sungai Tengah'],
            'kejari_hsu' => ['kejaksaan', 'Kejaksaan Negeri Hulu Sungai Utara', 'Kabupaten Hulu Sungai Utara'],
            'kejari_hss' => ['kejaksaan', 'Kejaksaan Negeri Hulu Sungai Selatan', 'Kabupaten Hulu Sungai Selatan'],
            'kejari_tabalong' => ['kejaksaan', 'Kejaksaan Negeri Tabalong', 'Kabupaten Tabalong'],
            'kejari_tanbu' => ['kejaksaan', 'Kejaksaan Negeri Tanah Bumbu', 'Kabupaten Tanah Bumbu'],
            'kejari_balangan' => ['kejaksaan', 'Kejaksaan Negeri Balangan', 'Kabupaten Balangan'],
            'kejari_kotabaru' => ['kejaksaan', 'Kejaksaan Negeri Kotabaru', 'Kabupaten Kotabaru'],
            'pt_bjm' => ['pengadilan', 'Pengadilan Tinggi Banjarmasin', 'Kalimantan Selatan'],
            'pn_bjm' => ['pengadilan', 'Pengadilan Negeri Banjarmasin', 'Kota Banjarmasin'],
            'pn_bjb' => ['pengadilan', 'Pengadilan Negeri Banjarbaru', 'Kota Banjarbaru'],
            'pn_mtp' => ['pengadilan', 'Pengadilan Negeri Martapura', 'Kabupaten Banjar'],
            'pn_mrh' => ['pengadilan', 'Pengadilan Negeri Marabahan', 'Kabupaten Barito Kuala'],
            'pn_kotabaru' => ['pengadilan', 'Pengadilan Negeri Kotabaru', 'Kabupaten Kotabaru'],
            'pn_pelaihari' => ['pengadilan', 'Pengadilan Negeri Pelaihari', 'Kabupaten Tanah Laut'],
            'pn_rantau' => ['pengadilan', 'Pengadilan Negeri Rantau', 'Kabupaten Tapin'],
            'pn_kandangan' => ['pengadilan', 'Pengadilan Negeri Kandangan', 'Kabupaten Hulu Sungai Selatan'],
            'pn_barabai' => ['pengadilan', 'Pengadilan Negeri Barabai', 'Kabupaten Hulu Sungai Tengah'],
            'pn_amuntai' => ['pengadilan', 'Pengadilan Negeri Amuntai', 'Kabupaten Hulu Sungai Utara'],
            'pn_tanjung' => ['pengadilan', 'Pengadilan Negeri Tanjung', 'Kabupaten Tabalong'],
            'pn_batulicin' => ['pengadilan', 'Pengadilan Negeri Batulicin', 'Kabupaten Tanah Bumbu'],
            'pn_paringin' => ['pengadilan', 'Pengadilan Negeri Paringin', 'Kabupaten Balangan'],
        ];

        $instansiList = [];
        foreach ($instansiRaw as $key => [$jenis, $nama, $wilayah]) {
            $instansiList[$key] = InstansiHukum::firstOrCreate(
                ['jenis' => $jenis, 'nama' => $nama],
                ['wilayah' => $wilayah]
            );
        }

        // 6. Master 45 Jaksa Penuntut Umum Realistis
        $jaksaRaw = [
            ['kejati', 'Ahmad Fauzi, S.H., M.H.', '198405122009121002', 'Jaksa Madya (IV/a)'],
            ['kejati', 'Eko Prasetyo, S.H., M.H.', '198203152007011002', 'Jaksa Utama Pratama (IV/b)'],
            ['kejati', 'Maya Lestari, S.H., M.H.', '198608242010122003', 'Jaksa Muda (III/d)'],
            ['kejati', 'Hendra Pratama, S.H.', '198902112014021001', 'Jaksa Pratama (III/c)'],
            ['kejati', 'Ratna Dewi, S.H.', '199107192015032002', 'Ajun Jaksa Madya (III/a)'],

            ['kejari_bjm', 'Rian Hidayat, S.H.', '198807202012011003', 'Jaksa Pratama (III/c)'],
            ['kejari_bjm', 'Linda Kusuma, S.H.', '199105142015022001', 'Jaksa Pratama (III/c)'],
            ['kejari_bjm', 'Fajar Nugroho, S.H., M.H.', '198504102009121001', 'Jaksa Muda (III/d)'],
            ['kejari_bjm', 'Siti Aminah, S.H.', '199309082016022003', 'Ajun Jaksa Madya (III/a)'],

            ['kejari_bjb', 'Nurul Hikmah, S.H., M.H.', '198611142010122001', 'Jaksa Muda (III/d)'],
            ['kejari_bjb', 'Bambang Sutrisno, S.H.', '198703212011011003', 'Jaksa Muda (III/d)'],
            ['kejari_bjb', 'Aditya Wardana, S.H.', '199008122014031002', 'Jaksa Pratama (III/c)'],
            ['kejari_bjb', 'Yuni Kartika, S.H.', '199211052016012001', 'Jaksa Pratama (III/c)'],

            ['kejari_banjar', 'Siti Maryam, S.H.', '198904122014022003', 'Jaksa Pratama (III/c)'],
            ['kejari_banjar', 'Dewi Anggraeni, S.H.', '199302182017012002', 'Ajun Jaksa Madya (III/a)'],
            ['kejari_banjar', 'Wahyudi Rahman, S.H., M.H.', '198412032008121004', 'Jaksa Madya (IV/a)'],
            ['kejari_banjar', 'Fitri Handayani, S.H.', '199006202015032004', 'Jaksa Pratama (III/c)'],

            ['kejari_batola', 'Diana Lestari, S.H.', '199003182015032002', 'Jaksa Pratama (III/c)'],
            ['kejari_batola', 'M. Iqbal Maulana, S.H.', '198801152013021001', 'Jaksa Pratama (III/c)'],
            ['kejari_batola', 'Sri Mulyani, S.H., M.H.', '198509272010012002', 'Jaksa Muda (III/d)'],

            ['kejari_tanahlaut', 'Hendra Saputra, S.H., M.H.', '198502152010011004', 'Jaksa Muda (III/d)'],
            ['kejari_tanahlaut', 'Ilham Maulana, S.H.', '198806122013021001', 'Jaksa Pratama (III/c)'],
            ['kejari_tanahlaut', 'Ratih Puspita, S.H.', '199204092016022001', 'Ajun Jaksa Madya (III/a)'],

            ['kejari_tapin', 'Wahyu Wibowo, S.H.', '198709102011011002', 'Jaksa Pratama (III/c)'],
            ['kejari_tapin', 'Anisa Triana, S.H.', '199108142015032003', 'Jaksa Pratama (III/c)'],
            ['kejari_tapin', 'Dodi Ferdiansyah, S.H., M.H.', '198311222008011002', 'Jaksa Muda (III/d)'],

            ['kejari_hst', 'M. Reza Pahlevi, S.H., M.H.', '198304192008121001', 'Jaksa Madya (IV/a)'],
            ['kejari_hst', 'Danu Syaifullah, S.H.', '198905262014021003', 'Jaksa Pratama (III/c)'],
            ['kejari_hst', 'Mega Utami, S.H.', '199207302017012004', 'Ajun Jaksa Madya (III/a)'],

            ['kejari_hsu', 'Anisa Rahmawati, S.H.', '199106252015032004', 'Jaksa Pratama (III/c)'],
            ['kejari_hsu', 'Rahmat Hidayat, S.H., M.H.', '198602182010121002', 'Jaksa Muda (III/d)'],
            ['kejari_hsu', 'Tri Wahyuningsih, S.H.', '199010122014032002', 'Jaksa Pratama (III/c)'],

            ['kejari_hss', 'Dedi Kurniawan, S.H.', '198812032014031001', 'Jaksa Pratama (III/c)'],
            ['kejari_hss', 'Cindy Permata, S.H.', '199303152017012001', 'Ajun Jaksa Madya (III/a)'],
            ['kejari_hss', 'Bima Sakti, S.H., M.H.', '198407072009011003', 'Jaksa Muda (III/d)'],

            ['kejari_tabalong', 'Faisal Akbar, S.H., M.H.', '198411082009121003', 'Jaksa Muda (III/d)'],
            ['kejari_tabalong', 'Nina Marlina, S.H.', '198909192014022002', 'Jaksa Pratama (III/c)'],
            ['kejari_tabalong', 'Johan Syahputra, S.H.', '199201282016021001', 'Jaksa Pratama (III/c)'],

            ['kejari_tanbu', 'Tri Wahyuni, S.H.', '198908172014022005', 'Jaksa Pratama (III/c)'],
            ['kejari_tanbu', 'Rizky Ramadhan, S.H.', '199012052015031002', 'Jaksa Pratama (III/c)'],
            ['kejari_tanbu', 'Farah Nabila, S.H., M.H.', '198606142010122004', 'Jaksa Muda (III/d)'],

            ['kejari_balangan', 'Bagus Prasetyo, S.H.', '199201102016021002', 'Jaksa Pratama (III/c)'],
            ['kejari_balangan', 'Ayu Wulandari, S.H.', '199404222018012003', 'Ajun Jaksa Madya (III/a)'],

            ['kejari_kotabaru', 'Arif Rahman, S.H., M.H.', '198510202010121002', 'Jaksa Muda (III/d)'],
            ['kejari_kotabaru', 'Desi Natalia, S.H.', '199103112015022003', 'Jaksa Pratama (III/c)'],
        ];

        $jaksaList = [];
        foreach ($jaksaRaw as $idx => [$instansiKey, $nama, $nip, $pangkat]) {
            $jaksaList['jaksa_'.($idx + 1)] = JaksaPenuntut::firstOrCreate(
                ['instansi_hukum_id' => $instansiList[$instansiKey]->id, 'nama_jaksa' => $nama],
                ['nip' => $nip, 'pangkat' => $pangkat]
            );
        }

        return array_merge(
            $jabatanList,
            $bidangList,
            $panggolList,
            $pasalList,
            $instansiList,
            $jaksaList
        );
    }

    /**
     * 20 Pengguna Penyidik PPNS realistis untuk variasi tim penyidik perkara.
     *
     * @param  array<string, mixed>  $master
     * @return array<string, User>
     */
    private function seedUsers(array $master): array
    {
        $alamat = Alamat::firstOrCreate(
            [
                'provinsi_kode' => '63',
                'kabupaten_kode' => '6371',
                'kecamatan_kode' => '637104',
                'desa_kode' => '6371041006',
                'alamat_detail' => 'Komplek Perumahan BBPOM Banjarmasin No. 12',
            ],
            [
                'provinsi_nama' => 'Kalimantan Selatan',
                'kabupaten_nama' => 'Kota Banjarmasin',
                'kecamatan_nama' => 'Banjarmasin Utara',
                'desa_nama' => 'Pangeran',
                'rt' => '002',
                'rw' => '001',
            ]
        );

        $penyidikDefinitions = [
            ['email' => 'penyidik@baptwo.test', 'nama' => 'Penyidik PPNS', 'nip' => '199203032018011003', 'jabatan' => 'ppns', 'bidang' => 'penindakan', 'panggol' => 'iii_d'],
            ['email' => 'budi.ppns@baptwo.test', 'nama' => 'Budi Hermawan, S.Farm., Apt.', 'nip' => '198906152014031002', 'jabatan' => 'ppns', 'bidang' => 'penindakan', 'panggol' => 'iii_d'],
            ['email' => 'siti.ppns@baptwo.test', 'nama' => 'Siti Rahmah, S.H.', 'nip' => '199308222019022005', 'jabatan' => 'analis', 'bidang' => 'penindakan', 'panggol' => 'iii_c'],
            ['email' => 'dewi.ppns@baptwo.test', 'nama' => 'Dewi Sartika, S.Si., Apt.', 'nip' => '199104102016012004', 'jabatan' => 'pfm_muda', 'bidang' => 'pemeriksaan', 'panggol' => 'iii_d'],
            ['email' => 'hendra.ppns@baptwo.test', 'nama' => 'Hendra Wijaya, S.Farm., Apt.', 'nip' => '198705142011011002', 'jabatan' => 'pfm_madya', 'bidang' => 'penindakan', 'panggol' => 'iv_a'],
            ['email' => 'rina.ppns@baptwo.test', 'nama' => 'Rina Anggraeni, S.H.', 'nip' => '199402172019012002', 'jabatan' => 'ppns', 'bidang' => 'penindakan', 'panggol' => 'iii_b'],
            ['email' => 'fauzan.ppns@baptwo.test', 'nama' => 'Ahmad Fauzan, S.Si.', 'nip' => '199011032015031001', 'jabatan' => 'pfm_pertama', 'bidang' => 'pengujian', 'panggol' => 'iii_c'],
            ['email' => 'maya.ppns@baptwo.test', 'nama' => 'Maya Kusuma, S.Farm., Apt.', 'nip' => '198809252012012003', 'jabatan' => 'pfm_muda', 'bidang' => 'penindakan', 'panggol' => 'iii_d'],
            ['email' => 'arif.ppns@baptwo.test', 'nama' => 'Arif Wibowo, S.H., M.H.', 'nip' => '198501192009021001', 'jabatan' => 'ppns', 'bidang' => 'penindakan', 'panggol' => 'iv_a'],
            ['email' => 'nurul.ppns@baptwo.test', 'nama' => 'Nurul Hidayati, S.Si., Apt.', 'nip' => '199207082017012004', 'jabatan' => 'pfm_pertama', 'bidang' => 'pemeriksaan', 'panggol' => 'iii_c'],
            ['email' => 'rizky.ppns@baptwo.test', 'nama' => 'Rizky Maulana, S.Farm.', 'nip' => '199503122020011002', 'jabatan' => 'ppns', 'bidang' => 'penindakan', 'panggol' => 'iii_a'],
            ['email' => 'fitriani.ppns@baptwo.test', 'nama' => 'Fitriani, S.H.', 'nip' => '199312042018022001', 'jabatan' => 'analis', 'bidang' => 'penindakan', 'panggol' => 'iii_b'],
            ['email' => 'johan.ppns@baptwo.test', 'nama' => 'Johan Prasetyo, S.Si.', 'nip' => '198904182014021003', 'jabatan' => 'pfm_muda', 'bidang' => 'pengujian', 'panggol' => 'iii_d'],
            ['email' => 'anisa.ppns@baptwo.test', 'nama' => 'Anisa Pratiwi, S.Farm., Apt.', 'nip' => '199109092016022002', 'jabatan' => 'pfm_pertama', 'bidang' => 'penindakan', 'panggol' => 'iii_c'],
            ['email' => 'dedi.ppns@baptwo.test', 'nama' => 'Dedi Kurniawan, S.H.', 'nip' => '198610152010121002', 'jabatan' => 'ppns', 'bidang' => 'penindakan', 'panggol' => 'iii_d'],
            ['email' => 'ratna.ppns@baptwo.test', 'nama' => 'Ratna Sari, S.Si.', 'nip' => '199406212019012003', 'jabatan' => 'pfm_pertama', 'bidang' => 'pemeriksaan', 'panggol' => 'iii_b'],
            ['email' => 'wahyu.ppns@baptwo.test', 'nama' => 'Wahyu Santosa, S.Farm., Apt.', 'nip' => '198712102011021001', 'jabatan' => 'pfm_madya', 'bidang' => 'penindakan', 'panggol' => 'iv_a'],
            ['email' => 'desi.ppns@baptwo.test', 'nama' => 'Desi Rahmawati, S.H.', 'nip' => '199208302017012002', 'jabatan' => 'analis', 'bidang' => 'penindakan', 'panggol' => 'iii_c'],
            ['email' => 'faisal.ppns@baptwo.test', 'nama' => 'Faisal Rahman, S.Si., Apt.', 'nip' => '199003272015021001', 'jabatan' => 'ppns', 'bidang' => 'penindakan', 'panggol' => 'iii_c'],
            ['email' => 'tri.ppns@baptwo.test', 'nama' => 'Tri Handayani, S.Farm.', 'nip' => '199605152021012001', 'jabatan' => 'pfm_pertama', 'bidang' => 'penindakan', 'panggol' => 'iii_a'],
        ];

        $users = [];
        foreach ($penyidikDefinitions as $idx => $def) {
            $users['penyidik_'.($idx + 1)] = User::updateOrCreate(
                ['email' => $def['email']],
                [
                    'nama' => $def['nama'],
                    'nip' => $def['nip'],
                    'role' => UserRole::PENYIDIK,
                    'jabatan_id' => $master[$def['jabatan']]->id,
                    'bidang_id' => $master[$def['bidang']]->id,
                    'panggol_id' => $master[$def['panggol']]->id,
                    'alamat_id' => $alamat->id,
                    'password' => Hash::make('password'),
                    'status' => 'pns',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        return $users;
    }

    /**
     * Menanam 500 perkara dari pra-SPDP sampai putusan pengadilan periode 2025-2026.
     *
     * @param  array<string, mixed>  $master
     * @param  array<string, User>  $users
     */
    private function seedPerkaraCases(array $master, array $users): void
    {
        $actor = $users['penyidik_1'];
        $lokasiList = $this->lokasiCatalog();
        $archetypeList = $this->archetypeCatalog();
        $rencana = $this->susunRencana();

        $penyidik = array_values($users);

        DB::disableQueryLog();
        DB::beginTransaction();

        try {
            foreach ($rencana as $urutan => $baris) {
                $archetype = $archetypeList[$baris['archetype']];
                $lokasi = $lokasiList[$baris['lokasi']];
                $bucket = $baris['bucket'];

                $tanggalLk = $this->hitungTanggalLk($baris['tahun'], $baris['idx_tahun'], $baris['total_tahun']);
                $adaSprindik = ! in_array($bucket, ['lk_draft', 'lk_final'], true);
                $tanggalSprindik = $adaSprindik ? $tanggalLk->copy()->addDays(2) : null;
                $tanggalSpdp = $adaSprindik ? $tanggalSprindik->copy()->addDays(3) : null;

                // Khusus berkas berjalan tahun 2026 dengan SPDP, terapkan profil SLA agar variasi tenggang waktu realistis
                if ($baris['tahun'] === 2026 && ! empty($baris['sla_profile']) && $tanggalSpdp !== null) {
                    $anchorDate = Carbon::create(2026, 10, 28)->startOfDay();
                    if ($baris['sla_profile'] === 'dalam') {
                        // SPDP terbit 5 s.d. 19 hari yang lalu -> batas waktu masih > 7 hari ke depan
                        $hariLalu = 5 + ($urutan % 15);
                        $tanggalSpdp = $anchorDate->copy()->subDays($hariLalu);
                        $tanggalSprindik = $tanggalSpdp->copy()->subDays(3);
                        $tanggalLk = $tanggalSprindik->copy()->subDays(2);
                    } elseif ($baris['sla_profile'] === 'mendekati') {
                        // SPDP terbit 24 s.d. 28 hari yang lalu -> batas waktu tinggal 2 s.d. 6 hari ke depan (<= 7 hari)
                        $hariLalu = 24 + ($urutan % 5);
                        $tanggalSpdp = $anchorDate->copy()->subDays($hariLalu);
                        $tanggalSprindik = $tanggalSpdp->copy()->subDays(3);
                        $tanggalLk = $tanggalSprindik->copy()->subDays(2);
                    }
                }

                $detailTkp = $this->susunDetailTkp($archetype, $lokasi, $urutan);
                $uraian = str_replace('{kabupaten}', $lokasi['kab'][1], $archetype['uraian']);

                $adaBerkas = ! in_array($bucket, ['lk_draft', 'lk_final', 'sprindik_draft', 'sprindik_final'], true);

                $tersangkaList = [];
                if ($adaBerkas) {
                    $pakaiPasalBerlapis = ($urutan % 3 === 0);
                    $tersangkaList[] = $this->susunTersangka($master, $lokasi, $archetype, $urutan, $pakaiPasalBerlapis);
                }

                $tahapan = $tanggalSpdp ? $this->susunTahapan($bucket, $urutan, $tanggalSpdp, $archetype, $lokasi) : [];
                $dpo = $baris['dpo'] ? $this->susunDpo($baris['dpo'], $tanggalSpdp, $urutan) : null;
                $sp3 = $bucket === 'sp3' ? $this->susunSp3($tanggalSpdp ? $tanggalSpdp->copy()->addDays(18) : $tanggalLk->copy()->addDays(12), $urutan) : null;

                $ppnsUtama = $penyidik[$urutan % count($penyidik)];
                $ppnsAnggota = [];
                if ($urutan % 4 !== 0) {
                    $ppnsAnggota[] = $penyidik[($urutan + 1) % count($penyidik)];
                }
                if ($urutan % 6 === 0) {
                    $ppnsAnggota[] = $penyidik[($urutan + 2) % count($penyidik)];
                }

                $this->createCase(
                    actor: $actor,
                    noLk: $this->nomorLk($tanggalLk),
                    tglLk: $tanggalLk->toDateString(),
                    uraian: $uraian,
                    noSp: $adaSprindik ? $this->nomorSprindik($tanggalSprindik) : null,
                    tglSp: $tanggalSprindik?->toDateString(),
                    statusPerkara: $adaBerkas ? 'final' : null,
                    lokasi: $lokasi,
                    tkpData: [
                        'prov' => $lokasi['prov'],
                        'kab' => $lokasi['kab'],
                        'kec' => $lokasi['kec'],
                        'kel' => $lokasi['kel'],
                        'detail' => $detailTkp,
                        'klasifikasi' => $archetype['klasifikasi'],
                        'legalitas' => $archetype['legalitas'],
                        'modus' => (($urutan * 37) % 100 < 32) ? 'online' : 'offline',
                    ],
                    ppnsUtama: $ppnsUtama,
                    ppnsAnggota: $ppnsAnggota,
                    tersangkaList: $tersangkaList,
                    tahapanChain: $tahapan,
                    dpoData: $dpo,
                    sp3Data: $sp3,
                    statusFormalOverride: $baris['tidak_selesai'] ? 'tidak_selesai' : null,
                    statusLaporan: $bucket === 'lk_draft' ? 'draft' : 'final',
                    statusSprindik: $adaSprindik ? ($bucket === 'sprindik_draft' ? 'draft' : 'final') : null,
                );

                // Commit transaksi setiap 50 perkara untuk menjaga stabilitas memori
                if (($urutan + 1) % 50 === 0) {
                    DB::commit();
                    DB::beginTransaction();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hitung tanggal LK dengan pola acak dinamis fluktuatif bulanan (bukan garis lurus/rata).
     */
    private function hitungTanggalLk(int $tahun, int $idxTahun, int $totalTahun): Carbon
    {
        // Pola tren bulanan naik-turun dinamis:
        // 2026 (350 perkara across Jan-Okt): 32, 22, 46, 26, 40, 24, 44, 36, 52, 28
        // 2025 (150 perkara across Jan-Des): 14, 8, 18, 10, 16, 7, 17, 12, 20, 11, 13, 4
        $distribusiBulan = ($tahun === 2026)
            ? [1 => 32, 2 => 22, 3 => 46, 4 => 26, 5 => 40, 6 => 24, 7 => 44, 8 => 36, 9 => 52, 10 => 28]
            : [1 => 14, 2 => 8, 3 => 18, 4 => 10, 5 => 16, 6 => 7, 7 => 17, 8 => 12, 9 => 20, 10 => 11, 11 => 13, 12 => 4];

        $bulan = 1;
        $sisaIndex = $idxTahun;
        foreach ($distribusiBulan as $m => $jumlah) {
            if ($sisaIndex < $jumlah) {
                $bulan = $m;
                break;
            }
            $sisaIndex -= $jumlah;
        }

        $totalDiBulan = $distribusiBulan[$bulan] ?? 1;
        $maxHariBulan = ($tahun === 2026 && $bulan === 10) ? 26 : 28;
        $hari = 1 + (int) round(($sisaIndex / max(1, $totalDiBulan - 1)) * ($maxHariBulan - 1));
        $hari = min(max($hari, 1), $maxHariBulan);

        return Carbon::create($tahun, $bulan, $hari)->startOfDay();
    }

    /**
     * Rencana tepat 500 perkara (2025-2026): komposisi tahapan, rentang 2 tahun, SLA, DPO, SP3, dan murni 1 tersangka.
     *
     * @return array<int, array<string, mixed>>
     */
    private function susunRencana(): array
    {
        $archetypeKeys = array_keys($this->archetypeCatalog());

        // 1. Tahun 2026 (Tahun Berjalan): 350 perkara
        // - ~55% Berjalan (192 perkara): 100 Pra-SPDP, 57 SPDP, 25 Tahap I, 10 P-21
        // - ~40% Selesai (140 perkara): 40 Tahap II, 100 Keputusan Pengadilan
        // - ~5% SP3 (18 perkara)
        $distribusi2026 = [
            // Pra-SPDP (100)
            'lk_draft' => 15,
            'lk_final' => 25,
            'sprindik_draft' => 15,
            'sprindik_final' => 25,
            'perkara_only' => 20,
            // SPDP & Subtahap (57)
            'spdp_draft' => 12,
            'spdp' => 20,
            'spdp_p16' => 15,
            'spdp_p16_p17p' => 10,
            // Tahap 1 (25)
            'tahap1_draft' => 8,
            'tahap1' => 10,
            'tahap1_p18p_p19' => 7,
            // P-21 (10)
            'p21_draft' => 4,
            'p21' => 6,
            // Selesai (140)
            'tahap2' => 40,
            'keputusan' => 100,
            // SP3 (18)
            'sp3' => 18,
        ];

        // 2. Tahun 2025 (Arsip Tahun Lalu): 150 perkara
        // - 0 Perkara Berjalan (sesuai aturan batas tahun mutlak BPOM)
        // - 115 perkara Selesai (30 Tahap II, 85 Keputusan Pengadilan)
        // - 10 perkara SP3
        // - 25 perkara Tidak Selesai (lewat batas tahun 31 Desember 2025: 8 SPDP, 10 Tahap I, 7 P-21)
        $distribusi2025 = [
            'tahap2' => 30,
            'keputusan' => 85,
            'sp3' => 10,
            'spdp' => 8,
            'tahap1' => 10,
            'p21' => 7,
        ];

        $rencana2026 = [];
        $nomor2026 = 0;
        foreach ($distribusi2026 as $bucket => $jumlah) {
            for ($i = 0; $i < $jumlah; $i++) {
                $rencana2026[] = [
                    'bucket' => $bucket,
                    'idx_bucket' => $i,
                    'archetype' => $archetypeKeys[$nomor2026 % count($archetypeKeys)],
                    'lokasi' => $this->getWeightedLokasiKey($nomor2026),
                    'tahun' => 2026,
                    'idx_tahun' => $nomor2026,
                    'total_tahun' => 350,
                    'tidak_selesai' => false,
                    'jumlah_tersangka' => 1,
                    'dpo' => null,
                    'sla_profile' => null,
                ];
                $nomor2026++;
            }
        }

        // Variasi SLA untuk 92 berkas berjalan dengan SPDP di 2026 (57 SPDP, 25 Tahap 1, 10 P-21):
        // 45 berkas dalam SLA (> 7 hari), 15 berkas mendekati SLA (<= 7 hari), 32 berkas lewat SLA
        $runningSpdpIndices2026 = [];
        foreach ($rencana2026 as $pos => $b) {
            if (in_array($b['bucket'], [
                'spdp_draft', 'spdp', 'spdp_p16', 'spdp_p16_p17p',
                'tahap1_draft', 'tahap1', 'tahap1_p18p_p19',
                'p21_draft', 'p21',
            ], true)) {
                $runningSpdpIndices2026[] = $pos;
            }
        }

        foreach ($runningSpdpIndices2026 as $seq => $pos) {
            if ($seq < 45) {
                $rencana2026[$pos]['sla_profile'] = 'dalam';
            } elseif ($seq < 60) {
                $rencana2026[$pos]['sla_profile'] = 'mendekati';
            } else {
                $rencana2026[$pos]['sla_profile'] = 'lewat';
            }
        }

        // DPO aktif (8) dan DPO cabut (4) pada berkas berjalan tahun 2026
        foreach ($runningSpdpIndices2026 as $seq => $pos) {
            if ($seq < 8) {
                $rencana2026[$pos]['dpo'] = 'aktif';
            } elseif ($seq >= 8 && $seq < 12) {
                $rencana2026[$pos]['dpo'] = 'cabut';
            }
        }

        $rencana2025 = [];
        $nomor2025 = 0;
        foreach ($distribusi2025 as $bucket => $jumlah) {
            $isExpiredTidakSelesai = in_array($bucket, ['spdp', 'tahap1', 'p21'], true);
            for ($i = 0; $i < $jumlah; $i++) {
                $globalIdx = 350 + $nomor2025;
                $rencana2025[] = [
                    'bucket' => $bucket,
                    'idx_bucket' => $i,
                    'archetype' => $archetypeKeys[$globalIdx % count($archetypeKeys)],
                    'lokasi' => $this->getWeightedLokasiKey($globalIdx),
                    'tahun' => 2025,
                    'idx_tahun' => $nomor2025,
                    'total_tahun' => 150,
                    'tidak_selesai' => $isExpiredTidakSelesai,
                    'jumlah_tersangka' => 1,
                    'dpo' => null,
                    'sla_profile' => null,
                ];
                $nomor2025++;
            }
        }

        return array_merge($rencana2025, $rencana2026);
    }

    /**
     * Mengembalikan key lokasi dari katalog dengan pembobotan sebaran wilayah Kalsel realistis:
     * - Kota Banjarmasin: ~35%
     * - Kota Banjarbaru: ~22%
     * - Kabupaten Banjar: ~15%
     * - Kabupaten Tanah Laut: ~10%
     * - Kabupaten Barito Kuala: ~7%
     * - 8 Kabupaten lainnya: ~11% (Tapin, HSS, HST, HSU, Tabalong, Balangan, Kotabaru, Tanbu)
     */
    private function getWeightedLokasiKey(int $seed): string
    {
        static $slots = null;
        if ($slots === null) {
            $slots = [];
            // Kota Banjarmasin (35 slots: bjm, bjm_utara)
            for ($i = 0; $i < 35; $i++) {
                $slots[] = ($i % 2 === 0) ? 'bjm' : 'bjm_utara';
            }
            // Kota Banjarbaru (22 slots: bjb, bjb_ulin)
            for ($i = 0; $i < 22; $i++) {
                $slots[] = ($i % 2 === 0) ? 'bjb' : 'bjb_ulin';
            }
            // Kabupaten Banjar (15 slots: banjar, banjar_kh)
            for ($i = 0; $i < 15; $i++) {
                $slots[] = ($i % 2 === 0) ? 'banjar' : 'banjar_kh';
            }
            // Kabupaten Tanah Laut (10 slots: tanahlaut)
            for ($i = 0; $i < 10; $i++) {
                $slots[] = 'tanahlaut';
            }
            // Kabupaten Barito Kuala (7 slots: batola, batola_alalak)
            for ($i = 0; $i < 7; $i++) {
                $slots[] = ($i % 2 === 0) ? 'batola' : 'batola_alalak';
            }
            // 8 Kabupaten lainnya (11 slots total)
            $slots[] = 'tapin';
            $slots[] = 'tapin';
            $slots[] = 'hss';
            $slots[] = 'hss';
            $slots[] = 'hst';
            $slots[] = 'hsu';
            $slots[] = 'tabalong';
            $slots[] = 'tabalong_tanjung';
            $slots[] = 'balangan';
            $slots[] = 'kotabaru';
            $slots[] = 'tanbu';
        }

        return $slots[$seed % count($slots)];
    }

    /**
     * Katalog 13 kabupaten/kota Kalimantan Selatan beserta kejaksaan dan pengadilan negerinya.
     *
     * @return array<string, array<string, mixed>>
     */
    private function lokasiCatalog(): array
    {
        return [
            'bjm' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6371', 'Kota Banjarmasin'],
                'kec' => ['637103', 'Banjarmasin Tengah'],
                'kel' => ['6371031001', 'Kertak Baru Ilir'],
                'kejari' => 'Kejaksaan Negeri Banjarmasin',
                'kode_jaksa' => '10',
                'pn' => 'Pengadilan Negeri Banjarmasin',
                'kode_pn' => 'Bjm',
            ],
            'bjm_utara' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6371', 'Kota Banjarmasin'],
                'kec' => ['637104', 'Banjarmasin Utara'],
                'kel' => ['6371041002', 'Alalak Utara'],
                'kejari' => 'Kejaksaan Negeri Banjarmasin',
                'kode_jaksa' => '10',
                'pn' => 'Pengadilan Negeri Banjarmasin',
                'kode_pn' => 'Bjm',
            ],
            'bjb' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6372', 'Kota Banjarbaru'],
                'kec' => ['637202', 'Banjarbaru Utara'],
                'kel' => ['6372021003', 'Mentaos'],
                'kejari' => 'Kejaksaan Negeri Banjarbaru',
                'kode_jaksa' => '11',
                'pn' => 'Pengadilan Negeri Banjarbaru',
                'kode_pn' => 'Bjb',
            ],
            'bjb_ulin' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6372', 'Kota Banjarbaru'],
                'kec' => ['637201', 'Landasan Ulin'],
                'kel' => ['6372011003', 'Guntung Payung'],
                'kejari' => 'Kejaksaan Negeri Banjarbaru',
                'kode_jaksa' => '11',
                'pn' => 'Pengadilan Negeri Banjarbaru',
                'kode_pn' => 'Bjb',
            ],
            'banjar' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6303', 'Kabupaten Banjar'],
                'kec' => ['630303', 'Martapura'],
                'kel' => ['6303031002', 'Jawa'],
                'kejari' => 'Kejaksaan Negeri Banjar',
                'kode_jaksa' => '13',
                'pn' => 'Pengadilan Negeri Martapura',
                'kode_pn' => 'Mtp',
            ],
            'banjar_kh' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6303', 'Kabupaten Banjar'],
                'kec' => ['630302', 'Kertak Hanyar'],
                'kel' => ['6303022003', 'Manarap Baru'],
                'kejari' => 'Kejaksaan Negeri Banjar',
                'kode_jaksa' => '13',
                'pn' => 'Pengadilan Negeri Martapura',
                'kode_pn' => 'Mtp',
            ],
            'batola' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6304', 'Kabupaten Barito Kuala'],
                'kec' => ['630403', 'Marabahan'],
                'kel' => ['6304031001', 'Marabahan Kota'],
                'kejari' => 'Kejaksaan Negeri Barito Kuala',
                'kode_jaksa' => '19',
                'pn' => 'Pengadilan Negeri Marabahan',
                'kode_pn' => 'Mrh',
            ],
            'batola_alalak' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6304', 'Kabupaten Barito Kuala'],
                'kec' => ['630401', 'Alalak'],
                'kel' => ['6304011002', 'Handil Bakti'],
                'kejari' => 'Kejaksaan Negeri Barito Kuala',
                'kode_jaksa' => '19',
                'pn' => 'Pengadilan Negeri Marabahan',
                'kode_pn' => 'Mrh',
            ],
            'tanahlaut' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6301', 'Kabupaten Tanah Laut'],
                'kec' => ['630104', 'Pelaihari'],
                'kel' => ['6301041002', 'Angsau'],
                'kejari' => 'Kejaksaan Negeri Tanah Laut',
                'kode_jaksa' => '15',
                'pn' => 'Pengadilan Negeri Pelaihari',
                'kode_pn' => 'Plh',
            ],
            'tapin' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6305', 'Kabupaten Tapin'],
                'kec' => ['630504', 'Tapin Utara'],
                'kel' => ['6305041001', 'Rantau Kiwa'],
                'kejari' => 'Kejaksaan Negeri Tapin',
                'kode_jaksa' => '16',
                'pn' => 'Pengadilan Negeri Rantau',
                'kode_pn' => 'Rnt',
            ],
            'hst' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6307', 'Kabupaten Hulu Sungai Tengah'],
                'kec' => ['630706', 'Barabai'],
                'kel' => ['6307061002', 'Barabai Barat'],
                'kejari' => 'Kejaksaan Negeri Hulu Sungai Tengah',
                'kode_jaksa' => '14',
                'pn' => 'Pengadilan Negeri Barabai',
                'kode_pn' => 'Brb',
            ],
            'hsu' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6308', 'Kabupaten Hulu Sungai Utara'],
                'kec' => ['630804', 'Amuntai Tengah'],
                'kel' => ['6308041003', 'Kebun Sari'],
                'kejari' => 'Kejaksaan Negeri Hulu Sungai Utara',
                'kode_jaksa' => '16',
                'pn' => 'Pengadilan Negeri Amuntai',
                'kode_pn' => 'Amt',
            ],
            'hss' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6306', 'Kabupaten Hulu Sungai Selatan'],
                'kec' => ['630604', 'Kandangan'],
                'kel' => ['6306041001', 'Kandangan Kota'],
                'kejari' => 'Kejaksaan Negeri Hulu Sungai Selatan',
                'kode_jaksa' => '17',
                'pn' => 'Pengadilan Negeri Kandangan',
                'kode_pn' => 'Kdg',
            ],
            'tabalong' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6309', 'Kabupaten Tabalong'],
                'kec' => ['630904', 'Murung Pudak'],
                'kel' => ['6309041002', 'Belimbing'],
                'kejari' => 'Kejaksaan Negeri Tabalong',
                'kode_jaksa' => '17',
                'pn' => 'Pengadilan Negeri Tanjung',
                'kode_pn' => 'Tjg',
            ],
            'tabalong_tanjung' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6309', 'Kabupaten Tabalong'],
                'kec' => ['630903', 'Tanjung'],
                'kel' => ['6309031002', 'Tanjung'],
                'kejari' => 'Kejaksaan Negeri Tabalong',
                'kode_jaksa' => '17',
                'pn' => 'Pengadilan Negeri Tanjung',
                'kode_pn' => 'Tjg',
            ],
            'tanbu' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6310', 'Kabupaten Tanah Bumbu'],
                'kec' => ['631001', 'Batulicin'],
                'kel' => ['6310011001', 'Batulicin'],
                'kejari' => 'Kejaksaan Negeri Tanah Bumbu',
                'kode_jaksa' => '21',
                'pn' => 'Pengadilan Negeri Batulicin',
                'kode_pn' => 'Btl',
            ],
            'balangan' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6311', 'Kabupaten Balangan'],
                'kec' => ['631102', 'Paringin'],
                'kel' => ['6311021001', 'Batu Piring'],
                'kejari' => 'Kejaksaan Negeri Balangan',
                'kode_jaksa' => '18',
                'pn' => 'Pengadilan Negeri Paringin',
                'kode_pn' => 'Prg',
            ],
            'kotabaru' => [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6302', 'Kabupaten Kotabaru'],
                'kec' => ['630209', 'Pulau Laut Utara'],
                'kel' => ['6302091002', 'Kotabaru Hilir'],
                'kejari' => 'Kejaksaan Negeri Kotabaru',
                'kode_jaksa' => '12',
                'pn' => 'Pengadilan Negeri Kotabaru',
                'kode_pn' => 'Ktb',
            ],
        ];
    }

    /**
     * Katalog jenis perkara beserta narasi, pasal, dan barang bukti khasnya.
     *
     * @return array<string, array<string, mixed>>
     */
    private function archetypeCatalog(): array
    {
        return [
            'obat_keras_g' => [
                'tempat' => ['Kios Obat', 'Toko Obat', 'Rumah Kontrakan'],
                'obyek' => 'obat keras daftar G jenis tramadol dan trihexyphenidyl',
                'uraian' => 'Peredaran obat keras daftar G jenis tramadol dan trihexyphenidyl tanpa izin apotek yang dijual bebas di {kabupaten}.',
                'barang_bukti' => '1.240 butir obat keras daftar G',
                'pasal' => ['pasal_kesehatan_1', 'pasal_kesehatan_6'],
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Pedagang Obat',
            ],
            'kosmetik_merkuri' => [
                'tempat' => ['Kios Kosmetik', 'Klinik Kecantikan', 'Toko Kosmetik'],
                'obyek' => 'kosmetik krim pemutih wajah berbahan merkuri',
                'uraian' => 'Dugaan peredaran kosmetik krim pemutih wajah berbahan merkuri tanpa nomor notifikasi BPOM di {kabupaten}.',
                'barang_bukti' => '312 pot krim pemutih bermerkuri',
                'pasal' => ['pasal_kesehatan_1', 'pasal_kesehatan_5'],
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Pemilik Klinik Kecantikan',
            ],
            'kosmetik_hidrokuinon' => [
                'tempat' => ['Gudang Kosmetik', 'Kios Kosmetik'],
                'obyek' => 'kosmetik pemutih mengandung hidrokuinon dan tretinoin dosis tinggi',
                'uraian' => 'Penyelundupan kosmetik pemutih mengandung hidrokuinon dan tretinoin dosis tinggi untuk diperdagangkan di {kabupaten}.',
                'barang_bukti' => '85 karton kosmetik tanpa izin edar',
                'pasal' => ['pasal_kesehatan_1', 'pasal_kesehatan_5'],
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Pemilik Toko Kosmetik',
            ],
            'pangan_izin' => [
                'tempat' => ['Gudang Sembako', 'Toko Kelontong'],
                'obyek' => 'pangan olahan kemasan tanpa izin edar BPOM',
                'uraian' => 'Peredaran pangan olahan kemasan tanpa izin edar BPOM yang dipasok ke pedagang eceran di {kabupaten}.',
                'barang_bukti' => '240 dus pangan olahan tanpa izin edar',
                'pasal' => ['pasal_pangan_1', 'pasal_konsumen_3'],
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Distributor Pangan',
            ],
            'pangan_kedaluwarsa' => [
                'tempat' => ['Gudang Sembako', 'Rumah Produksi Pangan'],
                'obyek' => 'pangan olahan kedaluwarsa yang dikemas ulang',
                'uraian' => 'Pengemasan ulang pangan olahan kedaluwarsa dengan label tanggal kedaluwarsa baru di {kabupaten}.',
                'barang_bukti' => '180 dus pangan kedaluwarsa hasil kemas ulang',
                'pasal' => ['pasal_pangan_2', 'pasal_konsumen_3'],
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Pemilik Gudang Sembako',
            ],
            'pangan_tercemar' => [
                'tempat' => ['Gudang Pangan', 'Pasar Tradisional'],
                'obyek' => 'pangan olahan tercemar dan tidak layak konsumsi',
                'uraian' => 'Peredaran pangan olahan tercemar dan berbau tidak layak konsumsi di {kabupaten}.',
                'barang_bukti' => '96 dus pangan tercemar',
                'pasal' => ['pasal_pangan_3'],
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Pedagang Pangan',
            ],
            'btp_berlebih' => [
                'tempat' => ['Rumah Produksi Pangan', 'Pabrik Rumahan'],
                'obyek' => 'pangan olahan dengan bahan tambahan pangan melebihi ambang batas',
                'uraian' => 'Penggunaan bahan tambahan pangan melebihi ambang batas maksimal pada produksi pangan olahan di {kabupaten}.',
                'barang_bukti' => '64 dus pangan olahan dengan BTP berlebih',
                'pasal' => ['pasal_pangan_2'],
                'klasifikasi' => 'produksi',
                'legalitas' => 'legal',
                'modus' => 'offline',
                'pekerjaan' => 'Produsen Pangan Rumahan',
            ],
            'oot_bko' => [
                'tempat' => ['Toko Jamu', 'Kios Herbal', 'Depot Jamu'],
                'obyek' => 'obat tradisional yang mengandung bahan kimia obat',
                'uraian' => 'Peredaran obat tradisional yang mengandung bahan kimia obat tanpa izin edar di {kabupaten}.',
                'barang_bukti' => '540 botol obat tradisional mengandung BKO',
                'pasal' => ['pasal_kesehatan_1', 'pasal_kesehatan_7'],
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Pedagang Jamu',
            ],
            'suplemen_impor' => [
                'tempat' => ['Gudang Ekspedisi', 'Ruko Distributor'],
                'obyek' => 'suplemen impor tanpa nomor izin edar BPOM',
                'uraian' => 'Pemasukan suplemen impor tanpa nomor izin edar BPOM melalui jasa pengiriman antar-pulau di {kabupaten}.',
                'barang_bukti' => '420 kaleng suplemen impor tanpa izin edar',
                'pasal' => ['pasal_pangan_1', 'pasal_konsumen_1'],
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'online',
                'pekerjaan' => 'Pemilik Toko Online',
            ],
            'prekursor' => [
                'tempat' => ['Gudang Kimia', 'Toko Bahan Kimia'],
                'obyek' => 'prekursor farmasi yang disalurkan tanpa izin resmi',
                'uraian' => 'Penyaluran prekursor farmasi tanpa izin resmi untuk pembuatan sediaan farmasi di {kabupaten}.',
                'barang_bukti' => '18 drum prekursor farmasi',
                'pasal' => ['pasal_kesehatan_1', 'pasal_narkotika_2'],
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Pedagang Bahan Kimia',
            ],
            'psikotropika' => [
                'tempat' => ['Apotek', 'Klinik Pratama'],
                'obyek' => 'psikotropika yang diedarkan tanpa izin edar',
                'uraian' => 'Pengedaran psikotropika tanpa izin edar pada sarana pelayanan kefarmasian di {kabupaten}.',
                'barang_bukti' => '240 butir psikotropika tanpa izin edar',
                'pasal' => ['pasal_kesehatan_1', 'pasal_psikotropika_1'],
                'klasifikasi' => 'retailer',
                'legalitas' => 'legal',
                'modus' => 'offline',
                'pekerjaan' => 'Tenaga Teknis Kefarmasian',
            ],
            'alkes' => [
                'tempat' => ['Toko Alat Kesehatan', 'Klinik Pratama'],
                'obyek' => 'alat kesehatan tanpa izin edar BPOM',
                'uraian' => 'Peredaran alat kesehatan tanpa izin edar BPOM pada toko alat kesehatan di {kabupaten}.',
                'barang_bukti' => '48 unit alat kesehatan tanpa izin edar',
                'pasal' => ['pasal_kesehatan_1', 'pasal_konsumen_2'],
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Pemilik Toko Alkes',
            ],
            'jamu_cair' => [
                'tempat' => ['Rumah Produksi Jamu', 'Pabrik Rumahan'],
                'obyek' => 'jamu cair rumahan tanpa izin edar',
                'uraian' => 'Produksi jamu cair rumahan tanpa standar higienitas dan tanpa izin edar di {kabupaten}.',
                'barang_bukti' => '260 botol jamu cair tanpa izin edar',
                'pasal' => ['pasal_kesehatan_1', 'pasal_konsumen_2'],
                'klasifikasi' => 'produksi',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Produsen Jamu Rumahan',
            ],
            'pangan_impor_halal' => [
                'tempat' => ['Cold Storage', 'Gudang Distributor'],
                'obyek' => 'pangan olahan impor tanpa izin edar dan tanpa sertifikat halal',
                'uraian' => 'Peredaran pangan olahan impor tanpa izin edar dan tanpa sertifikat halal di {kabupaten}.',
                'barang_bukti' => '310 dus pangan impor tanpa izin edar',
                'pasal' => ['pasal_pangan_1', 'pasal_pangan_4'],
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
                'pekerjaan' => 'Distributor Pangan Impor',
            ],
            'kosmetik_live' => [
                'tempat' => ['Ruko Toko Online', 'Rumah Kontrakan'],
                'obyek' => 'kosmetik tanpa izin edar yang dijual melalui siaran langsung',
                'uraian' => 'Penjualan kosmetik tanpa izin edar melalui siaran langsung media sosial di {kabupaten}.',
                'barang_bukti' => '150 paket kosmetik tanpa izin edar',
                'pasal' => ['pasal_kesehatan_1', 'pasal_konsumen_1'],
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'online',
                'pekerjaan' => 'Pemilik Toko Online',
            ],
            'pangan_label' => [
                'tempat' => ['Gudang Pangan', 'Toko Kelontong'],
                'obyek' => 'pangan olahan berlabel palsu',
                'uraian' => 'Peredaran pangan olahan berlabel palsu dan menyesatkan konsumen di {kabupaten}.',
                'barang_bukti' => '132 dus pangan olahan berlabel palsu',
                'pasal' => ['pasal_pangan_4', 'pasal_konsumen_1'],
                'klasifikasi' => 'distributor',
                'legalitas' => 'legal',
                'modus' => 'online',
                'pekerjaan' => 'Pelaku Usaha Pangan',
            ],
            'obat_resep' => [
                'tempat' => ['Apotek', 'Toko Obat'],
                'obyek' => 'obat keras yang dijual tanpa resep dokter',
                'uraian' => 'Penjualan obat keras resep dokter tanpa resep pada sarana pelayanan kefarmasian di {kabupaten}.',
                'barang_bukti' => '860 butir obat keras resep',
                'pasal' => ['pasal_kesehatan_1', 'pasal_kesehatan_2'],
                'klasifikasi' => 'retailer',
                'legalitas' => 'legal',
                'modus' => 'offline',
                'pekerjaan' => 'Apoteker',
            ],
            'pangan_kemasan' => [
                'tempat' => ['Gudang Pangan', 'Toko Kelontong'],
                'obyek' => 'pangan olahan dengan kemasan tidak memenuhi persyaratan',
                'uraian' => 'Peredaran pangan olahan dengan kemasan yang tidak memenuhi persyaratan keamanan di {kabupaten}.',
                'barang_bukti' => '74 dus pangan olahan dengan kemasan tidak layak',
                'pasal' => ['pasal_pangan_5', 'pasal_konsumen_2'],
                'klasifikasi' => 'retailer',
                'legalitas' => 'legal',
                'modus' => 'offline',
                'pekerjaan' => 'Pelaku Usaha Pangan',
            ],
        ];
    }

    /**
     * Alamat detail TKP yang konsisten dengan jenis perkara dan wilayah.
     *
     * @param  array<string, mixed>  $archetype
     * @param  array<string, mixed>  $lokasi
     */
    private function susunDetailTkp(array $archetype, array $lokasi, int $urutan): string
    {
        $tempat = $archetype['tempat'][$urutan % count($archetype['tempat'])];
        $usaha = self::NAMA_USAHA[($urutan * 3) % count(self::NAMA_USAHA)];
        $jalan = self::JALAN[($urutan * 7) % count(self::JALAN)];
        $nomor = 3 + (($urutan * 13) % 97);

        return "{$tempat} {$usaha}, Jl. {$jalan} No. {$nomor} {$lokasi['kel'][1]}";
    }

    /**
     * Data tersangka unik dan konsisten dengan wilayah TKP (1 Tersangka per Berkas).
     *
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $lokasi
     * @param  array<string, mixed>  $archetype
     * @return array<string, mixed>
     */
    private function susunTersangka(array $master, array $lokasi, array $archetype, int $seed, bool $pakaiPasalBerlapis): array
    {
        $this->urutanTersangka++;
        $idx = $this->urutanTersangka;

        $perempuan = $idx % 2 === 0;
        $namaDepan = $perempuan ? self::NAMA_DEPAN_PEREMPUAN : self::NAMA_DEPAN_LAKI;
        $namaBelakang = self::NAMA_BELAKANG[($seed * 3 + $idx) % count(self::NAMA_BELAKANG)];
        $nama = $namaDepan[($seed + $idx) % count($namaDepan)].' '.$namaBelakang;

        $tahunLahir = 1965 + ($idx * 7) % 36;
        $bulanLahir = 1 + ($idx * 5) % 12;
        $hariLahir = 1 + ($idx * 11) % 27;

        $nik = substr($lokasi['kab'][0], 0, 4)
            .substr($lokasi['kec'][0], -2)
            .sprintf('%02d%02d%02d%04d', $hariLahir, $bulanLahir, $tahunLahir % 100, $idx % 10000);

        $pasalKeys = $archetype['pasal'];

        // Bobot berjenjang Top Pasal disangkakan sesuai spesifikasi F-38:
        // Rank 1: Pasal 435 UU Kesehatan (pasal_kesehatan_1, ~42% perkara)
        // Rank 2: Pasal 436 UU Kesehatan (pasal_kesehatan_2, ~26% perkara)
        // Rank 3: Pasal 142 UU Pangan (pasal_pangan_1, ~16% perkara)
        // Rank 4: Pasal 62 UU Perlindungan Konsumen (pasal_konsumen_1, ~9% perkara)
        $pasalSeed = ($seed * 17 + $idx * 31) % 100;
        if ($pasalSeed < 42) {
            array_unshift($pasalKeys, 'pasal_kesehatan_1');
        } elseif ($pasalSeed < 68) {
            array_unshift($pasalKeys, 'pasal_kesehatan_2');
        } elseif ($pasalSeed < 84) {
            array_unshift($pasalKeys, 'pasal_pangan_1');
        } elseif ($pasalSeed < 93) {
            array_unshift($pasalKeys, 'pasal_konsumen_1');
        }

        if ($pakaiPasalBerlapis) {
            $pasalKeys[] = ($idx % 2 === 0) ? 'pasal_kuhp_2' : 'pasal_konsumen_1';
        }

        return [
            'nama' => $nama,
            'alias' => $idx % 5 === 0 ? ($perempuan ? 'Ibu ' : 'Pak ').$namaBelakang : null,
            'bin' => self::NAMA_BELAKANG[($idx * 11) % count(self::NAMA_BELAKANG)],
            'nik' => $nik,
            'tempat_lahir' => str_replace(['Kota ', 'Kabupaten '], '', $lokasi['kab'][1]),
            'tanggal_lahir' => sprintf('%04d-%02d-%02d', $tahunLahir, $bulanLahir, $hariLahir),
            'jenis_kelamin' => $perempuan ? 'perempuan' : 'laki-laki',
            'agama' => self::AGAMA[$idx % 10],
            'pekerjaan' => $archetype['pekerjaan'],
            'pasals' => array_map(fn (string $key): mixed => $master[$key], array_values(array_unique($pasalKeys))),
        ];
    }

    /**
     * Rantai tahapan berkas beserta nomor surat, tanggal, status, dan catatan.
     *
     * @param  array<string, mixed>  $archetype
     * @param  array<string, mixed>  $lokasi
     * @return array<int, array<string, mixed>>
     */
    private function susunTahapan(string $bucket, int $urutan, Carbon $tanggalSpdp, array $archetype, array $lokasi): array
    {
        if (in_array($bucket, ['lk_draft', 'lk_final', 'sprindik_draft', 'sprindik_final', 'perkara_only'], true)) {
            return [];
        }

        $urutanTahap = ['spdp'];

        $pakaiP16 = in_array($bucket, ['spdp_p16', 'spdp_p16_p17p'], true)
            || (in_array($bucket, ['tahap1', 'p21', 'tahap2', 'keputusan'], true) && $urutan % 3 === 0);
        $pakaiP17P = $bucket === 'spdp_p16_p17p'
            || (in_array($bucket, ['tahap1', 'p21', 'tahap2', 'keputusan'], true) && $urutan % 4 === 0);

        if ($bucket !== 'spdp_draft') {
            if ($pakaiP16) {
                $urutanTahap[] = 'p16';
            }
            if ($pakaiP17P) {
                $urutanTahap[] = 'p17p';
            }
        }

        if (in_array($bucket, ['tahap1_draft', 'tahap1', 'tahap1_p18p_p19', 'p21_draft', 'p21', 'tahap2', 'keputusan'], true)) {
            $urutanTahap[] = 'tahap1';
        }
        if ($bucket === 'tahap1_p18p_p19') {
            $urutanTahap[] = 'p18p';
            $urutanTahap[] = 'p19';
        }
        if (in_array($bucket, ['p21_draft', 'p21', 'tahap2', 'keputusan'], true)) {
            $urutanTahap[] = 'p21';
        }
        if (in_array($bucket, ['tahap2', 'keputusan'], true)) {
            $urutanTahap[] = 'tahap2';
        }
        if ($bucket === 'keputusan') {
            $urutanTahap[] = 'keputusan';
        }

        $offset = [
            'spdp' => 0,
            'p16' => 6,
            'p17p' => 5,
            'tahap1' => 14,
            'p18p' => 7,
            'p19' => 6,
            'p21' => 10,
            'tahap2' => 6,
            'keputusan' => 21,
        ];

        $draft = [
            'spdp' => $bucket === 'spdp_draft',
            'tahap1' => $bucket === 'tahap1_draft',
            'p21' => $bucket === 'p21_draft',
        ];

        $tanggal = $tanggalSpdp->copy();
        $hasil = [];

        foreach ($urutanTahap as $kode) {
            $tanggal = $tanggal->copy()->addDays($offset[$kode]);
            $status = ($draft[$kode] ?? false) ? 'draft' : 'final';
            if ($status === 'final' && $tanggal->isFuture()) {
                $tanggal = Carbon::create(2026, 10, 28)->startOfDay()->subDays(1);
            }

            $hasil[] = [
                'jenis' => $this->jenisTahap($kode),
                'tanggal' => $tanggal->toDateString(),
                'status' => $status,
                'catatan' => $this->catatanTahap($kode, $status, $archetype, $lokasi, $urutan),
            ];
        }

        return $hasil;
    }

    private function jenisTahap(string $kode): JenisTahap
    {
        return match ($kode) {
            'spdp' => JenisTahap::SPDP,
            'p16' => JenisTahap::P16,
            'p17p' => JenisTahap::P17P,
            'tahap1' => JenisTahap::TAHAP_1,
            'p18p' => JenisTahap::P18P,
            'p19' => JenisTahap::P19,
            'p21' => JenisTahap::P21,
            'tahap2' => JenisTahap::TAHAP_2,
            'keputusan' => JenisTahap::KEPUTUSAN_PENGADILAN,
        };
    }

    private function kodeTahap(JenisTahap $jenis): string
    {
        return match ($jenis) {
            JenisTahap::SPDP => 'spdp',
            JenisTahap::P16 => 'p16',
            JenisTahap::P17P => 'p17p',
            JenisTahap::TAHAP_1 => 'tahap1',
            JenisTahap::P18P => 'p18p',
            JenisTahap::P19 => 'p19',
            JenisTahap::P21 => 'p21',
            JenisTahap::TAHAP_2 => 'tahap2',
            JenisTahap::KEPUTUSAN_PENGADILAN => 'keputusan',
        };
    }

    /**
     * @param  array<string, mixed>  $archetype
     * @param  array<string, mixed>  $lokasi
     */
    private function catatanTahap(string $kode, string $status, array $archetype, array $lokasi, int $urutan): string
    {
        $drafTeks = $status === 'draft' ? ' (Konsep draft menunggu tanda tangan)' : '';

        return match ($kode) {
            'spdp' => "Penerbitan Surat Pemberitahuan Dimulainya Penyidikan terkait dugaan tindak pidana {$archetype['obyek']} di {$lokasi['kab'][1]} ditujukan kepada Kepala {$lokasi['kejari']}.{$drafTeks}",
            'p16' => "Penerimaan Surat P-16 dari {$lokasi['kejari']} perihal Penunjukan Jaksa Penuntut Umum untuk Mengikuti Perkembangan Penyidikan Perkara.",
            'p17p' => "Penerimaan Surat P-17P dari {$lokasi['kejari']} perihal Permintaan Perkembangan Hasil Penyidikan.",
            'tahap1' => "Pengiriman Berkas Perkara Tahap I kepada Kepala {$lokasi['kejari']} untuk dilakukan penelitian kelengkapan formil dan materiil.{$drafTeks}",
            'p18p' => "Penerimaan Surat P-18P dari {$lokasi['kejari']} yang menyatakan hasil penyidikan belum lengkap.",
            'p19' => 'Penerimaan Surat P-19 berikut petunjuk jaksa penuntut umum untuk pemenuhan kelengkapan berkas perkara.',
            'p21' => "Pemberitahuan resmi bahwa hasil penyidikan berkas perkara telah lengkap (P-21) dari {$lokasi['kejari']}.{$drafTeks}",
            'tahap2' => "Pelaksanaan Tahap II: Penyerahan Tersangka dan Barang Bukti ({$archetype['barang_bukti']}) kepada Jaksa Penuntut Umum di {$lokasi['kejari']}.",
            'keputusan' => 'Perkara telah berkekuatan hukum tetap (Inkracht) berdasarkan '.$this->vonisPengadilan($urutan, $lokasi),
            default => 'Catatan proses administrasi penanganan perkara.',
        };
    }

    /**
     * @param  array<string, mixed>  $lokasi
     */
    private function vonisPengadilan(int $urutan, array $lokasi): string
    {
        $vonis = self::VONIS[$urutan % count(self::VONIS)];

        return "{$lokasi['pn']} dengan amar putusan: pidana penjara {$vonis}, serta barang bukti dirampas untuk dimusnahkan.";
    }

    /**
     * Data DPO aktif atau dicabut.
     *
     * @return array<string, mixed>
     */
    private function susunDpo(string $mode, Carbon $tanggalSpdp, int $urutan): array
    {
        $tanggalDpo = $tanggalSpdp->copy()->addDays(12 + ($urutan % 10));
        $nomor = $this->nomorDpo($tanggalDpo);

        if ($mode === 'cabut') {
            $tanggalCabut = $tanggalDpo->copy()->addDays(20 + ($urutan % 15));

            return [
                'nomor_surat' => $nomor,
                'tanggal' => $tanggalDpo->toDateString(),
                'status' => 'nonaktif',
                'keterangan' => 'Tersangka melarikan diri saat pemanggilan kedua dan telah diterbitkan DPO resmi.',
                'tanggal_cabut' => $tanggalCabut->toDateString(),
                'alasan_pencabutan' => 'Tersangka berhasil diamankan oleh Tim Gabungan PPNS BPOM dan Korwas PPNS Polda Kalsel.',
            ];
        }

        return [
            'nomor_surat' => $nomor,
            'tanggal' => $tanggalDpo->toDateString(),
            'status' => 'aktif',
            'keterangan' => 'Tersangka tidak memenuhi 2 kali panggilan patut dan keberadaannya tidak diketahui, diterbitkan penetapan DPO.',
            'tanggal_cabut' => null,
            'alasan_pencabutan' => null,
        ];
    }

    /**
     * Data SP3 dengan alasan yuridis yang sah.
     *
     * @return array<string, mixed>
     */
    private function susunSp3(Carbon $tanggalSp3, int $urutan): array
    {
        $nomor = $this->nomorSp3($tanggalSp3);
        $alasan = self::ALASAN_SP3[$urutan % count(self::ALASAN_SP3)];

        return [
            'nomor_surat' => $nomor,
            'tanggal' => $tanggalSp3->toDateString(),
            'keterangan' => "Penyidikan perkara dihentikan berdasarkan Surat Perintah Penghentian Penyidikan. Alasan: {$alasan}",
        ];
    }

    private function nomorLk(Carbon $tanggal): string
    {
        $tahun = $tanggal->year;

        return $this->nomorBerurut("lk:{$tahun}", fn (int $n): string => sprintf(
            'LK/%03d/%s/%d/BPOM',
            $n,
            self::BULAN_ROMAWI[$tanggal->month],
            $tahun
        ));
    }

    private function nomorSprindik(Carbon $tanggal): string
    {
        $tahun = $tanggal->year;

        return $this->nomorBerurut("sprindik:{$tahun}", fn (int $n): string => sprintf(
            'SP.Sidik/%03d/%s/%d/BPOM',
            $n,
            self::BULAN_ROMAWI[$tanggal->month],
            $tahun
        ));
    }

    /**
     * Nomor surat tahapan sesuai jenisnya.
     *
     * @param  array<string, mixed>  $lokasi
     */
    private function nomorTahap(string $kode, Carbon $tanggal, array $lokasi): string
    {
        $tahun = $tanggal->year;

        return match ($kode) {
            'spdp' => $this->nomorBerurut("spdp:{$tahun}", fn (int $n): string => sprintf(
                'SPDP/%03d/%s/%d/BPOM',
                $n,
                self::BULAN_ROMAWI[$tanggal->month],
                $tahun
            )),
            'tahap1' => $this->nomorBerurut("tahap1:{$tahun}", fn (int $n): string => sprintf(
                'BA.T1/%03d/%s/%d/BPOM',
                $n,
                self::BULAN_ROMAWI[$tanggal->month],
                $tahun
            )),
            'tahap2' => $this->nomorBerurut("tahap2:{$tahun}", fn (int $n): string => sprintf(
                'BA.T2/%03d/%s/%d/BPOM',
                $n,
                self::BULAN_ROMAWI[$tanggal->month],
                $tahun
            )),
            'keputusan' => $this->nomorBerurut("putusan:{$tahun}", fn (int $n): string => sprintf(
                'Putusan PN No. %d/Pid.Sus/%d/PN.%s',
                $n,
                $tahun,
                $lokasi['kode_pn']
            )),
            default => $this->nomorBerurut("jaksa:{$tahun}", fn (int $n): string => sprintf(
                'B-%03d/O.3.%s/Fd.1/%02d/%d',
                $n,
                $lokasi['kode_jaksa'],
                $tanggal->month,
                $tahun
            )),
        };
    }

    private function nomorDpo(Carbon $tanggal): string
    {
        $tahun = $tanggal->year;

        return $this->nomorBerurut("dpo:{$tahun}", fn (int $n): string => sprintf(
            'DPO/%03d/%s/%d/BPOM',
            $n,
            self::BULAN_ROMAWI[$tanggal->month],
            $tahun
        ));
    }

    private function nomorSp3(Carbon $tanggal): string
    {
        $tahun = $tanggal->year;

        return $this->nomorBerurut("sp3:{$tahun}", fn (int $n): string => sprintf(
            'S.Tap/SP3/%03d/%s/%d/BPOM',
            $n,
            self::BULAN_ROMAWI[$tanggal->month],
            $tahun
        ));
    }

    /**
     * @param  callable(int): string  $format
     */
    private function nomorBerurut(string $kunci, callable $format): string
    {
        $this->urutanNomor[$kunci] = ($this->urutanNomor[$kunci] ?? 0) + 1;

        return $format($this->urutanNomor[$kunci]);
    }

    /**
     * Helper pembuatan Perkara, TKP, PPNS, Tersangka, Berkas Perkara, Tahapan, Dokumen, dan Audit Trail.
     *
     * @param  array<string, mixed>  $tkpData
     * @param  array<int, array<string, mixed>>  $tersangkaList
     * @param  array<int, array<string, mixed>>  $tahapanChain
     * @param  array<string, mixed>|null  $dpoData
     * @param  array<string, mixed>|null  $sp3Data
     */
    private function createCase(
        User $actor,
        string $noLk,
        string $tglLk,
        string $uraian,
        ?string $noSp,
        ?string $tglSp,
        ?string $statusPerkara,
        array $tkpData,
        array $lokasi,
        User $ppnsUtama,
        array $ppnsAnggota,
        array $tersangkaList,
        array $tahapanChain,
        ?array $dpoData = null,
        ?array $sp3Data = null,
        ?string $statusFormalOverride = null,
        string $statusLaporan = 'final',
        ?string $statusSprindik = null
    ): void {
        $hasSp = ! empty($noSp);
        $hasPerkara = $statusPerkara === 'final';
        $isLkFinal = $statusLaporan === 'final';
        $isSpFinal = $hasSp && ($statusSprindik === null || $statusSprindik === 'final');

        // Alamat & Entitas TKP (hanya dibuat jika tahap Perkara sudah final)
        $tkpId = null;
        if ($hasPerkara) {
            $alamatTkpNama = $tkpData['nama'] ?? (isset($tkpData['detail']) ? trim(explode(',', (string) $tkpData['detail'])[0]) : null);
            $alamatDetail = isset($tkpData['detail']) && str_contains((string) $tkpData['detail'], ',')
                ? trim(substr((string) $tkpData['detail'], strpos((string) $tkpData['detail'], ',') + 1))
                : ($tkpData['detail'] ?? null);

            $alamatTkp = Alamat::create([
                'nama' => $alamatTkpNama,
                'provinsi_kode' => $tkpData['prov'][0],
                'provinsi_nama' => $tkpData['prov'][1],
                'kabupaten_kode' => $tkpData['kab'][0],
                'kabupaten_nama' => $tkpData['kab'][1],
                'kecamatan_kode' => $tkpData['kec'][0],
                'kecamatan_nama' => $tkpData['kec'][1],
                'desa_kode' => $tkpData['kel'][0],
                'desa_nama' => $tkpData['kel'][1],
                'alamat_detail' => $alamatDetail,
                'rt' => '001',
                'rw' => '001',
            ]);

            $tkp = Tkp::create([
                'alamat_id' => $alamatTkp->id,
                'klasifikasi' => $tkpData['klasifikasi'],
                'legalitas' => $tkpData['legalitas'],
                'modus' => $tkpData['modus'],
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $tkpId = $tkp->id;
        }

        // Entitas Perkara
        $perkara = Perkara::updateOrCreate(
            ['nomor_laporan_kejadian' => $noLk],
            [
                'tanggal_laporan_kejadian' => $tglLk,
                'tahap_laporan_status' => $statusLaporan,
                'laporan_locked_at' => $isLkFinal ? Carbon::parse($tglLk)->addDay() : null,
                'laporan_finalized_by' => $isLkFinal ? $actor->id : null,
                'nomor_surat_perintah' => $noSp,
                'tanggal_surat_perintah' => $tglSp,
                'tahap_sprindik_status' => $hasSp ? ($statusSprindik ?? 'final') : null,
                'sprindik_locked_at' => $isSpFinal ? Carbon::parse($tglSp)->addDay() : null,
                'sprindik_finalized_by' => $isSpFinal ? $actor->id : null,
                'tahap_perkara_status' => $hasPerkara ? 'final' : null,
                'perkara_locked_at' => $hasPerkara ? Carbon::parse($tglSp ?? $tglLk)->addDays(3) : null,
                'perkara_finalized_by' => $hasPerkara ? $actor->id : null,
                'tkp_id' => $tkpId,
                'deskripsi' => $uraian,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]
        );

        // Dokumen LK
        Dokumen::updateOrCreate(
            [
                'perkara_id' => $perkara->id,
                'kategori' => 'lapkej',
            ],
            [
                'nama_dokumen' => "Laporan Kejadian - {$noLk}",
                'nama_file_asli' => str_replace(['/', '\\'], '-', $noLk).'.pdf',
                'file_path' => 'dokumen/sample-bap.pdf',
                'file_size' => 124500,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]
        );

        // Dokumen Sprindik (jika ada)
        if ($hasSp) {
            Dokumen::updateOrCreate(
                [
                    'perkara_id' => $perkara->id,
                    'kategori' => 'sprindik',
                ],
                [
                    'nama_dokumen' => "Surat Perintah Penyidikan - {$noSp}",
                    'nama_file_asli' => str_replace(['/', '\\'], '-', $noSp).'.pdf',
                    'file_path' => 'dokumen/sample-bap.pdf',
                    'file_size' => 135800,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]
            );
        }

        // Relasi PPNS Utama & Anggota (hanya jika tahap Perkara sudah dibuat)
        if ($hasPerkara) {
            $perkara->ppnsUsers()->syncWithoutDetaching([$ppnsUtama->id => ['peran' => 'utama']]);
            foreach ($ppnsAnggota as $anggota) {
                if ($anggota->id !== $ppnsUtama->id) {
                    $perkara->ppnsUsers()->syncWithoutDetaching([$anggota->id => ['peran' => 'anggota']]);
                }
            }
        }

        // Audit log LK
        AuditLog::create([
            'user_id' => $actor->id,
            'perkara_id' => $perkara->id,
            'aksi' => $isLkFinal ? 'finalisasi_laporan_kejadian' : 'simpan_draft_laporan_kejadian',
            'tabel' => 'perkara',
            'record_id' => $perkara->id,
            'data_lama' => null,
            'data_baru' => ['nomor_laporan_kejadian' => $noLk, 'status' => $statusLaporan],
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::parse($tglLk)->setTime(9, 30),
        ]);

        if ($hasSp) {
            AuditLog::create([
                'user_id' => $actor->id,
                'perkara_id' => $perkara->id,
                'aksi' => $isSpFinal ? 'finalisasi_sprindik' : 'simpan_draft_sprindik',
                'tabel' => 'perkara',
                'record_id' => $perkara->id,
                'data_lama' => null,
                'data_baru' => ['nomor_surat_perintah' => $noSp, 'status' => $statusSprindik ?? 'final'],
                'ip_address' => '127.0.0.1',
                'created_at' => Carbon::parse($tglSp)->setTime(10, 15),
            ]);
        }

        foreach ($tersangkaList as $urutan => $tersangkaData) {
            $this->createBerkas(
                actor: $actor,
                perkara: $perkara,
                noLk: $noLk,
                tglSp: $tglSp,
                tglLk: $tglLk,
                tkpData: $tkpData,
                lokasi: $lokasi,
                tersangkaData: $tersangkaData,
                tahapanChain: $tahapanChain,
                dpoData: $dpoData,
                sp3Data: $sp3Data,
                statusFormalOverride: $statusFormalOverride,
            );
        }
    }

    /**
     * Membuat satu Berkas Perkara (1 tersangka = 1 berkas) beserta tahapan, dokumen, dan audit trail.
     *
     * @param  array<string, mixed>  $tkpData
     * @param  array<string, mixed>  $tersangkaData
     * @param  array<int, array<string, mixed>>  $tahapanChain
     * @param  array<string, mixed>|null  $dpoData
     * @param  array<string, mixed>|null  $sp3Data
     */
    private function createBerkas(
        User $actor,
        Perkara $perkara,
        string $noLk,
        ?string $tglSp,
        string $tglLk,
        array $tkpData,
        array $lokasi,
        array $tersangkaData,
        array $tahapanChain,
        ?array $dpoData,
        ?array $sp3Data,
        ?string $statusFormalOverride
    ): void {
        // Alamat Tersangka
        $jalanTsk = self::JALAN[($this->urutanTersangka * 7) % count(self::JALAN)];
        $nomorTsk = 1 + (($this->urutanTersangka * 13) % 97);
        $alamatTersangka = Alamat::create([
            'provinsi_kode' => $tkpData['prov'][0],
            'provinsi_nama' => $tkpData['prov'][1],
            'kabupaten_kode' => $tkpData['kab'][0],
            'kabupaten_nama' => $tkpData['kab'][1],
            'kecamatan_kode' => $tkpData['kec'][0],
            'kecamatan_nama' => $tkpData['kec'][1],
            'desa_kode' => $tkpData['kel'][0],
            'desa_nama' => $tkpData['kel'][1],
            'alamat_detail' => "Jl. {$jalanTsk} No. {$nomorTsk}",
            'rt' => '002',
            'rw' => '001',
        ]);

        $tersangka = Tersangka::updateOrCreate(
            ['perkara_id' => $perkara->id, 'nik' => $tersangkaData['nik']],
            [
                'nama' => $tersangkaData['nama'],
                'alias' => $tersangkaData['alias'] ?? null,
                'bin_binti' => $tersangkaData['bin'] ?? null,
                'tempat_lahir' => $tersangkaData['tempat_lahir'],
                'tanggal_lahir' => $tersangkaData['tanggal_lahir'],
                'jenis_kelamin' => $tersangkaData['jenis_kelamin'],
                'agama' => $tersangkaData['agama'],
                'pekerjaan' => $tersangkaData['pekerjaan'],
                'kewarganegaraan' => 'WNI',
                'alamat_id' => $alamatTersangka->id,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]
        );

        // Nomor Berkas Perkara identik dengan nomor LK (1 Perkara = 1 Tersangka = 1 Berkas Perkara)
        $nomorBerkas = $noLk;

        $berkas = BerkasPerkara::updateOrCreate(
            ['perkara_id' => $perkara->id, 'tersangka_id' => $tersangka->id],
            [
                'nomor_berkas' => $nomorBerkas,
                'status_proses' => 'Baru',
                'status_formal' => null,
                'progress_persen' => 0,
                'is_dpo' => false,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]
        );

        // Hubungkan Pasal
        $pasalIds = array_map(fn ($p) => $p->id, $tersangkaData['pasals']);
        $berkas->pasals()->sync($pasalIds);

        AuditLog::create([
            'user_id' => $actor->id,
            'perkara_id' => $perkara->id,
            'berkas_perkara_id' => $berkas->id,
            'aksi' => 'buat_berkas_perkara',
            'tabel' => 'berkas_perkara',
            'record_id' => $berkas->id,
            'data_lama' => null,
            'data_baru' => ['nomor_berkas' => $berkas->nomor_berkas, 'tersangka' => $tersangka->nama],
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::parse($tglSp ?? $tglLk)->addDays(2)->setTime(11, 0),
        ]);

        // Proses Rantai Tahapan
        $progressMap = [
            JenisTahap::SPDP->value => 15,
            JenisTahap::TAHAP_1->value => 55,
            JenisTahap::P21->value => 85,
            JenisTahap::TAHAP_2->value => 100,
            JenisTahap::KEPUTUSAN_PENGADILAN->value => 100,
        ];

        $currentProgress = 0;
        $currentStatusProses = 'Baru';

        foreach ($tahapanChain as $tahapItem) {
            /** @var JenisTahap $jenis */
            $jenis = $tahapItem['jenis'];
            $tanggal = $tahapItem['tanggal'];
            $nomor = $this->nomorTahap($this->kodeTahap($jenis), Carbon::parse($tanggal), $lokasi);
            $catatan = $tahapItem['catatan'];
            $statusTahap = $tahapItem['status'] ?? 'final';
            $isTahapFinal = $statusTahap === 'final';

            $tahap = TahapanPerkara::updateOrCreate(
                [
                    'berkas_perkara_id' => $berkas->id,
                    'jenis_tahap' => $jenis->value,
                ],
                [
                    'nomor_surat' => $nomor,
                    'tanggal_surat' => $tanggal,
                    'catatan' => $catatan,
                    'status' => $statusTahap,
                    'finalized_at' => $isTahapFinal ? Carbon::parse($tanggal)->setTime(14, 0) : null,
                    'finalized_by' => $isTahapFinal ? $actor->id : null,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]
            );

            $dokumenKategori = ($jenis === JenisTahap::SPDP) ? 'spdp' : 'tahapan';

            Dokumen::updateOrCreate(
                [
                    'berkas_perkara_id' => $berkas->id,
                    'tahapan_perkara_id' => $tahap->id,
                ],
                [
                    'perkara_id' => $perkara->id,
                    'kategori' => $dokumenKategori,
                    'nama_dokumen' => "{$jenis->label()} - {$nomor}",
                    'nama_file_asli' => str_replace(['/', '\\'], '-', $nomor).'.pdf',
                    'file_path' => 'dokumen/sample-bap.pdf',
                    'file_size' => 142000,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]
            );

            if ($isTahapFinal) {
                if (isset($progressMap[$jenis->value])) {
                    $currentProgress = max($currentProgress, $progressMap[$jenis->value]);
                }
                $currentStatusProses = $jenis->label();

                if ($jenis === JenisTahap::SPDP) {
                    $tanggalSpdp = Carbon::parse($tanggal);
                    $sla1Bulan = $tanggalSpdp->copy()->addMonth();
                    $akhirTahun = $tanggalSpdp->copy()->endOfYear();
                    $effectiveDeadline = $sla1Bulan->gt($akhirTahun) ? $akhirTahun : $sla1Bulan;

                    $berkas->update([
                        'tanggal_spdp' => $tanggal,
                        'sla_deadline' => $effectiveDeadline->toDateString(),
                    ]);
                }
            }

            AuditLog::create([
                'user_id' => $actor->id,
                'perkara_id' => $perkara->id,
                'berkas_perkara_id' => $berkas->id,
                'aksi' => $isTahapFinal ? 'finalisasi_tahap' : 'simpan_draft_tahap',
                'tabel' => 'tahapan_perkara',
                'record_id' => $tahap->id,
                'data_lama' => null,
                'data_baru' => [
                    'tahap' => $jenis->label(),
                    'nomor_surat' => $nomor,
                    'tanggal_surat' => $tanggal,
                    'status' => $statusTahap,
                ],
                'ip_address' => '127.0.0.1',
                'created_at' => Carbon::parse($tanggal)->setTime(14, 30),
            ]);
        }

        // Tentukan Status Formal
        $statusFormal = null;
        if ($currentProgress >= 100) {
            $statusFormal = 'selesai';
        }

        $berkas->update([
            'progress_persen' => $currentProgress,
            'status_proses' => $currentStatusProses,
            'status_formal' => $statusFormalOverride ?? $statusFormal,
        ]);

        // DPO jika ada
        if ($dpoData !== null) {
            $isAktif = ($dpoData['status'] ?? 'aktif') === 'aktif';

            $dokDpo = Dokumen::updateOrCreate(
                [
                    'berkas_perkara_id' => $berkas->id,
                    'kategori' => 'dpo',
                ],
                [
                    'perkara_id' => $perkara->id,
                    'nama_dokumen' => "Surat DPO - {$dpoData['nomor_surat']}",
                    'nama_file_asli' => str_replace(['/', '\\'], '-', $dpoData['nomor_surat']).'.pdf',
                    'file_path' => 'dokumen/sample-bap.pdf',
                    'file_size' => 128000,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]
            );

            Dpo::updateOrCreate(
                ['berkas_perkara_id' => $berkas->id],
                [
                    'nomor_surat' => $dpoData['nomor_surat'],
                    'tanggal_penetapan' => $dpoData['tanggal'],
                    'tanggal_pencabutan' => $dpoData['tanggal_cabut'] ?? null,
                    'status' => $dpoData['status'] ?? 'aktif',
                    'keterangan' => $dpoData['keterangan'],
                    'alasan_pencabutan' => $dpoData['alasan_pencabutan'] ?? null,
                    'dokumen_id' => $dokDpo->id,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]
            );

            $berkas->update(['is_dpo' => $isAktif]);

            AuditLog::create([
                'user_id' => $actor->id,
                'perkara_id' => $perkara->id,
                'berkas_perkara_id' => $berkas->id,
                'aksi' => $isAktif ? 'penetapan_dpo' : 'cabut_dpo',
                'tabel' => 'dpo',
                'record_id' => $berkas->id,
                'data_lama' => ['is_dpo' => false],
                'data_baru' => ['is_dpo' => $isAktif, 'nomor_dpo' => $dpoData['nomor_surat']],
                'ip_address' => '127.0.0.1',
                'created_at' => Carbon::parse($dpoData['tanggal'])->setTime(11, 45),
            ]);
        }

        // SP3 jika ada
        if ($sp3Data !== null) {
            $dokSp3 = Dokumen::updateOrCreate(
                [
                    'berkas_perkara_id' => $berkas->id,
                    'kategori' => 'sp3',
                ],
                [
                    'perkara_id' => $perkara->id,
                    'nama_dokumen' => "Surat SP3 - {$sp3Data['nomor_surat']}",
                    'nama_file_asli' => str_replace(['/', '\\'], '-', $sp3Data['nomor_surat']).'.pdf',
                    'file_path' => 'dokumen/sample-bap.pdf',
                    'file_size' => 134000,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]
            );

            Sp3::updateOrCreate(
                ['berkas_perkara_id' => $berkas->id],
                [
                    'nomor_surat' => $sp3Data['nomor_surat'],
                    'tanggal_sp3' => $sp3Data['tanggal'],
                    'keterangan' => $sp3Data['keterangan'],
                    'dokumen_id' => $dokSp3->id,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]
            );

            $berkas->update([
                'status_proses' => 'SP3',
                'status_formal' => 'tidak_selesai',
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'perkara_id' => $perkara->id,
                'berkas_perkara_id' => $berkas->id,
                'aksi' => 'terbitkan_sp3',
                'tabel' => 'sp3',
                'record_id' => $berkas->id,
                'data_lama' => ['status_formal' => null],
                'data_baru' => ['status_formal' => 'tidak_selesai', 'status_proses' => 'SP3', 'nomor_sp3' => $sp3Data['nomor_surat']],
                'ip_address' => '127.0.0.1',
                'created_at' => Carbon::parse($sp3Data['tanggal'])->setTime(15, 0),
            ]);
        }
    }
}
