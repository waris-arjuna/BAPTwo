<?php

namespace App\Http\Controllers;

use App\Services\WilayahService;
use Illuminate\Http\JsonResponse;

class WilayahController extends Controller
{
    public function __construct(
        private readonly WilayahService $wilayahService
    ) {}

    public function provinsi(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->wilayahService->getProvinsi(),
        ]);
    }

    public function kabupaten(string $provinsiKode): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->wilayahService->getKabupaten($provinsiKode),
        ]);
    }

    public function kecamatan(string $kabupatenKode): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->wilayahService->getKecamatan($kabupatenKode),
        ]);
    }

    public function desa(string $kecamatanKode): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->wilayahService->getDesa($kecamatanKode),
        ]);
    }
}
