<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Helper fixture untuk test yang memerlukan baseline sample data perkara & tahapan
     */
    protected function seedBaselineCasesAndStages(): void
    {
        $samplePerkara = [
            [
                'id' => 1,
                'nomor_laporan_kejadian' => 'LK/001/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-02',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => null,
                'tanggal_surat_perintah' => null,
                'tahap_sprindik_status' => null,
                'deskripsi' => 'Dugaan peredaran sediaan farmasi tanpa izin edar.',
            ],
            [
                'id' => 2,
                'nomor_laporan_kejadian' => 'LK/002/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-04',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => 'SP/001/IX/2026',
                'tanggal_surat_perintah' => '2026-09-05',
                'tahap_sprindik_status' => 'final',
                'sprindik_locked_at' => now(),
                'deskripsi' => 'Dugaan peredaran kosmetik mengandung bahan berbahaya.',
            ],
            [
                'id' => 3,
                'nomor_laporan_kejadian' => 'LK/003/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-05',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => 'SP/002/IX/2026',
                'tanggal_surat_perintah' => '2026-09-06',
                'tahap_sprindik_status' => 'final',
                'sprindik_locked_at' => now(),
                'deskripsi' => 'Dugaan pangan olahan tanpa izin edar skala rumahan.',
            ],
            [
                'id' => 4,
                'nomor_laporan_kejadian' => 'LK/004/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-08',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => null,
                'tanggal_surat_perintah' => null,
                'tahap_sprindik_status' => null,
                'deskripsi' => 'Dugaan obat tradisional ilegal mengandung BKO.',
            ],
            [
                'id' => 5,
                'nomor_laporan_kejadian' => 'LK/005/IX/2026',
                'tanggal_laporan_kejadian' => '2026-09-10',
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => now(),
                'nomor_surat_perintah' => 'SP/003/IX/2026',
                'tanggal_surat_perintah' => '2026-09-11',
                'tahap_sprindik_status' => 'final',
                'sprindik_locked_at' => now(),
                'deskripsi' => 'Dugaan suplemen kesehatan palsu secara daring.',
            ],
        ];

        foreach ($samplePerkara as $item) {
            \App\Models\Perkara::create($item);
        }

        $perkara2 = \App\Models\Perkara::find(2);
        if ($perkara2) {
            $tersangka = \App\Models\Tersangka::create([
                'perkara_id' => $perkara2->id,
                'nama' => 'Budi Santoso',
                'nik' => '6371012345670001',
            ]);

            $berkas = \App\Models\BerkasPerkara::create([
                'id' => 1,
                'perkara_id' => $perkara2->id,
                'tersangka_id' => $tersangka->id,
                'nomor_berkas' => 'BP/001/IX/2026/BPOM',
            ]);

            \App\Models\TahapanPerkara::create([
                'id' => 1,
                'berkas_perkara_id' => $berkas->id,
                'jenis_tahap' => \App\Enums\JenisTahap::SPDP->value,
                'nomor_surat' => 'SPDP/001/IX/2026',
                'tanggal_surat' => '2026-09-04',
                'status' => 'final',
                'finalized_at' => now(),
            ]);

            \App\Models\TahapanPerkara::create([
                'id' => 2,
                'berkas_perkara_id' => $berkas->id,
                'jenis_tahap' => \App\Enums\JenisTahap::TAHAP_1->value,
                'nomor_surat' => 'BAP/001/IX/2026',
                'tanggal_surat' => '2026-09-10',
                'status' => 'final',
                'finalized_at' => now(),
            ]);

            \App\Models\TahapanPerkara::create([
                'id' => 3,
                'berkas_perkara_id' => $berkas->id,
                'jenis_tahap' => \App\Enums\JenisTahap::P21->value,
                'nomor_surat' => 'P-21/001/IX/2026',
                'tanggal_surat' => '2026-09-20',
                'status' => 'final',
                'finalized_at' => now(),
            ]);

            \App\Models\TahapanPerkara::create([
                'id' => 4,
                'berkas_perkara_id' => $berkas->id,
                'jenis_tahap' => \App\Enums\JenisTahap::TAHAP_2->value,
                'nomor_surat' => 'BA-T2/001/IX/2026',
                'tanggal_surat' => '2026-09-25',
                'status' => 'final',
                'finalized_at' => now(),
            ]);

            $tersangka2 = \App\Models\Tersangka::create([
                'perkara_id' => $perkara2->id,
                'nama' => 'Siti Aminah',
                'nik' => '6371012345670002',
            ]);

            $berkas2 = \App\Models\BerkasPerkara::create([
                'id' => 2,
                'perkara_id' => $perkara2->id,
                'tersangka_id' => $tersangka2->id,
                'nomor_berkas' => 'BP/002/IX/2026/BPOM',
            ]);

            \App\Models\TahapanPerkara::create([
                'id' => 5,
                'berkas_perkara_id' => $berkas2->id,
                'jenis_tahap' => \App\Enums\JenisTahap::SPDP->value,
                'nomor_surat' => 'SPDP/002/IX/2026',
                'tanggal_surat' => '2026-09-05',
                'status' => 'final',
                'finalized_at' => now(),
            ]);

            $tersangka3 = \App\Models\Tersangka::create([
                'perkara_id' => $perkara2->id,
                'nama' => 'Ahmad Fauzi',
                'nik' => '6371012345670003',
            ]);

            $berkas3 = \App\Models\BerkasPerkara::create([
                'id' => 3,
                'perkara_id' => $perkara2->id,
                'tersangka_id' => $tersangka3->id,
                'nomor_berkas' => 'BP/003/IX/2026/BPOM',
            ]);

            \App\Models\TahapanPerkara::create([
                'id' => 6,
                'berkas_perkara_id' => $berkas3->id,
                'jenis_tahap' => \App\Enums\JenisTahap::SPDP->value,
                'nomor_surat' => 'SPDP/003/IX/2026',
                'tanggal_surat' => '2026-09-06',
                'status' => 'final',
                'finalized_at' => now(),
            ]);

            \App\Models\TahapanPerkara::create([
                'id' => 7,
                'berkas_perkara_id' => $berkas3->id,
                'jenis_tahap' => \App\Enums\JenisTahap::TAHAP_1->value,
                'nomor_surat' => 'BAP/002/IX/2026',
                'tanggal_surat' => '2026-09-12',
                'status' => 'final',
                'finalized_at' => now(),
            ]);
        }
    }
}
