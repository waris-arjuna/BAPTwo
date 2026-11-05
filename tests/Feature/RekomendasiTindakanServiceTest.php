<?php

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Sp3;
use App\Models\TahapanPerkara;
use App\Models\Tersangka;
use App\Services\RekomendasiTindakanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-11-05 08:00:00');
});

function buatBerkas(array $atribut = []): BerkasPerkara
{
    static $counter = 1;
    $idx = $counter++;

    $perkaraData = [
        'nomor_laporan' => 'LK/TEST/'.$idx.'/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_sprindik' => 'SP/TEST/'.$idx.'/2026',
        'tanggal_sprindik' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => $atribut['tahap_perkara_status'] ?? 'final',
        'perkara_locked_at' => ($atribut['tahap_perkara_status'] ?? 'final') === 'final' ? now() : null,
    ];
    unset($atribut['tahap_perkara_status']);

    $perkara = $atribut['perkara'] ?? Perkara::create($perkaraData);
    unset($atribut['perkara']);

    $tersangka = Tersangka::create([
        'perkara_id' => $perkara->id,
        'nama' => 'Tersangka '.$idx,
    ]);

    $berkasDefaults = [
        'perkara_id' => $perkara->id,
        'tersangka_id' => $tersangka->id,
        'nomor_berkas' => 'BP/TEST/'.$idx.'/2026',
        'progress_persen' => 15,
        'is_dpo' => false,
        'status_formal' => null,
    ];

    return BerkasPerkara::create(array_merge($berkasDefaults, $atribut));
}

function svc(BerkasPerkara $b): array
{
    return app(RekomendasiTindakanService::class)->untukBerkas($b);
}

test('progress 15 dengan sisa 10 hari menghasilkan perintah tahap I bertone info', function () {
    $r = app(RekomendasiTindakanService::class)->untukBerkas(buatBerkas(['sla_deadline' => '2026-11-15', 'progress_persen' => 15]));
    expect($r['label'])->toBe('Serahkan berkas TAHAP I')
        ->and($r['hari_label'])->toBe('10 hari lagi')
        ->and($r['tone'])->toBe('info')
        ->and($r['tooltip'])->toContain('15 Nov 2026');
});

test('sisa 0 hari tampil Hari ini bertone warning', function () {
    $r = svc(buatBerkas(['sla_deadline' => '2026-11-05', 'progress_persen' => 15]));
    expect($r)->toMatchArray([
        'label' => 'Serahkan berkas TAHAP I',
        'hari_label' => 'Hari ini',
        'tone' => 'warning',
    ]);
});

test('lewat tenggat tampil Terlambat bertone error', function () {
    $r = svc(buatBerkas(['sla_deadline' => '2026-11-02', 'progress_persen' => 55]));
    expect($r)->toMatchArray([
        'label' => 'Pantau P-21 dari Kejaksaan',
        'hari_label' => 'Terlambat 3 hari',
        'tone' => 'error',
    ]);
});

test('progress 85 menghasilkan perintah tahap II', function () {
    $r = svc(buatBerkas(['sla_deadline' => '2026-11-15', 'progress_persen' => 85]));
    expect($r['label'])->toBe('Serahkan tersangka dan barang bukti (TAHAP II)')
        ->and($r['hari_label'])->toBe('10 hari lagi')
        ->and($r['tone'])->toBe('info');
});

test('draft tahapan didahulukan dari perintah progress', function () {
    $berkas = buatBerkas(['progress_persen' => 15, 'sla_deadline' => '2026-11-15']);
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::TAHAP_1->value,
        'status' => 'draft',
    ]);

    $r = svc($berkas);
    expect($r['label'])->toBe('Finalkan draft TAHAP I')
        ->and($r['hari_label'])->toBe('10 hari lagi')
        ->and($r['tone'])->toBe('info');
});

