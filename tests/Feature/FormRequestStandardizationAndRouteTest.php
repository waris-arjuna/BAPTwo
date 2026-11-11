<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('rute potensi terlimpah melakukan redirect native ke data perkara beserta query string', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);

    $response = $this->actingAs($penyidik)
        ->get(route('potensi-terlimpah.index', ['kesiapan' => 'siap_tahap_2', 'search' => 'obat']));

    $response->assertRedirect(route('data-perkara.index', ['kesiapan' => 'siap_tahap_2', 'search' => 'obat']));
});

test('perkara export request memvalidasi batas tahun dan bulan secara formal', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);

    $response = $this->actingAs($penyidik)
        ->get(route('perkara.export', ['mode' => 'tahun', 'tahun' => 'invalid-year']));

    $response->assertSessionHasErrors(['tahun']);
});

test('assign petugas request memvalidasi pencegahan penugasan akun sendiri oleh penyidik', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);

    $response = $this->actingAs($penyidik)
        ->post(route('data-petugas.store'), [
            'user_id' => $penyidik->id,
            'role' => UserRole::UMUM->value,
        ]);

    $response->assertSessionHasErrors(['user_id']);
});

test('save sub tahap request memvalidasi batas upload file pdf dan grup sub tahap', function () {
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK, 'is_active' => true]);
    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/099/XI/2026',
        'tanggal_laporan' => '2026-11-01',
        'tahap_laporan_status' => 'final',
        'nomor_sprindik' => 'SP/099/XI/2026',
        'tanggal_sprindik' => '2026-11-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'created_by' => $penyidik->id,
    ]);
    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka Test',
        'nik' => '6371012345670099',
        'created_by' => $penyidik->id,
    ]);
    $berkas = BerkasPerkara::create([
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/099/XI/2026',
        'created_by' => $penyidik->id,
    ]);

    $response = $this->actingAs($penyidik)
        ->post(route('berkas-perkara.tahapan.sub-tahap', $berkas), [
            'sub_tahap_group' => 'invalid_group',
        ]);

    $response->assertSessionHasErrors(['sub_tahap_group']);
});
