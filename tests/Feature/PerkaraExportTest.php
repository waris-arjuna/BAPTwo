<?php

use App\Enums\JenisTahap;
use App\Enums\UserRole;
use App\Exports\PerkaraRekapExport;
use App\Models\Alamat;
use App\Models\BerkasPerkara;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\Tkp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::ADMINISTRATOR]);
    $this->penyidik = User::factory()->create(['role' => UserRole::PENYIDIK]);
    $this->pimpinan = User::factory()->create(['role' => UserRole::PIMPINAN]);
    $this->umum = User::factory()->create(['role' => UserRole::UMUM]);

    // Buat data perkara contoh
    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/099/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-05',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/099/X/2026',
        'tanggal_surat_perintah' => '2026-10-06',
        'tahap_sprindik_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'H. Basri Rahman',
        'nik' => '6371012345670001',
        'jenis_kelamin' => 'laki-laki',
        'agama' => 'islam',
        'pekerjaan' => 'Pedagang',
        'kewarganegaraan' => 'WNI',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'LK/099/X/2026',
        'created_by' => $this->penyidik->id,
    ]);

    // Tambah tahapan SPDP
    TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'SPDP/099/X/2026',
        'tanggal_surat' => '2026-10-08',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);
});

test('administrator dan penyidik diizinkan mengekspor file rekap perkara ke Excel .xlsx', function () {
    $responseAdmin = $this->actingAs($this->admin)->get(route('perkara.export', ['mode' => 'tahun', 'tahun' => 2026]));
    $responseAdmin->assertOk();
    expect($responseAdmin->headers->get('content-type'))->toContain('spreadsheetml.sheet');

    $this->assertDatabaseHas('audit_log', [
        'aksi' => 'export_excel',
        'tabel' => 'perkara',
        'user_id' => $this->admin->id,
    ]);

    $responsePenyidik = $this->actingAs($this->penyidik)->get(route('perkara.export', ['mode' => 'tahun', 'tahun' => 2026]));
    $responsePenyidik->assertOk();

    $this->assertDatabaseHas('audit_log', [
        'aksi' => 'export_excel',
        'tabel' => 'perkara',
        'user_id' => $this->penyidik->id,
    ]);
});

test('pimpinan dan umum diblokir dari endpoint ekspor excel (403 Forbidden)', function () {
    $this->actingAs($this->pimpinan)
        ->get(route('perkara.export'))
        ->assertForbidden();

    $this->actingAs($this->umum)
        ->get(route('perkara.export'))
        ->assertForbidden();
});

test('filter mode tahun, bulan, dan rentang tanggal berfungsi valid pada ekspor LAPJU', function () {
    // Mode tahun
    $resTahun = $this->actingAs($this->admin)->get(route('perkara.export', [
        'mode' => 'tahun',
        'tahun' => 2026,
    ]));
    $resTahun->assertOk();
    expect($resTahun->headers->get('content-disposition'))->toContain('LAPJU_2026_');

    // Mode bulan
    $resBulan = $this->actingAs($this->penyidik)->get(route('perkara.export', [
        'mode' => 'bulan',
        'tahun' => 2026,
        'bulan' => 10,
    ]));
    $resBulan->assertOk();
    expect($resBulan->headers->get('content-disposition'))->toContain('LAPJU_2026-10_');

    // Mode semua (ekspor semua data perkara)
    $resSemua = $this->actingAs($this->penyidik)->get(route('perkara.export', [
        'mode' => 'semua',
    ]));
    $resSemua->assertOk();
    expect($resSemua->headers->get('content-disposition'))->toContain('LAPJU_Semua_');

    // Mode rentang tanggal
    $resRentang = $this->actingAs($this->penyidik)->get(route('perkara.export', [
        'mode' => 'rentang',
        'tanggal_mulai' => '2026-10-01',
        'tanggal_selesai' => '2026-10-31',
    ]));
    $resRentang->assertOk();
    expect($resRentang->headers->get('content-disposition'))->toContain('LAPJU_2026-10-01_sd_2026-10-31_');

    // Mode semua dengan query params lengkap seperti pada browser
    $resExact = $this->actingAs($this->penyidik)->get('/perkara-export?mode=semua&tahun=2026&bulan=10&tanggal_mulai=&tanggal_selesai=');
    $resExact->assertOk();
    expect($resExact->headers->get('content-type'))->toContain('spreadsheetml.sheet');
});

