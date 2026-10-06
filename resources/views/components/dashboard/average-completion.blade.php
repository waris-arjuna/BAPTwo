@php
    $averageProgress = 64;
    $selesai = 1;
    $tidakSelesai = 3;
@endphp

<div class="h-full rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
            Rata-rata Penyelesaian Perkara
        </h2>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Ringkasan perkembangan berkas pada tahun berjalan.
        </p>
    </div>

    <div class="space-y-6 p-5">

        {{-- Average Progress --}}
        <div>
            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Rata-rata Progress Penyidikan
                    </p>

                    <p class="mt-1 text-3xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $averageProgress }}%
                    </p>
                </div>

                <div class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-brand-100 text-sm font-semibold text-brand-500 dark:border-brand-500/20">
                    {{ $averageProgress }}%
                </div>

            </div>

            <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                <div
                    class="h-full rounded-full bg-brand-500"
                    style="width: {{ $averageProgress }}%;"
                ></div>
            </div>
        </div>

        {{-- Status --}}
        <div class="grid grid-cols-2 gap-4">

            <div class="rounded-xl border border-gray-200 px-4 py-4 dark:border-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Berkas Selesai
                </p>

                <p class="mt-2 text-2xl font-semibold text-success-600 dark:text-success-400">
                    {{ $selesai }}
                </p>

                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                    Status: Selesai
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 px-4 py-4 dark:border-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Berkas Tidak Selesai
                </p>

                <p class="mt-2 text-2xl font-semibold text-gray-700 dark:text-gray-300">
                    {{ $tidakSelesai }}
                </p>

                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                    Status: Tidak Selesai
                </p>
            </div>

        </div>

    </div>
</div>