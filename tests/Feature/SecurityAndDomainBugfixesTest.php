<?php

use App\Enums\JenisTahap;
use App\Enums\UserRole;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('config:clear');
});

test('pimpinan dan umum diblokir dari GET dan POST data-petugas (403 Forbidden)', function () {
    $pimpinan = User::factory()->create(['role' => UserRole::PIMPINAN, 'is_active' => true]);
    $umum = User::factory()->create(['role' => UserRole::UMUM, 'is_active' => true]);
    $targetPetugas = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);

    $this->actingAs($pimpinan)->get(route('data-petugas.index'))->assertForbidden();
    $this->actingAs($umum)->get(route('data-petugas.index'))->assertForbidden();

    $this->actingAs($pimpinan)->post(route('data-petugas.store'), [
        'user_id' => $targetPetugas->id,
        'role' => UserRole::UMUM->value,
    ])->assertForbidden();

    $this->actingAs($umum)->post(route('data-petugas.store'), [
        'user_id' => $targetPetugas->id,
        'role' => UserRole::PENYIDIK->value,
    ])->assertForbidden();
});

test('administrator dan penyidik dapat mengakses data-petugas dan mengubah role petugas non-admin', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMINISTRATOR, 'is_active' => true]);
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $target = User::factory()->create(['role' => UserRole::UMUM, 'is_active' => true]);

    // Admin akses dan ubah
    $this->actingAs($admin)->get(route('data-petugas.index'))->assertOk();
    $this->actingAs($admin)->post(route('data-petugas.store'), [
        'user_id' => $target->id,
        'role' => UserRole::PENYIDIK->value,
        'is_active' => 1,
    ])->assertRedirect(route('data-petugas.index'));
    expect($target->fresh()->role)->toBe(UserRole::PENYIDIK);

    // Penyidik akses dan ubah petugas lain
    $this->actingAs($penyidik)->get(route('data-petugas.index'))->assertOk();
    $this->actingAs($penyidik)->post(route('data-petugas.store'), [
        'user_id' => $target->id,
        'role' => UserRole::UMUM->value,
        'is_active' => 1,
    ])->assertRedirect(route('data-petugas.index'));
    expect($target->fresh()->role)->toBe(UserRole::UMUM);

    // Penyidik dilarang mengubah akun dirinya sendiri
    $this->actingAs($penyidik)->post(route('data-petugas.store'), [
        'user_id' => $penyidik->id,
        'role' => UserRole::PIMPINAN->value,
        'is_active' => 1,
    ])->assertSessionHasErrors('user_id');
});

test('recovery-data dan sampah hanya dapat diakses oleh administrator (403 bagi penyidik, pimpinan, umum)', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMINISTRATOR, 'is_active' => true]);
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $pimpinan = User::factory()->create(['role' => UserRole::PIMPINAN, 'is_active' => true]);

    $this->actingAs($admin)->get(route('recovery-data.index'))->assertOk();
    $this->actingAs($admin)->get(route('sampah.index'))->assertOk();

    $this->actingAs($penyidik)->get(route('recovery-data.index'))->assertForbidden();
    $this->actingAs($penyidik)->get(route('sampah.index'))->assertForbidden();

    $this->actingAs($pimpinan)->get(route('recovery-data.index'))->assertForbidden();
    $this->actingAs($pimpinan)->get(route('sampah.index'))->assertForbidden();
});

test('penyidik diblokir mengelola master data jabatan dan bidang, tetapi diizinkan untuk instansi hukum', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);

    // Akses POST master data jabatan -> ditolak 403
    $this->actingAs($penyidik)->post(route('master-data.store', 'jabatan'), [
        'nama_jabatan' => 'Auditor Muda',
    ])->assertForbidden();

    // Akses POST master data bidang -> ditolak 403
    $this->actingAs($penyidik)->post(route('master-data.store', 'bidang'), [
        'nama_bidang' => 'Penindakan Khusus',
    ])->assertForbidden();

    // Akses POST master data instansi-hukum -> diizinkan
    $this->actingAs($penyidik)->post(route('master-data.store', 'instansi-hukum'), [
        'nama_instansi' => 'Kejaksaan Negeri Banjarmasin Baru',
        'jenis' => 'kejaksaan',
        'wilayah_hukum' => 'Kota Banjarmasin',
    ])->assertRedirect();
});

