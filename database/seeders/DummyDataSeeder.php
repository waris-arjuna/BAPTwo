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
use Carbon\Carbon;
use Illuminate\Database\Seeder;
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
        'Reza', 'Taufik', 'Anwar', 'Danu',
    ];

    private const NAMA_DEPAN_PEREMPUAN = [
        'Siti', 'Dewi', 'Nurul', 'Maya', 'Anisa', 'Linda', 'Tri', 'Rina',
        'Fitri', 'Yuni', 'Ayu', 'Diah', 'Ratna', 'Novita', 'Sri', 'Endang',
        'Lestari', 'Mira', 'Hasna', 'Sabrina', 'Wulan', 'Indah', 'Rahmi', 'Vina',
        'Desi', 'Eka', 'Salma', 'Putri',
    ];

    private const NAMA_BELAKANG = [
        'Wijaya', 'Saputra', 'Hidayat', 'Pratama', 'Kurniawan', 'Setiawan', 'Maulana',
        'Hakim', 'Santosa', 'Gunawan', 'Rahmawati', 'Lestari', 'Anggraeni', 'Wibowo',
        'Sujatmiko', 'Ramadhan', 'Prasetyo', 'Sutrisno', 'Firmansyah', 'Siregar',
        'Nasution', 'Hakim', 'Mahfud', 'Anshari', 'Rusli', 'Syahrani', 'Mardianto',
        'Kusuma', 'Halim', 'Pahlevi', 'Bachri', 'Ismail', 'Rahman', 'Sofyan',
        'Mubarak', 'Wardana', 'Saputri', 'Maryam', 'Salsabila', 'Ningsih',
    ];

    private const JALAN = [
        'A. Yani', 'Pangeran Antasari', 'Veteran', 'Brigjend H. Hasan Basri',
        'Kemakmuran', 'Murakata', 'Ir. P.H.M. Noor', 'Basuki Rahmat',
        'Sekumpul Raya', 'Putri Cipta Sari', 'Jenderal Sudirman', 'Perdagangan',
        'Lambung Mangkurat', 'Trikora', 'Sultan Adam', 'Belitung Darat',
        'Kuripan', 'Pangeran Samudera', 'Achmad Yani Km. 34', 'Gerilya',
    ];

    private const NAMA_USAHA = [
        'Sumber Rejeki', 'Berkah Jaya', 'Ayu Cantik', 'Sehat Alami', 'Bugar Sehat',
        'Cahaya Farma', 'Mekar Sari', 'Tunas Baru', 'Sinar Abadi', 'Aneka Jaya',
        'Mitra Sehat', 'Karya Mandiri', 'Rasa Murni', 'Bina Usaha', 'Surya Kencana',
        'Harapan Baru', 'Sumber Makmur', 'Amanah Farma', 'Duta Niaga', 'Panca Jaya',
        'Nusa Indah', 'Sari Wangi', 'Cinta Bunda', 'Bintang Timur',
    ];

    private const PEKERJAAN_UMUM = [
        'Wiraswasta', 'Karyawan Swasta', 'Pedagang', 'Kurir Ekspedisi', 'Petani',
        'Buruh Harian', 'Ibu Rumah Tangga', 'Pemilik Ruko', 'Pemilik Toko Online',
        'Pengemudi Ojek Daring', 'Pemilik Warung', 'Karyawan Toko',
    ];

    private const VONIS = [
        '2 tahun penjara dan denda Rp 150.000.000 subsider 4 bulan kurungan',
        '1 tahun 6 bulan penjara dan denda Rp 100.000.000 subsider 2 bulan kurungan',
        '3 tahun penjara dan denda Rp 250.000.000 subsider 6 bulan kurungan',
        '10 bulan penjara dan denda Rp 50.000.000 subsider 1 bulan kurungan',
        '4 tahun penjara dan denda Rp 500.000.000 subsider 6 bulan kurungan',
        '1 tahun penjara dengan masa percobaan 2 tahun dan denda Rp 20.000.000',
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
     * Jalankan seeder 100 data perkara dummy yang beragam untuk seluruh alur BAPTwo.
     */
    public function run(): void
    {
        // 1. Pastikan sample PDF privat tersedia
        $this->ensureSamplePdf();

        // 2. Bersihkan data dummy lama yang sudah usang agar jumlah data tetap konsisten
        $this->cleanupObsoleteDummyCases();

        // 3. Siapkan Master Data Lengkap
        $master = $this->seedMasterData();

        // 4. Siapkan Pengguna Tambahan (PPNS)
        $users = $this->seedUsers($master);

        // 5. Tanam 100 Perkara dengan variasi jenis, tahapan, DPO, SP3, dan jumlah tersangka
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
     * Membersihkan nomor perkara dummy lama agar total perkara tepat 100 dan seeder bisa diulang.
     */
    private function cleanupObsoleteDummyCases(): void
    {
        $obsoleteCases = Perkara::with(['berkasPerkaras', 'tersangkas', 'tkp'])
            ->where('nomor_laporan_kejadian', 'like', 'LK/%')
            ->get();

        foreach ($obsoleteCases as $obs) {
            AuditLog::where('perkara_id', $obs->id)->delete();

            $obs->berkasPerkaras()->each(function ($b) {
                AuditLog::where('berkas_perkara_id', $b->id)->delete();
                $b->tahapan()->forceDelete();
                $b->dpos()->forceDelete();
                $b->sp3()?->forceDelete();
                $b->dokumen()->forceDelete();
                $b->pasals()->detach();
                $b->forceDelete();
            });

            $obs->tersangkas()->each(function ($t) {
                $alamatId = $t->alamat_id;
                $t->forceDelete();
                if ($alamatId) {
                    Alamat::where('id', $alamatId)->delete();
                }
            });

            $obs->ppnsUsers()->detach();
            $obs->dokumen()->forceDelete();

            $tkp = $obs->tkp;
            $obs->forceDelete();
            if ($tkp) {
                $alamatId = $tkp->alamat_id;
                $tkp->forceDelete();
                if ($alamatId) {
                    Alamat::where('id', $alamatId)->delete();
                }
            }
        }
    }

    /**
     * Master data lengkap: Jabatan, Bidang, Panggol, Pasal UU, Instansi Hukum, Jaksa.
     *
     * @return array<string, mixed>
     */
    private function seedMasterData(): array
    {
        $jabatanPpns = Jabatan::firstOrCreate(['nama_jabatan' => 'Penyidik Pegawai Negeri Sipil (PPNS)']);
        $jabatanPfm = Jabatan::firstOrCreate(['nama_jabatan' => 'Pengawas Farmasi dan Makanan Ahli Muda']);
        $jabatanAnalis = Jabatan::firstOrCreate(['nama_jabatan' => 'Analis Intelijen Penindakan']);

        $bidangPenindakan = Bidang::firstOrCreate(['nama_bidang' => 'Bidang Penindakan']);
        $bidangPemeriksaan = Bidang::firstOrCreate(['nama_bidang' => 'Bidang Pemeriksaan']);

        $panggolPenata = Panggol::firstOrCreate(['pangkat' => 'Penata', 'golongan' => 'III/c']);
        $panggolPenataTk1 = Panggol::firstOrCreate(['pangkat' => 'Penata Tk. I', 'golongan' => 'III/d']);
        $panggolPembina = Panggol::firstOrCreate(['pangkat' => 'Pembina', 'golongan' => 'IV/a']);

        $pasalKesehatan1 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Kesehatan', 'pasal' => 'Pasal 435 jo. Pasal 138 ayat (2) dan ayat (3)'],
            [
                'nomor' => '17',
                'tahun' => '2023',
                'isi' => 'Setiap Orang yang memproduksi atau mengedarkan Sediaan Farmasi dan/atau Alat Kesehatan yang tidak memenuhi standar.',
                'status' => 'aktif',
            ]
        );

        $pasalKesehatan2 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Kesehatan', 'pasal' => 'Pasal 436 ayat (2) jo. Pasal 145 ayat (1)'],
            [
                'nomor' => '17',
                'tahun' => '2023',
                'isi' => 'Setiap Orang yang tidak memiliki keahlian dan kewenangan untuk melakukan praktik kefarmasian.',
                'status' => 'aktif',
            ]
        );

        $pasalKesehatan3 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Kesehatan', 'pasal' => 'Pasal 436 ayat (1)'],
            [
                'nomor' => '17',
                'tahun' => '2023',
                'isi' => 'Setiap Orang yang memproduksi sediaan farmasi dan/atau alat kesehatan tanpa memiliki keahlian dan kewenangan.',
                'status' => 'aktif',
            ]
        );

        $pasalKesehatan4 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Kesehatan', 'pasal' => 'Pasal 437 ayat (1)'],
            [
                'nomor' => '17',
                'tahun' => '2023',
                'isi' => 'Setiap Orang yang memproduksi atau mengedarkan sediaan farmasi dan/atau alat kesehatan palsu.',
                'status' => 'aktif',
            ]
        );

        $pasalKesehatan5 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Kesehatan', 'pasal' => 'Pasal 438 ayat (1)'],
            [
                'nomor' => '17',
                'tahun' => '2023',
                'isi' => 'Setiap Orang yang memproduksi atau mengedarkan kosmetik yang mengandung bahan berbahaya atau dilarang.',
                'status' => 'aktif',
            ]
        );

        $pasalKesehatan6 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Kesehatan', 'pasal' => 'Pasal 439 ayat (1)'],
            [
                'nomor' => '17',
                'tahun' => '2023',
                'isi' => 'Setiap Orang yang memperjualbelikan obat keras daftar G secara bebas tanpa resep dokter atau izin resmi.',
                'status' => 'aktif',
            ]
        );

        $pasalKesehatan7 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Kesehatan', 'pasal' => 'Pasal 440 ayat (1)'],
            [
                'nomor' => '17',
                'tahun' => '2023',
                'isi' => 'Setiap Orang yang mengedarkan obat bahan alam (obat tradisional) yang mengandung bahan kimia obat (BKO).',
                'status' => 'aktif',
            ]
        );

        $pasalPangan1 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Pangan', 'pasal' => 'Pasal 142 jo. Pasal 91 ayat (1)'],
            [
                'nomor' => '18',
                'tahun' => '2012',
                'isi' => 'Pelaku Usaha Pangan yang dengan sengaja tidak memiliki izin edar terhadap setiap Pangan Olahan yang diperdagangkan.',
                'status' => 'aktif',
            ]
        );

        $pasalPangan2 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Pangan', 'pasal' => 'Pasal 136 jo. Pasal 75 ayat (1)'],
            [
                'nomor' => '18',
                'tahun' => '2012',
                'isi' => 'Pelaku Usaha yang menggunakan bahan tambahan pangan yang dilarang atau melampaui ambang batas maksimal.',
                'status' => 'aktif',
            ]
        );

        $pasalPangan3 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Pangan', 'pasal' => 'Pasal 138 jo. Pasal 80 ayat (1)'],
            [
                'nomor' => '18',
                'tahun' => '2012',
                'isi' => 'Pelaku Usaha yang memperdagangkan pangan tercemar, busuk, berbau, atau mengandung bahan beracun.',
                'status' => 'aktif',
            ]
        );

        $pasalPangan4 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Pangan', 'pasal' => 'Pasal 140 jo. Pasal 86 ayat (2)'],
            [
                'nomor' => '18',
                'tahun' => '2012',
                'isi' => 'Setiap Orang yang memproduksi atau mengedarkan pangan olahan dengan label atau iklan palsu dan menyesatkan.',
                'status' => 'aktif',
            ]
        );

        $pasalPangan5 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Pangan', 'pasal' => 'Pasal 141 jo. Pasal 89'],
            [
                'nomor' => '18',
                'tahun' => '2012',
                'isi' => 'Pelaku Usaha Pangan yang mengedarkan pangan olahan dengan kemasan yang membahayakan kesehatan manusia.',
                'status' => 'aktif',
            ]
        );

        $pasalKonsumen1 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Perlindungan Konsumen', 'pasal' => 'Pasal 62 ayat (1) jo. Pasal 8 ayat (1) huruf a'],
            [
                'nomor' => '8',
                'tahun' => '1999',
                'isi' => 'Memproduksi dan/atau memperdagangkan barang dan/atau jasa yang tidak memenuhi atau tidak sesuai dengan standar mutu.',
                'status' => 'aktif',
            ]
        );

        $pasalKonsumen2 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Perlindungan Konsumen', 'pasal' => 'Pasal 62 ayat (1) jo. Pasal 8 ayat (1) huruf d'],
            [
                'nomor' => '8',
                'tahun' => '1999',
                'isi' => 'Memperdagangkan barang yang tidak sesuai dengan kondisi, jaminan, keistimewaan, atau kemanjuran yang dijanjikan.',
                'status' => 'aktif',
            ]
        );

        $pasalKonsumen3 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Perlindungan Konsumen', 'pasal' => 'Pasal 62 ayat (1) jo. Pasal 8 ayat (1) huruf i'],
            [
                'nomor' => '8',
                'tahun' => '1999',
                'isi' => 'Memperdagangkan barang yang tidak mencantumkan tanggal kedaluwarsa atau jangka waktu penggunaan yang paling baik.',
                'status' => 'aktif',
            ]
        );

        $pasalNarkotika1 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Narkotika', 'pasal' => 'Pasal 114 ayat (1)'],
            [
                'nomor' => '35',
                'tahun' => '2009',
                'isi' => 'Menawarkan untuk dijual, menjual, membeli, menerima, menjadi perantara dalam jual beli, atau menyerahkan Narkotika Golongan I.',
                'status' => 'aktif',
            ]
        );

        $pasalNarkotika2 = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Narkotika', 'pasal' => 'Pasal 129 huruf a'],
            [
                'nomor' => '35',
                'tahun' => '2009',
                'isi' => 'Memproduksi, mengimpor, mengekspor, atau menyalurkan Prekursor Farmasi untuk pembuatan Narkotika secara melawan hukum.',
                'status' => 'aktif',
            ]
        );

        $pasalPsikotropika = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Psikotropika', 'pasal' => 'Pasal 60 ayat (1) huruf c jo. Pasal 14 ayat (1)'],
            [
                'nomor' => '5',
                'tahun' => '1997',
                'isi' => 'Mengedarkan psikotropika tanpa izin edar resmi atau tidak memenuhi standar mutu dan keamanan.',
                'status' => 'aktif',
            ]
        );

        $pasalKuhp1 = Pasal::firstOrCreate(
            ['nama_pasal' => 'KUHP', 'pasal' => 'Pasal 204 ayat (1)'],
            [
                'nomor' => '1',
                'tahun' => '1946',
                'isi' => 'Menjual, menawarkan, menerimakan atau membagi-bagikan barang yang diketahuinya membahayakan nyawa atau kesehatan orang.',
                'status' => 'aktif',
            ]
        );

        $pasalKuhp2 = Pasal::firstOrCreate(
            ['nama_pasal' => 'KUHP', 'pasal' => 'Pasal 55 ayat (1) ke-1'],
            [
                'nomor' => '1',
                'tahun' => '1946',
                'isi' => 'Turut serta melakukan, menyuruh melakukan, atau membujuk melakukan suatu perbuatan tindak pidana.',
                'status' => 'aktif',
            ]
        );

        $kejati = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Tinggi Kalimantan Selatan'],
            ['wilayah' => 'Kalimantan Selatan']
        );

        $kejariBjm = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Banjarmasin'],
            ['wilayah' => 'Kota Banjarmasin']
        );

        $kejariBjb = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Banjarbaru'],
            ['wilayah' => 'Kota Banjarbaru']
        );

        $kejariBanjar = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Banjar'],
            ['wilayah' => 'Kabupaten Banjar']
        );

        $kejariBatola = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Barito Kuala'],
            ['wilayah' => 'Kabupaten Barito Kuala']
        );

        $kejariTanahlaut = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Tanah Laut'],
            ['wilayah' => 'Kabupaten Tanah Laut']
        );

        $kejariTapin = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Tapin'],
            ['wilayah' => 'Kabupaten Tapin']
        );

        $kejariHst = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Hulu Sungai Tengah'],
            ['wilayah' => 'Kabupaten Hulu Sungai Tengah']
        );

        $kejariHsu = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Hulu Sungai Utara'],
            ['wilayah' => 'Kabupaten Hulu Sungai Utara']
        );

        $kejariHss = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Hulu Sungai Selatan'],
            ['wilayah' => 'Kabupaten Hulu Sungai Selatan']
        );

        $kejariTabalong = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Tabalong'],
            ['wilayah' => 'Kabupaten Tabalong']
        );

        $kejariTanbu = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Tanah Bumbu'],
            ['wilayah' => 'Kabupaten Tanah Bumbu']
        );

        $kejariBalangan = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Balangan'],
            ['wilayah' => 'Kabupaten Balangan']
        );

        $kejariKotabaru = InstansiHukum::firstOrCreate(
            ['jenis' => 'kejaksaan', 'nama' => 'Kejaksaan Negeri Kotabaru'],
            ['wilayah' => 'Kabupaten Kotabaru']
        );

        $ptBjm = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Tinggi Banjarmasin'],
            ['wilayah' => 'Kalimantan Selatan']
        );

        $pnBjm = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Banjarmasin'],
            ['wilayah' => 'Kota Banjarmasin']
        );

        $pnBjb = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Banjarbaru'],
            ['wilayah' => 'Kota Banjarbaru']
        );

        $pnMtp = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Martapura'],
            ['wilayah' => 'Kabupaten Banjar']
        );

        $pnMrh = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Marabahan'],
            ['wilayah' => 'Kabupaten Barito Kuala']
        );

        $pnKotabaru = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Kotabaru'],
            ['wilayah' => 'Kabupaten Kotabaru']
        );

        $pnPelaihari = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Pelaihari'],
            ['wilayah' => 'Kabupaten Tanah Laut']
        );

        $pnRantau = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Rantau'],
            ['wilayah' => 'Kabupaten Tapin']
        );

        $pnKandangan = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Kandangan'],
            ['wilayah' => 'Kabupaten Hulu Sungai Selatan']
        );

        $pnBarabai = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Barabai'],
            ['wilayah' => 'Kabupaten Hulu Sungai Tengah']
        );

        $pnAmuntai = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Amuntai'],
            ['wilayah' => 'Kabupaten Hulu Sungai Utara']
        );

        $pnTanjung = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Tanjung'],
            ['wilayah' => 'Kabupaten Tabalong']
        );

        $pnBatulicin = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Batulicin'],
            ['wilayah' => 'Kabupaten Tanah Bumbu']
        );

        $pnParingin = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Paringin'],
            ['wilayah' => 'Kabupaten Balangan']
        );

        $jaksa1 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejati->id, 'nama_jaksa' => 'Ahmad Fauzi, S.H., M.H.'],
            ['nip' => '198405122009121002', 'pangkat' => 'Jaksa Madya (IV/a)']
        );

        $jaksa2 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariBjm->id, 'nama_jaksa' => 'Rian Hidayat, S.H.'],
            ['nip' => '198807202012011003', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa3 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariBjb->id, 'nama_jaksa' => 'Nurul Hikmah, S.H., M.H.'],
            ['nip' => '198611142010122001', 'pangkat' => 'Jaksa Muda (III/d)']
        );

        $jaksa4 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariBanjar->id, 'nama_jaksa' => 'Siti Maryam, S.H.'],
            ['nip' => '198904122014022003', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa5 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariBatola->id, 'nama_jaksa' => 'Diana Lestari, S.H.'],
            ['nip' => '199003182015032002', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa6 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariTanahlaut->id, 'nama_jaksa' => 'Hendra Saputra, S.H., M.H.'],
            ['nip' => '198502152010011004', 'pangkat' => 'Jaksa Muda (III/d)']
        );

        $jaksa7 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariTapin->id, 'nama_jaksa' => 'Wahyu Wibowo, S.H.'],
            ['nip' => '198709102011011002', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa8 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariHst->id, 'nama_jaksa' => 'M. Reza Pahlevi, S.H., M.H.'],
            ['nip' => '198304192008121001', 'pangkat' => 'Jaksa Madya (IV/a)']
        );

        $jaksa9 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariHsu->id, 'nama_jaksa' => 'Anisa Rahmawati, S.H.'],
            ['nip' => '199106252015032004', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa10 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariHss->id, 'nama_jaksa' => 'Dedi Kurniawan, S.H.'],
            ['nip' => '198812032014031001', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa11 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariTabalong->id, 'nama_jaksa' => 'Faisal Akbar, S.H., M.H.'],
            ['nip' => '198411082009121003', 'pangkat' => 'Jaksa Muda (III/d)']
        );

        $jaksa12 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariTanbu->id, 'nama_jaksa' => 'Tri Wahyuni, S.H.'],
            ['nip' => '198908172014022005', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa13 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariBalangan->id, 'nama_jaksa' => 'Bagus Prasetyo, S.H.'],
            ['nip' => '199201102016021002', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa14 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariKotabaru->id, 'nama_jaksa' => 'Arif Rahman, S.H., M.H.'],
            ['nip' => '198510202010121002', 'pangkat' => 'Jaksa Muda (III/d)']
        );

        $jaksa15 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejati->id, 'nama_jaksa' => 'Eko Prasetyo, S.H., M.H.'],
            ['nip' => '198203152007011002', 'pangkat' => 'Jaksa Utama Pratama (IV/b)']
        );

        $jaksa16 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariBjm->id, 'nama_jaksa' => 'Linda Kusuma, S.H.'],
            ['nip' => '199105142015022001', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa17 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariBjb->id, 'nama_jaksa' => 'Bambang Sutrisno, S.H.'],
            ['nip' => '198703212011011003', 'pangkat' => 'Jaksa Muda (III/d)']
        );

        $jaksa18 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariBanjar->id, 'nama_jaksa' => 'Dewi Anggraeni, S.H.'],
            ['nip' => '199302182017012002', 'pangkat' => 'Ajun Jaksa Madya (III/a)']
        );

        $jaksa19 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariTanahlaut->id, 'nama_jaksa' => 'Ilham Maulana, S.H.'],
            ['nip' => '198806122013021001', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        $jaksa20 = JaksaPenuntut::firstOrCreate(
            ['instansi_hukum_id' => $kejariTanbu->id, 'nama_jaksa' => 'Rizky Ramadhan, S.H.'],
            ['nip' => '199012052015031002', 'pangkat' => 'Jaksa Pratama (III/c)']
        );

        return [
            'jabatan_ppns' => $jabatanPpns,
            'jabatan_pfm' => $jabatanPfm,
            'jabatan_analis' => $jabatanAnalis,
            'bidang_penindakan' => $bidangPenindakan,
            'bidang_pemeriksaan' => $bidangPemeriksaan,
            'panggol_penata' => $panggolPenata,
            'panggol_penata_tk1' => $panggolPenataTk1,
            'panggol_pembina' => $panggolPembina,
            'pasal_kesehatan_1' => $pasalKesehatan1,
            'pasal_kesehatan_2' => $pasalKesehatan2,
            'pasal_kesehatan_3' => $pasalKesehatan3,
            'pasal_kesehatan_4' => $pasalKesehatan4,
            'pasal_kesehatan_5' => $pasalKesehatan5,
            'pasal_kesehatan_6' => $pasalKesehatan6,
            'pasal_kesehatan_7' => $pasalKesehatan7,
            'pasal_pangan_1' => $pasalPangan1,
            'pasal_pangan_2' => $pasalPangan2,
            'pasal_pangan_3' => $pasalPangan3,
            'pasal_pangan_4' => $pasalPangan4,
            'pasal_pangan_5' => $pasalPangan5,
            'pasal_konsumen_1' => $pasalKonsumen1,
            'pasal_konsumen_2' => $pasalKonsumen2,
            'pasal_konsumen_3' => $pasalKonsumen3,
            'pasal_narkotika_1' => $pasalNarkotika1,
            'pasal_narkotika_2' => $pasalNarkotika2,
            'pasal_psikotropika_1' => $pasalPsikotropika,
            'pasal_kuhp_1' => $pasalKuhp1,
            'pasal_kuhp_2' => $pasalKuhp2,
            'kejati' => $kejati,
            'kejari_bjm' => $kejariBjm,
            'kejari_bjb' => $kejariBjb,
            'kejari_banjar' => $kejariBanjar,
            'kejari_batola' => $kejariBatola,
            'kejari_tanahlaut' => $kejariTanahlaut,
            'kejari_tapin' => $kejariTapin,
            'kejari_hst' => $kejariHst,
            'kejari_hsu' => $kejariHsu,
            'kejari_hss' => $kejariHss,
            'kejari_tabalong' => $kejariTabalong,
            'kejari_tanbu' => $kejariTanbu,
            'kejari_balangan' => $kejariBalangan,
            'kejari_kotabaru' => $kejariKotabaru,
            'pt_bjm' => $ptBjm,
            'pn_bjm' => $pnBjm,
            'pn_bjb' => $pnBjb,
            'pn_mtp' => $pnMtp,
            'pn_mrh' => $pnMrh,
            'pn_kotabaru' => $pnKotabaru,
            'pn_pelaihari' => $pnPelaihari,
            'pn_rantau' => $pnRantau,
            'pn_kandangan' => $pnKandangan,
            'pn_barabai' => $pnBarabai,
            'pn_amuntai' => $pnAmuntai,
            'pn_tanjung' => $pnTanjung,
            'pn_batulicin' => $pnBatulicin,
            'pn_paringin' => $pnParingin,
            'jaksa_1' => $jaksa1,
            'jaksa_2' => $jaksa2,
            'jaksa_3' => $jaksa3,
            'jaksa_4' => $jaksa4,
            'jaksa_5' => $jaksa5,
            'jaksa_6' => $jaksa6,
            'jaksa_7' => $jaksa7,
            'jaksa_8' => $jaksa8,
            'jaksa_9' => $jaksa9,
            'jaksa_10' => $jaksa10,
            'jaksa_11' => $jaksa11,
            'jaksa_12' => $jaksa12,
            'jaksa_13' => $jaksa13,
            'jaksa_14' => $jaksa14,
            'jaksa_15' => $jaksa15,
            'jaksa_16' => $jaksa16,
            'jaksa_17' => $jaksa17,
            'jaksa_18' => $jaksa18,
            'jaksa_19' => $jaksa19,
            'jaksa_20' => $jaksa20,
        ];
    }

    /**
     * Pengguna PPNS tambahan untuk variasi tim penyidik.
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

        $penyidik1 = User::firstOrCreate(
            ['email' => 'penyidik@baptwo.test'],
            [
                'nama' => 'Penyidik PPNS',
                'nip' => '199203032018011003',
                'role' => UserRole::PENYIDIK,
                'jabatan_id' => $master['jabatan_ppns']->id,
                'bidang_id' => $master['bidang_penindakan']->id,
                'panggol_id' => $master['panggol_penata_tk1']->id,
                'alamat_id' => $alamat->id,
                'password' => Hash::make('password'),
                'status' => 'pns',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $penyidik2 = User::firstOrCreate(
            ['email' => 'budi.ppns@baptwo.test'],
            [
                'nama' => 'Budi Hermawan, S.Farm., Apt.',
                'nip' => '198906152014031002',
                'role' => UserRole::PENYIDIK,
                'jabatan_id' => $master['jabatan_ppns']->id,
                'bidang_id' => $master['bidang_penindakan']->id,
                'panggol_id' => $master['panggol_penata_tk1']->id,
                'alamat_id' => $alamat->id,
                'password' => Hash::make('password'),
                'status' => 'pns',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $penyidik3 = User::firstOrCreate(
            ['email' => 'siti.ppns@baptwo.test'],
            [
                'nama' => 'Siti Rahmah, S.H.',
                'nip' => '199308222019022005',
                'role' => UserRole::PENYIDIK,
                'jabatan_id' => $master['jabatan_analis']->id,
                'bidang_id' => $master['bidang_penindakan']->id,
                'panggol_id' => $master['panggol_penata']->id,
                'alamat_id' => $alamat->id,
                'password' => Hash::make('password'),
                'status' => 'pns',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $penyidik4 = User::firstOrCreate(
            ['email' => 'dewi.ppns@baptwo.test'],
            [
                'nama' => 'Dewi Sartika, S.Si., Apt.',
                'nip' => '199104102016012004',
                'role' => UserRole::PENYIDIK,
                'jabatan_id' => $master['jabatan_pfm']->id,
                'bidang_id' => $master['bidang_pemeriksaan']->id,
                'panggol_id' => $master['panggol_penata_tk1']->id,
                'alamat_id' => $alamat->id,
                'password' => Hash::make('password'),
                'status' => 'pns',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        return [
            'penyidik_1' => $penyidik1,
            'penyidik_2' => $penyidik2,
            'penyidik_3' => $penyidik3,
            'penyidik_4' => $penyidik4,
        ];
    }

    /**
     * Menanam 100 perkara dari pra-SPDP sampai putusan pengadilan.
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

        foreach ($rencana as $urutan => $baris) {
            $archetype = $archetypeList[$baris['archetype']];
            $lokasi = $lokasiList[$baris['lokasi']];
            $bucket = $baris['bucket'];

            $tanggalLk = Carbon::create($baris['tahun'], 1, 5)->startOfDay()->addDays($urutan + ($urutan % 3));
            $adaSprindik = ! in_array($bucket, ['lk_draft', 'lk_final'], true);
            $tanggalSprindik = $adaSprindik ? $tanggalLk->copy()->addDays(2) : null;
            $tanggalSpdp = $adaSprindik ? $tanggalSprindik->copy()->addDays(4) : null;

            $detailTkp = $this->susunDetailTkp($archetype, $lokasi, $urutan);
            $uraian = str_replace('{kabupaten}', $lokasi['kab'][1], $archetype['uraian']);

            $adaBerkas = ! in_array($bucket, ['lk_draft', 'lk_final', 'sprindik_draft', 'sprindik_final'], true);

            $tersangkaList = [];
            if ($adaBerkas) {
                for ($orang = 0; $orang < $baris['jumlah_tersangka']; $orang++) {
                    $tersangkaList[] = $this->susunTersangka($master, $lokasi, $archetype, $urutan * 3 + $orang, $orang);
                }
            }

            $tahapan = $tanggalSpdp ? $this->susunTahapan($bucket, $urutan, $tanggalSpdp, $archetype, $lokasi) : [];
            $dpo = $baris['dpo'] ? $this->susunDpo($baris['dpo'], $tanggalSpdp, $urutan) : null;
            $sp3 = $bucket === 'sp3' ? $this->susunSp3($tanggalSpdp->copy()->addDays(20)) : null;

            $ppnsUtama = $penyidik[$urutan % count($penyidik)];
            $ppnsAnggota = [];
            if ($urutan % 5 !== 0) {
                $ppnsAnggota[] = $penyidik[($urutan + 1) % count($penyidik)];
            }
            if ($urutan % 7 === 0) {
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
                    'modus' => $urutan % 6 === 0 ? 'offline' : $archetype['modus'],
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
        }
    }

    /**
     * Rencana 100 perkara: komposisi tahapan, tahun, DPO, SP3, dan jumlah tersangka.
     *
     * @return array<int, array<string, mixed>>
     */
    private function susunRencana(): array
    {
        $distribusi = [
            'lk_draft' => 4,
            'lk_final' => 7,
            'sprindik_draft' => 5,
            'sprindik_final' => 8,
            'perkara_only' => 6,
            'spdp_draft' => 5,
            'spdp' => 9,
            'spdp_p16' => 6,
            'spdp_p16_p17p' => 5,
            'tahap1_draft' => 5,
            'tahap1' => 7,
            'tahap1_p18p_p19' => 5,
            'p21_draft' => 4,
            'p21' => 6,
            'tahap2' => 8,
            'keputusan' => 6,
            'sp3' => 4,
        ];

        $archetypeKeys = array_keys($this->archetypeCatalog());
        $lokasiKeys = array_keys($this->lokasiCatalog());

        $rencana = [];
        $nomor = 0;
        foreach ($distribusi as $bucket => $jumlah) {
            for ($i = 0; $i < $jumlah; $i++) {
                $rencana[] = [
                    'bucket' => $bucket,
                    'idx_bucket' => $i,
                    'archetype' => $archetypeKeys[$nomor % count($archetypeKeys)],
                    'lokasi' => $lokasiKeys[($nomor * 5 + 3) % count($lokasiKeys)],
                    'tahun' => 2026,
                    'tidak_selesai' => false,
                    'jumlah_tersangka' => 1,
                    'dpo' => null,
                ];
                $nomor++;
            }
        }

        // Perkara yang sudah selesai pada tahun sebelumnya
        foreach ([
            ['keputusan', 0, 2024],
            ['keputusan', 1, 2025],
            ['keputusan', 2, 2024],
            ['keputusan', 3, 2025],
            ['tahap2', 0, 2025],
        ] as [$bucket, $idx, $tahun]) {
            foreach ($rencana as $pos => $baris) {
                if ($baris['bucket'] === $bucket && $baris['idx_bucket'] === $idx) {
                    $rencana[$pos]['tahun'] = $tahun;
                    break;
                }
            }
        }

        // Berkas SPDP tahun lalu yang belum selesai (status formal tidak selesai)
        foreach ([
            ['spdp', 0, 2024],
            ['spdp_p16', 0, 2025],
            ['tahap1_draft', 0, 2024],
            ['tahap1', 1, 2025],
            ['p21', 0, 2024],
        ] as [$bucket, $idx, $tahun]) {
            foreach ($rencana as $pos => $baris) {
                if ($baris['bucket'] === $bucket && $baris['idx_bucket'] === $idx) {
                    $rencana[$pos]['tahun'] = $tahun;
                    $rencana[$pos]['tidak_selesai'] = true;
                    break;
                }
            }
        }

        // Indeks baris yang memiliki berkas perkara
        $berkasIdx = [];
        foreach ($rencana as $pos => $baris) {
            if (! in_array($baris['bucket'], ['lk_draft', 'lk_final', 'sprindik_draft', 'sprindik_final'], true)) {
                $berkasIdx[] = $pos;
            }
        }

        // Delapan perkara dengan dua tersangka dan dua perkara dengan tiga tersangka
        foreach ([5, 14, 23, 31, 44, 57, 66, 72] as $posisi) {
            $rencana[$berkasIdx[$posisi]]['jumlah_tersangka'] = 2;
        }
        foreach ([37, 60] as $posisi) {
            $rencana[$berkasIdx[$posisi]]['jumlah_tersangka'] = 3;
        }

        // DPO aktif dan DPO yang sudah dicabut pada berkas berjalan
        $kandidatDpo = array_values(array_filter(
            $berkasIdx,
            fn (int $pos): bool => in_array($rencana[$pos]['bucket'], [
                'spdp', 'spdp_p16', 'spdp_p16_p17p', 'tahap1_draft', 'tahap1',
                'tahap1_p18p_p19', 'p21_draft', 'p21',
            ], true)
        ));

        foreach ([1, 5, 9, 14, 19, 24, 29, 34] as $posisi) {
            $rencana[$kandidatDpo[$posisi]]['dpo'] = 'aktif';
        }
        foreach ([3, 11, 17, 26] as $posisi) {
            $rencana[$kandidatDpo[$posisi]]['dpo'] = 'cabut';
        }

        return $rencana;
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
                'pasal' => ['pasal_kesehatan_1', 'pasal_kesehatan_6', 'pasal_kuhp_2'],
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
                'pasal' => ['pasal_kesehatan_1', 'pasal_kesehatan_5', 'pasal_konsumen_1'],
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
                'pasal' => ['pasal_pangan_1', 'pasal_konsumen_3', 'pasal_kuhp_2'],
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
                'klasifikasi' => 'distributor',
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
                'klasifikasi' => 'distributor',
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
     * Data tersangka unik dan konsisten dengan wilayah TKP.
     *
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $lokasi
     * @param  array<string, mixed>  $archetype
     * @return array<string, mixed>
     */
    private function susunTersangka(array $master, array $lokasi, array $archetype, int $seed, int $orangKe = 0): array
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
            .sprintf('%02d%02d%02d%04d', $hariLahir, $bulanLahir, $tahunLahir % 100, $idx);

        $pasalKeys = $archetype['pasal'];
        if ($orangKe > 0) {
            $pasalKeys[] = 'pasal_kuhp_'.(($idx % 2) + 1);
        }

        return [
            'nama' => $nama,
            'alias' => $idx % 4 === 0 ? ($perempuan ? 'Ibu ' : 'Pak ').$namaBelakang : null,
            'bin' => self::NAMA_BELAKANG[($idx * 11) % count(self::NAMA_BELAKANG)],
            'nik' => $nik,
            'tempat_lahir' => str_replace(['Kota ', 'Kabupaten '], '', $lokasi['kab'][1]),
            'tanggal_lahir' => sprintf('%04d-%02d-%02d', $tahunLahir, $bulanLahir, $hariLahir),
            'jenis_kelamin' => $perempuan ? 'perempuan' : 'laki-laki',
            'agama' => self::AGAMA[$idx % 10],
            'pekerjaan' => $orangKe === 0 ? $archetype['pekerjaan'] : self::PEKERJAAN_UMUM[$idx % count(self::PEKERJAAN_UMUM)],
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
     * Catatan tahapan yang menyebut instansi hukum dan objek perkara.
     *
     * @param  array<string, mixed>  $archetype
     * @param  array<string, mixed>  $lokasi
     */
    private function catatanTahap(string $kode, string $status, array $archetype, array $lokasi, int $urutan): string
    {
        return match ($kode) {
            'spdp' => $status === 'draft'
                ? "Formulir SPDP {$archetype['obyek']} masih disusun sebelum dikirim ke {$lokasi['kejari']}."
                : "Pemberitahuan dimulainya penyidikan {$archetype['obyek']} disampaikan ke {$lokasi['kejari']}.",
            'p16' => "Penunjukan Jaksa Penuntut Umum oleh {$lokasi['kejari']} untuk mengikuti perkembangan penyidikan.",
            'p17p' => "Permintaan perkembangan hasil penyidikan dari {$lokasi['kejari']}.",
            'tahap1' => $status === 'draft'
                ? "Berkas perkara tahap 1 masih dalam penyusunan resume sebelum diserahkan ke {$lokasi['kejari']}."
                : "Penyerahan berkas perkara tahap 1 kepada Jaksa Peneliti {$lokasi['kejari']}.",
            'p18p' => "Pemberitahuan bahwa berkas perkara belum lengkap dari {$lokasi['kejari']}.",
            'p19' => "Petunjuk perbaikan berkas perkara dari Jaksa Penuntut Umum {$lokasi['kejari']}.",
            'p21' => $status === 'draft'
                ? "Berkas perkara dalam proses penelitian oleh Jaksa Penuntut Umum {$lokasi['kejari']}."
                : "Berkas perkara dinyatakan lengkap (P-21) oleh {$lokasi['kejari']}.",
            'tahap2' => "Penyerahan tersangka dan barang bukti berupa {$archetype['barang_bukti']} kepada {$lokasi['kejari']}.",
            'keputusan' => "{$lokasi['pn']}. Terbukti bersalah melakukan tindak pidana {$archetype['obyek']}. Vonis: "
                .self::VONIS[$urutan % count(self::VONIS)]
                .($urutan % 2 === 0 ? '. Putusan berkekuatan hukum tetap.' : '. Terdakwa mengajukan upaya hukum banding.'),
        };
    }

    /**
     * Data penetapan DPO aktif atau DPO yang sudah dicabut.
     *
     * @return array<string, mixed>
     */
    private function susunDpo(string $status, Carbon $tanggalSpdp, int $urutan): array
    {
        $tanggal = $tanggalSpdp->copy()->addDays(25);
        $aktif = $status === 'aktif';

        $data = [
            'nomor_surat' => $this->nomorDpo($tanggal),
            'tanggal' => $tanggal->toDateString(),
            'status' => $aktif ? 'aktif' : 'nonaktif',
            'keterangan' => $aktif
                ? 'Tersangka tidak memenuhi panggilan penyidik sebanyak tiga kali berturut-turut. Penetapan DPO dikoordinasikan dengan kepolisian setempat.'
                : 'Tersangka sempat tidak memenuhi panggilan penyidik saat proses penyidikan berjalan.',
        ];

        if (! $aktif) {
            $data['tanggal_cabut'] = $tanggal->copy()->addDays(9 + ($urutan % 5))->toDateString();
            $data['alasan_pencabutan'] = 'Tersangka berhasil diamankan tim penyidik di luar wilayah kerja, sehingga penetapan DPO dicabut.';
        }

        return $data;
    }

    /**
     * Data penghentian penyidikan (SP3).
     *
     * @return array<string, mixed>
     */
    private function susunSp3(Carbon $tanggal): array
    {
        return [
            'nomor_surat' => $this->nomorSp3($tanggal),
            'tanggal' => $tanggal->toDateString(),
            'keterangan' => 'Penghentian penyidikan karena hasil pengujian laboratorium menunjukkan sampel tidak terbukti mengandung bahan berbahaya sesuai dugaan awal.',
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
        // Alamat TKP
        $alamatTkpNama = $tkpData['nama'] ?? (isset($tkpData['detail']) ? trim(explode(',', (string) $tkpData['detail'])[0]) : null);
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
            'alamat_detail' => $tkpData['detail'],
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

        $hasSp = ! empty($noSp);
        $hasPerkara = $statusPerkara === 'final';
        $isLkFinal = $statusLaporan === 'final';
        $isSpFinal = $hasSp && ($statusSprindik === null || $statusSprindik === 'final');

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
                'tkp_id' => $tkp->id,
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

        // Relasi PPNS Utama & Anggota
        $perkara->ppnsUsers()->syncWithoutDetaching([$ppnsUtama->id => ['peran' => 'utama']]);
        foreach ($ppnsAnggota as $anggota) {
            if ($anggota->id !== $ppnsUtama->id) {
                $perkara->ppnsUsers()->syncWithoutDetaching([$anggota->id => ['peran' => 'anggota']]);
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
                dpoData: $urutan === 0 ? $dpoData : null,
                sp3Data: $urutan === 0 ? $sp3Data : null,
                statusFormalOverride: $statusFormalOverride,
                urutanTersangka: $urutan,
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
        ?string $statusFormalOverride,
        int $urutanTersangka
    ): void {
        // Alamat Tersangka
        $alamatTersangka = Alamat::create([
            'provinsi_kode' => $tkpData['prov'][0],
            'provinsi_nama' => $tkpData['prov'][1],
            'kabupaten_kode' => $tkpData['kab'][0],
            'kabupaten_nama' => $tkpData['kab'][1],
            'kecamatan_kode' => $tkpData['kec'][0],
            'kecamatan_nama' => $tkpData['kec'][1],
            'desa_kode' => $tkpData['kel'][0],
            'desa_nama' => $tkpData['kel'][1],
            'alamat_detail' => 'Tempat tinggal tersangka di '.$tkpData['kel'][1],
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

        // Nomor Berkas Perkara mengikuti nomor LK, diberi akhiran bila tersangka lebih dari satu
        $nomorBerkas = $urutanTersangka === 0 ? $noLk : $noLk.'/T'.($urutanTersangka + 1);

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

        // Hubungkan Pasal berlapis
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
