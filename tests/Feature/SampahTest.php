<?php

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'administrator']);
    $this->penyidik = User::factory()->create(['role' => 'penyidik']);
    $this->pimpinan = User::factory()->create(['role' => 'pimpinan']);

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/TRASH/001/X/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'draft',
        'deskripsi' => 'Kasus yang akan diuji soft delete dan sampah.',
        'created_by' => $this->penyidik->id,
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Sampah',
        'nik' => '6371012345678888',
        'created_by' => $this->penyidik->id,
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/TRASH/001/X/2026',
        'created_by' => $this->penyidik->id,
    ]);
});

test('halaman sampah dapat diakses oleh seluruh role terautentikasi', function () {
    $this->actingAs($this->penyidik)
        ->get(route('sampah.index'))
        ->assertOk()
        ->assertSee('Recovery Data');

    $this->actingAs($this->admin)
        ->get(route('sampah.index'))
        ->assertOk();

    $this->actingAs($this->pimpinan)
        ->get(route('sampah.index'))
        ->assertOk();
});

test('perkara yang di-soft-delete muncul di tab perkara dan dapat dipulihkan oleh penyidik', function () {
    // 1. Soft delete perkara
    $this->perkara->delete();
    expect($this->perkara->fresh()->trashed())->toBeTrue();

    // 2. Cek tampil di halaman sampah tab perkara
    $response = $this->actingAs($this->penyidik)
        ->get(route('sampah.index', ['tab' => 'perkara']));

    $response->assertOk();
    $response->assertSee('LK/TRASH/001/X/2026');
    $response->assertSee('Pulihkan');

    // 3. Pulihkan perkara
    $restoreResponse = $this->actingAs($this->penyidik)
        ->post(route('sampah.perkara.restore', $this->perkara->id));

    $restoreResponse->assertRedirect(route('sampah.index', ['tab' => 'perkara']));
    $restoreResponse->assertSessionHas('success');

    expect($this->perkara->fresh()->trashed())->toBeFalse();

    // 4. Pastikan tercatat di audit log
    $this->assertDatabaseHas('audit_log', [
        'berkas_perkara_id' => null,
        'aksi' => 'restore_perkara',
        'tabel' => 'perkara',
        'record_id' => $this->perkara->id,
        'user_id' => $this->penyidik->id,
    ]);
});

test('pimpinan dilarang memulihkan data dari sampah (403 Forbidden)', function () {
    $this->perkara->delete();

    $this->actingAs($this->pimpinan)
        ->post(route('sampah.perkara.restore', $this->perkara->id))
        ->assertForbidden();

    expect($this->perkara->fresh()->trashed())->toBeTrue();
});

test('berkas perkara yang di-soft-delete muncul di tab berkas dan dapat dipulihkan', function () {
    $this->berkas->delete();
    expect($this->berkas->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($this->penyidik)
        ->get(route('sampah.index', ['tab' => 'berkas']));

    $response->assertOk();
    $response->assertSee('BP/TRASH/001/X/2026');

    $this->actingAs($this->penyidik)
        ->post(route('sampah.berkas.restore', $this->berkas->id))
        ->assertRedirect(route('sampah.index', ['tab' => 'berkas']));

    expect($this->berkas->fresh()->trashed())->toBeFalse();

    $this->assertDatabaseHas('audit_log', [
        'aksi' => 'restore_berkas_perkara',
        'tabel' => 'berkas_perkara',
        'record_id' => $this->berkas->id,
        'user_id' => $this->penyidik->id,
    ]);
});

test('pengguna yang di-soft-delete muncul di tab user dan hanya administrator yang dapat memulihkannya', function () {
    $userToDelete = User::factory()->create([
        'nama' => 'User Terhapus',
        'email' => 'terhapus@baptwo.test',
        'role' => 'penyidik',
    ]);

    $userToDelete->delete();
    expect($userToDelete->fresh()->trashed())->toBeTrue();

    // Akses tab user
    $this->actingAs($this->admin)
        ->get(route('sampah.index', ['tab' => 'user']))
        ->assertOk()
        ->assertSee('User Terhapus');

    // Penyidik dilarang memulihkan pengguna
    $this->actingAs($this->penyidik)
        ->post(route('sampah.user.restore', $userToDelete->id))
        ->assertForbidden();

    // Administrator dapat memulihkan pengguna
    $this->actingAs($this->admin)
        ->post(route('sampah.user.restore', $userToDelete->id))
        ->assertRedirect(route('sampah.index', ['tab' => 'user']))
        ->assertSessionHas('success');

    expect($userToDelete->fresh()->trashed())->toBeFalse();
});

test('request ajax dengan header X-Filter-Partial mengembalikan partial tabel sampah', function () {
    $response = $this->actingAs($this->penyidik)
        ->get(route('sampah.index', ['tab' => 'perkara']), [
            'X-Filter-Partial' => '1',
        ]);

    $response->assertOk();
    $response->assertViewIs('pages.sampah._table-partial');
});
