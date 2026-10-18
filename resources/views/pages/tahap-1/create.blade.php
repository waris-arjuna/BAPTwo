@extends('layouts.app')

@section('content')
    @include('pages.tahapan._select-berkas', [
        'stageName' => 'Tahap 1',
        'stageIndexRoute' => 'tahap-1.index',
        'description' => 'Pilih Berkas Perkara yang telah menyelesaikan SPDP untuk melanjutkan ke Tahap 1 (Pelimpahan Berkas Perkara ke Kejaksaan).',
        'emptyMessage' => 'Tidak ada Berkas Perkara yang siap diproses ke Tahap 1.',
        'emptyDescription' => 'Tahap 1 hanya dapat dicatat setelah SPDP selesai difinalkan.',
        'prevStageIndexRoute' => 'spdp.index',
        'prevStageName' => 'SPDP',
        'badgeText' => 'SPDP Selesai',
        'jenis' => 'tahap_1',
        'availableBerkas' => $availableBerkas,
    ])
@endsection