test('struktur spreadsheet LAPJU mematuhi 45 kolom, header template, merge sel, dan freeze pane A7', function () {
    $export = new PerkaraRekapExport(mode: 'tahun', tahun: 2026);
    $spreadsheet = $export->generateSpreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // 1. Verifikasi Freeze Panes berada di bawah header dan nomor kolom (A7)
    expect($sheet->getFreezePane())->toBe('A7');

    // 2. Verifikasi Baris 1: Judul Utama A1:AS1
    expect($sheet->getCell('A1')->getValue())->toBe('PROSES PENYIDIKAN / HISTORY LAPJU');
    $mergeCells = $sheet->getMergeCells();
    expect(isset($mergeCells['A1:AS1']))->toBeTrue();

    // 3. Verifikasi Baris 2: Periode & Tanggal Export A2:AS2
    expect(isset($mergeCells['A2:AS2']))->toBeTrue();
    expect($sheet->getCell('A2')->getValue())->toContain('Periode: Tahun 2026');
    expect($sheet->getCell('A2')->getValue())->toContain('Tanggal Export:');

    // 4. Verifikasi Baris 6 memuat nomor kolom 1 s.d. 45 lengkap dan dikoreksi (AQ=43, AR=44, AS=45)
    for ($col = 1; $col <= 45; $col++) {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        expect((int) $sheet->getCell("{$colLetter}6")->getValue())->toBe($col);
    }

    // 5. Verifikasi Data Baris 7 (dimulai dari baris 7)
    expect($sheet->getCell('A7')->getValue())->toBe('1');
    expect($sheet->getCell('B7')->getValue())->toBe('LK/099/X/2026');

    // Verifikasi kolom tanggal merupakan sel tanggal Excel dengan format dd/mm/yyyy
    expect($sheet->getStyle('C7')->getNumberFormat()->getFormatCode())->toBe('dd/mm/yyyy');

    // Verifikasi nama tersangka di baris 7
    expect($sheet->getCell('I7')->getValue())->toBe('H. Basri Rahman');
    expect($sheet->getCell('J7')->getValue())->toBe('Laki-laki');

    // Verifikasi SPDP di baris 7
    expect($sheet->getCell('T7')->getValue())->toBe('SPDP/099/X/2026');
    expect($sheet->getStyle('U7')->getNumberFormat()->getFormatCode())->toBe('dd/mm/yyyy');

    // Verifikasi nilai kosong ditulis "-"
    expect($sheet->getCell('V7')->getValue())->toBe('-'); // P-16 kosong
});

test('tombol ekspor excel tampil untuk penyidik dan admin di halaman data-perkara dan seluruh-perkara', function () {
    $this->actingAs($this->penyidik)->get(route('data-perkara.index'))
        ->assertOk()
        ->assertSee('Ekspor Excel');

    $this->actingAs($this->admin)->get(route('seluruh-perkara.index'))
        ->assertOk()
        ->assertSee('Ekspor Excel');

    $this->actingAs($this->pimpinan)->get(route('seluruh-perkara.index'))
        ->assertOk()
        ->assertDontSee('Ekspor Excel');
});

test('modal ekspor excel memuat input rentang tanggal dengan konfigurasi flatpickr dan pelindung click outside', function () {
    $response = $this->actingAs($this->penyidik)->get(route('data-perkara.index'));

    $response->assertOk()
        ->assertSee('name="tanggal_mulai"', false)
        ->assertSee('name="tanggal_selesai"', false)
        ->assertSee('flatpickr-calendar', false)
        ->assertSee('fpMulai', false)
        ->assertSee('fpSelesai', false)
        ->assertSee('Semua Data', false);
});

test('format nilai ganda bernomor urut 1, 2 dst pada satu sel dengan wrap text', function () {
    // Tambah tersangka kedua pada perkara yang sama
    $tersangka2 = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Dewi Sartika',
        'jenis_kelamin' => 'perempuan',
        'agama' => 'islam',
        'pekerjaan' => 'Apoteker',
        'kewarganegaraan' => 'WNI',
        'created_by' => $this->penyidik->id,
    ]);

    $pasal1 = Pasal::firstOrCreate(['pasal' => 'Pasal 196 UU Kesehatan', 'nama_pasal' => 'Pasal 196']);
    $pasal2 = Pasal::firstOrCreate(['pasal' => 'Pasal 197 UU Kesehatan', 'nama_pasal' => 'Pasal 197']);
    $this->berkas->pasals()->sync([$pasal1->id, $pasal2->id]);

    $export = new PerkaraRekapExport(mode: 'tahun', tahun: 2026);
    $spreadsheet = $export->generateSpreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Verifikasi Multi-Tersangka di Kolom I (Nama) dan J (Jenis Kelamin) baris 7
    expect($sheet->getCell('I7')->getValue())->toContain("1. H. Basri Rahman\n2. Dewi Sartika");
    expect($sheet->getCell('J7')->getValue())->toContain("1. Laki-laki\n2. Perempuan");

    // Verifikasi Multi-Pasal di Kolom F baris 7
    expect($sheet->getCell('F7')->getValue())->toContain("1. Pasal 196 UU Kesehatan\n2. Pasal 197 UU Kesehatan");
});

