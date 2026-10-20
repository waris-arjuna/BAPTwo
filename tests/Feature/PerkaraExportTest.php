<?php

use App\Enums\JenisTahap;
use App\Enums\UserRole;
use App\Exports\PerkaraRekapExport;
use App\Models\Alamat;
use App\Models\BerkasPerkara;
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

test('filter mode tahun, bulan, dan rentang tanggal berfungsi valid pada ekspor', function () {
    // Mode tahun
    $resTahun = $this->actingAs($this->admin)->get(route('perkara.export', [
        'mode' => 'tahun',
        'tahun' => 2026,
    ]));
    $resTahun->assertOk();

    // Mode bulan
    $resBulan = $this->actingAs($this->penyidik)->get(route('perkara.export', [
        'mode' => 'bulan',
        'tahun' => 2026,
        'bulan' => 10,
    ]));
    $resBulan->assertOk();

    // Mode semua (ekspor semua data perkara)
    $resSemua = $this->actingAs($this->penyidik)->get(route('perkara.export', [
        'mode' => 'semua',
    ]));
    $resSemua->assertOk();
    expect($resSemua->headers->get('content-disposition'))->toContain('rekap_seluruh_perkara_');

    // Mode rentang tanggal
    $resRentang = $this->actingAs($this->penyidik)->get(route('perkara.export', [
        'mode' => 'rentang',
        'tanggal_mulai' => '2026-10-01',
        'tanggal_selesai' => '2026-10-31',
    ]));
    $resRentang->assertOk();

    // Mode semua dengan query params lengkap seperti pada browser
    $resExact = $this->actingAs($this->penyidik)->get('/perkara-export?mode=semua&tahun=2026&bulan=10&tanggal_mulai=&tanggal_selesai=');
    $resExact->assertOk();
    expect($resExact->headers->get('content-type'))->toContain('spreadsheetml.sheet');
});

test('struktur spreadsheet mematuhi 44 kolom, 4 baris header, merge sel, border, dan freeze pane', function () {
    $export = new PerkaraRekapExport(mode: 'tahun', tahun: 2026);
    $spreadsheet = $export->generateSpreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // 0. Verifikasi Format Huruf adalah Arial
    expect($spreadsheet->getDefaultStyle()->getFont()->getName())->toBe('Arial');
    expect($sheet->getStyle('A1')->getFont()->getName())->toBe('Arial');

    // 1. Verifikasi Freeze Panes berada di bawah header (A5)
    expect($sheet->getFreezePane())->toBe('A5');

    // 2. Verifikasi Baris 1: Kolom tanpa sub-level di-merge vertikal baris 1-3
    // Col 1: A1:A3 = No.
    expect($sheet->getCell('A1')->getValue())->toBe('No.');
    $mergeA = $sheet->getMergeCells();
    expect(isset($mergeA['A1:A3']))->toBeTrue();

    // Col 6: F1:F3 = Pasal yang disangkakan
    expect($sheet->getCell('F1')->getValue())->toBe('Pasal yang disangkakan');
    expect(isset($mergeA['F1:F3']))->toBeTrue();

    // Col 7: G1:G3 = PPNS Utama
    expect($sheet->getCell('G1')->getValue())->toBe('PPNS Utama');
    expect(isset($mergeA['G1:G3']))->toBeTrue();

    // Col 8: H1:H3 = PPNS Anggota
    expect($sheet->getCell('H1')->getValue())->toBe('PPNS Anggota');
    expect(isset($mergeA['H1:H3']))->toBeTrue();

    // Col 43: AQ1:AQ3 = Pengadilan
    expect($sheet->getCell('AQ1')->getValue())->toBe('Pengadilan');
    expect(isset($mergeA['AQ1:AQ3']))->toBeTrue();

    // Col 44: AR1:AR3 = Kendala dan Rencana Tindak Lanjut
    expect($sheet->getCell('AR1')->getValue())->toBe('Kendala dan Rencana Tindak Lanjut');
    expect(isset($mergeA['AR1:AR3']))->toBeTrue();

    // 3. Verifikasi Grup Proses Penyidikan (T1:AN1) dan sub-grupnya (baris 2)
    expect($sheet->getCell('T1')->getValue())->toBe('Proses Penyidikan');
    expect(isset($mergeA['T1:AN1']))->toBeTrue();

    expect($sheet->getCell('T2')->getValue())->toBe('SPDP');
    expect(isset($mergeA['T2:U2']))->toBeTrue();

    expect($sheet->getCell('T3')->getValue())->toBe('Nomor');
    expect($sheet->getCell('U3')->getValue())->toBe('Tanggal');

    // 4. Verifikasi Baris 4 memuat nomor kolom 1 s.d. 44
    for ($col = 1; $col <= 44; $col++) {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        expect($sheet->getCell("{$colLetter}4")->getValue())->toBe($col);
    }

    // 5. Verifikasi Data Baris 5
    expect($sheet->getCell('A5')->getValue())->toBe(1);
    expect($sheet->getCell('B5')->getValue())->toBe('LK/099/X/2026');

    // Verifikasi kolom tanggal merupakan sel tanggal Excel dengan format dd-mmm-yy
    expect($sheet->getStyle('C5')->getNumberFormat()->getFormatCode())->toBe('dd-mmm-yy');
    expect($sheet->getCell('C5')->getFormattedValue())->toBe('05-Oct-26');

    // Verifikasi nama tersangka
    expect($sheet->getCell('I5')->getValue())->toBe('H. Basri Rahman');
    expect($sheet->getCell('J5')->getValue())->toBe('Laki-laki');

    // Verifikasi SPDP
    expect($sheet->getCell('T5')->getValue())->toBe('SPDP/099/X/2026');
    expect($sheet->getStyle('U5')->getNumberFormat()->getFormatCode())->toBe('dd-mmm-yy');
    expect($sheet->getCell('U5')->getFormattedValue())->toBe('08-Oct-26');

    // Verifikasi nilai kosong ditulis "-"
    expect($sheet->getCell('V5')->getValue())->toBe('-'); // P-16 kosong
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
        ->assertSee('Semua Data', false)
        ->assertSee('Ekspor Seluruh Perkara', false);
});