test('finalisasi draft tahapan wajib mengunggah file PDF jika belum ada dokumen tersimpan', function () {
    Storage::fake('local');
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/01/TEST/2026',
        'tanggal_laporan_kejadian' => '2026-05-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/01/TEST/2026',
        'tanggal_surat_perintah' => '2026-05-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Test',
        'created_by' => $penyidik->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/01/TEST/2026',
        'created_by' => $penyidik->id,
    ]);

    // Buat draft tahapan SPDP tanpa dokumen
    $tahapDraft = TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'B-01/SPDP/2026',
        'tanggal_surat' => '2026-05-10',
        'status' => 'draft',
        'created_by' => $penyidik->id,
    ]);

    // Update sebagai draft tanpa dokumen -> diizinkan
    $this->actingAs($penyidik)->put(route('berkas-perkara.tahapan.update', [$berkas, $tahapDraft]), [
        'action_type' => 'draft',
        'nomor_surat' => 'B-01/SPDP/2026-REV',
        'tanggal_surat' => '2026-05-11',
    ])->assertRedirect();

    // Update sebagai 'lanjut' tanpa dokumen -> ditolak validasi
    $this->actingAs($penyidik)->put(route('berkas-perkara.tahapan.update', [$berkas, $tahapDraft]), [
        'action_type' => 'lanjut',
        'nomor_surat' => 'B-01/SPDP/2026-REV',
        'tanggal_surat' => '2026-05-11',
    ])->assertSessionHasErrors(['dokumen' => 'Dokumen wajib diunggah saat melanjutkan tahapan.']);

    // Update sebagai 'lanjut' dengan upload PDF -> berhasil
    $pdf = UploadedFile::fake()->create('spdp.pdf', 1024, 'application/pdf');
    $this->actingAs($penyidik)->put(route('berkas-perkara.tahapan.update', [$berkas, $tahapDraft]), [
        'action_type' => 'lanjut',
        'nomor_surat' => 'B-01/SPDP/2026-REV',
        'tanggal_surat' => '2026-05-11',
        'dokumen' => $pdf,
    ])->assertRedirect();

    expect($tahapDraft->fresh()->isFinal())->toBeTrue();
});

test('sub tahapan tersimpan sebagai draft jika tahapan induk disimpan sebagai draft', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/02/TEST/2026',
        'tanggal_laporan_kejadian' => '2026-05-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/02/TEST/2026',
        'tanggal_surat_perintah' => '2026-05-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Sub',
        'created_by' => $penyidik->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/02/TEST/2026',
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)->post(route('berkas-perkara.tahapan.store', $berkas), [
        'action_type' => 'draft',
        'jenis_tahap' => JenisTahap::SPDP->value,
        'nomor_surat' => 'SPDP/DRAFT/01',
        'tanggal_surat' => '2026-06-01',
        'p16_nomor_surat' => 'P16/DRAFT/01',
        'p16_tanggal_surat' => '2026-06-02',
    ]);

    $response->assertRedirect();

    $subTahapP16 = TahapanPerkara::where('berkas_perkara_id', $berkas->id)
        ->where('jenis_tahap', JenisTahap::P16->value)
        ->first();

    expect($subTahapP16)->not->toBeNull();
    expect($subTahapP16->status)->toBe('draft');
    expect($subTahapP16->finalized_at)->toBeNull();
});

test('sprindik show mengembalikan 404 jika sprindik belum dibuat untuk perkara tersebut', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $perkaraTanpaSprindik = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/03/TEST/2026',
        'tanggal_laporan_kejadian' => '2026-05-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => null,
        'tahap_sprindik_status' => null,
        'created_by' => $penyidik->id,
    ]);

    $this->actingAs($penyidik)
        ->get(route('sprindik.show', $perkaraTanpaSprindik))
        ->assertNotFound();
});

test('potensi terlimpah dan data perkara menampilkan data tanpa membebani memori', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/04/TEST/2026',
        'tanggal_laporan_kejadian' => '2026-01-15',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/04/TEST/2026',
        'tanggal_surat_perintah' => '2026-01-16',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka SLA',
        'created_by' => $penyidik->id,
    ]);
    BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/04/TEST/2026',
        'status_formal' => null,
        'tanggal_spdp' => '2026-01-20',
        'sla_deadline' => '2026-02-20',
        'created_by' => $penyidik->id,
    ]);

    $this->actingAs($penyidik)
        ->get(route('potensi-terlimpah.index'))
        ->assertOk()
        ->assertSee('Potensi Terlimpah');

    $this->actingAs($penyidik)
        ->get(route('data-perkara.index', ['sla' => 'lewat_sla']))
        ->assertOk();
});

