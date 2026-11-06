<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->rawPath = resource_path('data/wilayah/raw');
    File::ensureDirectoryExists($this->rawPath);

    // Backup any existing files if needed
    $this->backupFiles = [
        'provinces.json' => resource_path('data/wilayah/provinces.json'),
        'regencies.json' => resource_path('data/wilayah/regencies.json'),
    ];
    $this->backups = [];
    foreach ($this->backupFiles as $key => $file) {
        if (File::exists($file)) {
            $this->backups[$key] = File::get($file);
        }
    }
});

afterEach(function () {
    // Restore backups
    foreach ($this->backupFiles as $key => $file) {
        if (isset($this->backups[$key])) {
            File::put($file, $this->backups[$key]);
        }
    }

    // Clean up created mock raw files and test partitions
    @unlink($this->rawPath.'/provinces.json');
    @unlink($this->rawPath.'/regencies.json');
    @unlink($this->rawPath.'/districts.json');
    @unlink($this->rawPath.'/villages.json');

    @unlink(resource_path('data/wilayah/districts/6371.json'));
    @unlink(resource_path('data/wilayah/villages/6371.json'));
});

it('dapat mempartisi file wilayah mentah menjadi file per-kabupaten yang terstruktur', function () {
    File::put($this->rawPath.'/provinces.json', json_encode([
        ['code' => '63', 'name' => 'Kalimantan Selatan'],
    ]));
    File::put($this->rawPath.'/regencies.json', json_encode([
        ['code' => '6371', 'province_code' => '63', 'name' => 'Kota Banjarmasin'],
    ]));
    File::put($this->rawPath.'/districts.json', json_encode([
        ['code' => '637101', 'regency_code' => '6371', 'name' => 'Banjarmasin Selatan'],
    ]));
    File::put($this->rawPath.'/villages.json', json_encode([
        ['code' => '6371011001', 'district_code' => '637101', 'name' => 'Mantuil'],
    ]));

    $this->artisan('wilayah:import')
        ->expectsOutputToContain('Import dan partisi wilayah berhasil!')
        ->assertSuccessful();

    expect(File::exists(resource_path('data/wilayah/provinces.json')))->toBeTrue()
        ->and(File::exists(resource_path('data/wilayah/regencies.json')))->toBeTrue()
        ->and(File::exists(resource_path('data/wilayah/districts/6371.json')))->toBeTrue()
        ->and(File::exists(resource_path('data/wilayah/villages/6371.json')))->toBeTrue();

    $villages = json_decode(File::get(resource_path('data/wilayah/villages/6371.json')), true);
    expect($villages)->toBeArray()
        ->and($villages['637101'])->toBeArray()
        ->and($villages['637101'][0]['name'])->toBe('Mantuil');
});
