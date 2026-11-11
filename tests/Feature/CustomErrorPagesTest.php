<?php

use App\Enums\UserRole;
use App\Models\User;

test('halaman 404 merender tampilan kustom TailAdmin BAPTwo dalam Bahasa Indonesia untuk guest', function () {
    $response = $this->get('/halaman-yang-pasti-tidak-ada-random-xyz-123');

    $response->assertStatus(404);
    $response->assertSee('404');
    $response->assertSee('Halaman Tidak Ditemukan');
    $response->assertSee('BAPTwo');
    $response->assertSee('BPOM Kalsel');
    $response->assertSee(route('masuk'));
});

test('halaman 404 merender tautan dashboard untuk user yang terotentikasi', function () {
    $user = User::factory()->make(['role' => UserRole::PENYIDIK]);
    $response = $this->actingAs($user)->get('/halaman-yang-pasti-tidak-ada-random-xyz-456');

    $response->assertStatus(404);
    $response->assertSee('404');
    $response->assertSee(route('dashboard'));
});

test('halaman 403 merender tampilan kustom hak akses ditolak dengan kode 403', function () {
    $response = $this->get('/403');

    $response->assertStatus(403);
    $response->assertSee('403');
    $response->assertSee('Akses Ditolak');
});

test('halaman 419 merender tampilan kustom sesi berakhir dengan kode 419', function () {
    $response = $this->get('/419');

    $response->assertStatus(419);
    $response->assertSee('419');
    $response->assertSee('Sesi Telah Berakhir');
    $response->assertSee('Masuk Kembali');
    $response->assertSee(route('masuk'));
});

test('halaman 500 merender tampilan kustom kesalahan server dengan kode 500', function () {
    $response = $this->get('/500');

    $response->assertStatus(500);
    $response->assertSee('500');
    $response->assertSee('Terjadi Kesalahan Sistem');
});

test('halaman 503 merender tampilan kustom pemeliharaan sistem dengan kode 503', function () {
    $response = $this->get('/503');

    $response->assertStatus(503);
    $response->assertSee('503');
    $response->assertSee('Sistem Dalam Pemeliharaan');
    $response->assertSee('Muat Ulang Halaman');
});

test('halaman 429 merender tampilan kustom batas permintaan dengan kode 429', function () {
    $response = $this->get('/429');

    $response->assertStatus(429);
    $response->assertSee('429');
    $response->assertSee('Terlalu Banyak Permintaan');
});

test('halaman 5xx generik menangani status non-standar seperti 505 dengan kode yang tepat', function () {
    $response = $this->get('/505');

    $response->assertStatus(505);
    $response->assertSee('505');
    $response->assertSee('Terjadi Kesalahan Sistem');
});

test('halaman 4xx generik menangani status seperti 400 dengan kode yang tepat', function () {
    $response = $this->get('/400');

    $response->assertStatus(400);
    $response->assertSee('400');
    $response->assertSee('Permintaan Tidak Dapat Diproses');
});
