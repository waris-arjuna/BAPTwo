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
        'isi' => 'Sediaan farmasi tanpa izin edar.',
        'status' => 'aktif',
    ]);

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/REVISI-C/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/REVISI-C/2026',
        'tanggal_surat_perintah' => '2026-10-11',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
    ]);
});

it('rejects NIK that is not exactly 16 numeric digits (Poin 17)', function () {
    // 15 digits
    $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $this->perkara), [
            'action_type' => 'lanjut',
            'tkp_provinsi_kode' => '63',
            'tkp_kabupaten_kode' => '63.71',
            'tkp_kecamatan_kode' => '63.71.01',
            'tkp_desa_kode' => '63.71.01.1001',
            'tkp_alamat_detail' => 'Jl. Test',
            'tkp_klasifikasi' => 'retailer',
            'tkp_legalitas' => 'legal',
            'tkp_modus' => 'online',
            'ppns_utama_id' => $this->penyidik->id,
            'tersangka_nama' => 'Budi Santoso',
            'tersangka_nik' => '123456789012345', // 15 digits
            'tersangka_jenis_kelamin' => 'laki-laki',
            'tersangka_tempat_lahir' => 'Banjarmasin',
            'tersangka_tanggal_lahir' => '1990-01-01',
            'tersangka_agama' => 'islam',
            'tersangka_pekerjaan' => 'Wiraswasta',
            'tersangka_kewarganegaraan' => 'WNI',
            'pasal_ids' => [$this->pasal->id],
        ])
        ->assertSessionHasErrors(['tersangka.0.nik']);

    // 16 characters containing letters
    $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $this->perkara), [
            'action_type' => 'lanjut',
            'tkp_provinsi_kode' => '63',
            'tkp_kabupaten_kode' => '63.71',
            'tkp_kecamatan_kode' => '63.71.01',
            'tkp_desa_kode' => '63.71.01.1001',
            'tkp_alamat_detail' => 'Jl. Test',
            'tkp_klasifikasi' => 'retailer',
            'tkp_legalitas' => 'legal',
            'tkp_modus' => 'online',
            'ppns_utama_id' => $this->penyidik->id,
            'tersangka_nama' => 'Budi Santoso',
            'tersangka_nik' => '123456789012345A', // contains letter
            'tersangka_jenis_kelamin' => 'laki-laki',
            'tersangka_tempat_lahir' => 'Banjarmasin',
            'tersangka_tanggal_lahir' => '1990-01-01',
            'tersangka_agama' => 'islam',
            'tersangka_pekerjaan' => 'Wiraswasta',
            'tersangka_kewarganegaraan' => 'WNI',
            'pasal_ids' => [$this->pasal->id],
        ])
        ->assertSessionHasErrors(['tersangka.0.nik']);
});

it('accepts valid 16-digit numeric NIK and saves Perkara Pra-SPDP with single entity (Poin 11, 13, 17)', function () {
    $response = $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $this->perkara), [
            'action_type' => 'lanjut',
            'tkp_provinsi_kode' => '63',
            'tkp_kabupaten_kode' => '63.71',
            'tkp_kecamatan_kode' => '63.71.01',
            'tkp_desa_kode' => '63.71.01.1001',
            'tkp_alamat_detail' => 'Jl. Ahmad Yani Km 6',
            'tkp_klasifikasi' => 'distributor',
            'tkp_legalitas' => 'ilegal',
            'tkp_modus' => 'offline',
            'ppns_utama_id' => $this->penyidik->id,
            'tersangka_nama' => 'Hendra Pratama',
            'tersangka_nik' => '6371011234560001', // exactly 16 digits
            'tersangka_jenis_kelamin' => 'laki-laki',
            'tersangka_tempat_lahir' => 'Banjarmasin',
            'tersangka_tanggal_lahir' => '1985-06-15',
            'tersangka_agama' => 'islam',
            'tersangka_pekerjaan' => 'Pengusaha',
            'tersangka_kewarganegaraan' => 'WNI',
            'pasal_ids' => [$this->pasal->id],
        ]);

    $response->assertRedirect(route('perkara.show', $this->perkara));

    $this->perkara->refresh();
    expect($this->perkara->tahap_perkara_status)->toBe('final')
        ->and($this->perkara->tersangkas()->count())->toBe(1)
        ->and($this->perkara->berkasPerkaras()->count())->toBe(1);

    $tersangka = $this->perkara->tersangkas()->first();
    expect($tersangka->nik)->toBe('6371011234560001')
        ->and($tersangka->nama)->toBe('Hendra Pratama');

    $berkas = $this->perkara->berkasPerkaras()->first();
    // Nomor Berkas otomatis sama dengan Nomor Laporan Kejadian (Poin 11 & 13)
    expect($berkas->nomor_berkas)->toBe('LK/REVISI-C/2026')
        ->and($berkas->pasals()->count())->toBe(1);
});

it('rejects province other than Kalimantan Selatan (kode 63) per Poin 4', function () {
    $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $this->perkara), [
            'action_type' => 'lanjut',
            'tkp_provinsi_kode' => '31', // DKI Jakarta
            'tkp_kabupaten_kode' => '31.71',
            'tkp_kecamatan_kode' => '31.71.01',
            'tkp_desa_kode' => '31.71.01.1001',
            'tkp_alamat_detail' => 'Jl. Thamrin',
            'tkp_klasifikasi' => 'retailer',
            'tkp_legalitas' => 'legal',
            'tkp_modus' => 'online',
            'ppns_utama_id' => $this->penyidik->id,
            'tersangka_nama' => 'Budi',
            'tersangka_nik' => '6371011234560001',
            'tersangka_jenis_kelamin' => 'laki-laki',
            'tersangka_tempat_lahir' => 'Jakarta',
            'tersangka_tanggal_lahir' => '1990-01-01',
            'tersangka_agama' => 'islam',
            'tersangka_pekerjaan' => 'Karyawan',
            'tersangka_kewarganegaraan' => 'WNI',
            'pasal_ids' => [$this->pasal->id],
        ])
        ->assertSessionHasErrors(['tkp_provinsi_kode']);
});
