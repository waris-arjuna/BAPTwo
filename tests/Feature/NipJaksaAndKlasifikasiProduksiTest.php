<?php

use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->penyidik()->create();
    $this->pasal = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'pasal' => 'Pasal 435',
        'nomor' => '17',
        'tahun' => '2023',
        'isi' => 'Mengedarkan sediaan farmasi tanpa izin',
        'status' => 'aktif',
    ]);
});

function validPerkaraPayload(User $penyidik, Pasal $pasal, array $overrides = []): array
{
    return array_merge([
        'action_type' => 'lanjut',
        'tkp_provinsi_kode' => '63',
        'tkp_provinsi_nama' => 'Kalimantan Selatan',
        'tkp_kabupaten_kode' => '63.71',
        'tkp_kabupaten_nama' => 'Kota Banjarmasin',
        'tkp_kecamatan_kode' => '63.71.01',
        'tkp_kecamatan_nama' => 'Banjarmasin Tengah',
        'tkp_desa_kode' => '63.71.01.1001',
        'tkp_desa_nama' => 'Kertak Baru',
        'tkp_alamat_detail' => 'Jl. Pangeran Samudra No. 10',
        'tkp_klasifikasi' => 'produksi',
        'tkp_legalitas' => 'legal',
        'tkp_modus' => 'offline',
        'ppns_utama_id' => $penyidik->id,
        'penuntut_umum_nama' => 'Jaksa Penuntut Pratama, S.H.',
        'penuntut_umum_nip' => '198501012010011001',
        'tersangka' => [
            [
                'nama' => 'Tersangka Contoh',
                'nik' => '6371011234560001',
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'laki-laki',
                'agama' => 'islam',
                'pekerjaan' => 'Wiraswasta',
                'kewarganegaraan' => 'WNI',
                'provinsi_kode' => '63',
                'provinsi_nama' => 'Kalimantan Selatan',
                'kabupaten_kode' => '63.71',
                'kabupaten_nama' => 'Kota Banjarmasin',
                'kecamatan_kode' => '63.71.01',
                'kecamatan_nama' => 'Banjarmasin Tengah',
                'desa_kode' => '63.71.01.1001',
                'desa_nama' => 'Kertak Baru',
                'alamat_detail' => 'Jl. Lambung Mangkurat No. 5',
                'nomor_berkas' => 'BP/001/X/2026',
                'pasal_ids' => [$pasal->id],
            ],
        ],
    ], $overrides);
}

it('accepts tkp klasifikasi produksi in validation and persists to database', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_sprindik_status' => 'final',
        'nomor_surat_perintah' => 'SP/001/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
    ]);

    $response = $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $perkara), validPerkaraPayload($this->penyidik, $this->pasal, [
            'tkp_klasifikasi' => 'produksi',
        ]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('perkara.show', $perkara));

    $perkara->refresh();
    expect($perkara->tkp)->not->toBeNull()
        ->and($perkara->tkp->klasifikasi)->toBe('produksi');
});

it('rejects invalid tkp klasifikasi outside retailer, distributor, and produksi', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/002/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_sprindik_status' => 'final',
        'nomor_surat_perintah' => 'SP/002/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
    ]);

    $response = $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $perkara), validPerkaraPayload($this->penyidik, $this->pasal, [
            'tkp_klasifikasi' => 'pabrik_luar',
        ]));

    $response->assertSessionHasErrors(['tkp_klasifikasi']);
});

it('accepts valid 18 digits numeric nip jaksa and persists to database', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/003/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_sprindik_status' => 'final',
        'nomor_surat_perintah' => 'SP/003/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
    ]);

    $validNip = '198205122008011002';

    $response = $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $perkara), validPerkaraPayload($this->penyidik, $this->pasal, [
            'penuntut_umum_nip' => $validNip,
        ]));

    $response->assertSessionHasNoErrors();
    $perkara->refresh();
    expect($perkara->penuntut_umum_nip)->toBe($validNip);
});

it('allows penuntut umum nip to be empty or null', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/004/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_sprindik_status' => 'final',
        'nomor_surat_perintah' => 'SP/004/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
    ]);

    $response = $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $perkara), validPerkaraPayload($this->penyidik, $this->pasal, [
            'penuntut_umum_nip' => null,
        ]));

    $response->assertSessionHasNoErrors();
    $perkara->refresh();
    expect($perkara->penuntut_umum_nip)->toBeNull();
});

it('rejects penuntut umum nip with less than 18 digits with Indonesian message', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/005/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_sprindik_status' => 'final',
        'nomor_surat_perintah' => 'SP/005/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
    ]);

    $response = $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $perkara), validPerkaraPayload($this->penyidik, $this->pasal, [
            'penuntut_umum_nip' => '198205122008011',
        ]));

    $response->assertSessionHasErrors([
        'penuntut_umum_nip' => 'NIP Jaksa Penuntut Umum harus tepat 18 digit angka.',
    ]);
});

it('rejects penuntut umum nip with more than 18 digits with Indonesian message', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/006/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_sprindik_status' => 'final',
        'nomor_surat_perintah' => 'SP/006/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
    ]);

    $response = $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $perkara), validPerkaraPayload($this->penyidik, $this->pasal, [
            'penuntut_umum_nip' => '19820512200801100299',
        ]));

    $response->assertSessionHasErrors([
        'penuntut_umum_nip' => 'NIP Jaksa Penuntut Umum harus tepat 18 digit angka.',
    ]);
});

it('rejects penuntut umum nip with non-numeric characters with Indonesian message', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/007/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_sprindik_status' => 'final',
        'nomor_surat_perintah' => 'SP/007/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
    ]);

    $response = $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $perkara), validPerkaraPayload($this->penyidik, $this->pasal, [
            'penuntut_umum_nip' => '1982051220080110AB',
        ]));

    $response->assertSessionHasErrors('penuntut_umum_nip');
});

it('renders option produksi and numeric input protection in perkara form', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/008/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_sprindik_status' => 'final',
        'nomor_surat_perintah' => 'SP/008/X/2026',
        'tanggal_surat_perintah' => '2026-10-03',
    ]);

    $response = $this->actingAs($this->penyidik)->get(route('perkara.edit', $perkara));
    $response->assertOk();

    $response->assertSee('<option value="produksi"', false);
    $response->assertSee('Produksi</option>', false);
    $response->assertSee('name="penuntut_umum_nip"', false);
    $response->assertSee('maxlength="18"', false);
    $response->assertSee('inputmode="numeric"', false);
});