test('penetapan dpo berhasil pada berkas berstatus formal dalam_proses dan ditolak jika sp3 atau putusan final', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/DPO/01',
        'tanggal_laporan_kejadian' => '2026-02-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/DPO/01',
        'tanggal_surat_perintah' => '2026-02-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Buron',
        'created_by' => $penyidik->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/DPO/01',
        'status_formal' => null, // Dalam Proses
        'is_dpo' => false,
        'created_by' => $penyidik->id,
    ]);

    // DPO berhasil ditetapkan pada berkas yang sedang berjalan
    $this->actingAs($penyidik)->post(route('berkas-perkara.dpo.store', $berkas), [
        'nomor_surat' => 'DPO/001/2026',
        'tanggal_penetapan' => '2026-02-10',
        'keterangan' => 'Kabur saat penyidikan',
    ])->assertRedirect();

    expect($berkas->fresh()->is_dpo)->toBeTrue();

    // Berkas dengan SP3 / tidak_selesai ditolak untuk DPO
    $tersangkaSp3 = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka SP3',
        'created_by' => $penyidik->id,
    ]);
    $berkasSp3 = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangkaSp3->id,
        'nomor_berkas' => 'BP/SP3/01',
        'status_formal' => 'tidak_selesai',
        'is_dpo' => false,
        'created_by' => $penyidik->id,
    ]);
    $this->actingAs($penyidik)->post(route('berkas-perkara.dpo.store', $berkasSp3), [
        'nomor_surat' => 'DPO/SP3/001',
        'tanggal_penetapan' => '2026-02-12',
    ])->assertRedirect()->assertSessionHas('error');
});

test('menyimpan sub-tahap pada spdp yang sudah final tidak menimpa catatan spdp induk', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SPDP/SUB/01',
        'tanggal_laporan_kejadian' => '2026-03-01',
        'tahap_laporan_status' => 'final',
        'nomor_surat_perintah' => 'SP/SPDP/SUB/01',
        'tanggal_surat_perintah' => '2026-03-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka SPDP Final',
        'created_by' => $penyidik->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/SPDP/SUB/01',
        'created_by' => $penyidik->id,
    ]);

    $spdpFinal = TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'SPDP/ASLI/01',
        'tanggal_surat' => '2026-03-10',
        'catatan' => 'Catatan Asli SPDP Jangan Ditimpa',
        'status' => 'final',
        'finalized_at' => now()->subDays(5),
        'created_by' => $penyidik->id,
    ]);

    // Buka edit dan submit form SPDP final dengan menambahkan P-16 dan catatan SPDP dikosongkan
    $this->actingAs($penyidik)->put(route('berkas-perkara.tahapan.update', [$berkas, $spdpFinal]), [
        'action_type' => 'lanjut',
        'nomor_surat' => 'SPDP/ASLI/01',
        'tanggal_surat' => '2026-03-10',
        'catatan' => '', // kosong
        'p16_nomor_surat' => 'P16/NEW/01',
        'p16_tanggal_surat' => '2026-03-15',
        'p16_catatan' => 'Penunjukan Jaksa',
    ])->assertRedirect();

    // Catatan SPDP asli tetap utuh
    expect($spdpFinal->fresh()->catatan)->toBe('Catatan Asli SPDP Jangan Ditimpa');

    // Sub-tahap P-16 tersimpan
    $p16 = TahapanPerkara::where('berkas_perkara_id', $berkas->id)
        ->where('jenis_tahap', JenisTahap::P16->value)
        ->first();
    expect($p16)->not->toBeNull();
    expect($p16->nomor_surat)->toBe('P16/NEW/01');
});

test('restore perkara dan berkas di sampah memerlukan hak akses manage-users (admin only)', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMINISTRATOR, 'is_active' => true]);
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/TRASH/01',
        'tanggal_laporan_kejadian' => '2026-01-01',
        'created_by' => $admin->id,
    ]);
    $perkara->delete();

    // Penyidik ditolak 403
    $this->actingAs($penyidik)->post(route('sampah.perkara.restore', $perkara->id))->assertForbidden();

    // Admin berhasil
    $this->actingAs($admin)->post(route('sampah.perkara.restore', $perkara->id))->assertRedirect();
    expect($perkara->fresh()->trashed())->toBeFalse();
});

test('tombol tetapkan DPO muncul pada perkara tahap 2 yang belum memiliki keputusan pengadilan final', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/TAHAP2/DPO/01',
        'tanggal_laporan_kejadian' => '2026-02-01',
        'tahap_laporan_status' => 'final',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Tahap 2',
        'created_by' => $penyidik->id,
    ]);

    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/TAHAP2/01',
        'status_formal' => 'selesai',
        'status_proses' => 'TAHAP II',
        'progress_persen' => 100,
        'is_dpo' => false,
        'created_by' => $penyidik->id,
    ]);

    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_2,
        'nomor_surat' => 'B/T2/01',
        'tanggal_surat' => '2026-03-01',
        'status' => 'final',
        'finalized_at' => now(),
        'created_by' => $penyidik->id,
    ]);

    expect($berkas->canActivateDpo())->toBeTrue();

    $response = $this->actingAs($penyidik)->get(route('perkara.show', $perkara));
    $response->assertOk();
    $response->assertSee('Tetapkan DPO');
});
