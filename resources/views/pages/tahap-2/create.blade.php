@extends('layouts.app')

@section('content')
    @include('pages.tahapan._select-berkas', [
        'stageName' => 'Tahap 2',
        'stageIndexRoute' => 'tahap-2.index',
        'description' => 'Pilih Berkas Perkara yang telah menyelesaikan P-21 untuk melakukan Pelimpahan Tersangka & Barang Bukti (Tahap II).',
        'emptyMessage' => 'Tidak ada Berkas Perkara yang siap diproses ke Tahap 2.',
        'emptyDescription' => 'Tahapan Tahap 2 hanya dapat dicatat setelah tahapan P-21 selesai difinalkan.',
        'prevStageIndexRoute' => 'p21.index',
        'prevStageName' => 'P-21',
        'badgeText' => 'P-21 Selesai',
        'jenis' => 'tahap_2',
        'availableBerkas' => $availableBerkas,
    ])
@endsection
