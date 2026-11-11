<?php

declare(strict_types=1);

test('app css contains global x-cloak rule and layout has no redundant inline style', function () {
    $cssContent = file_get_contents(resource_path('css/app.css'));
    expect($cssContent)->toContain('[x-cloak]')
        ->toContain('display: none !important');

    $layoutContent = file_get_contents(resource_path('views/layouts/app.blade.php'));
    expect($layoutContent)->not->toContain('<style>')
        ->not->toContain('[x-cloak]');
});

test('vite configuration defines manual chunk splitting for apexcharts', function () {
    $viteConfig = file_get_contents(base_path('vite.config.js'));

    expect($viteConfig)->toContain('manualChunks')
        ->toContain('vendor-apexcharts')
        ->toContain('chunkSizeWarningLimit');
});
