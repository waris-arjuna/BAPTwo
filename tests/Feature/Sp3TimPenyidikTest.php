<?php

use App\Models\BerkasPerkara;
use App\Models\Jabatan;
use App\Models\Perkara;
use App\Models\Sp3;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

test('penerbitan SP3 mewajibkan pilihan penandatangan_id dari tabel user', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/SP3/01/2026',
        'tanggal_laporan' => '2026-06-01',
        'tahap_laporan_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Uji SP3',
        'nik' => '6371012345670001',
        'created_by' => $penyidik->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/SP3/01/2026',
        'status_formal' => null,
        'created_by' => $penyidik->id,
    ]);

    $file = UploadedFile::fake()->create('sp3.pdf', 200, 'application/pdf');

    // Uji 1: penandatangan_id dikosongkan
    $response = $this->actingAs($penyidik)->post(route('berkas-perkara.sp3.store', $berkas), [
        'nomor_surat' => 'SPPP/01/BPOM/BJM/2026',
        'tanggal_sp3' => '2026-06-15',
        'keterangan' => 'Alasan penghentian penyidikan karena kurang bukti.',
        'dokumen' => $file,
    ]);

    $response->assertSessionHasErrors(['penandatangan_id']);

    // Uji 2: penandatangan_id tidak ada di database
    $responseInvalid = $this->actingAs($penyidik)->post(route('berkas-perkara.sp3.store', $berkas), [
        'nomor_surat' => 'SPPP/01/BPOM/BJM/2026',
        'tanggal_sp3' => '2026-06-15',
        'keterangan' => 'Alasan penghentian penyidikan karena kurang bukti.',
        'penandatangan_id' => 99999,
        'dokumen' => $file,
    ]);

    $responseInvalid->assertSessionHasErrors(['penandatangan_id']);
});

test('penerbitan SP3 dengan penandatangan_id otomatis menyimpan nama dan jabatan pegawai serta penyidik utama perkara', function () {
    $admin = User::factory()->create(['role' => 'administrator']);
    $jabatanKepala = Jabatan::create([
        'nama_jabatan' => 'Kepala Balai Besar POM di Banjarmasin',
        'kode_jabatan' => 'KABALAI',
        'eselon' => 'II.b',
    ]);
    $pejabatPenandatangan = User::factory()->create([
        'nama' => 'Drs. Leonard Duma, Apt., MM',
        'jabatan_id' => $jabatanKepala->id,
        'nip' => '196805121995031001',
    ]);

    $penyidikUtama = User::factory()->create(['role' => 'penyidik', 'nama' => 'Penyidik Utama Satu']);
    $penyidikAnggota1 = User::factory()->create(['role' => 'penyidik', 'nama' => 'Penyidik Anggota A']);

    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/SP3/02/2026',
        'tanggal_laporan' => '2026-07-01',
        'tahap_laporan_status' => 'final',
        'created_by' => $admin->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Uji SP3 Lengkap',
        'nik' => '6371012345670002',
        'created_by' => $admin->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/SP3/02/2026',
        'status_formal' => null,
        'created_by' => $admin->id,
    ]);

    // Relasikan PPNS ke Perkara
    $perkara->ppnsUsers()->attach($penyidikUtama->id, ['peran' => 'utama']);
    $perkara->ppnsUsers()->attach($penyidikAnggota1->id, ['peran' => 'anggota']);

    $file = UploadedFile::fake()->create('sp3.pdf', 300, 'application/pdf');

    $response = $this->actingAs($admin)->post(route('berkas-perkara.sp3.store', $berkas), [
        'nomor_surat' => 'SPPP/05/BPOM/BJM/2026',
        'tanggal_sp3' => '2026-07-20',
        'keterangan' => 'Berdasarkan gelar perkara, perkara dihentikan.',
        'penandatangan_id' => $pejabatPenandatangan->id,
        'dokumen' => $file,
    ]);

    $response->assertRedirect(route('perkara.show', $perkara->id));
    $response->assertSessionHas('success');

    $sp3 = Sp3::where('berkas_perkara_id', $berkas->id)->first();
    expect($sp3)->not->toBeNull()
        ->and($sp3->nomor_surat)->toBe('SPPP/05/BPOM/BJM/2026')
        ->and($sp3->penyidik_utama_id)->toBe($penyidikUtama->id)
        ->and($sp3->penandatangan_nama)->toBe('Drs. Leonard Duma, Apt., MM')
        ->and($sp3->penandatangan_jabatan)->toBe('Kepala Balai Besar POM di Banjarmasin')
        ->and($sp3->penyidikUtama->id)->toBe($penyidikUtama->id);

    // Pastikan audit log mencatat data tim penyidik & penandatangan
    $this->assertDatabaseHas('audit_log', [
        'aksi' => 'terbitkan_sp3',
        'tabel' => 'sp3',
        'record_id' => $sp3->id,
        'berkas_perkara_id' => $berkas->id,
    ]);
});

test('halaman show perkara memuat modal SP3 tanpa input tim penyidik dan modal detail tetap menampilkan tim penyidik perkara', function () {
    $penyidik = User::factory()->create(['role' => 'penyidik']);
    $penyidikUtama = User::factory()->create(['role' => 'penyidik', 'nama' => 'PPNS Utama Senior']);
    $penyidikAnggota = User::factory()->create(['role' => 'penyidik', 'nama' => 'PPNS Anggota Muda']);

    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/SP3/03/2026',
        'tanggal_laporan' => '2026-08-01',
        'tahap_laporan_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Putusan SP3',
        'nik' => '6371012345670003',
        'created_by' => $penyidik->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/SP3/03/2026',
        'status_formal' => 'tidak_selesai',
        'status_proses' => 'SP3',
        'created_by' => $penyidik->id,
    ]);

    $perkara->ppnsUsers()->attach($penyidikUtama->id, ['peran' => 'utama']);
    $perkara->ppnsUsers()->attach($penyidikAnggota->id, ['peran' => 'anggota']);

    $sp3 = Sp3::create([
        'berkas_perkara_id' => $berkas->id,
        'nomor_surat' => 'SPPP/99/BPOM/BJM/2026',
        'tanggal_sp3' => '2026-08-10',
        'keterangan' => 'Penghentian penyidikan resmi.',
        'penyidik_utama_id' => $penyidikUtama->id,
        'penandatangan_nama' => 'Drs. Leonard Duma, Apt., MM',
        'penandatangan_jabatan' => 'Kepala Balai Besar POM di Banjarmasin',
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)->get(route('perkara.show', $perkara->id));
    $response->assertOk();

    // 1. Verifikasi modal input SP3 menggunakan searchable-select penandatangan dan TIDAK ada input tim penyidik
    $response->assertSee('name="penandatangan_id"', false);
    $response->assertDontSee('name="penyidik_anggota_ids[]"', false);

    // 2. Verifikasi modal detail SP3 tetap menampilkan tim penyidik perkara dan penandatangan
    $response->assertSee('SP3 Terbit (Tidak Selesai)');
    $response->assertSee('Drs. Leonard Duma, Apt., MM');
    $response->assertSee('Kepala Balai Besar POM di Banjarmasin');
    $response->assertSee('PPNS Utama Senior');
    $response->assertSee('PPNS Anggota Muda');
});
