@php
    $sprindiks = [
        [
            'id' => 1,
            'nomor' => 'SP/001/IX/2026',
            'tanggal' => '03-09-2026',
            'laporan' => 'LK/001/IX/2026',
            'dokumen' => 'sprindik-001.pdf',
            'status' => 'Belum Menjadi Perkara',
        ],
        [
            'id' => 2,
            'nomor' => 'SP/002/IX/2026',
            'tanggal' => '05-09-2026',
            'laporan' => 'LK/002/IX/2026',
            'dokumen' => 'sprindik-002.pdf',
            'status' => 'Perkara Terbentuk',
        ],
        [
            'id' => 3,
            'nomor' => 'SP/003/IX/2026',
            'tanggal' => '08-09-2026',
            'laporan' => 'LK/004/IX/2026',
            'dokumen' => 'sprindik-003.pdf',
            'status' => 'Belum Menjadi Perkara',
        ],
    ];
@endphp


@extends('layouts.app')

@section ('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <a
                        href="{{ url('/') }}"
                        class="transition hover:text-brand-500"
                    >
                        Home
                    </a>

                    <span>/</span>

                    <span class="text-gray-700 dark:text-gray-300">
                        Sprindik
                    </span>
                </div>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Sprindik
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola data Surat Perintah Penyidikan sebagai dasar pelaksanaan penyidikan.
                </p>
            </div>

            <a
                href="{{ url('/sprindik/tambah') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>

                Tambah Sprindik
            </a>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Toolbar --}}
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                    {{-- Search --}}
                    <div class="relative w-full lg:max-w-md">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2">
                            <svg
                                class="h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 5.35 5.35a7.5 7.5 0 0 0 11.3 11.3Z"
                                />
                            </svg>
                        </span>

                        <input
                            type="text"
                            placeholder="Cari nomor Sprindik..."
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-10 pr-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500"
                        >
                    </div>

                    {{-- Filter --}}
                    <div class="flex flex-col gap-3 sm:flex-row">

                        <select
                            class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="">Semua Status</option>
                            <option value="belum">Belum Menjadi Perkara</option>
                            <option value="terbentuk">Perkara Terbentuk</option>
                        </select>

                        <input
                            type="date"
                            class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >

                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                        >
                            Filter
                        </button>
                    </div>
                </div>
            </div>

            {{-- Responsive table --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                No
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Nomor Sprindik
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Tanggal
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Laporan Kejadian
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Dokumen
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Status Proses
                            </th>

                            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($sprindiks as $index => $sprindik)

                            @php
                                $statusClass = match ($sprindik['status']) {
                                    'Belum Menjadi Perkara' =>
                                        'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',

                                    'Perkara Terbentuk' =>
                                        'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',

                                    default =>
                                        'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                                };
                            @endphp

                            <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-5 py-4">
                                    <a
                                        href="{{ url('/sprindik/' . $sprindik['id']) }}"
                                        class="text-sm font-medium text-gray-800 transition hover:text-brand-500 dark:text-white/90"
                                    >
                                        {{ $sprindik['nomor'] }}
                                    </a>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $sprindik['tanggal'] }}
                                </td>

                                <td class="px-5 py-4">
                                    <a
                                        href="{{ url('/laporan-kejadian/1') }}"
                                        class="text-sm text-gray-700 hover:text-brand-500 dark:text-gray-300"
                                    >
                                        {{ $sprindik['laporan'] }}
                                    </a>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <svg
                                            class="h-5 w-5 text-red-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M14 2v6h6"
                                            />
                                        </svg>

                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $sprindik['dokumen'] }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                                        {{ $sprindik['status'] }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        x-data="{ open: false }"
                                        class="relative flex justify-center"
                                    >
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                        >
                                            Aksi

                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="m6 9 6 6 6-6"
                                                />
                                            </svg>
                                        </button>

                                        <div
                                            x-show="open"
                                            x-cloak
                                            @click.outside="open = false"
                                            class="absolute right-0 top-11 z-20 w-44 rounded-lg border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                                        >
                                            <a
                                                href="{{ url('/sprindik/' . $sprindik['id']) }}"
                                                class="block rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                            >
                                                Lihat
                                            </a>

                                            @if ($sprindik['status'] === 'Belum Menjadi Perkara')

                                                <a
                                                    href="#"
                                                    class="block rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                                >
                                                    Edit
                                                </a>

                                                <a
                                                    href="#"
                                                    class="block rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                                >
                                                    Buat Perkara
                                                </a>

                                            @else

                                                <a
                                                    href="#"
                                                    class="block rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                                >
                                                    Lihat Perkara
                                                </a>

                                            @endif
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-col gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan
                    <span class="font-medium text-gray-700 dark:text-gray-300">1–3</span>
                    dari
                    <span class="font-medium text-gray-700 dark:text-gray-300">3</span>
                    Sprindik
                </p>

                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        disabled
                        class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-400 dark:border-gray-800"
                    >
                        Sebelumnya
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white"
                    >
                        1
                    </button>

                    <button
                        type="button"
                        disabled
                        class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-400 dark:border-gray-800"
                    >
                        Berikutnya
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection