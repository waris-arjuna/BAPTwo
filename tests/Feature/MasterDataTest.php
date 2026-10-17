<?php

use App\Models\BerkasPerkara;
use App\Models\Bidang;
use App\Models\InstansiHukum;
use App\Models\Jabatan;
use App\Models\JaksaPenuntut;
use App\Models\Panggol;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('enforces RBAC for Master Data according to Poin 7', function () {
    $penyidik = User::factory()->penyidik()->create();
    $pimpinan = User::factory()->pimpinan()->create();
    $admin = User::factory()->administrator()->create();

    $allTypes = ['jabatan', 'bidang', 'panggol', 'pasal', 'instansi-hukum', 'jaksa-penuntut'];

    // 1. Pemimpin: diblokir total dari semua tipe (403)
    foreach ($allTypes as $type) {
        $this->actingAs($pimpinan)
            ->get(route('master-data.index', ['type' => $type]))
            ->assertForbidden();
    }

    // 2. Penyidik: hanya dapat melihat pasal & instansi-hukum; 4 tipe lain diblokir (403)
    foreach (['pasal', 'instansi-hukum'] as $type) {
        $this->actingAs($penyidik)
            ->get(route('master-data.index', ['type' => $type]))
            ->assertOk();
    }
    foreach (['jabatan', 'bidang', 'panggol', 'jaksa-penuntut'] as $type) {
        $this->actingAs($penyidik)
            ->get(route('master-data.index', ['type' => $type]))
            ->assertForbidden();
    }

    // 3. Administrator: dapat melihat seluruh 6 tipe
    foreach ($allTypes as $type) {
        $this->actingAs($admin)
            ->get(route('master-data.index', ['type' => $type]))
            ->assertOk();
    }
});

it('allows Penyidik to manage Pasal and Instansi Hukum while Admin can manage all and Pimpinan is blocked', function () {
    $penyidik = User::factory()->penyidik()->create();
    $pimpinan = User::factory()->pimpinan()->create();
    $admin = User::factory()->administrator()->create();

    // Pimpinan diblokir (403) saat mencoba tambah Master Data apa pun
    $this->actingAs($pimpinan)
        ->post(route('master-data.store', ['type' => 'pasal']), [
            'nama_pasal' => 'UU Ilegal',
        ])
        ->assertForbidden();

    // Penyidik diblokir saat mencoba kelola Jabatan (karena hanya Admin yang mengelola)
    $this->actingAs($penyidik)
        ->post(route('master-data.store', ['type' => 'jabatan']), [
            'nama_jabatan' => 'Penyidik Madya',
        ])
        ->assertForbidden();

    // Admin dapat menambah, update, soft delete, dan restore Jabatan
    $this->actingAs($admin)
        ->post(route('master-data.store', ['type' => 'jabatan']), [
            'nama_jabatan' => 'Penyidik Madya',
        ])
        ->assertRedirect(route('master-data.index', ['type' => 'jabatan']));

    $jabatan = Jabatan::where('nama_jabatan', 'Penyidik Madya')->first();
    expect($jabatan)->not->toBeNull();

    // Update Jabatan oleh Admin
    $this->actingAs($admin)
        ->put(route('master-data.update', ['type' => 'jabatan', 'id' => $jabatan->id]), [
            'nama_jabatan' => 'Penyidik Ahli Madya',
        ])
        ->assertRedirect(route('master-data.index', ['type' => 'jabatan']));

    expect($jabatan->fresh()->nama_jabatan)->toBe('Penyidik Ahli Madya');

    // Soft Delete Jabatan oleh Admin
    $this->actingAs($admin)
        ->delete(route('master-data.destroy', ['type' => 'jabatan', 'id' => $jabatan->id]))
        ->assertRedirect(route('master-data.index', ['type' => 'jabatan']));

    expect(Jabatan::find($jabatan->id))->toBeNull()
        ->and(Jabatan::onlyTrashed()->find($jabatan->id))->not->toBeNull();

    // Restore Jabatan oleh Admin
    $this->actingAs($admin)
        ->post(route('master-data.restore', ['type' => 'jabatan', 'id' => $jabatan->id]))
        ->assertRedirect(route('master-data.index', ['type' => 'jabatan']));

    expect(Jabatan::find($jabatan->id))->not->toBeNull();

    // Penyidik dapat menambah, mengedit, dan menghapus Pasal dan Instansi Hukum
    $this->actingAs($penyidik)
        ->post(route('master-data.store', ['type' => 'pasal']), [
            'nama_pasal' => 'UU Kesehatan',
            'pasal' => 'Pasal 435',
            'nomor' => '17',
            'tahun' => '2023',
            'isi' => 'Uraian pasal kesehatan',
            'status' => 'aktif',
        ])
        ->assertRedirect(route('master-data.index', ['type' => 'pasal']));

    expect(Pasal::count())->toBe(1);

    $this->actingAs($penyidik)
        ->post(route('master-data.store', ['type' => 'instansi-hukum']), [
            'jenis' => 'kejaksaan',
            'nama' => 'Kejaksaan Negeri Banjarmasin',
            'wilayah' => 'Kota Banjarmasin',
        ])
        ->assertRedirect(route('master-data.index', ['type' => 'instansi-hukum']));

    $kejari = InstansiHukum::first();
    expect($kejari)->not->toBeNull();

    // Penyidik diblokir mengelola Jaksa Penuntut (Poin 7)
    $this->actingAs($penyidik)
        ->post(route('master-data.store', ['type' => 'jaksa-penuntut']), [
            'instansi_hukum_id' => $kejari->id,
            'nama_jaksa' => 'Hendra Wijaya, S.H.',
        ])
        ->assertForbidden();

    // Admin dapat mengelola Jaksa Penuntut
    $this->actingAs($admin)
        ->post(route('master-data.store', ['type' => 'jaksa-penuntut']), [
            'instansi_hukum_id' => $kejari->id,
            'nama_jaksa' => 'Hendra Wijaya, S.H.',
            'nip' => '198701012012121001',
            'pangkat' => 'Jaksa Muda',
        ])
        ->assertRedirect(route('master-data.index', ['type' => 'jaksa-penuntut']));

    expect(JaksaPenuntut::count())->toBe(1);
});

it('allows Administrator to select Jabatan, Bidang, and Panggol from Master Data when creating or updating a user', function () {
    $admin = User::factory()->administrator()->create();
    $jabatan = Jabatan::create(['nama_jabatan' => 'PPNS Utama']);
    $bidang = Bidang::create(['nama_bidang' => 'Bidang Penindakan']);
    $panggol = Panggol::create(['pangkat' => 'Pembina', 'golongan' => 'IV/a']);

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'nama' => 'Pegawai Baru',
            'nip' => '199505052020121001',
            'email' => 'pegawai.baru@baptwo.test',
            'role' => 'penyidik',
            'status' => 'pns',
            'jabatan_id' => $jabatan->id,
            'bidang_id' => $bidang->id,
            'panggol_id' => $panggol->id,
            'password' => 'password123',
            'is_active' => '1',
        ])
        ->assertRedirect(route('users.index'));

    $created = User::where('email', 'pegawai.baru@baptwo.test')->first();
    expect($created)->not->toBeNull()
        ->and($created->jabatan_id)->toBe($jabatan->id)
        ->and($created->bidang_id)->toBe($bidang->id)
        ->and($created->panggol_id)->toBe($panggol->id);
});

