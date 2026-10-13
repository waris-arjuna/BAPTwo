<?php

namespace Tests\Feature;

use App\Enums\JenisTahap;
use App\Models\Alamat;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Models\Tkp;
use App\Models\User;
use App\Services\PerkaraService;
use App\Services\TahapanService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TahapanSingleUseValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $penyidik;

    protected function setUp(): void
    {
        parent::setUp();

        $this->penyidik = User::factory()->create([
            'role' => 'penyidik',
            'is_active' => true,
        ]);
    }

    private function createPerkaraLaporanFinal(): Perkara
    {
        return Perkara::create([
            'nomor_laporan_kejadian' => 'LK/01/X/2026',
            'tanggal_laporan_kejadian' => '2026-10-01',
            'tahap_laporan_status' => 'final',
            'laporan_locked_at' => now(),
            'laporan_finalized_by' => $this->penyidik->id,
            'created_by' => $this->penyidik->id,
        ]);
    }

    private function createBerkasWithSprindikAndPerkaraFinal(): BerkasPerkara
    {
        $perkara = Perkara::create([
            'nomor_laporan_kejadian' => 'LK/SP/01/2026',
            'tanggal_laporan_kejadian' => '2026-10-01',
            'tahap_laporan_status' => 'final',
            'nomor_surat_perintah' => 'SP/01/2026',
            'tanggal_surat_perintah' => '2026-10-05',
            'tahap_sprindik_status' => 'final',
            'tahap_perkara_status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);

        $alamat = Alamat::create([
            'alamat_detail' => 'Jl. Test No. 1',
        ]);

        $tkp = Tkp::create([
            'alamat_id' => $alamat->id,
            'klasifikasi_lokasi' => 'Gudang',
        ]);
        $perkara->update(['tkp_id' => $tkp->id]);

        $tersangka = Tersangka::create([
            'perkara_id' => $perkara->id,
            'nama' => 'John Doe',
            'nik' => '6371010101010001',
            'created_by' => $this->penyidik->id,
        ]);

        return BerkasPerkara::create([
            'perkara_id' => $perkara->id,
            'tersangka_id' => $tersangka->id,
            'nomor_berkas' => 'BP/01/2026',
            'created_by' => $this->penyidik->id,
        ]);
    }

    public function test_laporan_kejadian_hanya_dapat_digunakan_satu_kali_pada_sprindik(): void
    {
        $perkara = $this->createPerkaraLaporanFinal();
        $perkaraService = app(PerkaraService::class);

        // 1. Awalnya muncul di getAvailableForSprindik
        $available = $perkaraService->getAvailableForSprindik();
        $this->assertTrue($available->contains('id', $perkara->id));

        // 2. Attach Sprindik
        $perkaraService->attachSprindik($perkara, [
            'action_type' => 'lanjut',
            'nomor_sprindik' => 'SP/01/TEST/2026',
            'tanggal_sprindik' => '2026-10-02',
            'deskripsi' => 'Catatan sprindik',
        ], null, $this->penyidik);

        // 3. Setelah dipakai, tidak lagi muncul di getAvailableForSprindik
        $availableAfter = $perkaraService->getAvailableForSprindik();
        $this->assertFalse($availableAfter->contains('id', $perkara->id));

        // 4. Mencoba attachSprindik lagi melempar DomainException
        $this->expectException(DomainException::class);
        $perkaraService->attachSprindik($perkara->fresh(), [
            'action_type' => 'lanjut',
            'nomor_sprindik' => 'SP/02/DUPLIKAT/2026',
            'tanggal_sprindik' => '2026-10-03',
        ], null, $this->penyidik);
    }

    public function test_request_store_sprindik_menolak_laporan_kejadian_yang_sudah_memiliki_sprindik(): void
    {
        $perkara = $this->createPerkaraLaporanFinal();
        $perkara->update([
            'nomor_surat_perintah' => 'SP/EXISTING/2026',
            'tahap_sprindik_status' => 'final',
        ]);

        $response = $this->actingAs($this->penyidik)
            ->post(route('sprindik.store'), [
                'perkara_id' => $perkara->id,
                'nomor_sprindik' => 'SP/BARU/2026',
                'tanggal_sprindik' => '2026-10-05',
                'action_type' => 'lanjut',
            ]);

        $response->assertSessionHasErrors(['perkara_id']);
    }

    public function test_sprindik_create_controller_redirects_if_perkara_id_already_has_sprindik(): void
    {
        $perkara = $this->createPerkaraLaporanFinal();
        $perkara->update([
            'nomor_surat_perintah' => 'SP/EXISTING/2026',
            'tahap_sprindik_status' => 'final',
        ]);

        $response = $this->actingAs($this->penyidik)
            ->get(route('sprindik.create', ['perkara_id' => $perkara->id]));

        $response->assertRedirect(route('sprindik.create'));
        $response->assertSessionHas('error');
    }

    public function test_berkas_yang_sudah_memiliki_spdp_tidak_dapat_dipilih_lagi_pada_tahapan_spdp(): void
    {
        $berkas = $this->createBerkasWithSprindikAndPerkaraFinal();
        $tahapanService = app(TahapanService::class);

        // Sebelum ada SPDP: muncul di getAvailableBerkasForSpdp
        $this->assertTrue($tahapanService->getAvailableBerkasForSpdp()->contains('id', $berkas->id));

        // Buat SPDP final
        TahapanPerkara::create([
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => JenisTahap::SPDP,
            'nomor_surat' => 'SPDP/01/2026',
            'tanggal_surat' => '2026-10-06',
            'status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);

        // Setelah ada SPDP: tidak muncul lagi di getAvailableBerkasForSpdp
        $this->assertFalse($tahapanService->getAvailableBerkasForSpdp()->contains('id', $berkas->id));

        // Akses via spdp.create?berkas_id= ditolak
        $response = $this->actingAs($this->penyidik)
            ->get(route('spdp.create', ['berkas_id' => $berkas->id]));

        $response->assertRedirect(route('spdp.index'));
        $response->assertSessionHas('error');
    }

    public function test_berkas_yang_sudah_memiliki_tahap_1_tidak_dapat_dipilih_lagi_pada_tahap_1(): void
    {
        $berkas = $this->createBerkasWithSprindikAndPerkaraFinal();
        $tahapanService = app(TahapanService::class);

        TahapanPerkara::create([
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => JenisTahap::SPDP,
            'nomor_surat' => 'SPDP/01/2026',
            'tanggal_surat' => '2026-10-06',
            'status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);

        // Siap untuk Tahap 1
        $this->assertTrue($tahapanService->getAvailableBerkasForTahap1()->contains('id', $berkas->id));

        // Buat Tahap 1 (meskipun draft!)
        TahapanPerkara::create([
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => JenisTahap::TAHAP_1,
            'nomor_surat' => 'T1/01/2026',
            'tanggal_surat' => '2026-10-07',
            'status' => 'draft',
            'created_by' => $this->penyidik->id,
        ]);

        // Tidak boleh muncul lagi di getAvailableBerkasForTahap1
        $this->assertFalse($tahapanService->getAvailableBerkasForTahap1()->contains('id', $berkas->id));

        // Akses via tahap-1.create?berkas_id= ditolak
        $response = $this->actingAs($this->penyidik)
            ->get(route('tahap-1.create', ['berkas_id' => $berkas->id]));

        $response->assertRedirect(route('tahap-1.index'));
        $response->assertSessionHas('error');
    }

    public function test_store_tahapan_request_menolak_tahapan_yang_sudah_pernah_dicatat_pada_berkas(): void
    {
        $berkas = $this->createBerkasWithSprindikAndPerkaraFinal();

        TahapanPerkara::create([
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => JenisTahap::SPDP,
            'nomor_surat' => 'SPDP/01/2026',
            'tanggal_surat' => '2026-10-06',
            'status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);

        // Mencoba store SPDP lagi pada berkas yang sama
        $response = $this->actingAs($this->penyidik)
            ->post(route('berkas-perkara.tahapan.store', $berkas), [
                'jenis_tahap' => 'spdp',
                'nomor_surat' => 'SPDP/DUPLIKAT/2026',
                'tanggal_surat' => '2026-10-07',
                'action_type' => 'lanjut',
            ]);

        $response->assertSessionHasErrors(['jenis_tahap']);
    }

    public function test_berkas_tahapan_create_menolak_query_jenis_yang_tidak_tersedia(): void
    {
        $berkas = $this->createBerkasWithSprindikAndPerkaraFinal();

        TahapanPerkara::create([
            'berkas_perkara_id' => $berkas->id,
            'jenis_tahap' => JenisTahap::SPDP,
            'nomor_surat' => 'SPDP/01/2026',
            'tanggal_surat' => '2026-10-06',
            'status' => 'final',
            'created_by' => $this->penyidik->id,
        ]);

        // Coba akses ?jenis=spdp (karena SPDP sudah ada, tidak ada di availableStages)
        $response = $this->actingAs($this->penyidik)
            ->get(route('berkas-perkara.tahapan.create', [
                'berkas' => $berkas,
                'jenis' => 'spdp',
            ]));

        $response->assertRedirect(route('perkara.show', $berkas->perkara_id));
        $response->assertSessionHas('error');
    }
}
