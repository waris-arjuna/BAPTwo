@extends('layouts.app')

@section('content')
    @include('pages.tahapan._select-berkas', [
        'stageName' => 'P-21',
        'stageIndexRoute' => 'p21.index',
        'description' => 'Pilih Berkas Perkara yang telah menyelesaikan Tahap 1 untuk mencatat Surat P-21 (Hasil Penyidikan Lengkap).',
        'emptyMessage' => 'Tidak ada Berkas Perkara yang siap dicatatkan P-21.',
        'emptyDescription' => 'Tahapan P-21 hanya dapat dicatat setelah Tahap 1 selesai difinalkan.',
        'prevStageIndexRoute' => 'tahap-1.index',
        'prevStageName' => 'Tahap 1',
        'badgeText' => 'Tahap 1 Selesai',
        'jenis' => 'p21',
        'availableBerkas' => $availableBerkas,
    ])
@endsection