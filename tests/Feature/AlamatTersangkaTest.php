<?php

use App\Models\Alamat;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use App\Services\PerkaraService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('perkara service menyimpan alamat tersangka dan mengaitkan alamat_id', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/01/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/01/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    $service = app(PerkaraService::class);

    $payload = [
        'action_type' => 'draft',
        'tkp_klasifikasi' => 'retailer',
        'tkp_legalitas' => 'legal',
        'tkp_modus' => 'offline',
        'tkp_provinsi_kode' => '63',
        'tkp_provinsi_nama' => 'Kalimantan Selatan',
        'tkp_kabupaten_kode' => '6371',
        'tkp_kabupaten_nama' => 'Kota Banjarmasin',
        'tkp_kecamatan_kode' => '637101',
        'tkp_kecamatan_nama' => 'Banjarmasin Selatan',
        'tkp_desa_kode' => '6371011001',
        'tkp_desa_nama' => 'Kelayan Barat',
        'tkp_alamat_detail' => 'Gudang Retail',
        'tersangka' => [
            [
                'nama' => 'Budi Santoso',
                'nik' => '6371011234560001',
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Wiraswasta',
                'kewarganegaraan' => 'WNI',
                'provinsi_kode' => '32',
                'provinsi_nama' => 'Jawa Barat',
                'kabupaten_kode' => '3273',
                'kabupaten_nama' => 'Kota Bandung',
                'kecamatan_kode' => '327301',
                'kecamatan_nama' => 'Sukasari',
                'desa_kode' => '3273011001',
                'desa_nama' => 'Sarijadi',
                'rt' => '02',
                'rw' => '05',
                'alamat_detail' => 'Jl. Sarijadi Raya No. 12',
            ],
        ],
    ];

    $service->savePerkaraBerkas($perkara, $payload, $penyidik);

    $tersangka = Tersangka::where('perkara_id', $perkara->id)->first();
    expect($tersangka)->not->toBeNull()
        ->and($tersangka->alamat_id)->not->toBeNull();

    $alamat = Alamat::find($tersangka->alamat_id);
    expect($alamat)->not->toBeNull()
        ->and($alamat->provinsi_kode)->toBe('32')
        ->and($alamat->kabupaten_nama)->toBe('Kota Bandung')
        ->and($alamat->alamat_detail)->toBe('Jl. Sarijadi Raya No. 12');
});

test('validasi form perkara menolak tanggal lahir masa depan', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/02/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/02/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)->put(route('perkara.update', $perkara), [
        'action_type' => 'draft',
        'tersangka' => [
            [
                'nama' => 'Tersangka Masa Depan',
                'tanggal_lahir' => now()->addDay()->toDateString(),
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['tersangka.0.tanggal_lahir']);
});

test('simpan draft tetap berhasil tanpa alamat tersangka', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/03/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/03/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)->put(route('perkara.update', $perkara), [
        'action_type' => 'draft',
        'tersangka' => [
            [
                'nama' => 'Tersangka Draft',
            ],
        ],
    ]);

    $response->assertRedirect(route('perkara.show', $perkara));
    $tersangka = Tersangka::where('perkara_id', $perkara->id)->first();
    expect($tersangka)->not->toBeNull()
        ->and($tersangka->alamat_id)->toBeNull();
});

test('simpan dan lanjut menolak jika alamat tersangka tidak lengkap', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/04/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/04/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    $pasal = Pasal::create([
        'nama_pasal' => 'UU Uji',
        'pasal' => 'Pasal 1',
        'nomor' => '1',
        'tahun' => '2026',
        'isi' => 'Uji pasal',
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($penyidik)->put(route('perkara.update', $perkara), [
        'action_type' => 'lanjut',
        'tkp_klasifikasi' => 'retailer',
        'tkp_legalitas' => 'legal',
        'tkp_modus' => 'offline',
        'tkp_provinsi_kode' => '63',
        'tkp_kabupaten_kode' => '6371',
        'tkp_kecamatan_kode' => '637101',
        'tkp_desa_kode' => '6371011001',
        'tkp_alamat_detail' => 'Gudang Retail',
        'ppns_utama_id' => $penyidik->id,
        'tersangka' => [
            [
                'nama' => 'Budi Santoso',
                'nik' => '6371011234560001',
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Wiraswasta',
                'kewarganegaraan' => 'WNI',
                'pasal_ids' => [$pasal->id],
                // Tanpa alamat
            ],
        ],
    ]);

    $response->assertSessionHasErrors([
        'tersangka.0.provinsi_kode',
        'tersangka.0.kabupaten_kode',
        'tersangka.0.kecamatan_kode',
        'tersangka.0.desa_kode',
        'tersangka.0.alamat_detail',
    ]);
});

test('form perkara merender datepicker untuk tanggal lahir tersangka', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/05/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/05/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)->get(route('perkara.edit', $perkara));
    $response->assertOk();
    $response->assertSee('name="tersangka_tanggal_lahir"', false);
    $response->assertSee('custom-datepicker', false);
    $response->assertDontSee('<input type="date" name="tersangka_tanggal_lahir"', false);
});

test('form perkara merender box ringkasan alamat tersangka dan modal alamat', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/06/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/06/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)->get(route('perkara.edit', $perkara));
    $response->assertOk();
    $response->assertSee('Atur Alamat Tersangka', false);
    $response->assertSee('tersangka_alamat_ringkasan', false);
    $response->assertSee('isAlamatModalOpen', false);
    $response->assertSee('name="tersangka_provinsi_kode"', false);
    $response->assertSee('name="tersangka_alamat_detail"', false);
});

test('halaman show perkara menampilkan alamat lengkap tersangka', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/07/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/07/X/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    $alamat = Alamat::create([
        'provinsi_kode' => '32',
        'provinsi_nama' => 'Jawa Barat',
        'kabupaten_kode' => '3273',
        'kabupaten_nama' => 'Kota Bandung',
        'kecamatan_kode' => '327301',
        'kecamatan_nama' => 'Sukasari',
        'desa_kode' => '3273011001',
        'desa_nama' => 'Sarijadi',
        'rt' => '02',
        'rw' => '05',
        'alamat_detail' => 'Jl. Sarijadi Raya No. 12',
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Budi Santoso',
        'alamat_id' => $alamat->id,
        'created_by' => $penyidik->id,
    ]);

    $perkara->berkasPerkaras()->create([
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/01/2026',
        'status_formal' => null,
        'status_proses' => 'Baru',
        'progress_persen' => 0,
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)->get(route('perkara.show', $perkara));
    $response->assertOk();
    $response->assertSee('Jl. Sarijadi Raya No. 12', false);
    $response->assertSee('Kota Bandung', false);
    $response->assertSee('Jawa Barat', false);
});