it('rejects invalid input character types across Master Data and User Management', function () {
    $admin = User::factory()->administrator()->create();
    $kejari = InstansiHukum::create([
        'jenis' => 'kejaksaan',
        'nama' => 'Kejaksaan Negeri Banjarmasin',
        'wilayah' => 'Kota Banjarmasin',
    ]);

    // 1. Jabatan: harus huruf saja (ditolak jika ada angka)
    $this->actingAs($admin)
        ->post(route('master-data.store', ['type' => 'jabatan']), [
            'nama_jabatan' => 'Jabatan 12345',
        ])
        ->assertSessionHasErrors('nama_jabatan');

    // 2. Panggol: pangkat harus huruf saja, golongan hanya huruf/angka dan /
    $this->actingAs($admin)
        ->post(route('master-data.store', ['type' => 'panggol']), [
            'pangkat' => 'Pembina 99',
            'golongan' => 'IV@a!',
        ])
        ->assertSessionHasErrors(['pangkat', 'golongan']);

    // 3. Pasal: nomor harus angka saja, tahun harus 4 digit angka
    $this->actingAs($admin)
        ->post(route('master-data.store', ['type' => 'pasal']), [
            'nama_pasal' => 'UU Kesehatan',
            'pasal' => 'Pasal 435',
            'nomor' => '17A',
            'tahun' => '202X',
            'status' => 'aktif',
        ])
        ->assertSessionHasErrors(['nomor', 'tahun']);

    // 4. Jaksa Penuntut: nama_jaksa harus huruf/gelar saja, nip harus angka saja
    $this->actingAs($admin)
        ->post(route('master-data.store', ['type' => 'jaksa-penuntut']), [
            'instansi_hukum_id' => $kejari->id,
            'nama_jaksa' => 'Jaksa 007',
            'nip' => 'NIP-12345ABC',
            'pangkat' => 'Jaksa Muda',
        ])
        ->assertSessionHasErrors(['nama_jaksa', 'nip']);

    // 5. Manajemen Pengguna: nama harus huruf/gelar saja, nip harus angka saja
    $this->actingAs($admin)
        ->post(route('users.store'), [
            'nama' => 'Budi 123',
            'nip' => '1995ABC',
            'email' => 'budi123@baptwo.test',
            'role' => 'penyidik',
            'status' => 'pns',
            'password' => 'password123',
        ])
        ->assertSessionHasErrors(['nama', 'nip']);
});

