<?php

test('css mendefinisikan token durasi, easing, scrollbar-gutter stable, dan prefers-reduced-motion', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('--duration-fast: 150ms;')
        ->toContain('--duration-normal: 200ms;')
        ->toContain('--duration-standard: 250ms;')
        ->toContain('--duration-slow: 400ms;')
        ->toContain('--ease-in-smooth: cubic-bezier(0.4, 0, 1, 1);')
        ->toContain('--ease-out-smooth: cubic-bezier(0.22, 1, 0.36, 1);')
        ->toContain('scrollbar-gutter: stable;')
        ->toContain('prefers-reduced-motion: reduce');
});

test('tidak ada penggunaan class transition-all di seluruh view blade', function () {
    $viewsPath = resource_path('views');
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
    $offendingFiles = [];

    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $content = file_get_contents($file->getPathname());
            if (str_contains($content, 'transition-all')) {
                $offendingFiles[] = $file->getPathname();
            }
        }
    }

    expect($offendingFiles)->toBeEmpty();
});

test('progress bar di seluruh partial tabel menggunakan transform scaleX gpu compositor dengan origin-left', function () {
    $filesToCheck = [
        resource_path('views/components/dashboard/_progress-partial.blade.php'),
        resource_path('views/pages/pencarian-perkara/_table-partial.blade.php'),
        resource_path('views/pages/progress-perkara/_table-partial.blade.php'),
    ];

    foreach ($filesToCheck as $filePath) {
        $content = file_get_contents($filePath);
        expect($content)->toContain('origin-left')
            ->toContain('scaleX');
    }
});
