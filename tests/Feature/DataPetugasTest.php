<?php

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('administrator dan penyidik dapat mengakses halaman data petugas', function () {
    $admin = User::factory()->administrator()->create(['nama' => 'Admin Test']);
    $penyidik = User::factory()->penyidik()->create(['nama' => 'Penyidik Lapangan']);
    $pimpinan = User::factory()->pimpinan()->create(['nama' => 'Pejabat Pimpinan']);

    // 1. Administrator dapat melihat daftar petugas non-admin
    $responseAdmin = $this->actingAs($admin)->get(route('data-petugas.index'));
    $responseAdmin->assertOk()
        ->assertSee('Data Petugas')
        ->assertSee('Penyidik Lapangan')
        ->assertSee('Pejabat Pimpinan');

    // 2. Penyidik dapat melihat daftar data petugas
    $responsePenyidik = $this->actingAs($penyidik)->get(route('data-petugas.index'));
    $responsePenyidik->assertOk()
        ->assertSee('Data Petugas');
});

test('administrator dapat memperbarui role dan status penugasan petugas non-admin', function () {
    $admin = User::factory()->administrator()->create();
    $petugas = User::factory()->penyidik()->create([
        'nama' => 'Hendra Setiawan',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->post(route('data-petugas.store'), [
        'user_id' => $petugas->id,
        'role' => UserRole::UMUM->value,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('data-petugas.index'))
        ->assertSessionHas('success');

    $petugas->refresh();
    expect($petugas->role)->toBe(UserRole::UMUM);

    // Audit log tercatat
    $log = AuditLog::where('aksi', 'atur_petugas')->latest('id')->first();
    expect($log)->not->toBeNull()
        ->and($log->record_id)->toBe($petugas->id)
        ->and($log->data_baru['role'])->toBe(UserRole::UMUM->value);
});

test('penugasan menolak peran administrator atau user berstatus administrator', function () {
    $admin1 = User::factory()->administrator()->create();
    $admin2 = User::factory()->administrator()->create();

    // 1. Mencoba memilih user yang merupakan administrator
    $response = $this->actingAs($admin1)->post(route('data-petugas.store'), [
        'user_id' => $admin2->id,
        'role' => UserRole::PENYIDIK->value,
    ]);
    $response->assertSessionHasErrors('user_id');

    // 2. Mencoba memberikan role administrator
    $petugas = User::factory()->penyidik()->create();
    $response2 = $this->actingAs($admin1)->post(route('data-petugas.store'), [
        'user_id' => $petugas->id,
        'role' => UserRole::ADMINISTRATOR->value,
    ]);
    $response2->assertSessionHasErrors('role');
});

test('halaman pengaturan umum dapat diakses oleh administrator', function () {
    $admin = User::factory()->administrator()->create();

    $response = $this->actingAs($admin)->get(route('pengaturan.umum'));
    $response->assertOk()
        ->assertSee('Pengaturan Umum')
        ->assertSee('Balai Besar Pengawas Obat dan Makanan di Banjarmasin')
        ->assertSee('BAPTwo v1.0');
});