it('applies consistent search, status, and date filters across stage tables, Master Data, and User Management', function () {
    $this->seedBaselineCasesAndStages();
    $admin = User::factory()->administrator()->create(['nama' => 'Admin Utama']);
    $activeUser = User::factory()->penyidik()->create(['nama' => 'Penyidik Aktif', 'is_active' => true]);
    $inactiveUser = User::factory()->penyidik()->create(['nama' => 'Penyidik Pasif', 'is_active' => false]);

    $jabatanAktif = Jabatan::create(['nama_jabatan' => 'Jabatan Aktif']);
    $jabatanTerhapus = Jabatan::create(['nama_jabatan' => 'Jabatan Terhapus']);
    $jabatanTerhapus->delete();

    // 1. Stage table filter (Laporan Kejadian: search + status + tanggal_dari + Reset button + Datepicker)
    $this->actingAs($admin)
        ->get('/laporan-kejadian?search=LK/001&status=Belum+Dilanjutkan&tanggal_dari=2026-09-02')
        ->assertOk()
        ->assertSee('LK/001/IX/2026')
        ->assertDontSee('LK/002/IX/2026')
        ->assertSee('custom-datepicker')
        ->assertSee('fi-rr-calendar')
        ->assertSee('name="tanggal_dari"', false)
        ->assertSee(__('Reset'));

    // Empty state + Reset button when filter matches 0 rows (D-011 point 5)
    $this->actingAs($admin)
        ->get('/laporan-kejadian?search=TIDAK-ADA-DATA')
        ->assertOk()
        ->assertSee(__('No data available.'))
        ->assertSee(__('Reset'));

    // 2. Master Data filter (status=aktif vs status=nonaktif)
    $this->actingAs($admin)
        ->get(route('master-data.index', ['type' => 'jabatan', 'status' => 'aktif']))
        ->assertOk()
        ->assertSee('Jabatan Aktif')
        ->assertDontSee('Jabatan Terhapus')
        ->assertSee(__('Reset'));

    $this->actingAs($admin)
        ->get(route('master-data.index', ['type' => 'jabatan', 'status' => 'nonaktif']))
        ->assertOk()
        ->assertSee('Jabatan Terhapus')
        ->assertDontSee('Jabatan Aktif');

    // 3. User Management filter (status=nonaktif)
    $this->actingAs($admin)
        ->get(route('users.index', ['status' => 'nonaktif']))
        ->assertOk()
        ->assertSee($inactiveUser->nama)
        ->assertDontSee($activeUser->nama)
        ->assertSee(__('Reset'));

    // 4. AJAX Partial response (X-Filter-Partial) does not include full page layout
    $this->actingAs($admin)
        ->get('/laporan-kejadian?search=LK/001', ['X-Filter-Partial' => '1'])
        ->assertOk()
        ->assertSee('LK/001/IX/2026')
        ->assertDontSee('<!DOCTYPE html>')
        ->assertDontSee('id="main-content"');

    $this->actingAs($admin)
        ->get(route('master-data.index', ['type' => 'jabatan', 'search' => 'Jabatan Aktif']), ['X-Filter-Partial' => '1'])
        ->assertOk()
        ->assertSee('Jabatan Aktif')
        ->assertDontSee('<!DOCTYPE html>');

    $this->actingAs($admin)
        ->get(route('users.index', ['search' => 'Admin Utama']), ['X-Filter-Partial' => '1'])
        ->assertOk()
        ->assertSee('Admin Utama')
        ->assertDontSee('<!DOCTYPE html>');

    $perkaraSample = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/099/IX/2026',
        'tanggal_laporan_kejadian' => '2026-09-01',
        'uraian_kejadian' => 'Uraian LK',
        'status' => 'draft',
    ]);
    $tersangkaSample = Tersangka::create([
        'perkara_id' => $perkaraSample->id,
        'nama' => 'Andi Saputra',
        'nik' => '1234567890123456',
    ]);
    $berkasSample = BerkasPerkara::create([
        'perkara_id' => $perkaraSample->id,
        'tersangka_id' => $tersangkaSample->id,
        'nomor_berkas' => 'BP/099/2026',
        'status_proses' => 'SPDP',
        'status_formal' => null,
        'progress_persen' => 15,
    ]);

    $this->actingAs($admin)
        ->get('/?search=Andi', ['X-Filter-Partial' => '1'])
        ->assertOk()
        ->assertSee('Andi Saputra')
        ->assertDontSee('<!DOCTYPE html>');

    // 5. Searching by status keyword in search input
    $this->actingAs($admin)
        ->get('/laporan-kejadian?search=Sprindik+Tersedia')
        ->assertOk()
        ->assertSee('LK/002/IX/2026')
        ->assertDontSee('LK/001/IX/2026');

    $this->actingAs($admin)
        ->get(route('master-data.index', ['type' => 'jabatan', 'search' => 'aktif']))
        ->assertOk()
        ->assertSee('Jabatan Aktif')
        ->assertDontSee('Jabatan Terhapus');

    $this->actingAs($admin)
        ->get(route('master-data.index', ['type' => 'jabatan', 'search' => 'nonaktif']))
        ->assertOk()
        ->assertSee('Jabatan Terhapus')
        ->assertDontSee('Jabatan Aktif');

    $this->actingAs($admin)
        ->get(route('users.index', ['search' => 'aktif']))
        ->assertOk()
        ->assertSee($activeUser->nama)
        ->assertDontSee($inactiveUser->nama);

    $this->actingAs($admin)
        ->get(route('users.index', ['search' => 'nonaktif']))
        ->assertOk()
        ->assertSee($inactiveUser->nama)
        ->assertDontSee($activeUser->nama);
});
