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
    /**
     * Jalankan seeder 20 data dummy lengkap dan beragam untuk seluruh alur BAPTwo.
     */
    public function run(): void
    {
        // 1. Pastikan sample PDF privat tersedia
        $this->ensureSamplePdf();

        // 2. Bersihkan data dummy lama yang sudah usang jika ada di database
        $this->cleanupObsoleteDummyCases();

        // 3. Siapkan Master Data Lengkap
        $master = $this->seedMasterData();

        // 4. Siapkan Pengguna Tambahan (PPNS)
        $users = $this->seedUsers($master);

        // 5. Siapkan 20 Data Perkara Beragam (Pra-SPDP s.d. Putusan, DPO, SP3, SLA)
        $this->seedTwentyCases($master, $users);
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
     * Membersihkan nomor perkara dummy lama yang tidak terpakai agar total perkara tepat 20.
     */
    private function cleanupObsoleteDummyCases(): void
    {
        $obsoleteCases = Perkara::where('nomor_laporan_kejadian', 'like', 'LK/%')->get();

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

        $pasalPangan = Pasal::firstOrCreate(
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

        $pasalKonsumen = Pasal::firstOrCreate(
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
            'pasal_pangan_1' => $pasalPangan,
            'pasal_pangan_2' => $pasalPangan2,
            'pasal_pangan_3' => $pasalPangan3,
            'pasal_pangan_4' => $pasalPangan4,
            'pasal_pangan_5' => $pasalPangan5,
            'pasal_konsumen_1' => $pasalKonsumen,
            'pasal_konsumen_2' => $pasalKonsumen2,
            'pasal_konsumen_3' => $pasalKonsumen3,
            'pasal_narkotika_1' => $pasalNarkotika1,
            'pasal_narkotika_2' => $pasalNarkotika2,
            'pasal_psikotropika_1' => $pasalPsikotropika,
            'pasal_kuhp_1' => $pasalKuhp1,
            'pasal_kuhp_2' => $pasalKuhp2,
            'pasal_pangan' => $pasalPangan,
            'pasal_konsumen' => $pasalKonsumen,
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
     * Menanam 20 data perkara lengkap dengan variasi tahapan dari pra-SPDP hingga Putusan, DPO, dan SP3.
     *
     * @param  array<string, mixed>  $master
     * @param  array<string, User>  $users
     */
    private function seedTwentyCases(array $master, array $users): void
    {
        $actor = $users['penyidik_1'];

        // =========================================================================
        // KASUS 1: Pra-SPDP — Laporan Kejadian Baru (Draft)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/001/I/2026/BPOM',
            tglLk: '2026-01-08',
            uraian: 'Dugaan peredaran sediaan farmasi kosmetik racikan pemutih tanpa izin edar yang mengandung bahan berbahaya merkuri pada kios kosmetik di pasar tradisional.',
            noSp: null,
            tglSp: null,
            statusPerkara: null,
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6371', 'Kota Banjarmasin'],
                'kec' => ['637103', 'Banjarmasin Tengah'],
                'kel' => ['6371031001', 'Kertak Baru Ilir'],
                'detail' => 'Kios Kosmetik Ayu Cantik, Blok B No. 14 Pasar Sentra Antasari',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [],
            tersangkaData: null,
            tahapanChain: [],
            statusLaporan: 'draft'
        );

        // =========================================================================
        // KASUS 2: Pra-SPDP — Laporan Kejadian Final (Belum Sprindik)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/002/I/2026/BPOM',
            tglLk: '2026-01-15',
            uraian: 'Temuan peredaran produk pangan olahan kadaluwarsa yang dikemas ulang (repacking) dengan stiker label tanggal kadaluwarsa palsu pada gudang distributor sembako.',
            noSp: null,
            tglSp: null,
            statusPerkara: null,
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6372', 'Kota Banjarbaru'],
                'kec' => ['637201', 'Landasan Ulin'],
                'kel' => ['6372011003', 'Guntung Payung'],
                'detail' => 'Gudang Sembako Berkah Mandiri, Jl. Ahmad Yani Km. 31 No. 8',
                'klasifikasi' => 'distributor',
                'legalitas' => 'legal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_2'],
            ppnsAnggota: [$users['penyidik_3']],
            tersangkaData: null,
            tahapanChain: []
        );

        // =========================================================================
        // KASUS 3: Pra-SPDP — Sprindik Tersedia (Draft Sprindik)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/003/I/2026/BPOM',
            tglLk: '2026-01-20',
            uraian: 'Penyelidikan peredaran jamu tradisional penambah stamina pria kemasan botol tanpa izin edar yang diduga dicampur bahan kimia obat sildenafil.',
            noSp: 'SP.Sidik/001/I/2026/BPOM',
            tglSp: '2026-01-22',
            statusPerkara: null,
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6303', 'Kabupaten Banjar'],
                'kec' => ['630302', 'Kertak Hanyar'],
                'kel' => ['6303022003', 'Manarap Baru'],
                'detail' => 'Toko Jamu Sehat Alami, Jl. A. Yani Km. 8 No. 12 Kertak Hanyar',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_3'],
            ppnsAnggota: [$users['penyidik_1']],
            tersangkaData: null,
            tahapanChain: [],
            statusSprindik: 'draft'
        );

        // =========================================================================
        // KASUS 4: Pra-SPDP — Sprindik Final (Belum Dibuat Data Perkara)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/004/I/2026/BPOM',
            tglLk: '2026-01-25',
            uraian: 'Pemasukan dan peredaran suplemen fitnes impor tanpa nomor izin edar BPOM RI melalui jalur jasa titip pengiriman ekspedisi antar-pulau.',
            noSp: 'SP.Sidik/002/I/2026/BPOM',
            tglSp: '2026-01-28',
            statusPerkara: null,
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6304', 'Kabupaten Barito Kuala'],
                'kec' => ['630401', 'Alalak'],
                'kel' => ['6304011002', 'Handil Bakti'],
                'detail' => 'Ruko Komplek Semangat Dalam Blok A No. 5 Handil Bakti',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'online',
            ],
            ppnsUtama: $users['penyidik_4'],
            ppnsAnggota: [$users['penyidik_2']],
            tersangkaData: null,
            tahapanChain: []
        );

        // =========================================================================
        // KASUS 5: Data Perkara Terbentuk — Belum SPDP (Progress 0%, Status: Baru)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/005/II/2026/BPOM',
            tglLk: '2026-02-02',
            uraian: 'Peracikan dan peredaran jamu kuat tradisional ilegal yang mengandung bahan kimia obat (BKO) Sildenafil Citrate tanpa izin edar di Pelaihari.',
            noSp: 'SP.Sidik/003/II/2026/BPOM',
            tglSp: '2026-02-05',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6301', 'Kabupaten Tanah Laut'],
                'kec' => ['630104', 'Pelaihari'],
                'kel' => ['6301041002', 'Angsau'],
                'detail' => 'Depot Jamu Segar Bugar Sehat, Jl. Kemakmuran No. 18 Pelaihari',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [$users['penyidik_3']],
            tersangkaData: [
                'nama' => 'Hendra Wijaya',
                'alias' => 'Koh Hendra',
                'bin' => 'Suryanto Wijaya',
                'nik' => '6301041204820003',
                'tempat_lahir' => 'Pelaihari',
                'tanggal_lahir' => '1982-04-12',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'buddha',
                'pekerjaan' => 'Wiraswasta',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: []
        );

        // =========================================================================
        // KASUS 6: SPDP — Status Draft (Progress 0%, Status: Baru)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/006/II/2026/BPOM',
            tglLk: '2026-02-08',
            uraian: 'Peredaran obat tradisional dalam bentuk kapsul penurun asam urat mengandung deksametason tanpa izin edar pada kios jamu di Rantau.',
            noSp: 'SP.Sidik/004/II/2026/BPOM',
            tglSp: '2026-02-10',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6305', 'Kabupaten Tapin'],
                'kec' => ['630504', 'Tapin Utara'],
                'kel' => ['6305041001', 'Rantau Kiwa'],
                'detail' => 'Kios Herbal Berkah, Jl. Brigjend H. Hasan Basry No. 11 Rantau',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_2'],
            ppnsAnggota: [$users['penyidik_4']],
            tersangkaData: [
                'nama' => 'M. Ramli',
                'alias' => 'Ramli',
                'bin' => 'H. Abdullah',
                'nik' => '6305041506840002',
                'tempat_lahir' => 'Rantau',
                'tanggal_lahir' => '1984-06-15',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Pedagang Jamu',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/001/II/2026/BPOM',
                    'tanggal' => '2026-02-14',
                    'status' => 'draft',
                    'catatan' => 'Formulir SPDP dalam penyusunan data surat pemberitahuan ke Kejaksaan Negeri Tapin.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 7: SPDP — Selesai Difinalkan (Progress 15%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/007/II/2026/BPOM',
            tglLk: '2026-02-12',
            uraian: 'Peredaran gelap sediaan farmasi obat keras daftar G jenis Tramadol dan Hexymer tanpa kewenangan secara daring di Tanjung.',
            noSp: 'SP.Sidik/005/II/2026/BPOM',
            tglSp: '2026-02-14',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6309', 'Kabupaten Tabalong'],
                'kec' => ['630904', 'Murung Pudak'],
                'kel' => ['6309041002', 'Belimbing'],
                'detail' => 'Rumah Kontrakan Titik Drop Pengiriman, Jl. Pertamina No. 27 Murung Pudak',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'online',
            ],
            ppnsUtama: $users['penyidik_3'],
            ppnsAnggota: [$users['penyidik_1']],
            tersangkaData: [
                'nama' => 'Faisal Rahman',
                'alias' => 'Ical',
                'bin' => 'Rahman Hakim',
                'nik' => '6309041808960001',
                'tempat_lahir' => 'Tanjung',
                'tanggal_lahir' => '1996-08-18',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Karyawan Swasta',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/002/II/2026/BPOM',
                    'tanggal' => '2026-02-18',
                    'status' => 'final',
                    'catatan' => 'Pemberitahuan dimulainya penyidikan telah disampaikan secara resmi ke Kejaksaan Negeri Tabalong.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 8: SPDP — Final + Sub-Tahap P-16 (Progress 15%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/008/II/2026/BPOM',
            tglLk: '2026-02-16',
            uraian: 'Peredaran produk kosmetik krim pemutih wajah bermerkuri tanpa nomor notifikasi BPOM secara live streaming media sosial di Barabai.',
            noSp: 'SP.Sidik/006/II/2026/BPOM',
            tglSp: '2026-02-18',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6307', 'Kabupaten Hulu Sungai Tengah'],
                'kec' => ['630706', 'Barabai'],
                'kel' => ['6307061002', 'Barabai Barat'],
                'detail' => 'Ruko Beauty Shop, Jl. Murakata No. 34 Barabai',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'online',
            ],
            ppnsUtama: $users['penyidik_4'],
            ppnsAnggota: [$users['penyidik_2']],
            tersangkaData: [
                'nama' => 'Siti Nurhaliza',
                'alias' => 'Liza Beauty',
                'bin' => 'H. Mansyur',
                'nik' => '6307066403950003',
                'tempat_lahir' => 'Barabai',
                'tanggal_lahir' => '1995-03-24',
                'jenis_kelamin' => 'perempuan',
                'agama' => 'islam',
                'pekerjaan' => 'Wiraswasta',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_konsumen']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/003/II/2026/BPOM',
                    'tanggal' => '2026-02-22',
                    'status' => 'final',
                    'catatan' => 'SPDP dikirimkan ke Kejari Hulu Sungai Tengah.',
                ],
                [
                    'jenis' => JenisTahap::P16,
                    'nomor' => 'B-108/O.3.15/Fd.1/02/2026',
                    'tanggal' => '2026-02-26',
                    'status' => 'final',
                    'catatan' => 'Surat perintah penunjukan Jaksa Penuntut Umum Kejari Hulu Sungai Tengah untuk mengikuti perkembangan penyidikan.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 9: SPDP — Final + Sub-Tahap P-16 & P-17P (Progress 15%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/009/II/2026/BPOM',
            tglLk: '2026-02-20',
            uraian: 'Distribusi pangan olahan daging beku impor tanpa izin edar BPOM dan tanpa sertifikasi halal pada cold storage di Amuntai.',
            noSp: 'SP.Sidik/007/II/2026/BPOM',
            tglSp: '2026-02-23',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6308', 'Kabupaten Hulu Sungai Utara'],
                'kec' => ['630804', 'Amuntai Tengah'],
                'kel' => ['6308041003', 'Kebun Sari'],
                'detail' => 'Cold Storage Daging Beku, Jl. Basuki Rahmat No. 77 Amuntai',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [$users['penyidik_3']],
            tersangkaData: [
                'nama' => 'Ridwansyah',
                'alias' => 'Iwan',
                'bin' => 'Syarkawi',
                'nik' => '6308041011830004',
                'tempat_lahir' => 'Amuntai',
                'tanggal_lahir' => '1983-11-10',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Pedagang',
                'pasals' => [$master['pasal_pangan'], $master['pasal_konsumen']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/004/II/2026/BPOM',
                    'tanggal' => '2026-02-27',
                    'status' => 'final',
                    'catatan' => 'SPDP dikirimkan ke Kejari Hulu Sungai Utara.',
                ],
                [
                    'jenis' => JenisTahap::P16,
                    'nomor' => 'B-125/O.3.16/Fd.1/03/2026',
                    'tanggal' => '2026-03-03',
                    'status' => 'final',
                    'catatan' => 'Penerimaan surat penunjukan JPU Kejaksaan Negeri HSU.',
                ],
                [
                    'jenis' => JenisTahap::P17P,
                    'nomor' => 'B-167/O.3.16/Fd.1/03/2026',
                    'tanggal' => '2026-03-17',
                    'status' => 'final',
                    'catatan' => 'Penerimaan surat permintaan perkembangan penyidikan perkara dari Kejaksaan Negeri HSU.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 10: Tahap 1 — Status Draft (Progress 15%, Status: SPDP)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/010/II/2026/BPOM',
            tglLk: '2026-02-22',
            uraian: 'Peredaran obat tradisional sirup masuk angin herbal palsu dalam kemasan sachet skala eceran pada warung-warung di Paringin.',
            noSp: 'SP.Sidik/008/II/2026/BPOM',
            tglSp: '2026-02-25',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6311', 'Kabupaten Balangan'],
                'kec' => ['631102', 'Paringin'],
                'kel' => ['6311021001', 'Batu Piring'],
                'detail' => 'Warung Sembako Berkah Mandiri, Jl. A. Yani No. 19 Paringin',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_2'],
            ppnsAnggota: [$users['penyidik_4']],
            tersangkaData: [
                'nama' => 'Akhmad Zaini',
                'alias' => 'Zaini',
                'bin' => 'M. Rusli',
                'nik' => '6311020509890002',
                'tempat_lahir' => 'Paringin',
                'tanggal_lahir' => '1989-09-05',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Pedagang',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/005/II/2026/BPOM',
                    'tanggal' => '2026-03-01',
                    'status' => 'final',
                    'catatan' => 'SPDP diterbitkan dan dikirimkan ke Kejari Balangan.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/001/III/2026/BPOM',
                    'tanggal' => '2026-03-12',
                    'status' => 'draft',
                    'catatan' => 'Berkas perkara Tahap 1 sedang dalam penyusunan resume perkara sebelum pelimpahan fisik.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 11: Tahap 1 — Selesai Difinalkan (Progress 55%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/011/II/2026/BPOM',
            tglLk: '2026-02-25',
            uraian: 'Pabrikasi rumahan obat tradisional cair dalam botol tanpa higienitas standar dan tidak memiliki izin edar di Kandangan.',
            noSp: 'SP.Sidik/009/II/2026/BPOM',
            tglSp: '2026-02-27',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6306', 'Kabupaten Hulu Sungai Selatan'],
                'kec' => ['630604', 'Kandangan'],
                'kel' => ['6306041001', 'Kandangan Kota'],
                'detail' => 'Rumah Produksi Jamu Tradisional, Jl. Pangeran Antasari No. 55 Kandangan',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_3'],
            ppnsAnggota: [$users['penyidik_1']],
            tersangkaData: [
                'nama' => 'Drs. H. Mulyadi',
                'alias' => 'Haji Mul',
                'bin' => 'H. Marwan',
                'nik' => '6306041407760001',
                'tempat_lahir' => 'Kandangan',
                'tanggal_lahir' => '1976-07-14',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Wiraswasta',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/006/III/2026/BPOM',
                    'tanggal' => '2026-03-03',
                    'status' => 'final',
                    'catatan' => 'SPDP dikirimkan ke Kejaksaan Negeri HSS.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/002/III/2026/BPOM',
                    'tanggal' => '2026-03-16',
                    'status' => 'final',
                    'catatan' => 'Penyerahan berkas perkara tahap 1 kepada Jaksa Peneliti Kejaksaan Negeri Hulu Sungai Selatan.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 12: Tahap 1 — Final + Sub-Tahap P-18P & P-19 (Progress 55%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/012/II/2026/BPOM',
            tglLk: '2026-02-28',
            uraian: 'Penyelundupan kosmetik pemutih ilegal mengandung hidrokuinon dan tretinoin dosis tinggi melalui pelabuhan feri Batulicin.',
            noSp: 'SP.Sidik/010/III/2026/BPOM',
            tglSp: '2026-03-02',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6310', 'Kabupaten Tanah Bumbu'],
                'kec' => ['631001', 'Batulicin'],
                'kel' => ['6310011001', 'Batulicin'],
                'detail' => 'Komp. Pergudangan Pelabuhan Samudera Batulicin Blok C No. 9',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_4'],
            ppnsAnggota: [$users['penyidik_2']],
            tersangkaData: [
                'nama' => 'Bambang Sujatmiko',
                'alias' => 'Bambang',
                'bin' => 'Sujatmiko',
                'nik' => '6310010903790001',
                'tempat_lahir' => 'Batulicin',
                'tanggal_lahir' => '1979-03-09',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Wiraswasta',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_konsumen']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/007/III/2026/BPOM',
                    'tanggal' => '2026-03-06',
                    'status' => 'final',
                    'catatan' => 'SPDP disampaikan ke Kejari Tanah Bumbu.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/003/III/2026/BPOM',
                    'tanggal' => '2026-03-18',
                    'status' => 'final',
                    'catatan' => 'Pelimpahan berkas perkara tahap 1 ke Jaksa Peneliti.',
                ],
                [
                    'jenis' => JenisTahap::P18P,
                    'nomor' => 'B-240/O.3.21/Fd.1/03/2026',
                    'tanggal' => '2026-03-25',
                    'status' => 'final',
                    'catatan' => 'Pemberitahuan hasil penyidikan berkas perkara belum lengkap dari Kejari Tanah Bumbu.',
                ],
                [
                    'jenis' => JenisTahap::P19,
                    'nomor' => 'B-258/O.3.21/Fd.1/04/2026',
                    'tanggal' => '2026-04-02',
                    'status' => 'final',
                    'catatan' => 'Petunjuk jaksa P-19 untuk melengkapi uji laboratorium kuantitatif zat aktif.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 13: P-21 — Selesai Lengkap (Progress 85%, Siap Tahap II, SLA Berjalan)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/013/III/2026/BPOM',
            tglLk: '2026-03-05',
            uraian: 'Peredaran obat tradisional stamina pria ilegal tanpa izin edar asal luar pulau di toko obat Kabupaten Kotabaru.',
            noSp: 'SP.Sidik/011/III/2026/BPOM',
            tglSp: '2026-03-07',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6302', 'Kabupaten Kotabaru'],
                'kec' => ['630209', 'Pulau Laut Utara'],
                'kel' => ['6302091002', 'Kotabaru Hilir'],
                'detail' => 'Toko Obat Sumber Rejeki, Jl. Putri Cipta Sari No. 40 Kotabaru',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [$users['penyidik_4']],
            tersangkaData: [
                'nama' => 'Irwan Maulana',
                'alias' => 'Pak Irwan',
                'bin' => 'H. Maulana',
                'nik' => '6302091402740002',
                'tempat_lahir' => 'Kotabaru',
                'tanggal_lahir' => '1974-02-14',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Pedagang Obat',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/008/III/2026/BPOM',
                    'tanggal' => '2026-03-12',
                    'status' => 'final',
                    'catatan' => 'SPDP diterbitkan dan dikirimkan ke Kejari Kotabaru.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/004/III/2026/BPOM',
                    'tanggal' => '2026-03-24',
                    'status' => 'final',
                    'catatan' => 'Penyerahan berkas Tahap 1 ke Jaksa.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-385/O.3.12/Fd.1/04/2026',
                    'tanggal' => '2026-04-04',
                    'status' => 'final',
                    'catatan' => 'Surat pemberitahuan berkas perkara dinyatakan lengkap (P-21). Siap penyerahan Tahap II.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 14: P-21 Final + Tahap II Draft (Progress 85%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/014/III/2026/BPOM',
            tglLk: '2026-03-08',
            uraian: 'Peredaran obat keras daftar G jenis Tramadol dan Trihexyphenidyl tanpa izin apotek di Banjarmasin Selatan.',
            noSp: 'SP.Sidik/012/III/2026/BPOM',
            tglSp: '2026-03-10',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6371', 'Kota Banjarmasin'],
                'kec' => ['637101', 'Banjarmasin Selatan'],
                'kel' => ['6371011005', 'Kelayan Dalam'],
                'detail' => 'Kios Jamu Jl. Kelayan B No. 89 Banjarmasin Selatan',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_2'],
            ppnsAnggota: [$users['penyidik_3']],
            tersangkaData: [
                'nama' => 'M. Rizky Pratama',
                'alias' => 'Kiki',
                'bin' => 'Mansyur Pratama',
                'nik' => '6371012409970001',
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1997-09-24',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Karyawan Swasta',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/009/III/2026/BPOM',
                    'tanggal' => '2026-03-14',
                    'status' => 'final',
                    'catatan' => 'SPDP dikirimkan ke Kejari Banjarmasin.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/005/III/2026/BPOM',
                    'tanggal' => '2026-03-25',
                    'status' => 'final',
                    'catatan' => 'Penyerahan berkas Tahap 1.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-402/O.3.10/Fd.1/04/2026',
                    'tanggal' => '2026-04-06',
                    'status' => 'final',
                    'catatan' => 'P-21 terbit dari Kejari Banjarmasin.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_2,
                    'nomor' => 'BA.T2/001/IV/2026/BPOM',
                    'tanggal' => '2026-04-10',
                    'status' => 'draft',
                    'catatan' => 'Draft berita acara pelimpahan tersangka dan barang bukti obat keras sedang dipersiapkan.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 15: Tahap II — Selesai Pelimpahan (Progress 100%, Status: Selesai)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/015/III/2026/BPOM',
            tglLk: '2026-03-10',
            uraian: 'Penyelundupan kosmetik pemutih ilegal mengandung merkuri pada klinik kecantikan di Banjarbaru.',
            noSp: 'SP.Sidik/013/III/2026/BPOM',
            tglSp: '2026-03-12',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6372', 'Kota Banjarbaru'],
                'kec' => ['637202', 'Banjarbaru Utara'],
                'kel' => ['6372021003', 'Mentaos'],
                'detail' => 'Klinik Cantik Alami, Jl. P. Suriansyah No. 18 Banjarbaru Utara',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_3'],
            ppnsAnggota: [$users['penyidik_1']],
            tersangkaData: [
                'nama' => 'drg. Maya Rosita',
                'alias' => 'Dokter Maya',
                'bin' => 'Syamsul Bachri',
                'nik' => '6372025209930001',
                'tempat_lahir' => 'Banjarbaru',
                'tanggal_lahir' => '1993-09-12',
                'jenis_kelamin' => 'perempuan',
                'agama' => 'islam',
                'pekerjaan' => 'Praktisi Estetika',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/010/III/2026/BPOM',
                    'tanggal' => '2026-03-15',
                    'status' => 'final',
                    'catatan' => 'SPDP dikirimkan ke Kejari Banjarbaru.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/006/III/2026/BPOM',
                    'tanggal' => '2026-03-24',
                    'status' => 'final',
                    'catatan' => 'Pelimpahan berkas perkara tahap 1 tanpa pengembalian.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-450/O.3.14/Fd.1/04/2026',
                    'tanggal' => '2026-04-03',
                    'status' => 'final',
                    'catatan' => 'P-21 dinyatakan lengkap oleh JPU Kejari Banjarbaru.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_2,
                    'nomor' => 'BA.T2/002/IV/2026/BPOM',
                    'tanggal' => '2026-04-09',
                    'status' => 'final',
                    'catatan' => 'Penyerahan tersangka drg. Maya Rosita dan barang bukti 450 pot krim racikan ke Kejaksaan Negeri Banjarbaru.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 16: Putusan Pengadilan — PN Martapura (Progress 100%, Vonis Inkrah)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/016/III/2026/BPOM',
            tglLk: '2026-03-01',
            uraian: 'Penyalahgunaan izin usaha apotek menjual obat keras sediaan psikotropika dan OOT daftar G tanpa resep di Kabupaten Banjar.',
            noSp: 'SP.Sidik/014/III/2026/BPOM',
            tglSp: '2026-03-03',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6303', 'Kabupaten Banjar'],
                'kec' => ['630303', 'Martapura'],
                'kel' => ['6303031002', 'Jawa'],
                'detail' => 'Apotek Sehat Farma, Jl. Sekumpul Raya No. 99 Martapura',
                'klasifikasi' => 'retailer',
                'legalitas' => 'legal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [$users['penyidik_2'], $users['penyidik_4']],
            tersangkaData: [
                'nama' => 'Hendra Gunawan, A.Md.Farm.',
                'alias' => 'Hendra',
                'bin' => 'Syarkawi Gunawan',
                'nik' => '6303031005880005',
                'tempat_lahir' => 'Martapura',
                'tanggal_lahir' => '1988-05-10',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Tenaga Teknis Kefarmasian',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/011/III/2026/BPOM',
                    'tanggal' => '2026-03-05',
                    'status' => 'final',
                    'catatan' => 'SPDP ke Kejaksaan Negeri Kabupaten Banjar.',
                ],
                [
                    'jenis' => JenisTahap::P16,
                    'nomor' => 'B-201/O.3.13/Fd.1/03/2026',
                    'tanggal' => '2026-03-08',
                    'status' => 'final',
                    'catatan' => 'Penunjukan JPU Kejaksaan Negeri Banjar.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/007/III/2026/BPOM',
                    'tanggal' => '2026-03-15',
                    'status' => 'final',
                    'catatan' => 'Pelimpahan berkas perkara tahap 1.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-510/O.3.13/Fd.1/03/2026',
                    'tanggal' => '2026-03-24',
                    'status' => 'final',
                    'catatan' => 'P-21 dinyatakan lengkap oleh JPU.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_2,
                    'nomor' => 'BA.T2/003/III/2026/BPOM',
                    'tanggal' => '2026-03-29',
                    'status' => 'final',
                    'catatan' => 'Penyerahan tersangka dan barang bukti 2.000 butir obat keras ke Kejari Banjar.',
                ],
                [
                    'jenis' => JenisTahap::KEPUTUSAN_PENGADILAN,
                    'nomor' => 'Putusan PN No. 45/Pid.Sus/2026/PN.Mtp',
                    'tanggal' => '2026-04-18',
                    'status' => 'final',
                    'catatan' => 'Pengadilan Negeri Martapura. Terbukti bersalah melanggar Pasal 435 jo. Pasal 138 ayat (2) UU Kesehatan. Vonis: 2 tahun penjara dan denda Rp 150.000.000 subsider 4 bulan kurungan (Inkrah).',
                ],
            ]
        );

        // =========================================================================
        // KASUS 17: Putusan Pengadilan — PN Marabahan (Progress 100%, Vonis Inkrah)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/017/III/2026/BPOM',
            tglLk: '2026-03-04',
            uraian: 'Pemasukan dan pengedaran produk makanan ringan olahan impor tanpa izin edar skala besar di Barito Kuala.',
            noSp: 'SP.Sidik/015/III/2026/BPOM',
            tglSp: '2026-03-06',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6304', 'Kabupaten Barito Kuala'],
                'kec' => ['630403', 'Marabahan'],
                'kel' => ['6304031001', 'Marabahan Kota'],
                'detail' => 'Gudang Snack Impor, Jl. Veteran No. 5 Marabahan Kota',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_4'],
            ppnsAnggota: [$users['penyidik_1']],
            tersangkaData: [
                'nama' => 'Dewi Anggraini',
                'alias' => 'Ibu Dewi',
                'bin' => 'H. Mahfud',
                'nik' => '6304035511890002',
                'tempat_lahir' => 'Marabahan',
                'tanggal_lahir' => '1989-11-15',
                'jenis_kelamin' => 'perempuan',
                'agama' => 'islam',
                'pekerjaan' => 'Pedagang',
                'pasals' => [$master['pasal_pangan'], $master['pasal_konsumen']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/012/III/2026/BPOM',
                    'tanggal' => '2026-03-09',
                    'status' => 'final',
                    'catatan' => 'SPDP dikirimkan ke Kejari Barito Kuala.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/008/III/2026/BPOM',
                    'tanggal' => '2026-03-20',
                    'status' => 'final',
                    'catatan' => 'Pelimpahan berkas Tahap 1.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-530/O.3.19/Fd.1/03/2026',
                    'tanggal' => '2026-03-28',
                    'status' => 'final',
                    'catatan' => 'P-21 dinyatakan lengkap oleh Jaksa.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_2,
                    'nomor' => 'BA.T2/004/IV/2026/BPOM',
                    'tanggal' => '2026-04-05',
                    'status' => 'final',
                    'catatan' => 'Pelimpahan tersangka dan barang bukti 200 dus pangan impor tanpa izin edar ke Kejari Barito Kuala.',
                ],
                [
                    'jenis' => JenisTahap::KEPUTUSAN_PENGADILAN,
                    'nomor' => 'Putusan PN No. 28/Pid.Sus/2026/PN.Mrh',
                    'tanggal' => '2026-04-25',
                    'status' => 'final',
                    'catatan' => 'Pengadilan Negeri Marabahan. Terbukti bersalah melanggar Pasal 142 UU Pangan. Vonis: 1 tahun 4 bulan penjara dan denda Rp 100.000.000 subsider 2 bulan kurungan (Inkrah).',
                ],
            ]
        );

        // =========================================================================
        // KASUS 18: DPO Aktif (Buron di Tahap II, Gate Tahap II Terkunci)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/018/III/2026/BPOM',
            tglLk: '2026-03-12',
            uraian: 'Penyelundupan kosmetik impor ilegal dari luar pulau sindikat lintas provinsi melalui jalur darat Tanjung - Balikpapan.',
            noSp: 'SP.Sidik/016/III/2026/BPOM',
            tglSp: '2026-03-15',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6309', 'Kabupaten Tabalong'],
                'kec' => ['630903', 'Tanjung'],
                'kel' => ['6309031002', 'Tanjung'],
                'detail' => 'Komp. Ruko Ekspedisi Antar-Provinsi, Jl. Ir. P.H.M. Noor No. 88 Tanjung',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_2'],
            ppnsAnggota: [$users['penyidik_1']],
            tersangkaData: [
                'nama' => 'Johan Kurniawan',
                'alias' => 'Ko Johan',
                'bin' => 'Kurniawan',
                'nik' => '6309031806850001',
                'tempat_lahir' => 'Tanjung',
                'tanggal_lahir' => '1985-06-18',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'kristen',
                'pekerjaan' => 'Wiraswasta',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/013/III/2026/BPOM',
                    'tanggal' => '2026-03-18',
                    'status' => 'final',
                    'catatan' => 'SPDP diterbitkan.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/009/III/2026/BPOM',
                    'tanggal' => '2026-03-29',
                    'status' => 'final',
                    'catatan' => 'Penyerahan berkas perkara Tahap 1.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-580/O.3.17/Fd.1/04/2026',
                    'tanggal' => '2026-04-08',
                    'status' => 'final',
                    'catatan' => 'P-21 terbit dari Jaksa Penuntut Umum Kejari Tabalong.',
                ],
            ],
            dpoData: [
                'nomor_surat' => 'DPO/001/IV/2026/BPOM',
                'tanggal' => '2026-04-12',
                'keterangan' => 'Tersangka melarikan diri saat pemanggilan resmi pelaksanaan Tahap II sebanyak 3 kali berturut-turut. Diterbitkan DPO koordinasi Polres Tabalong dan Polda Kalsel.',
            ]
        );

        // =========================================================================
        // KASUS 19: DPO Dicabut (Nonaktif, Berhasil Diamankan, Lanjut P-21)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/019/III/2026/BPOM',
            tglLk: '2026-03-15',
            uraian: 'Peredaran suplemen pangan diet impor tanpa izin edar BPOM berlabel palsu via toko online di Banjarmasin.',
            noSp: 'SP.Sidik/017/III/2026/BPOM',
            tglSp: '2026-03-17',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6371', 'Kota Banjarmasin'],
                'kec' => ['637104', 'Banjarmasin Utara'],
                'kel' => ['6371041002', 'Alalak Utara'],
                'detail' => 'Gudang Paket Ekspedisi, Jl. Alalak Utara No. 44 Banjarmasin',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'online',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [$users['penyidik_2'], $users['penyidik_3']],
            tersangkaData: [
                'nama' => 'Rudi Hermanto, S.E.',
                'alias' => 'Koh Rudi',
                'bin' => 'Hermanto',
                'nik' => '6371042808850002',
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1985-08-28',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'kristen',
                'pekerjaan' => 'Pemilik Toko Online',
                'pasals' => [$master['pasal_pangan'], $master['pasal_konsumen']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/014/III/2026/BPOM',
                    'tanggal' => '2026-03-20',
                    'status' => 'final',
                    'catatan' => 'Pemberitahuan SPDP ke Kejari Banjarmasin.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/010/III/2026/BPOM',
                    'tanggal' => '2026-03-28',
                    'status' => 'final',
                    'catatan' => 'Penyerahan berkas perkara tahap 1 ke Jaksa Peneliti.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-610/O.3.10/Fd.1/04/2026',
                    'tanggal' => '2026-04-14',
                    'status' => 'final',
                    'catatan' => 'Berkas perkara dinyatakan lengkap (P-21) oleh Kejari Banjarmasin setelah tersangka diamankan kembali.',
                ],
            ],
            dpoData: [
                'nomor_surat' => 'DPO/002/IV/2026/BPOM',
                'tanggal' => '2026-04-01',
                'tanggal_cabut' => '2026-04-10',
                'status' => 'nonaktif',
                'keterangan' => 'Tersangka sempat melarikan diri ke luar kota saat proses Tahap 1.',
                'alasan_pencabutan' => 'Tersangka berhasil diamankan tim intelijen dan penindakan BBPOM di Banjarmasin di persembunyiannya. Status DPO resmi dicabut.',
            ]
        );

        // =========================================================================
        // KASUS 20: SP3 Terbit (Penghentian Penyidikan, Status Formal: Tidak Selesai)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/020/III/2026/BPOM',
            tglLk: '2026-03-18',
            uraian: 'Penyelidikan dugaan peredaran obat tradisional stamina pria yang dicurigai mengandung bahan kimia obat sildenafil di Kotabaru.',
            noSp: 'SP.Sidik/018/III/2026/BPOM',
            tglSp: '2026-03-20',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6302', 'Kabupaten Kotabaru'],
                'kec' => ['630209', 'Pulau Laut Utara'],
                'kel' => ['6302091001', 'Kotabaru Hulu'],
                'detail' => 'Toko Herbal Sehat Alami, Jl. Veteran No. 45 Kotabaru',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_3'],
            ppnsAnggota: [$users['penyidik_4']],
            tersangkaData: [
                'nama' => 'Suriansyah bin Imron',
                'alias' => 'Haji Isur',
                'bin' => 'Imron',
                'nik' => '6302091505800003',
                'tempat_lahir' => 'Kotabaru',
                'tanggal_lahir' => '1980-05-15',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Pedagang Jamu',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/015/III/2026/BPOM',
                    'tanggal' => '2026-03-22',
                    'status' => 'final',
                    'catatan' => 'Pemberitahuan dimulainya penyidikan tindak pidana sediaan farmasi ilegal.',
                ],
            ],
            dpoData: null,
            sp3Data: [
                'nomor_surat' => 'S.Tap/SP3/001/IV/2026/BPOM',
                'tanggal' => '2026-04-15',
                'keterangan' => 'Penghentian penyidikan karena setelah hasil pengujian laboratorium Pusat Pengembangan Pengujian Obat dan Makanan Nasional (PPPOMN), sampel tidak terbukti mengandung BKO (bukan tindak pidana).',
            ]
        );
    }

    /**
     * Helper pembuatan Perkara, TKP, PPNS, Tersangka, Berkas Perkara, Tahapan, Dokumen, dan Audit Trail.
     *
     * @param  array<string, mixed>  $tkpData
     * @param  array<string, mixed>|null  $tersangkaData
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
        User $ppnsUtama,
        array $ppnsAnggota,
        ?array $tersangkaData,
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

        // Jika kasus belum memiliki tersangka / berkas, selesai sampai di sini
        if ($tersangkaData === null) {
            return;
        }

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
            'alamat_detail' => 'Tempat Tinggal Tersangka '.$tersangkaData['nama'],
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

        // Buat Berkas Perkara (Nomor Berkas identik LK per D-035 Poin 11 & 13)
        $berkas = BerkasPerkara::updateOrCreate(
            ['perkara_id' => $perkara->id, 'tersangka_id' => $tersangka->id],
            [
                'nomor_berkas' => $noLk,
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
            $nomor = $tahapItem['nomor'];
            $tanggal = $tahapItem['tanggal'];
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

        // SP3 jika ada (D-035 Poin 5, 15, 22)
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