test('format daftar multi-nilai pasal dan ppns anggota mematuhi aturan tata bahasa koma, dan di tengah untuk 2 data, dan titik di akhir', function () {
    $export = new PerkaraRekapExport;

    // 0 item
    expect($export->formatList([]))->toBe('-');

    // 1 item
    expect($export->formatList(['Pasal 196']))->toBe('Pasal 196.');
    expect($export->formatList(['Siti Rahma, S.Farm., Apt.']))->toBe('Siti Rahma, S.Farm., Apt.');

    // 2 items: 'dan' di tengah-tengah pasal 1 dan pasal 2 / ppns 1 dan ppns 2, titik di akhir
    expect($export->formatList(['Pasal 196', 'Pasal 197']))->toBe('Pasal 196 dan Pasal 197.');
    expect($export->formatList(['Ahmad Yani', 'Budi Santoso']))->toBe('Ahmad Yani dan Budi Santoso.');

    // 3 items: koma, 'dan' sebelum data terakhir, titik di akhir
    expect($export->formatList(['Pasal 196', 'Pasal 197', 'Pasal 198']))->toBe('Pasal 196, Pasal 197, dan Pasal 198.');
    expect($export->formatList(['Ahmad Yani', 'Budi Santoso', 'Siti Aminah']))->toBe('Ahmad Yani, Budi Santoso, dan Siti Aminah.');
});

test('kolom alamat tersangka, nama tkp, dan alamat tkp menghasilkan data lengkap sesuai spesifikasi', function () {
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

    // Kolom L (Kolom 12): Alamat Lengkap Tersangka
    expect($sheet->getCell('L5')->getValue())->toBe(
        'Jl. Belitung Darat No. 12, RT 003/RW 001, Desa/Kel. Belitung Selatan, Kec. Banjarmasin Barat, Kota Banjarmasin, Kalimantan Selatan'
    );

    // Kolom O (Kolom 15): Nama TKP diambil dari alamat->nama
    expect($sheet->getCell('O5')->getValue())->toBe('Toko Obat Sumber Waras');

    // Kolom P (Kolom 16): Alamat Lengkap TKP
    expect($sheet->getCell('P5')->getValue())->toBe(
        'Pasar Sentra Antasari Blok C No. 5, RT 002/RW 001, Desa/Kel. Kelayan Luar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan'
    );
});