test('kolom alamat tersangka, nama tkp, dan alamat tkp menghasilkan data lengkap sesuai spesifikasi pada baris 7', function () {
    $alamatTsk = Alamat::create([
        'alamat_detail' => 'Jl. Belitung Darat No. 12',
        'rt' => '003',
        'rw' => '001',
        'desa_nama' => 'Belitung Selatan',
        'kecamatan_nama' => 'Banjarmasin Barat',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'provinsi_nama' => 'Kalimantan Selatan',
    ]);
    $this->tersangka->update(['alamat_id' => $alamatTsk->id]);

    $alamatTkp = Alamat::create([
        'nama' => 'Toko Obat Sumber Waras',
        'alamat_detail' => 'Pasar Sentra Antasari Blok C No. 5',
        'rt' => '002',
        'rw' => '001',
        'desa_nama' => 'Kelayan Luar',
        'kecamatan_nama' => 'Banjarmasin Tengah',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'provinsi_nama' => 'Kalimantan Selatan',
    ]);
    $tkp = Tkp::create([
        'alamat_id' => $alamatTkp->id,
        'klasifikasi' => 'retailer',
        'legalitas' => 'ilegal',
        'modus' => 'offline',
    ]);
    $this->perkara->update(['tkp_id' => $tkp->id]);

    $export = new PerkaraRekapExport(mode: 'tahun', tahun: 2026);
    $spreadsheet = $export->generateSpreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Kolom L (Kolom 12): Alamat Lengkap Tersangka pada Baris 7
    expect($sheet->getCell('L7')->getValue())->toBe(
        'Jl. Belitung Darat No. 12, RT 003/RW 001, Desa/Kel. Belitung Selatan, Kec. Banjarmasin Barat, Kota Banjarmasin, Kalimantan Selatan'
    );

    // Kolom O (Kolom 15): Nama TKP diambil dari alamat->nama pada Baris 7
    expect($sheet->getCell('O7')->getValue())->toBe('Toko Obat Sumber Waras');

    // Kolom P (Kolom 16): Alamat Lengkap TKP pada Baris 7
    expect($sheet->getCell('P7')->getValue())->toBe(
        'Pasar Sentra Antasari Blok C No. 5, RT 002/RW 001, Desa/Kel. Kelayan Luar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan'
    );
});

test('ekspor excel menghapus Tempat tinggal tersangka di dan nama sarana pada kolom alamat', function () {
    $alamatTsk = Alamat::create([
        'alamat_detail' => 'Tempat tinggal tersangka di Jl. Hasan Basry No. 88',
        'rt' => '001',
        'rw' => '002',
        'desa_nama' => 'Pangeran',
        'kecamatan_nama' => 'Banjarmasin Utara',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'provinsi_nama' => 'Kalimantan Selatan',
    ]);
    $this->tersangka->update(['alamat_id' => $alamatTsk->id]);

    $alamatTkp = Alamat::create([
        'nama' => 'Apotek Sehat Barokah',
        'alamat_detail' => 'Apotek Sehat Barokah, Jl. Veteran No. 45',
        'rt' => '003',
        'rw' => '001',
        'desa_nama' => 'Melayu',
        'kecamatan_nama' => 'Banjarmasin Tengah',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'provinsi_nama' => 'Kalimantan Selatan',
    ]);
    $tkp = Tkp::create([
        'alamat_id' => $alamatTkp->id,
        'klasifikasi' => 'retailer',
        'legalitas' => 'legal',
        'modus' => 'offline',
    ]);
    $this->perkara->update(['tkp_id' => $tkp->id]);

    $export = new PerkaraRekapExport(mode: 'tahun', tahun: 2026);
    $spreadsheet = $export->generateSpreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Kolom L (Kolom 12): Frasa 'Tempat tinggal tersangka di ' harus terhapus
    $valL7 = $sheet->getCell('L7')->getValue();
    expect($valL7)->not->toContain('Tempat tinggal tersangka di');
    expect($valL7)->toBe(
        'Jl. Hasan Basry No. 88, RT 001/RW 002, Desa/Kel. Pangeran, Kec. Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan'
    );

    // Kolom O (Kolom 15): Nama Sarana / TKP tetap 'Apotek Sehat Barokah'
    expect($sheet->getCell('O7')->getValue())->toBe('Apotek Sehat Barokah');

    // Kolom P (Kolom 16): Nama sarana tidak boleh diulang di awal kolom alamat
    $valP7 = $sheet->getCell('P7')->getValue();
    expect($valP7)->not->toStartWith('Apotek Sehat Barokah');
    expect($valP7)->toBe(
        'Jl. Veteran No. 45, RT 003/RW 001, Desa/Kel. Melayu, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan'
    );
});

test('periode tanpa data perkara tidak mengunduh file dan memberikan respons warning', function () {
    // Mode tahun 2099 tanpa data
    $resEmpty = $this->actingAs($this->admin)->get(route('perkara.export', [
        'mode' => 'tahun',
        'tahun' => 2099,
    ]));

    // Redirect back dengan session warning
    $resEmpty->assertRedirect();
    $resEmpty->assertSessionHas('warning', 'Tidak ada data pada periode ini');

    // Pre-check count via AJAX/JSON
    $resCheck = $this->actingAs($this->admin)->getJson(route('perkara.export', [
        'mode' => 'tahun',
        'tahun' => 2099,
        'check' => 1,
    ]));

    $resCheck->assertOk();
    $resCheck->assertJson([
        'count' => 0,
        'message' => 'Tidak ada data pada periode ini',
    ]);
});
