<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create([
        'nama' => 'Penyidik Satu',
        'role' => 'penyidik',
        'is_active' => true,
    ]);

    $this->penyidikDua = User::factory()->create([
        'nama' => 'Penyidik Dua',
        'role' => 'penyidik',
        'is_active' => true,
    ]);
});

test('tanggal laporan kejadian yang disimpan sebagai draft tampil kembali saat form edit dibuka', function () {
    $this->actingAs($this->penyidik)
        ->post(route('laporan-kejadian.store'), [
            'action_type' => 'draft',
            'nomor_laporan' => 'LK/DRAFT-TGL/01/2026',
            'tanggal_laporan' => '2026-10-15',
            'deskripsi' => 'Draft LK dengan tanggal',
        ])
        ->assertRedirect();

    $perkara = Perkara::where('nomor_laporan_kejadian', 'LK/DRAFT-TGL/01/2026')->firstOrFail();
    expect($perkara->tanggal_laporan_kejadian?->format('Y-m-d'))->toBe('2026-10-15');

    $this->actingAs($this->penyidik)
        ->get(route('laporan-kejadian.edit', $perkara))
        ->assertOk()
        ->assertSee('value="2026-10-15"', false);
});

test('tanggal sprindik yang disimpan sebagai draft tampil kembali saat form edit dibuka', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/SPR-TGL/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'laporan_finalized_by' => $this->penyidik->id,
        'created_by' => $this->penyidik->id,
    ]);

    $this->actingAs($this->penyidik)
        ->post(route('sprindik.store'), [
            'action_type' => 'draft',
            'perkara_id' => $perkara->id,
            'nomor_sprindik' => 'SP/DRAFT-TGL/01/2026',
            'tanggal_sprindik' => '2026-10-18',
        ])
        ->assertRedirect();

    $perkara->refresh();
    expect($perkara->tanggal_surat_perintah?->format('Y-m-d'))->toBe('2026-10-18');

    $this->actingAs($this->penyidik)
        ->get(route('sprindik.edit', $perkara))
        ->assertOk()
        ->assertSee('value="2026-10-18"', false);
});

test('tanggal lahir tersangka dan tanggal tahapan yang disimpan sebagai draft tampil kembali saat form edit dibuka', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PRK-TGL/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/PRK-TGL/01/2026',
        'tanggal_surat_perintah' => '2026-10-12',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $this->actingAs($this->penyidik)
        ->put(route('perkara.update', $perkara), [
            'action_type' => 'draft',
            'tersangka_nama' => 'Tersangka Draft',
            'tersangka_tanggal_lahir' => '1992-08-17',
        ])
        ->assertRedirect();

    $this->actingAs($this->penyidik)
        ->get(route('perkara.edit', $perkara))
        ->assertOk()
        ->assertSee('value="1992-08-17"', false);

    // Finalkan perkara untuk menguji draft tahapan SPDP & P-16
    $perkara->update(['tahap_perkara_status' => 'final', 'perkara_locked_at' => now()]);
    $berkas = BerkasPerkara::where('perkara_id', $perkara->id)->firstOrFail();

    $this->actingAs($this->penyidik)
        ->post(route('berkas-perkara.tahapan.store', $berkas), [
            'action_type' => 'draft',
            'jenis_tahap' => JenisTahap::SPDP->value,
            'nomor_surat' => 'SPDP/DRAFT/01/2026',
            'tanggal_surat' => '2026-10-20',
            'p16_nomor_surat' => 'P16/DRAFT/01/2026',
            'p16_tanggal_surat' => '2026-10-22',
        ])
        ->assertRedirect();

    $tahapSpdp = $berkas->tahapan()->where('jenis_tahap', JenisTahap::SPDP->value)->firstOrFail();

    $this->actingAs($this->penyidik)
        ->get(route('berkas-perkara.tahapan.edit', [$berkas, $tahapSpdp]))
        ->assertOk()
        ->assertSee('value="2026-10-20"', false)
        ->assertSee('value="2026-10-22"', false);
});

test('penyidik yang sudah dipilih sebagai PPNS Utama ditolak jika dikirim lagi sebagai Penyidik Anggota', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/PPNS/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-10',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'nomor_surat_perintah' => 'SP/PPNS/01/2026',
        'tanggal_surat_perintah' => '2026-10-12',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)
        ->from(route('perkara.edit', $perkara))
        ->put(route('perkara.update', $perkara), [
            'action_type' => 'draft',
            'ppns_utama_id' => $this->penyidik->id,
            'ppns_anggota_ids' => [$this->penyidik->id, $this->penyidikDua->id],
            'tersangka_nama' => 'Tersangka Uji PPNS',
        ]);

    $response->assertRedirect(route('perkara.edit', $perkara))
        ->assertSessionHasErrors('ppns_anggota_ids.0');
});
