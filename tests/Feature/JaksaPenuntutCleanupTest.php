<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('tabel jaksa_penuntut sudah dihapus dari skema database aktif', function () {
    expect(Schema::hasTable('jaksa_penuntut'))->toBeFalse();
    expect(file_exists(app_path('Models/JaksaPenuntut.php')))->toBeFalse();
});

test('migration down dapat merekonstruksi tabel jaksa_penuntut kembali saat rollback', function () {
    $migration = require database_path('migrations/2026_11_11_000002_drop_jaksa_penuntut_table.php');

    $migration->down();
    expect(Schema::hasTable('jaksa_penuntut'))->toBeTrue();

    $migration->up();
    expect(Schema::hasTable('jaksa_penuntut'))->toBeFalse();
});
