<?php

namespace App\Services;

use App\Models\Alamat;

class AlamatService
{
    public function __construct(
        protected WilayahService $wilayahService
    ) {}

    /**
     * Resolusi payload alamat (TKP / Tersangka) dengan auto-fill nama wilayah via WilayahService.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, ?string>
     */
    public function resolveAlamatPayload(array $data, ?Alamat $existing = null, string $prefix = ''): array
    {
        $provKode = (string) ($data[$prefix.'provinsi_kode'] ?? $existing?->provinsi_kode ?? '');
        $kabKode = (string) ($data[$prefix.'kabupaten_kode'] ?? $existing?->kabupaten_kode ?? '');
        $kecKode = (string) ($data[$prefix.'kecamatan_kode'] ?? $existing?->kecamatan_kode ?? '');
        $desaKode = (string) ($data[$prefix.'desa_kode'] ?? $existing?->desa_kode ?? '');

        $provNama = (string) ($data[$prefix.'provinsi_nama'] ?? '');
        if ($provNama === '' && $provKode !== '') {
            foreach ($this->wilayahService->getProvinsi() as $item) {
                if ($item['code'] === $provKode) {
                    $provNama = $item['name'];
                    break;
                }
            }
        }

        $kabNama = (string) ($data[$prefix.'kabupaten_nama'] ?? '');
        if ($kabNama === '' && $provKode !== '' && $kabKode !== '') {
            foreach ($this->wilayahService->getKabupaten($provKode) as $item) {
                if ($item['code'] === $kabKode) {
                    $kabNama = $item['name'];
                    break;
                }
            }
        }

        $kecNama = (string) ($data[$prefix.'kecamatan_nama'] ?? '');
        if ($kecNama === '' && $kabKode !== '' && $kecKode !== '') {
            foreach ($this->wilayahService->getKecamatan($kabKode) as $item) {
                if ($item['code'] === $kecKode) {
                    $kecNama = $item['name'];
                    break;
                }
            }
        }

        $desaNama = (string) ($data[$prefix.'desa_nama'] ?? '');
        if ($desaNama === '' && $kecKode !== '' && $desaKode !== '') {
            foreach ($this->wilayahService->getDesa($kecKode) as $item) {
                if ($item['code'] === $desaKode) {
                    $desaNama = $item['name'];
                    break;
                }
            }
        }

        return [
            'nama' => $data[$prefix.'nama'] ?? $existing?->nama,
            'provinsi_kode' => $provKode ?: null,
            'provinsi_nama' => $provNama ?: ($existing?->provinsi_nama ?? null),
            'kabupaten_kode' => $kabKode ?: null,
            'kabupaten_nama' => $kabNama ?: ($existing?->kabupaten_nama ?? null),
            'kecamatan_kode' => $kecKode ?: null,
            'kecamatan_nama' => $kecNama ?: ($existing?->kecamatan_nama ?? null),
            'desa_kode' => $desaKode ?: null,
            'desa_nama' => $desaNama ?: ($existing?->desa_nama ?? null),
            'rt' => $data[$prefix.'rt'] ?? $existing?->rt,
            'rw' => $data[$prefix.'rw'] ?? $existing?->rw,
            'alamat_detail' => $data[$prefix.'alamat_detail'] ?? $existing?->alamat_detail,
        ];
    }
}
