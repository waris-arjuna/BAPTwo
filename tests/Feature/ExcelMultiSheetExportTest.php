    <?php

use App\Enums\JenisTahap;
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

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->penyidik()->create();
    $this->pimpinan = User::factory()->pimpinan()->create();
    $this->admin = User::factory()->administrator()->create();

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/X/2026/001',
        'tanggal_laporan_kejadian' => '2026-03-10',
        'nomor_surat_perintah' => 'SP/X/2026/001',
        'tanggal_surat_perintah' => '2026-03-12',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $this->penyidik->id,
    ]);
});

test('perkara rekap export menghasilkan spreadsheet multi-sheet dengan sheet 1 history lapju dan sheet 2 ringkasan dashboard', function () {
    // Siapkan data dummy perkara dan tahapan
    $alamat = Alamat::create([
        'provinsi_kode' => '63',
        'provinsi_nama' => 'Kalimantan Selatan',
        'kabupaten_kode' => '6371',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'kecamatan_kode' => '637104',
        'kecamatan_nama' => 'Banjarmasin Utara',
        'desa_kode' => '6371041006',
        'desa_nama' => 'Pangeran',
    ]);

    $tkp = Tkp::create([
        'alamat_id' => $alamat->id,
        'nama_sarana' => 'Toko Obat Sehat',
        'klasifikasi' => 'retailer',
        'modus' => 'offline',
    ]);

    $this->perkara->update(['tkp_id' => $tkp->id]);

    $tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Uji Ekspor',
        'created_by' => $this->penyidik->id,
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/2026/001',
        'status_proses' => 'TAHAP II',
        'progress_persen' => 100,
        'status_formal' => 'selesai',
        'tanggal_spdp' => '2026-03-15',
        'created_by' => $this->penyidik->id,
    ]);

    $pasal = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'nomor' => '17',
        'tahun' => '2023',
        'pasal' => 'Pasal 435',
        'isi' => 'Mengedarkan sediaan farmasi tanpa standar keamanan.',
        'status' => 'aktif',
    ]);
    $berkas->pasals()->attach($pasal->id);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_2->value,
        'nomor_surat' => 'B-12/T2/2026',
        'tanggal_surat' => '2026-04-10',
        'status' => 'final',
        'bobot' => 100,
        'progress_persen' => 100,
        'created_by' => $this->penyidik->id,
    ]);

    $exporter = new PerkaraRekapExport(mode: 'tahun', tahun: 2026);
    $spreadsheet = $exporter->generateSpreadsheet();

    // 1. Verifikasi tepat 2 sheet
    expect($spreadsheet->getSheetCount())->toBe(2);

    $sheetNames = $spreadsheet->getSheetNames();
    expect($sheetNames[0])->toBe('History LAPJU')
        ->and($sheetNames[1])->toBe('Ringkasan Dashboard');

    // 2. Verifikasi sheet aktif saat dibuka adalah Sheet 1
    expect($spreadsheet->getActiveSheetIndex())->toBe(0);

    // 3. Verifikasi Sheet 2 memuat 5 blok tabel agregasi
    $summarySheet = $spreadsheet->getSheetByName('Ringkasan Dashboard');
    expect($summarySheet)->not->toBeNull();

    $sheetContent = [];
    for ($r = 1; $r <= 70; $r++) {
        $val = $summarySheet->getCell("A{$r}")->getValue();
        if ($val) {
            $sheetContent[] = (string) $val;
        }
    }
    $allText = implode("\n", $sheetContent);

    expect($allText)->toContain('RINGKASAN EKSEKUTIF & STATISTIK PENANGANAN PERKARA')
        ->and($allText)->toContain('I. REKAPITULASI STATUS PERKARA')
        ->and($allText)->toContain('II. TREN PERKARA BULANAN')
        ->and($allText)->toContain('III. SEBARAN WILAYAH TEMPAT KEJADIAN PERKARA (TKP)')
        ->and($allText)->toContain('IV. PASAL UNDANG-UNDANG PALING BANYAK DISANGKAKAN')
        ->and($allText)->toContain('V. RATA-RATA WAKTU PENYELESAIAN PER TAHAPAN');
});

test('endpoint ekspor excel dapat diunduh oleh penyidik dan menghasilkan file xlsx yang valid', function () {
    $response = $this->actingAs($this->penyidik)
        ->get(route('perkara.export', ['mode' => 'tahun', 'tahun' => 2026]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect($response->headers->get('Content-Disposition'))->toContain('LAPJU_2026_');
});

test('role umum atau pimpinan diblokir dari mendownload ekspor excel', function () {
    $this->actingAs($this->pimpinan)
        ->get(route('perkara.export', ['mode' => 'tahun', 'tahun' => 2026]))
        ->assertForbidden();
});
