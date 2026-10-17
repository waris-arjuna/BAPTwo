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
     * Jalankan seeder 10 data dummy lengkap dan beragam untuk seluruh alur BAPTwo.
     */
    public function run(): void
    {
        // 1. Pastikan sample PDF privat tersedia
        $this->ensureSamplePdf();

        // 2. Siapkan Master Data Tambahan
        $master = $this->seedMasterData();

        // 3. Siapkan Pengguna Tambahan (PPNS)
        $users = $this->seedUsers($master);

        // 4. Siapkan 10 Data Perkara Beragam
        $this->seedTenCases($master, $users);
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

        $pasalPangan = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Pangan', 'pasal' => 'Pasal 142 jo. Pasal 91 ayat (1)'],
            [
                'nomor' => '18',
                'tahun' => '2012',
                'isi' => 'Pelaku Usaha Pangan yang dengan sengaja tidak memiliki izin edar terhadap setiap Pangan Olahan yang diperdagangkan.',
                'status' => 'aktif',
            ]
        );

        $pasalKonsumen = Pasal::firstOrCreate(
            ['nama_pasal' => 'UU Perlindungan Konsumen', 'pasal' => 'Pasal 62 ayat (1) jo. Pasal 8 ayat (1) huruf a'],
            [
                'nomor' => '8',
                'tahun' => '1999',
                'isi' => 'Memproduksi dan/atau memperdagangkan barang dan/atau jasa yang tidak memenuhi atau tidak sesuai dengan standar.',
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

        $pnBjm = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Banjarmasin'],
            ['wilayah' => 'Kota Banjarmasin']
        );

        $pnMtp = InstansiHukum::firstOrCreate(
            ['jenis' => 'pengadilan', 'nama' => 'Pengadilan Negeri Martapura'],
            ['wilayah' => 'Kabupaten Banjar']
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
            'pasal_pangan' => $pasalPangan,
            'pasal_konsumen' => $pasalKonsumen,
            'kejati' => $kejati,
            'kejari_bjm' => $kejariBjm,
            'kejari_bjb' => $kejariBjb,
            'kejari_banjar' => $kejariBanjar,
            'pn_bjm' => $pnBjm,
            'pn_mtp' => $pnMtp,
            'jaksa_1' => $jaksa1,
            'jaksa_2' => $jaksa2,
            'jaksa_3' => $jaksa3,
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
     * Menanam 10 data perkara lengkap dengan variasi tahapan dari pra-SPDP hingga Putusan & DPO.
     *
     * @param  array<string, mixed>  $master
     * @param  array<string, User>  $users
     */
    private function seedTenCases(array $master, array $users): void
    {
        $actor = $users['penyidik_1'];

        // =========================================================================
        // KASUS 1: Laporan Kejadian Baru (Pra-SPDP) — Belum ada Sprindik
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/001/I/2026/BPOM',
            tglLk: '2026-01-08',
            uraian: 'Temuan peredaran kosmetik ilegal tanpa izin edar mengandung bahan berbahaya merkuri pada kios kosmetik di pasar tradisional.',
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
            tahapanChain: []
        );

        // =========================================================================
        // KASUS 2: Sprindik Tersedia (Pra-SPDP) — Belum dibuat Data Perkara
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/002/I/2026/BPOM',
            tglLk: '2026-01-15',
            uraian: 'Dugaan peredaran pangan olahan kadaluwarsa yang dikemas ulang (repacking) dengan label palsu pada gudang distributor sembako.',
            noSp: 'SP.Sidik/001/I/2026/BPOM',
            tglSp: '2026-01-18',
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
        // KASUS 3: Data Perkara Terbentuk — Belum SPDP (Progress 0%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/003/II/2026/BPOM',
            tglLk: '2026-02-02',
            uraian: 'Peracikan dan peredaran jamu kuat tradisional ilegal yang mengandung bahan kimia obat (BKO) Sildenafil Citrate tanpa izin edar.',
            noSp: 'SP.Sidik/002/II/2026/BPOM',
            tglSp: '2026-02-05',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6303', 'Kabupaten Banjar'],
                'kec' => ['630305', 'Martapura'],
                'kel' => ['6303051001', 'Keraton'],
                'detail' => 'Depot Jamu Segar Bugar Sehat, Jl. Menteri Empat No. 22',
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
                'nik' => '6303051204820003',
                'tempat_lahir' => 'Martapura',
                'tanggal_lahir' => '1982-04-12',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'buddha',
                'pekerjaan' => 'Wiraswasta',
                'nomor_berkas' => 'BP/001/II/2026/BPOM',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [] // Belum ada tahapan progress
        );

        // =========================================================================
        // KASUS 4: SPDP Selesai Difinalkan (Progress 15%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/004/II/2026/BPOM',
            tglLk: '2026-02-10',
            uraian: 'Peredaran gelap sediaan farmasi obat keras daftar G (Tramadol dan Trihexyphenidyl) tanpa kewenangan secara daring di media sosial.',
            noSp: 'SP.Sidik/003/II/2026/BPOM',
            tglSp: '2026-02-12',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6307', 'Kabupaten Hulu Sungai Tengah'],
                'kec' => ['630706', 'Barabai'],
                'kel' => ['6307061002', 'Barabai Barat'],
                'detail' => 'Rumah Kontrakan Titik Drop Pengiriman, Jl. Perintis Kemerdekaan No. 15',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'online',
            ],
            ppnsUtama: $users['penyidik_2'],
            ppnsAnggota: [$users['penyidik_4']],
            tersangkaData: [
                'nama' => 'M. Rizky Pratama',
                'alias' => 'Kiki',
                'bin' => 'Mansyur Pratama',
                'nik' => '6307062409970001',
                'tempat_lahir' => 'Barabai',
                'tanggal_lahir' => '1997-09-24',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Swasta',
                'nomor_berkas' => 'BP/002/II/2026/BPOM',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/001/II/2026/BPOM',
                    'tanggal' => '2026-02-16',
                    'catatan' => 'Pemberitahuan dimulainya penyidikan telah disampaikan ke Kejaksaan Tinggi Kalimantan Selatan.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 5: SPDP dengan Sub-Tahap P-16 & P-17P (Progress 15%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/005/III/2026/BPOM',
            tglLk: '2026-03-01',
            uraian: 'Pemasukan dan pengedaran produk makanan ringan olahan impor tanpa izin edar BPOM RI skala menengah di wilayah Barito Kuala.',
            noSp: 'SP.Sidik/004/III/2026/BPOM',
            tglSp: '2026-03-04',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6304', 'Kabupaten Barito Kuala'],
                'kec' => ['630403', 'Marabahan'],
                'kel' => ['6304031001', 'Marabahan Kota'],
                'detail' => 'Ruko Gudang Distributor Snack Impor, Jl. Veteran No. 5 Marabahan',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_3'],
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
                'nomor_berkas' => 'BP/003/III/2026/BPOM',
                'pasals' => [$master['pasal_pangan'], $master['pasal_konsumen']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/002/III/2026/BPOM',
                    'tanggal' => '2026-03-06',
                    'catatan' => 'SPDP dikirimkan ke Kejari Barito Kuala.',
                ],
                [
                    'jenis' => JenisTahap::P16,
                    'nomor' => 'B-112/O.3.4/Fd.1/03/2026',
                    'tanggal' => '2026-03-10',
                    'catatan' => 'Penerimaan surat penunjukan jaksa penuntut umum (P-16) untuk mengikuti perkembangan penyidikan.',
                ],
                [
                    'jenis' => JenisTahap::P17P,
                    'nomor' => 'B-156/O.3.4/Fd.1/03/2026',
                    'tanggal' => '2026-03-24',
                    'catatan' => 'Penerimaan surat permintaan perkembangan penyidikan perkara dari Kejaksaan.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 6: TAHAP I Selesai (Progress 55%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/006/III/2026/BPOM',
            tglLk: '2026-03-15',
            uraian: 'Distribusi suplemen kesehatan stamina ilegal dengan klaim palsu dan tanpa izin edar melalui toko daring marketplace.',
            noSp: 'SP.Sidik/005/III/2026/BPOM',
            tglSp: '2026-03-18',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6305', 'Kabupaten Tapin'],
                'kec' => ['630504', 'Tapin Utara'],
                'kel' => ['6305041002', 'Rantau Kanan'],
                'detail' => 'Rumah Gudang Packing Ekspedisi, Jl. Brigjend Hasan Basry No. 44 Rantau',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'online',
            ],
            ppnsUtama: $users['penyidik_4'],
            ppnsAnggota: [$users['penyidik_2']],
            tersangkaData: [
                'nama' => 'Agus Setiawan',
                'alias' => 'Agus',
                'bin' => 'Subhan Setiawan',
                'nik' => '6305041708930004',
                'tempat_lahir' => 'Rantau',
                'tanggal_lahir' => '1993-08-17',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Pedagang Online',
                'nomor_berkas' => 'BP/004/III/2026/BPOM',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/003/III/2026/BPOM',
                    'tanggal' => '2026-03-22',
                    'catatan' => 'SPDP diterbitkan dan dikirimkan.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/001/IV/2026/BPOM',
                    'tanggal' => '2026-04-05',
                    'catatan' => 'Penyerahan berkas perkara tahap pertama kepada Jaksa Peneliti Kejaksaan Tinggi Kalsel.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 7: TAHAP I dengan Sub-Tahap P-18P & P-19 (Progress 55%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/007/IV/2026/BPOM',
            tglLk: '2026-04-01',
            uraian: 'Produksi sediaan kosmetik perawatan kulit racikan tanpa izin edar dan mengandung bahan berbahaya hidrokuinon skala home industry.',
            noSp: 'SP.Sidik/006/IV/2026/BPOM',
            tglSp: '2026-04-03',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6301', 'Kabupaten Tanah Laut'],
                'kec' => ['630104', 'Pelaihari'],
                'kel' => ['6301041001', 'Pelaihari'],
                'detail' => 'Rumah Industri Kosmetik Ratna Beauty, Jl. Datu Insad No. 19 Pelaihari',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [$users['penyidik_3']],
            tersangkaData: [
                'nama' => 'Ratna Juwita',
                'alias' => 'Ratna',
                'bin' => 'Syamsuri',
                'nik' => '6301046205860002',
                'tempat_lahir' => 'Pelaihari',
                'tanggal_lahir' => '1986-05-22',
                'jenis_kelamin' => 'perempuan',
                'agama' => 'islam',
                'pekerjaan' => 'Pengusaha Salon',
                'nomor_berkas' => 'BP/005/IV/2026/BPOM',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_konsumen']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/004/IV/2026/BPOM',
                    'tanggal' => '2026-04-08',
                    'catatan' => 'SPDP disampaikan ke Kejari Tanah Laut.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/002/IV/2026/BPOM',
                    'tanggal' => '2026-04-20',
                    'catatan' => 'Penyerahan berkas perkara tahap 1 ke Jaksa.',
                ],
                [
                    'jenis' => JenisTahap::P18P,
                    'nomor' => 'B-240/O.3.4/Fd.1/04/2026',
                    'tanggal' => '2026-04-28',
                    'catatan' => 'Penerimaan P-18 pemberitahuan hasil penyidikan berkas perkara belum lengkap.',
                ],
                [
                    'jenis' => JenisTahap::P19,
                    'nomor' => 'B-255/O.3.4/Fd.1/05/2026',
                    'tanggal' => '2026-05-04',
                    'catatan' => 'Penerimaan petunjuk jaksa P-19 untuk kelengkapan berkas perkara.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 8: P-21 Lengkap (Progress 85%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/008/IV/2026/BPOM',
            tglLk: '2026-04-10',
            uraian: 'Peredaran obat tradisional stamina pria tanpa izin edar yang didatangkan melalui jalur ekspedisi pelabuhan laut Batulicin.',
            noSp: 'SP.Sidik/007/IV/2026/BPOM',
            tglSp: '2026-04-14',
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
            ppnsUtama: $users['penyidik_2'],
            ppnsAnggota: [$users['penyidik_1']],
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
                'nomor_berkas' => 'BP/006/IV/2026/BPOM',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/005/IV/2026/BPOM',
                    'tanggal' => '2026-04-18',
                    'catatan' => 'SPDP diterbitkan.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/003/V/2026/BPOM',
                    'tanggal' => '2026-05-02',
                    'catatan' => 'Penyerahan berkas Tahap 1 ke Kejari Tanah Bumbu.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-310/O.3.4/Fd.1/05/2026',
                    'tanggal' => '2026-05-22',
                    'catatan' => 'Surat pemberitahuan bahwa hasil penyidikan berkas perkara telah lengkap (P-21). Siap penyerahan Tahap II.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 9: TAHAP II & Keputusan Pengadilan (Status Selesai 100%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/009/V/2026/BPOM',
            tglLk: '2026-05-02',
            uraian: 'Operasi apotek rakyat ilegal tanpa SIA/SIPA yang memperjualbelikan obat-obatan daftar G dan antibiotik keras tanpa resep dokter.',
            noSp: 'SP.Sidik/008/V/2026/BPOM',
            tglSp: '2026-05-05',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6302', 'Kabupaten Kotabaru'],
                'kec' => ['630209', 'Pulau Laut Utara'],
                'kel' => ['6302091003', 'Kotabaru Hulu'],
                'detail' => 'Kios Apotek Sehat Mandiri, Jl. Veteran No. 7 Kotabaru',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_3'],
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
                'nomor_berkas' => 'BP/007/V/2026/BPOM',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/006/V/2026/BPOM',
                    'tanggal' => '2026-05-10',
                    'catatan' => 'SPDP diterbitkan.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/004/V/2026/BPOM',
                    'tanggal' => '2026-05-25',
                    'catatan' => 'Penyerahan berkas Tahap 1.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-385/O.3.4/Fd.1/06/2026',
                    'tanggal' => '2026-06-15',
                    'catatan' => 'Berkas dinyatakan lengkap P-21 oleh Jaksa Penuntut Umum.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_2,
                    'nomor' => 'BA.T2/001/VI/2026/BPOM',
                    'tanggal' => '2026-06-28',
                    'catatan' => 'Penyerahan tanggung jawab tersangka dan barang bukti (Tahap II) kepada Kejaksaan Negeri Kotabaru.',
                ],
                [
                    'jenis' => JenisTahap::KEPUTUSAN_PENGADILAN,
                    'nomor' => 'Putusan PN No. 142/Pid.Sus/2026/PN.Bjm',
                    'tanggal' => '2026-08-18',
                    'catatan' => 'Terdakwa divonis bersalah pidana penjara 2 tahun 6 bulan dan denda Rp 300.000.000 subsider 3 bulan kurungan.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 10: Kasus DPO Aktif (Buron / DPO Gate Aktif pada Tahap II)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/010/V/2026/BPOM',
            tglLk: '2026-05-12',
            uraian: 'Penyelundupan kosmetik impor tanpa izin edar dari luar pulau jaringan sindikat lintas provinsi melalui jalur darat Tanjung - Balikpapan.',
            noSp: 'SP.Sidik/009/V/2026/BPOM',
            tglSp: '2026-05-15',
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
            ppnsUtama: $users['penyidik_4'],
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
                'nomor_berkas' => 'BP/008/V/2026/BPOM',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'SPDP/007/V/2026/BPOM',
                    'tanggal' => '2026-05-20',
                    'catatan' => 'SPDP diterbitkan.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'BA.T1/005/VI/2026/BPOM',
                    'tanggal' => '2026-06-12',
                    'catatan' => 'Penyerahan berkas perkara Tahap 1.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-420/O.3.4/Fd.1/07/2026',
                    'tanggal' => '2026-07-05',
                    'catatan' => 'P-21 terbit dari Jaksa Penuntut Umum.',
                ],
            ],
            dpoData: [
                'nomor_surat' => 'DPO/001/VII/2026/BPOM',
                'tanggal' => '2026-07-20',
                'keterangan' => 'Tersangka melarikan diri saat pemanggilan resmi pelaksanaan Tahap II sebanyak 3 kali berturut-turut. Diterbitkan DPO koordinasi Polda Kalsel.',
            ]
        );

        // =========================================================================
        // KASUS 11: Penghentian Penyidikan (SP3) — Status Formal Tidak Selesai (Poin 5, 15, 22)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/011/II/2026/BPOM',
            tglLk: '2026-02-10',
            uraian: 'Penyelidikan dugaan peredaran sediaan farmasi tanpa izin edar berupa obat tradisional stamina yang dicurigai mengandung bahan kimia obat sildenafil di Kotabaru.',
            noSp: 'SP.Sidik/011/II/2026/BPOM',
            tglSp: '2026-02-12',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6302', 'Kabupaten Kotabaru'],
                'kec' => ['630209', 'Pulau Laut Utara'],
                'kel' => ['6302091001', 'Kotabaru Hulu'],
                'detail' => 'Toko Herbal Sehat Alami, Jl. Veteran No. 45',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_2'],
            ppnsAnggota: [$users['penyidik_3']],
            tersangkaData: [
                'nik' => '6302091505800003',
                'nama' => 'Suriansyah bin Imron',
                'alias' => 'Haji Isur',
                'bin' => 'Imron',
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
                    'nomor' => 'B-011/SPDP/II/2026/BPOM',
                    'tanggal' => '2026-02-15',
                    'catatan' => 'Pemberitahuan dimulainya penyidikan tindak pidana sediaan farmasi ilegal.',
                ],
            ],
            dpoData: null,
            sp3Data: [
                'nomor_surat' => 'S.Tap/SP3/001/III/2026/BPOM',
                'tanggal' => '2026-03-20',
                'keterangan' => 'Penghentian penyidikan karena setelah uji laboratorium forensik, kandungan BKO tidak memenuhi batas pembuktian materil dan saksi ahli menyatakan bukan tindak pidana.',
            ]
        );

        // =========================================================================
        // KASUS 12: DPO Dicabut (Nonaktif) & Lanjut Tahap P-21 (Variasi 3 PPNS Anggota & 3 Pasal)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/012/II/2026/BPOM',
            tglLk: '2026-02-18',
            uraian: 'Peredaran pangan olahan tanpa izin edar jenis suplemen diet impor ilegal berlabel palsu pada gudang e-commerce di Banjarbaru.',
            noSp: 'SP.Sidik/012/II/2026/BPOM',
            tglSp: '2026-02-20',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6372', 'Kota Banjarbaru'],
                'kec' => ['637202', 'Banjarbaru Utara'],
                'kel' => ['6372021003', 'Mentaos'],
                'detail' => 'Gudang Paket Borneo Express, Jl. P. Suriansyah No. 18',
                'klasifikasi' => 'distributor',
                'legalitas' => 'ilegal',
                'modus' => 'online',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [$users['penyidik_2'], $users['penyidik_3'], $users['penyidik_4']],
            tersangkaData: [
                'nik' => '6372022808850002',
                'nama' => 'Rudi Hermanto, S.E.',
                'alias' => 'Koh Rudi',
                'bin' => 'Hermanto',
                'tempat_lahir' => 'Banjarbaru',
                'tanggal_lahir' => '1985-08-28',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'kristen',
                'pekerjaan' => 'Pemilik Toko Online',
                'pasals' => [$master['pasal_pangan'], $master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'B-012/SPDP/II/2026/BPOM',
                    'tanggal' => '2026-02-22',
                    'catatan' => 'Pemberitahuan SPDP ke Kejari Banjarbaru.',
                ],
                [
                    'jenis' => JenisTahap::P16,
                    'nomor' => 'B-412/O.3.14/Eku.1/02/2026',
                    'tanggal' => '2026-02-25',
                    'catatan' => 'Kejaksaan Negeri Banjarbaru. Jaksa Penuntut Umum: Firman Hidayat, S.H., M.H.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'B-012/Tahap-I/III/2026/BPOM',
                    'tanggal' => '2026-03-05',
                    'catatan' => 'Penyerahan berkas perkara tahap 1 ke Jaksa Peneliti.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-620/O.3.14/Eku.1/03/2026',
                    'tanggal' => '2026-03-25',
                    'catatan' => 'Berkas perkara dinyatakan lengkap (P-21) oleh Kejari Banjarbaru setelah tersangka diamankan kembali.',
                ],
            ],
            dpoData: [
                'nomor_surat' => 'DPO/002/III/2026/BPOM',
                'tanggal' => '2026-03-08',
                'tanggal_cabut' => '2026-03-22',
                'status' => 'nonaktif',
                'keterangan' => 'Tersangka sempat melarikan diri ke luar kota saat pemanggilan Tahap 1, namun berhasil diamankan tim intelijen dan DPO dicabut.',
            ]
        );

        // =========================================================================
        // KASUS 13: Jalur Cepat Tahap II Selesai Tanpa Sub-Tahap Opsional (Progress 100%)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/013/II/2026/BPOM',
            tglLk: '2026-02-24',
            uraian: 'Penyelundupan kosmetik pemutih tanpa izin edar dan racikan berbahaya merkuri pada klinik kecantikan di Kabupaten Banjar.',
            noSp: 'SP.Sidik/013/II/2026/BPOM',
            tglSp: '2026-02-26',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6303', 'Kabupaten Banjar'],
                'kec' => ['630303', 'Martapura'],
                'kel' => ['6303031002', 'Jawa'],
                'detail' => 'Klinik Cantik Alami, Jl. Sekumpul Raya No. 99',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_3'],
            ppnsAnggota: [$users['penyidik_1'], $users['penyidik_2']],
            tersangkaData: [
                'nik' => '6303035209930001',
                'nama' => 'drg. Maya Rosita',
                'alias' => 'Dokter Maya',
                'bin' => 'Syamsul Bachri',
                'tempat_lahir' => 'Martapura',
                'tanggal_lahir' => '1993-09-12',
                'jenis_kelamin' => 'perempuan',
                'agama' => 'islam',
                'pekerjaan' => 'Praktisi Estetika',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'B-013/SPDP/II/2026/BPOM',
                    'tanggal' => '2026-02-28',
                    'catatan' => 'SPDP dikirimkan ke Kejari Kabupaten Banjar.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'B-013/Tahap-I/III/2026/BPOM',
                    'tanggal' => '2026-03-08',
                    'catatan' => 'Pelimpahan berkas perkara tahap 1 tanpa pengembalian (langsung P-21).',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-733/O.3.13/Eku.1/03/2026',
                    'tanggal' => '2026-03-18',
                    'catatan' => 'P-21 dinyatakan lengkap oleh Jaksa Penuntut Umum.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_2,
                    'nomor' => 'BA-Pelimpahan/013/III/2026/BPOM',
                    'tanggal' => '2026-03-24',
                    'catatan' => 'Penyerahan tersangka dan barang bukti 450 pot cream racikan ke Kejaksaan Negeri Kabupaten Banjar.',
                ],
            ]
        );

        // =========================================================================
        // KASUS 14: Perkara Tahun 2025 Melewati Batas Tahun Mutlak (Status Formal: Tidak Selesai)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/045/XI/2025/BPOM',
            tglLk: '2025-11-12',
            uraian: 'Peredaran obat tradisional stamina pria mengandung bahan kimia obat sildenafil tanpa izin edar di warung malam Kabupaten Tanah Laut.',
            noSp: 'SP.Sidik/045/XI/2025/BPOM',
            tglSp: '2025-11-14',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6301', 'Kabupaten Tanah Laut'],
                'kec' => ['630103', 'Pelaihari'],
                'kel' => ['6301031001', 'Angsau'],
                'detail' => 'Warung Kopi Remang Simpang Tiga, Jl. A. Yani Km. 4 Pelaihari',
                'klasifikasi' => 'retailer',
                'legalitas' => 'ilegal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_4'],
            ppnsAnggota: [$users['penyidik_1']],
            tersangkaData: [
                'nik' => '6301031204780004',
                'nama' => 'Arbain bin Mastur',
                'alias' => 'Pak Bain',
                'bin' => 'Mastur',
                'tempat_lahir' => 'Pelaihari',
                'tanggal_lahir' => '1978-04-12',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Pedagang Warung',
                'pasals' => [$master['pasal_kesehatan_1']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'B-045/SPDP/XI/2025/BPOM',
                    'tanggal' => '2025-11-18',
                    'catatan' => 'SPDP ke Kejaksaan Negeri Tanah Laut.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'B-045/Tahap-I/XII/2025/BPOM',
                    'tanggal' => '2025-12-05',
                    'catatan' => 'Penyerahan berkas perkara tahap 1.',
                ],
                [
                    'jenis' => JenisTahap::P19,
                    'nomor' => 'B-998/O.3.18/Eku.1/12/2025',
                    'tanggal' => '2025-12-20',
                    'catatan' => 'Petunjuk P-19 dari Jaksa Penuntut Umum untuk melengkapi keterangan saksi ahli.',
                ],
            ],
            dpoData: null,
            sp3Data: null,
            statusFormalOverride: 'tidak_selesai'
        );

        // =========================================================================
        // KASUS 15: Perkara Tuntas Putusan Pengadilan Negeri Marabahan (Vonis Inkrah)
        // =========================================================================
        $this->createCase(
            actor: $actor,
            noLk: 'LK/015/III/2026/BPOM',
            tglLk: '2026-03-01',
            uraian: 'Penyalahgunaan izin usaha toko obat menjual obat-obat tertentu (OOT) daftar G golongan trihexyphenidyl dan tramadol tanpa resep dokter di Barito Kuala.',
            noSp: 'SP.Sidik/015/III/2026/BPOM',
            tglSp: '2026-03-03',
            statusPerkara: 'final',
            tkpData: [
                'prov' => ['63', 'Kalimantan Selatan'],
                'kab' => ['6304', 'Kabupaten Barito Kuala'],
                'kec' => ['630401', 'Alalak'],
                'kel' => ['6304011002', 'Handil Bakti'],
                'detail' => 'Toko Obat Sehat Farma, Komplek Semangat Dalam Blok C No. 21',
                'klasifikasi' => 'distributor',
                'legalitas' => 'legal',
                'modus' => 'offline',
            ],
            ppnsUtama: $users['penyidik_1'],
            ppnsAnggota: [$users['penyidik_2'], $users['penyidik_4']],
            tersangkaData: [
                'nik' => '6304011005880005',
                'nama' => 'Hendra Gunawan, A.Md.Farm.',
                'alias' => 'Hendra',
                'bin' => 'Syarkawi Gunawan',
                'tempat_lahir' => 'Marabahan',
                'tanggal_lahir' => '1988-05-10',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Tenaga Teknis Kefarmasian',
                'pasals' => [$master['pasal_kesehatan_1'], $master['pasal_kesehatan_2']],
            ],
            tahapanChain: [
                [
                    'jenis' => JenisTahap::SPDP,
                    'nomor' => 'B-015/SPDP/III/2026/BPOM',
                    'tanggal' => '2026-03-05',
                    'catatan' => 'SPDP ke Kejaksaan Negeri Barito Kuala.',
                ],
                [
                    'jenis' => JenisTahap::P16,
                    'nomor' => 'B-515/O.3.19/Eku.1/03/2026',
                    'tanggal' => '2026-03-07',
                    'catatan' => 'Kejaksaan Negeri Barito Kuala. Jaksa Penuntut Umum: Diana Lestari, S.H.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_1,
                    'nomor' => 'B-015/Tahap-I/III/2026/BPOM',
                    'tanggal' => '2026-03-12',
                    'catatan' => 'Pelimpahan berkas perkara tahap 1.',
                ],
                [
                    'jenis' => JenisTahap::P21,
                    'nomor' => 'B-815/O.3.19/Eku.1/03/2026',
                    'tanggal' => '2026-03-20',
                    'catatan' => 'P-21 berkas dinyatakan lengkap.',
                ],
                [
                    'jenis' => JenisTahap::TAHAP_2,
                    'nomor' => 'BA-Pelimpahan/015/III/2026/BPOM',
                    'tanggal' => '2026-03-25',
                    'catatan' => 'Pelimpahan tersangka Hendra Gunawan dan barang bukti 2.000 butir obat keras ke Kejari Barito Kuala.',
                ],
                [
                    'jenis' => JenisTahap::KEPUTUSAN_PENGADILAN,
                    'nomor' => '42/Pid.Sus/2026/PN Mrh',
                    'tanggal' => '2026-04-05',
                    'catatan' => 'Pengadilan Negeri Marabahan. Terbukti bersalah melanggar Pasal 435 jo. Pasal 138 ayat (2) UU Kesehatan. Vonis: 2 tahun penjara dan denda Rp 150.000.000 subsider 4 bulan kurungan (Inkrah).',
                ],
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
        ?string $statusFormalOverride = null
    ): void {
        // Alamat TKP
        $alamatTkp = Alamat::create([
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

        // Entitas Perkara
        $perkara = Perkara::updateOrCreate(
            ['nomor_laporan_kejadian' => $noLk],
            [
                'tanggal_laporan_kejadian' => $tglLk,
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => Carbon::parse($tglLk)->addDay(),
                'laporan_finalized_by' => $actor->id,
                'nomor_surat_perintah' => $noSp,
                'tanggal_surat_perintah' => $tglSp,
                'tahap_sprindik_status' => $hasSp ? 'final' : null,
                'sprindik_locked_at' => $hasSp ? Carbon::parse($tglSp)->addDay() : null,
                'sprindik_finalized_by' => $hasSp ? $actor->id : null,
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
            'aksi' => 'finalisasi_laporan_kejadian',
            'tabel' => 'perkara',
            'record_id' => $perkara->id,
            'data_lama' => null,
            'data_baru' => ['nomor_laporan_kejadian' => $noLk, 'status' => 'final'],
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::parse($tglLk)->setTime(9, 30),
        ]);

        if ($hasSp) {
            AuditLog::create([
                'user_id' => $actor->id,
                'perkara_id' => $perkara->id,
                'aksi' => 'finalisasi_sprindik',
                'tabel' => 'perkara',
                'record_id' => $perkara->id,
                'data_lama' => null,
                'data_baru' => ['nomor_surat_perintah' => $noSp, 'status' => 'final'],
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

        // Buat Berkas Perkara
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

            $tahap = TahapanPerkara::updateOrCreate(
                [
                    'berkas_perkara_id' => $berkas->id,
                    'jenis_tahap' => $jenis->value,
                ],
                [
                    'nomor_surat' => $nomor,
                    'tanggal_surat' => $tanggal,
                    'catatan' => $catatan,
                    'status' => 'final',
                    'finalized_at' => Carbon::parse($tanggal)->setTime(14, 0),
                    'finalized_by' => $actor->id,
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

            if (isset($progressMap[$jenis->value])) {
                $currentProgress = max($currentProgress, $progressMap[$jenis->value]);
            }
            $currentStatusProses = $jenis->label();

            if ($jenis === JenisTahap::SPDP) {
                $berkas->update(['tanggal_spdp' => $tanggal]);
            }

            AuditLog::create([
                'user_id' => $actor->id,
                'perkara_id' => $perkara->id,
                'berkas_perkara_id' => $berkas->id,
                'aksi' => 'finalisasi_tahap',
                'tabel' => 'tahapan_perkara',
                'record_id' => $tahap->id,
                'data_lama' => null,
                'data_baru' => [
                    'tahap' => $jenis->label(),
                    'nomor_surat' => $nomor,
                    'tanggal_surat' => $tanggal,
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
