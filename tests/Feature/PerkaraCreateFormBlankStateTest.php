<?php

use App\Models\Alamat;
use App\Models\InstansiHukum;
use App\Models\Perkara;
use App\Models\Tkp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->penyidik = User::factory()->create([
        'role' => 'penyidik',
        'is_active' => true,
    ]);

    $this->ppnsAnggota = User::factory()->create([
        'role' => 'penyidik',
        'is_active' => true,
    ]);
});

test('halaman buat perkara baru menampilkan form dalam keadaan kosong murni', function () {
    // Buat perkara dengan Sprindik final tapi tahap_perkara_status masih null
    $alamatTkp = Alamat::create([
        'nama' => 'Toko Kosmetik Ayu',
        'provinsi_kode' => '63',
        'provinsi_nama' => 'Kalimantan Selatan',
        'kabupaten_kode' => '6371',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'kecamatan_kode' => '637101',
        'kecamatan_nama' => 'Banjarmasin Selatan',
        'desa_kode' => '6371011001',
        'desa_nama' => 'Kelayan Barat',
        'alamat_detail' => 'Jl. Kelayan B No. 12',
        'rt' => '005',
        'rw' => '002',
    ]);

    $tkp = Tkp::create([
        'alamat_id' => $alamatTkp->id,
        'klasifikasi' => 'retailer',
        'legalitas' => 'ilegal',
        'modus' => 'online',
        'created_by' => $this->penyidik->id,
        'updated_by' => $this->penyidik->id,
    ]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/TEST-BLANK/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'laporan_finalized_by' => $this->penyidik->id,
        'nomor_surat_perintah' => 'SP/TEST-BLANK/01/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'sprindik_finalized_by' => $this->penyidik->id,
        'tahap_perkara_status' => null, // Belum pernah dibuat/draft
        'tkp_id' => $tkp->id, // Meskipun ada record tkp prematur di DB
        'created_by' => $this->penyidik->id,
    ]);

    // Pasangkan relasi PPNS prematur
    $perkara->ppnsUsers()->attach([
        $this->penyidik->id => ['peran' => 'utama'],
        $this->ppnsAnggota->id => ['peran' => 'anggota'],
    ]);

    $response = $this->actingAs($this->penyidik)->get(route('perkara.edit', $perkara));

    $response->assertOk();
    $response->assertSee('Buat Perkara & Berkas Perkara');
    $response->assertDontSee('Edit Draft Perkara & Berkas Perkara');

    // 1. Nama Sarana harus kosong
    $response->assertDontSee('value="Toko Kosmetik Ayu"', false);
    $response->assertSee('value=""', false);

    // 2. RT dan RW harus kosong
    $response->assertDontSee('value="005"', false);
    $response->assertDontSee('value="002"', false);

    // 3. Detail Alamat harus kosong
    $response->assertDontSee('Jl. Kelayan B No. 12');

    // 4. Kabupaten / Kecamatan / Desa pada x-data Alpine harus kosong
    $response->assertSee("kabupatenKode: ''", false);
    $response->assertSee("kecamatanKode: ''", false);
    $response->assertSee("desaKode: ''", false);

    // 5. Klasifikasi Sarana tidak terpilih
    $response->assertDontSee('value="retailer" selected', false);
    $response->assertDontSee('value="distributor" selected', false);
    $response->assertDontSee('value="produksi" selected', false);

    // 6. Legalitas dan Modus tidak tercentang (checked)
    $response->assertDontSee('value="legal" checked', false);
    $response->assertDontSee('value="ilegal" checked', false);
    $response->assertDontSee('value="online" checked', false);
    $response->assertDontSee('value="offline" checked', false);

    // 7. Tim Penyidikan (PPNS Utama & Anggota) harus kosong
    $response->assertSee('selectedAnggotaIds: []', false);
    $response->assertDontSee('value="'.$this->penyidik->id.'" selected', false);

    // 8. JPU harus kosong
    $response->assertDontSee('value="Jaksa"', false);
});

