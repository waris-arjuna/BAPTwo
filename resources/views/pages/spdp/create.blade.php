@extends('layouts.app')

@section('content')
    @include('pages.tahapan._select-berkas', [
        'stageName' => 'SPDP',
        'stageIndexRoute' => 'spdp.index',
        'description' => 'Pilih Berkas Perkara yang telah menyelesaikan Sprindik untuk melanjutkan ke Surat Pemberitahuan Dimulainya Penyidikan (SPDP).',
        'emptyMessage' => 'Tidak ada Berkas Perkara yang siap diproses ke SPDP.',
        'emptyDescription' => 'SPDP hanya dapat dicatat untuk berkas perkara yang Sprindik-nya telah difinalkan.',
        'prevStageIndexRoute' => 'sprindik.index',
        'prevStageName' => 'Sprindik',
        'badgeText' => 'Sprindik Selesai',
        'jenis' => 'spdp',
        'availableBerkas' => $availableBerkas,
    ])
@endsection