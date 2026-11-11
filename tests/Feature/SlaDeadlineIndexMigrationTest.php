<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('tabel berkas_perkara memiliki indeks pada kolom sla_deadline', function () {
    expect(Schema::hasIndex('berkas_perkara', ['sla_deadline']))->toBeTrue();
});

test('migration down dapat menghapus indeks sla_deadline tanpa error', function () {
    $migration = require database_path('migrations/2026_11_11_000001_add_index_to_sla_deadline_on_berkas_perkara_table.php');

    $migration->down();
    expect(Schema::hasIndex('berkas_perkara', ['sla_deadline']))->toBeFalse();

    $migration->up();
    expect(Schema::hasIndex('berkas_perkara', ['sla_deadline']))->toBeTrue();
});
