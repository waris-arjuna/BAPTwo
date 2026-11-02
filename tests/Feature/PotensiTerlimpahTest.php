<?php

use App\Helpers\MenuHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->penyidik()->create();
    $this->pimpinan = User::factory()->pimpinan()->create();
    $this->admin = User::factory()->administrator()->create();
});

test('rute potensi terlimpah dialihkan ke halaman daftar perkara dengan mempertahankan query parameter', function () {
    $this->actingAs($this->penyidik)
        ->get(route('potensi-terlimpah.index', ['kesiapan' => 'siap_tahap_2']))
        ->assertRedirect(route('data-perkara.index', ['kesiapan' => 'siap_tahap_2']));

    $this->actingAs($this->pimpinan)
        ->get(route('potensi-terlimpah.index'))
        ->assertRedirect(route('data-perkara.index'));

    $this->actingAs($this->admin)
        ->get(route('potensi-terlimpah.index', ['search' => 'narkotika']))
        ->assertRedirect(route('data-perkara.index', ['search' => 'narkotika']));
});

test('menu navigasi utama menyatukan fitur menjadi link langsung daftar perkara tanpa submenu potensi terlimpah', function () {
    $mainItems = MenuHelper::getMainNavItems();
    $daftarPerkaraItem = collect($mainItems)->firstWhere('name', 'Daftar Perkara');

    expect($daftarPerkaraItem)->not->toBeNull()
        ->and($daftarPerkaraItem['path'])->toBe('/data-perkara')
        ->and(isset($daftarPerkaraItem['subItems']))->toBeFalse();
});
