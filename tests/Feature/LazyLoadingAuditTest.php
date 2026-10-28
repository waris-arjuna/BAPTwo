<?php

use App\Enums\JenisTahap;
use App\Enums\UserRole;
use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Pasal;
use App\Models\Perkara;
use App\Models\Sp3;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Model::preventLazyLoading(true);

    $this->admin = User::factory()->administrator()->create();
    $this->penyidik = User::factory()->penyidik()->create();
    $this->pimpinan = User::factory()->pimpinan()->create();
    $this->umum = User::factory()->create(['role' => UserRole::UMUM]);

    $this->perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/AUDIT-01/X/2026',
        'nomor_surat_perintah' => 'SP/AUDIT-01/X/2026',
        'tanggal_surat_perintah' => '2026-10-10',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
    ]);

    $this->tersangka = Tersangka::create([
        'perkara_id' => $this->perkara->id,
        'nama' => 'Tersangka Audit',
        'nik' => '6371019988776655',
        'jenis_kelamin' => 'laki-laki',
    ]);

    $this->pasal = Pasal::create([
        'nama_pasal' => 'UU Kesehatan',
        'pasal' => 'Pasal 435',
        'nomor' => '17',
        'tahun' => '2023',
        'status' => 'aktif',
    ]);

    $this->berkas = BerkasPerkara::create([
        'perkara_id' => $this->perkara->id,
        'tersangka_id' => $this->tersangka->id,
        'nomor_berkas' => 'BP/AUDIT-01/2026',
        'status_formal' => null,
        'status_proses' => 'SPDP',
        'progress_persen' => 15,
        'is_dpo' => false,
    ]);
    $this->berkas->pasals()->attach($this->pasal->id);

    $this->tahapan = TahapanPerkara::create([
        'berkas_perkara_id' => $this->berkas->id,
        'jenis_tahap' => JenisTahap::SPDP,
        'nomor_surat' => 'B-01/SPDP/2026',
        'tanggal_surat' => '2026-10-12',
        'status' => 'final',
        'finalized_at' => now(),
    ]);
});

test('halaman progress perkara dapat diakses tanpa LazyLoadingViolationException saat preventLazyLoading aktif', function () {
    // 1. Role Pimpinan
    $responsePimpinan = $this->actingAs($this->pimpinan)->get(route('progress-perkara.index'));
    $responsePimpinan->assertOk()
        ->assertSee('BP/AUDIT-01/2026')
        ->assertSee('Tahapan Terakhir');

    // 2. Partial AJAX
    $responsePartial = $this->actingAs($this->pimpinan)
        ->withHeader('X-Filter-Partial', '1')
        ->get(route('progress-perkara.index'));
    $responsePartial->assertOk()
        ->assertSee('BP/AUDIT-01/2026');

    // 3. Role Umum (Progress Perkara)
    $responseUmum = $this->actingAs($this->umum)->get(route('progress-perkara.index'));
    $responseUmum->assertOk()
        ->assertSee('BP/AUDIT-01/2026');

    // 4. Role Admin dialihkan ke Data Perkara dan halaman Data Perkara bebas LazyLoadingViolationException
    $responseAdminRedirect = $this->actingAs($this->admin)->get(route('progress-perkara.index'));
    $responseAdminRedirect->assertRedirect(route('data-perkara.index'));

    $responseDataPerkara = $this->actingAs($this->admin)->get(route('data-perkara.index'));
    $responseDataPerkara->assertOk();
});

test('audit halaman utama bebas dari LazyLoadingViolationException untuk seluruh role', function () {
    $roles = [$this->admin, $this->pimpinan, $this->penyidik];

    foreach ($roles as $user) {
        // Dashboard
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        // Seluruh Perkara (Pimpinan/Umum: 200 OK, Admin/Penyidik: 302 Redirect ke data-perkara)
        if ($user->isAdmin() || $user->isPenyidik()) {
            $this->actingAs($user)->get(route('seluruh-perkara.index'))->assertRedirect(route('data-perkara.index'));
        } else {
            $this->actingAs($user)->get(route('seluruh-perkara.index'))->assertOk();
        }

        // Pencarian Perkara
        $this->actingAs($user)->get(route('pencarian-perkara.index'))->assertOk();

        // Data Perkara (Daftar Perkara)
        $this->actingAs($user)->get(route('data-perkara.index'))->assertOk();

        // Detail Perkara
        $this->actingAs($user)->get(route('perkara.show', $this->perkara))->assertOk();
    }
});

test('halaman detail perkara dengan SP3 dan dokumen bebas dari LazyLoadingViolationException', function () {
    $dokumenSp3 = Dokumen::create([
        'berkas_perkara_id' => $this->berkas->id,
        'kategori' => 'sp3',
        'nama_dokumen' => 'Dokumen SP3',
        'nama_file_asli' => 'sp3.pdf',
        'file_path' => 'dokumen/sp3.pdf',
        'file_size' => 1024,
    ]);

    Sp3::create([
        'berkas_perkara_id' => $this->berkas->id,
        'nomor_surat' => 'SP3/01/X/2026',
        'tanggal_sp3' => '2026-10-15',
        'keterangan' => 'Tidak cukup bukti',
        'dokumen_id' => $dokumenSp3->id,
    ]);

    $this->berkas->update([
        'status_formal' => 'tidak_selesai',
        'status_proses' => 'SP3',
    ]);

    foreach ([$this->admin, $this->pimpinan, $this->penyidik, $this->umum] as $user) {
        $response = $this->actingAs($user)->get(route('perkara.show', $this->perkara));
        $response->assertOk()
            ->assertSee('SP3 Terbit (Tidak Selesai)');
    }
});