test('dpo tetap kuning walau lewat tenggat', function () {
    $r = svc(buatBerkas(['is_dpo' => true, 'sla_deadline' => '2026-11-02']));
    expect($r['label'])->toBe('Cari tersangka, cabut DPO jika ditemukan')
        ->and($r['tone'])->toBe('warning')
        ->and($r['hari_label'])->toBe('Terlambat 3 hari');
});

test('sp3 menghasilkan pil netral tanpa hari', function () {
    $berkas = buatBerkas(['status_formal' => 'tidak_selesai']);
    Sp3::create([
        'berkas_perkara_id' => $berkas->id,
        'nomor_surat' => 'SP3/01/2026',
        'tanggal_sp3' => '2026-11-01',
        'keterangan' => 'Penghentian penyidikan',
    ]);

    $r = svc($berkas);
    expect($r['label'])->toBe('Penyidikan dihentikan (SP3)')
        ->and($r['tone'])->toBe('netral')
        ->and($r['hari_label'])->toBeNull()
        ->and($r['icon'])->toBe('fi fi-rr-ban');
});

test('tidak selesai tanpa sp3 menghasilkan lewat batas tahun', function () {
    $r = svc(buatBerkas(['status_formal' => 'tidak_selesai']));
    expect($r['label'])->toBe('Lewat batas tahun')
        ->and($r['tone'])->toBe('netral')
        ->and($r['hari_label'])->toBeNull()
        ->and($r['icon'])->toBe('fi fi-rr-calendar-xmark');
});

test('selesai tanpa putusan meminta catat keputusan pengadilan', function () {
    $r = svc(buatBerkas(['status_formal' => 'selesai']));
    expect($r['label'])->toBe('Catat Keputusan Pengadilan')
        ->and($r['tone'])->toBe('netral')
        ->and($r['hari_label'])->toBeNull()
        ->and($r['icon'])->toBe('fi fi-rr-gavel');
});

test('selesai dengan putusan final tidak ada tindakan', function () {
    $berkas = buatBerkas(['status_formal' => 'selesai']);
    TahapanPerkara::create([
        'berkas_perkara_id' => $berkas->id,
        'jenis_tahap' => JenisTahap::KEPUTUSAN_PENGADILAN->value,
        'status' => 'final',
        'tanggal_surat' => '2026-11-01',
    ]);

    $r = svc($berkas);
    expect($r['label'])->toBe('Tidak ada tindakan')
        ->and($r['tone'])->toBe('netral')
        ->and($r['hari_label'])->toBeNull()
        ->and($r['icon'])->toBe('fi fi-rr-check-circle');
});

test('perkara draft meminta finalkan data perkara', function () {
    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/DRAFT/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_sprindik' => 'SP/DRAFT/2026',
        'tanggal_sprindik' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'draft',
        'perkara_locked_at' => null,
    ]);

    $r = app(RekomendasiTindakanService::class)->untukPerkara($perkara);
    expect($r['label'])->toBe('Finalkan data perkara')
        ->and($r['tone'])->toBe('netral')
        ->and($r['hari_label'])->toBeNull()
        ->and($r['icon'])->toBe('fi fi-rr-edit');
});

test('untukPerkara memilih berkas paling mendesak', function () {
    $perkara = Perkara::create([
        'nomor_laporan' => 'LK/MULTI/2026',
        'tanggal_laporan' => '2026-10-01',
        'tahap_laporan_status' => 'final',
        'nomor_sprindik' => 'SP/MULTI/2026',
        'tanggal_sprindik' => '2026-10-02',
        'tahap_sprindik_status' => 'final',
        'tahap_perkara_status' => 'final',
        'perkara_locked_at' => now(),
    ]);

    buatBerkas(['perkara' => $perkara, 'sla_deadline' => '2026-11-30']);
    buatBerkas(['perkara' => $perkara, 'sla_deadline' => '2026-11-07']);

    $r = app(RekomendasiTindakanService::class)->untukPerkara($perkara);
    expect($r['hari_label'])->toBe('2 hari lagi');
});
