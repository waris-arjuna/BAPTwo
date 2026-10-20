<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->penyidik = User::where('role', 'penyidik')->firstOrFail();
});

test('layout app dan fullscreen menyertakan komponen toast', function () {
    // 1. Fullscreen layout (halaman masuk)
    $responseGuest = $this->get(route('masuk'));
    $responseGuest->assertOk();
    $responseGuest->assertSee('toastManager', false);

    // 2. App layout (dashboard berautentikasi)
    $responseAuth = $this->actingAs($this->penyidik)->get(route('dashboard'));
    $responseAuth->assertOk();
    $responseAuth->assertSee('toastManager', false);
});

test('flash session berbentuk string dinormalisasi dengan judul default yang tepat', function () {
    $response = $this->actingAs($this->penyidik)
        ->withSession([
            'success' => 'Operasi data berhasil dilakukan.',
            'warning' => 'Perhatikan ketentuan berkas.',
            'info' => 'Informasi pembaruan sistem.',
        ])
        ->get(route('dashboard'));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('Operasi data berhasil dilakukan.');
    expect($content)->toContain('Berhasil');
    expect($content)->toContain('Perhatikan ketentuan berkas.');
    expect($content)->toContain('Perhatian');
    expect($content)->toContain('Informasi pembaruan sistem.');
});

test('flash session berbentuk array memuat judul dan pesan kustom', function () {
    $response = $this->actingAs($this->penyidik)
        ->withSession([
            'success' => [
                'title' => 'Sprindik Tersimpan',
                'message' => 'Sprindik untuk perkara berhasil difinalkan.',
            ],
            'error' => [
                'title' => 'Berkas Tidak Memenuhi Syarat',
                'message' => 'Tahapan sebelumnya belum difinalkan.',
            ],
        ])
        ->get(route('dashboard'));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('Sprindik Tersimpan');
    expect($content)->toContain('Sprindik untuk perkara berhasil difinalkan.');
    expect($content)->toContain('Berkas Tidak Memenuhi Syarat');
    expect($content)->toContain('Tahapan sebelumnya belum difinalkan.');
});

test('error validasi form memunculkan toast ringkasan kesalahan', function () {
    $response = $this->actingAs($this->penyidik)
        ->withSession([
            'errors' => new ViewErrorBag,
        ]);

    // Simulasikan request gagal validasi
    $failed = $this->actingAs($this->penyidik)
        ->post(route('sprindik.store'), [
            'perkara_id' => 99999,
        ]);

    $failed->assertSessionHasErrors();

    // Ikuti redirect
    $follow = $this->actingAs($this->penyidik)->get(route('sprindik.create'));
    $follow->assertOk();
    $follow->assertSee('Periksa Isian Formulir');
    $follow->assertSee('isian yang perlu diperbaiki sebelum menyimpan');
});

test('payload teks berbahaya ter-escape aman dari serangan XSS', function () {
    $xssString = "<script>alert('xss-payload')</script>";

    $response = $this->actingAs($this->penyidik)
        ->withSession([
            'success' => [
                'title' => 'XSS Test '.$xssString,
                'message' => 'Pesan bahaya '.$xssString,
            ],
        ])
        ->get(route('dashboard'));

    $response->assertOk();
    $content = $response->getContent();

    // Pastikan tidak ada script tag mentah yang dieksekusi di HTML
    // Di serialisasi @js(), karakter kurung sudut diserialisasi sebagai unicode escape
    expect($content)->toContain('u003Cscript');
    expect($content)->not->toContain('<script>alert(\'xss-payload\')</script>');
});

test('halaman login menampilkan banner inline sesi berakhir tanpa modal dan tanpa toast duplikat', function () {
    $response = $this->get(route('masuk', ['session_expired' => 1]));

    $response->assertOk();
    $response->assertSee('Sesi Berakhir');
    $response->assertSee('Anda keluar otomatis setelah 30 menit tidak ada aktivitas.');
    // Pastikan modal error lama dan modal pop-up tidak tampil
    $response->assertDontSee('showErrorModal');
});

test('halaman login gagal validasi tidak memunculkan modal popup lama', function () {
    $response = $this->post(route('masuk'), [
        'email' => 'invalid@example.com',
        'password' => 'wrongpassword',
        'captcha' => '999',
    ]);

    $response->assertSessionHasErrors(['captcha']);

    $loginPage = $this->get(route('masuk'));
    $loginPage->assertOk();
    $loginPage->assertDontSee('showErrorModal');
    $loginPage->assertSee('toastManager', false);
});
