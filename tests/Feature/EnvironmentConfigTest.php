<?php

it('has correct BAPTwo environment and locale configurations', function () {
    expect(config('app.name'))->toBe('BAPTwo');
    expect(config('app.locale'))->toBe('id');
    expect(config('app.fallback_locale'))->toBe('id');
    expect(config('app.faker_locale'))->toBe('id_ID');
});

it('has correct defaults in .env.example', function () {
    $envExample = file_get_contents(base_path('.env.example'));

    expect($envExample)->toContain('APP_NAME=BAPTwo');
    expect($envExample)->toContain('APP_LOCALE=id');
    expect($envExample)->toContain('APP_FALLBACK_LOCALE=id');
    expect($envExample)->toContain('APP_FAKER_LOCALE=id_ID');
    expect($envExample)->toContain('DB_DATABASE=baptwo');
});
