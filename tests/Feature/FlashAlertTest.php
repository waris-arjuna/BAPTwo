<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('flash alert component renders success message with 10s timer and close button', function () {
    $user = User::factory()->create([
        'role' => UserRole::PENYIDIK,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['success' => 'Operasi berhasil disimpan'])
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Operasi berhasil disimpan');
    $response->assertSee('fi-rr-cross-small');
    $response->assertSee('fi-rr-check');
    $response->assertSee('10000');
    $response->assertSee('show = false');
});

test('flash alert component renders error message with close button and 10s timer', function () {
    $user = User::factory()->create([
        'role' => UserRole::PENYIDIK,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['error' => 'Terjadi kesalahan sistem'])
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Terjadi kesalahan sistem');
    $response->assertSee('fi-rr-cross-small');
    $response->assertSee('fi-rr-cross-circle');
    $response->assertSee('10000');
});

test('flash alert component renders warning and profile_success messages', function () {
    $user = User::factory()->create([
        'role' => UserRole::PENYIDIK,
    ]);

    $warningResponse = $this->actingAs($user)
        ->withSession(['warning' => 'Peringatan penting'])
        ->get(route('dashboard'));

    $warningResponse->assertOk();
    $warningResponse->assertSee('Peringatan penting');
    $warningResponse->assertSee('fi-rr-triangle-warning');

    $profileResponse = $this->actingAs($user)
        ->withSession(['profile_success' => 'Profil berhasil diperbarui'])
        ->get(route('profile'));

    $profileResponse->assertOk();
    $profileResponse->assertSee('Profil berhasil diperbarui');
    $profileResponse->assertSee('fi-rr-cross-small');
});

test('flash alert component renders nothing when no session flash messages exist', function () {
    $user = User::factory()->create([
        'role' => UserRole::PENYIDIK,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertDontSee('Tutup notifikasi');
});
