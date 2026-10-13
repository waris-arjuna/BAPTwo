@php
    $perkaraList ??= app(\App\Services\MockStageService::class)->getProgressPerkara(request());
@endphp

<div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- Header --}}
    <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between dark:border-gray-800">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Progress Perkara
            </h2>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Monitoring perkembangan perkara tahun berjalan.
            </p>
        </div>
    </div>

    {{-- Toolbar Filter --}}
    <x-common.table-filter
        searchPlaceholder="Cari nomor perkara, tersangka, atau pasal..."
        :statusOptions="[
            'Dalam Proses' => 'Dalam Proses',
            'Selesai' => 'Selesai',
            'Tidak Selesai' => 'Tidak Selesai',
        ]"
    />

    <div id="table-body-partial">
        @include('components.dashboard._progress-partial', ['perkaraList' => $perkaraList])
    </div>

</div>