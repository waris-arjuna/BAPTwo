<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tempSource = storage_path('framework/testing/wilayah_raw');
    $this->tempTarget = storage_path('framework/testing/wilayah_target');

    File::ensureDirectoryExists($this->tempSource);
    File::ensureDirectoryExists($this->tempTarget);
});

afterEach(function () {
    File::deleteDirectory($this->tempSource);
    File::deleteDirectory($this->tempTarget);
});

it('dapat mempartisi file wilayah mentah menjadi file per-kabupaten yang terstruktur', function () {
    File::put($this->tempSource.'/provinces.json', json_encode([
        ['code' => '63', 'name' => 'Kalimantan Selatan'],
    ]));
    File::put($this->tempSource.'/regencies.json', json_encode([
        ['code' => '6371', 'province_code' => '63', 'name' => 'Kota Banjarmasin'],
    ]));
    File::put($this->tempSource.'/districts.json', json_encode([
        ['code' => '637101', 'regency_code' => '6371', 'name' => 'Banjarmasin Selatan'],
    ]));
    File::put($this->tempSource.'/villages.json', json_encode([
        ['code' => '6371011001', 'district_code' => '637101', 'name' => 'Mantuil'],
    ]));

    $this->artisan('wilayah:import', [
        '--source' => $this->tempSource,
        '--target' => $this->tempTarget,
    ])
        ->expectsOutputToContain('Import dan partisi wilayah berhasil!')
        ->assertSuccessful();

    expect(File::exists($this->tempTarget.'/provinces.json'))->toBeTrue()
        ->and(File::exists($this->tempTarget.'/regencies.json'))->toBeTrue()
        ->and(File::exists($this->tempTarget.'/districts/6371.json'))->toBeTrue()
        ->and(File::exists($this->tempTarget.'/villages/6371.json'))->toBeTrue();

    $villages = json_decode(File::get($this->tempTarget.'/villages/6371.json'), true);
    expect($villages)->toBeArray()
        ->and($villages['637101'])->toBeArray()
        ->and($villages['637101'][0]['name'])->toBe('Mantuil');
});
