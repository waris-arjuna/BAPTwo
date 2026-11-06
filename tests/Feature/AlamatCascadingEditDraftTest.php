<?php

use App\Models\Alamat;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\Tkp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

test('endpoint wilayah desa fallback ke JSON lokal districts_kalsel untuk kecamatan Banjarmasin', function () {
    Http::fake([
        'https://api.kodewilayah.web.id/*' => Http::response(null, 500),
    ]);

    $user = User::factory()->create();

    // 1. Banjarmasin Selatan (637101) - 12 kelurahan
    $resSelatan = $this->actingAs($user)
        ->getJson(route('wilayah.desa', '637101'))
        ->assertOk()
        ->assertJsonPath('success', true);

    $dataSelatan = $resSelatan->json('data');
    expect(count($dataSelatan))->toBe(12);
    $kelayanBarat = collect($dataSelatan)->firstWhere('code', '6371011004');
    expect($kelayanBarat)->not->toBeNull()
        ->and($kelayanBarat['name'])->toBe('Kelayan Barat');

    // 2. Banjarmasin Timur (637102) - 9 kelurahan
    $resTimur = $this->actingAs($user)
        ->getJson(route('wilayah.desa', '637102'))
        ->assertOk();
    $dataTimur = $resTimur->json('data');
    expect(count($dataTimur))->toBe(9);
    $kuripan = collect($dataTimur)->firstWhere('code', '6371021001');
    expect($kuripan)->not->toBeNull()
        ->and($kuripan['name'])->toBe('Kuripan');

    // 3. Banjarmasin Barat (637103) - 9 kelurahan
    $resBarat = $this->actingAs($user)
        ->getJson(route('wilayah.desa', '637103'))
        ->assertOk();
    $dataBarat = $resBarat->json('data');
    expect(count($dataBarat))->toBe(9);
    $belitungSelatan = collect($dataBarat)->firstWhere('code', '6371031001');
    expect($belitungSelatan)->not->toBeNull()
        ->and($belitungSelatan['name'])->toBe('Belitung Selatan');

    // 4. Banjarbaru Cempaka (637203) - 4 kelurahan
    $resCempaka = $this->actingAs($user)
        ->getJson(route('wilayah.desa', '637203'))
        ->assertOk();
    $dataCempaka = $resCempaka->json('data');
    expect(count($dataCempaka))->toBe(4);
});

test('halaman edit draft perkara menyajikan data tkp tersimpan termasuk desa_kode dan desa_nama', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);

    $alamatTkp = Alamat::create([
        'provinsi_kode' => '63',
        'provinsi_nama' => 'Kalimantan Selatan',
        'kabupaten_kode' => '6371',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'kecamatan_kode' => '637101',
        'kecamatan_nama' => 'Banjarmasin Selatan',
        'desa_kode' => '6371011004',
        'desa_nama' => 'Kelayan Barat',
        'rt' => '005',
        'rw' => '002',
        'alamat_detail' => 'Jl. Kelayan B No. 12',
    ]);

    $tkp = Tkp::create([
        'nama' => 'Kios Obat Berkah',
        'alamat_id' => $alamatTkp->id,
    ]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/WIL/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/WIL/01/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'tahap_perkara_status' => 'draft',
        'tkp_id' => $tkp->id,
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)->get(route('perkara.edit', $perkara));
    $response->assertOk();

    // Pastikan konfigurasi inisialisasi tkpWilayahForm menerima desaKode yang tersimpan
    $response->assertSee('6371011004');
    $response->assertSee('Kelayan Barat');
    $response->assertSee('637101');
    $response->assertSee('Banjarmasin Selatan');
    $response->assertSee('6371');
    $response->assertSee('Kota Banjarmasin');
});

test('halaman edit draft perkara menyajikan alamat lengkap tersangka tersimpan', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);

    $alamatTersangka = Alamat::create([
        'provinsi_kode' => '63',
        'provinsi_nama' => 'Kalimantan Selatan',
        'kabupaten_kode' => '6371',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'kecamatan_kode' => '637101',
        'kecamatan_nama' => 'Banjarmasin Selatan',
        'desa_kode' => '6371011004',
        'desa_nama' => 'Kelayan Barat',
        'rt' => '001',
        'rw' => '001',
        'alamat_detail' => 'Gg. Sejahtera No. 5',
    ]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/WIL/02/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/WIL/02/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'tahap_perkara_status' => 'draft',
        'created_by' => $penyidik->id,
    ]);

    Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Ahmad Pengedar',
        'nik' => '6371010101900001',
        'alamat_id' => $alamatTersangka->id,
    ]);

    $response = $this->actingAs($penyidik)->get(route('perkara.edit', $perkara));
    $response->assertOk();

    // Verifikasi bahwa data tersangkaAlamatModalForm memuat desa_kode yang tersimpan
    $response->assertSee('6371011004');
    $response->assertSee('Kelayan Barat');
});

test('halaman profile memuat data alamat pengguna lengkap dengan desa_kode', function () {
    $alamatUser = Alamat::create([
        'provinsi_kode' => '63',
        'provinsi_nama' => 'Kalimantan Selatan',
        'kabupaten_kode' => '6371',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'kecamatan_kode' => '637101',
        'kecamatan_nama' => 'Banjarmasin Selatan',
        'desa_kode' => '6371011004',
        'desa_nama' => 'Kelayan Barat',
        'alamat_detail' => 'Komp. Melati Indah',
    ]);

    $user = User::factory()->create([
        'alamat_id' => $alamatUser->id,
    ]);

    $response = $this->actingAs($user)->get(route('profile'));
    $response->assertOk();

    // Pastikan konfigurasi Alpine profile memuat desa_kode tersimpan
    $response->assertSee('6371011004');
    $response->assertSee('Kelayan Barat');
});