test('halaman edit draft perkara menampilkan data tersimpan dengan lengkap', function () {
    $kejaksaan = InstansiHukum::create([
        'nama' => 'Kejaksaan Negeri Banjarmasin',
        'jenis' => 'kejaksaan',
        'wilayah_hukum' => 'Kota Banjarmasin',
        'created_by' => $this->penyidik->id,
    ]);

    $alamatTkp = Alamat::create([
        'nama' => 'Depot Jamu Sehat',
        'provinsi_kode' => '63',
        'provinsi_nama' => 'Kalimantan Selatan',
        'kabupaten_kode' => '6371',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'kecamatan_kode' => '637101',
        'kecamatan_nama' => 'Banjarmasin Selatan',
        'desa_kode' => '6371011001',
        'desa_nama' => 'Kelayan Barat',
        'alamat_detail' => 'Jl. Pangeran Samudera No. 45',
        'rt' => '010',
        'rw' => '003',
    ]);

    $tkp = Tkp::create([
        'alamat_id' => $alamatTkp->id,
        'klasifikasi' => 'produksi',
        'legalitas' => 'legal',
        'modus' => 'offline',
        'created_by' => $this->penyidik->id,
        'updated_by' => $this->penyidik->id,
    ]);

    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/TEST-DRAFT/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'laporan_finalized_by' => $this->penyidik->id,
        'nomor_surat_perintah' => 'SP/TEST-DRAFT/01/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'sprindik_finalized_by' => $this->penyidik->id,
        'tahap_perkara_status' => 'draft', // Tersimpan sebagai draft
        'tkp_id' => $tkp->id,
        'penuntut_umum_instansi_id' => $kejaksaan->id,
        'penuntut_umum_nama' => 'Ahmad Fauzi, S.H.',
        'penuntut_umum_nip' => '198501012010011001',
        'created_by' => $this->penyidik->id,
    ]);

    $perkara->ppnsUsers()->attach([
        $this->penyidik->id => ['peran' => 'utama'],
        $this->ppnsAnggota->id => ['peran' => 'anggota'],
    ]);

    $response = $this->actingAs($this->penyidik)->get(route('perkara.edit', $perkara));

    $response->assertOk();
    $response->assertSee('Edit Draft Perkara & Berkas Perkara');

    // Memuat data draft yang tersimpan
    $response->assertSee('value="Depot Jamu Sehat"', false);
    $response->assertSee('value="010"', false);
    $response->assertSee('value="003"', false);
    $response->assertSee('Jl. Pangeran Samudera No. 45');
    $response->assertSee("kabupatenKode: '6371'", false);
    $response->assertSeeInOrder(['value="produksi"', 'selected'], false);
    $response->assertSeeInOrder(['value="legal"', 'checked'], false);
    $response->assertSeeInOrder(['value="offline"', 'checked'], false);
    $response->assertSee('value="Ahmad Fauzi, S.H."', false);
    $response->assertSee('value="198501012010011001"', false);
});

test('nilai old input tetap dipertahankan saat validasi form buat perkara gagal', function () {
    $perkara = Perkara::create([
        'nomor_laporan_kejadian' => 'LK/TEST-OLD/01/2026',
        'tanggal_laporan_kejadian' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'laporan_locked_at' => now(),
        'laporan_finalized_by' => $this->penyidik->id,
        'nomor_surat_perintah' => 'SP/TEST-OLD/01/2026',
        'tanggal_surat_perintah' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'sprindik_locked_at' => now(),
        'sprindik_finalized_by' => $this->penyidik->id,
        'tahap_perkara_status' => null,
        'created_by' => $this->penyidik->id,
    ]);

    $response = $this->actingAs($this->penyidik)
        ->withSession([
            '_old_input' => [
                'tkp_nama' => 'Gudang Input Pengguna',
                'tkp_klasifikasi' => 'distributor',
                'tkp_legalitas' => 'ilegal',
                'tkp_modus' => 'online',
                'tkp_rt' => '077',
                'tkp_rw' => '088',
            ],
        ])
        ->get(route('perkara.edit', $perkara));

    $response->assertOk();
    $response->assertSee('value="Gudang Input Pengguna"', false);
    $response->assertSeeInOrder(['value="distributor"', 'selected'], false);
    $response->assertSeeInOrder(['value="ilegal"', 'checked'], false);
    $response->assertSeeInOrder(['value="online"', 'checked'], false);
    $response->assertSee('value="077"', false);
    $response->assertSee('value="088"', false);
});
