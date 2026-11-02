<?php

use App\Helpers\MenuHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders complete menu structure for Penyidik role according to Poin 5', function () {
    $penyidik = User::factory()->penyidik()->create();

    $this->actingAs($penyidik);
    $groups = MenuHelper::getMenuGroups();

    // Penyidik memiliki 3 grup menu: Menu Utama, Perkara, Sistem
    expect($groups)->toHaveCount(3);

    // 1. Menu Utama (Poin 10 & 11)
    $mainGroup = $groups[0];
    expect($mainGroup['title'])->toBe('Menu Utama');
    $mainItems = collect($mainGroup['items']);
    expect($mainItems->pluck('name')->all())->toEqual(['Dashboard', 'Daftar Perkara']);

    $daftarPerkaraItem = $mainItems->firstWhere('name', 'Daftar Perkara');
    expect($daftarPerkaraItem['path'])->toBe('/data-perkara');

    // 2. Perkara (7 tahapan berurutan)
    $perkaraGroup = $groups[1];
    expect($perkaraGroup['title'])->toBe('Perkara');
    expect(collect($perkaraGroup['items'])->pluck('name')->all())->toEqual([
        'Laporan Kejadian',
        'Sprindik',
        'SPDP',
        'Tahap 1',
        'P21',
        'Tahap 2',
        'Putusan Perkara',
    ]);

    // 3. Sistem (Master Data: hanya Pasal & Instansi Hukum; Data Petugas)
    $sistemGroup = $groups[2];
    expect($sistemGroup['title'])->toBe('Sistem');
    $sistemItems = collect($sistemGroup['items']);

    $masterData = $sistemItems->firstWhere('name', 'Master Data');
    expect($masterData)->not->toBeNull();
    expect(collect($masterData['subItems'])->pluck('name')->all())
        ->toEqual(['Pasal / UU', 'Instansi Hukum']);

    $dataPetugas = $sistemItems->firstWhere('name', 'Data Petugas');
    expect($dataPetugas)->not->toBeNull()
        ->and($dataPetugas['path'])->toBe('/data-petugas');
});
