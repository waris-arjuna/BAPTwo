@extends('layouts.app')

@section('content')
    @include('pages.tahapan._select-berkas', [
        'stageName' => 'Putusan Perkara',
        'stageIndexRoute' => 'putusan-perkara.index',
        'description' => 'Pilih Berkas Perkara yang telah menyelesaikan Tahap 2 untuk mencatat Keputusan Pengadilan (Amar Putusan & Vonis).',
        'emptyMessage' => 'Tidak ada Berkas Perkara yang siap dicatatkan Putusan.',
        'emptyDescription' => 'Putusan Perkara hanya dapat dicatat setelah Tahap 2 (Pelimpahan Tersangka & Barang Bukti) selesai difinalkan.',
        'prevStageIndexRoute' => 'tahap-2.index',
        'prevStageName' => 'Tahap 2',
        'badgeText' => 'Tahap 2 Selesai',
        'jenis' => 'keputusan_pengadilan',
        'availableBerkas' => $availableBerkas,
    ])
@endsection
