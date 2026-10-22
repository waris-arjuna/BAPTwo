<?php

use App\Helpers\MenuHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('role pimpinan dan umum hanya memiliki 1 grup menu utama dengan 3 menu monitoring', function () {
    $pimpinan = User::factory()->pimpinan()->create();
    $umum = User::factory()->umum()->create();

    foreach ([$pimpinan, $umum] as $user) {
        $this->actingAs($user);

        $groups = MenuHelper::getMenuGroups();

        // 1. Hanya 1 grup: Menu Utama
        expect($groups)->toHaveCount(1)
            ->and($groups[0]['title'])->toBe('Menu Utama');

        // 2. Tepat 3 menu: Dashboard, Pencarian Perkara, Potensi Terlimpah
        $items = $groups[0]['items'];
        expect($items)->toHaveCount(3);

        $names = array_column($items, 'name');
        $paths = array_column($items, 'path');

        expect($names)->toEqual(['Dashboard', 'Pencarian Perkara', 'Potensi Terlimpah'])
            ->and($paths)->toEqual(['/', '/pencarian-perkara', '/potensi-terlimpah']);

        // 3. Pastikan tidak ada grup Perkara atau Sistem
        expect(collect($groups)->firstWhere('title', 'Perkara'))->toBeNull()
            ->and(collect($groups)->firstWhere('title', 'Sistem'))->toBeNull();

        // 4. Verifikasi rendering sidebar pada halaman dashboard
        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Ambil HTML nav menu saja untuk verifikasi elemen navigasi
        $content = $response->getContent();
        preg_match('/<nav[^>]*>(.*?)<\/nav>/s', $content, $matches);
        $navHtml = $matches[1] ?? '';

        expect($navHtml)->toContain('Dashboard')
            ->and($navHtml)->toContain('Pencarian Perkara')
            ->and($navHtml)->toContain('Potensi Terlimpah')
            ->and($navHtml)->not->toContain('Laporan Kejadian')
            ->and($navHtml)->not->toContain('Sprindik')
            ->and($navHtml)->not->toContain('SPDP')
            ->and($navHtml)->not->toContain('Tahap 1')
            ->and($navHtml)->not->toContain('P21')
            ->and($navHtml)->not->toContain('Tahap 2')
            ->and($navHtml)->not->toContain('Putusan Perkara')
            ->and($navHtml)->not->toContain('Master Data')
            ->and($navHtml)->not->toContain('Pengaturan')
            ->and($navHtml)->not->toContain('Recovery Data')
            ->and($navHtml)->not->toContain('Sampah');
    }
});

test('icon potensi terlimpah pada menu helper menggunakan flaticon valid', function () {
    $icon = MenuHelper::getIconSvg('potensi-terlimpah');
    expect($icon)->toContain('fi fi-rr-time-forward');
});
