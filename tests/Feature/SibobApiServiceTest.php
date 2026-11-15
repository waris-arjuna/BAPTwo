<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\SibobApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('syncs users from SIBOB API, maps no_pegawai to nip, assigns penyidik to Penindakan, and deactivates OUT status', function () {
    config([
        'services.sibob.base_url' => 'https://sibob.bbpombanjarmasin.com',
        'services.sibob.service_email' => 'magangit@mail.com',
        'services.sibob.service_password' => 'magang14!T',
    ]);

    Http::fake([
        'https://sibob.bbpombanjarmasin.com/api/v1/login' => Http::response([
            'status' => 'success',
            'data' => [
                'token' => 'fake-sibob-token-123',
                'user' => ['id' => 1209, 'email' => 'magangit@mail.com', 'name' => 'MAGANG-IT'],
            ],
        ], 200),
        'https://sibob.bbpombanjarmasin.com/api/v1/users' => Http::response([
            [
                'id' => 1,
                'nip' => '',
                'no_pegawai' => 'superadmin',
                'name' => 'Superadmin',
                'email' => 'example@gmail.com',
                'jenis_kelamin' => 'P',
                'divisi_name' => 'Tata Usaha',
                'jabasn_name' => null,
                'golongan' => 'III/a',
                'status' => 'PPNPN',
                'aktif' => 'Y',
            ],
            [
                'id' => 15,
                'nip' => '',
                'no_pegawai' => '199204152015021001',
                'name' => 'Benny Ismayandi, S,Farm, Apt',
                'email' => 'benyy.ismayandi@pom.go.id',
                'jenis_kelamin' => 'L',
                'divisi_name' => 'Penindakan',
                'jabasn_name' => 'Pengawas Farmasi dan Makanan Ahli Muda',
                'golongan' => 'III/d',
                'status' => 'PNS',
                'aktif' => 'Y',
            ],
            [
                'id' => 11,
                'nip' => '',
                'no_pegawai' => '196810151990032001',
                'name' => 'Risnaniah, S.M.',
                'email' => 'risnaniah@pom.go.id',
                'jenis_kelamin' => 'P',
                'divisi_name' => 'Tata Usaha',
                'jabasn_name' => 'Penata Layanan Operasional',
                'golongan' => 'IX/',
                'status' => 'PPPK',
                'aktif' => 'Y',
            ],
            [
                'id' => 99,
                'nip' => '',
                'no_pegawai' => '196501011990031001',
                'name' => 'Pegawai Purna Tugas',
                'email' => 'purna@pom.go.id',
                'jenis_kelamin' => 'L',
                'divisi_name' => 'Pemeriksaan',
                'jabasn_name' => 'Pengawas Farmasi',
                'golongan' => '-/-',
                'status' => 'OUT',
                'aktif' => 'Y',
            ],
        ], 200),
    ]);

    $service = app(SibobApiService::class);
    $summary = $service->syncAllUsers();

    expect($summary)->toMatchArray([
        'created' => 3,
        'updated' => 0,
        'deactivated' => 1,
        'total' => 3,
    ]);

    expect(User::where('email', 'example@gmail.com')->exists())->toBeFalse();

    $penyidik = User::where('email', 'benyy.ismayandi@pom.go.id')->firstOrFail();
    expect($penyidik->nip)->toBe('199204152015021001')
        ->and($penyidik->role)->toBe(UserRole::PENYIDIK)
        ->and($penyidik->is_active)->toBeTrue()
        ->and($penyidik->jenis_kelamin)->toBe('laki-laki')
        ->and($penyidik->status)->toBe('pns')
        ->and($penyidik->bidang?->nama_bidang)->toBe('Penindakan')
        ->and($penyidik->jabatan?->nama_jabatan)->toBe('Pengawas Farmasi dan Makanan Ahli Muda')
        ->and($penyidik->panggol?->golongan)->toBe('III/d');

    $umum = User::where('email', 'risnaniah@pom.go.id')->firstOrFail();
    expect($umum->role)->toBe(UserRole::UMUM)
        ->and($umum->status)->toBe('pppk')
        ->and($umum->panggol?->golongan)->toBe('IX');

    $out = User::where('email', 'purna@pom.go.id')->firstOrFail();
    expect($out->is_active)->toBeFalse()
        ->and($out->status)->toBe('lainnya')
        ->and($out->panggol_id)->toBeNull();
});

it('preserves existing BAPTwo role and active status when re-syncing users from SIBOB', function () {
    $existing = User::factory()->create([
        'nama' => 'Risnaniah Lama',
        'nip' => '196810151990032001',
        'email' => 'risnaniah@pom.go.id',
        'role' => UserRole::ADMINISTRATOR,
        'is_active' => true,
    ]);

    $service = app(SibobApiService::class);
    $updated = $service->syncUserFromSibobData([
        'id' => 11,
        'nip' => '',
        'no_pegawai' => '196810151990032001',
        'name' => 'Risnaniah, S.M.',
        'email' => 'risnaniah@pom.go.id',
        'jenis_kelamin' => 'P',
        'divisi_name' => 'Tata Usaha',
        'jabasn_name' => 'Penata Layanan Operasional',
        'golongan' => 'III/d',
        'status' => 'PNS',
        'aktif' => 'Y',
    ]);

    expect($updated->id)->toBe($existing->id)
        ->and($updated->nama)->toBe('Risnaniah, S.M.')
        ->and($updated->role)->toBe(UserRole::ADMINISTRATOR)
        ->and($updated->is_active)->toBeTrue();
});
