<?php

use App\Models\Perkara;
use App\Models\User;
use Database\Seeders\AdminPimpinanSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DummyDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('DatabaseSeeder menolak eksekusi saat environment production', function () {
    app()['env'] = 'production';

    $seeder = new DatabaseSeeder;
    $userCountBefore = User::count();

    $seeder->run();

    expect(User::count())->toBe($userCountBefore);
});

test('DummyDataSeeder menolak eksekusi saat environment production via instance', function () {
    app()['env'] = 'production';

    $seeder = new DummyDataSeeder;
    $perkaraCountBefore = Perkara::count();

    $seeder->run();

    expect(Perkara::count())->toBe($perkaraCountBefore);
});

test('AdminPimpinanSeeder menolak eksekusi saat environment production via instance', function () {
    app()['env'] = 'production';

    $seeder = new AdminPimpinanSeeder;

    $seeder->run();

    expect(User::where('email', 'admin.it@baptwo.test')->exists())->toBeFalse()
        ->and(User::where('email', 'kabag.tu@baptwo.test')->exists())->toBeFalse();
});

test('db:seed dengan DummyDataSeeder mengeluarkan peringatan dan berhenti saat production', function () {
    app()['env'] = 'production';

    $perkaraCountBefore = Perkara::count();

    $this->artisan('db:seed', ['--class' => 'DummyDataSeeder', '--force' => true])
        ->expectsOutput('DummyDataSeeder hanya dapat dijalankan pada environment local atau testing.')
        ->assertSuccessful();

    expect(Perkara::count())->toBe($perkaraCountBefore);
});

test('db:seed dengan AdminPimpinanSeeder mengeluarkan peringatan dan berhenti saat production', function () {
    app()['env'] = 'production';

    $this->artisan('db:seed', ['--class' => 'AdminPimpinanSeeder', '--force' => true])
        ->expectsOutput('AdminPimpinanSeeder hanya dapat dijalankan pada environment local atau testing.')
        ->assertSuccessful();

    expect(User::where('email', 'admin.it@baptwo.test')->exists())->toBeFalse();
});
