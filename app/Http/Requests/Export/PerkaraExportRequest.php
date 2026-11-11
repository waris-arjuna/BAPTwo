<?php

declare(strict_types=1);

namespace App\Http\Requests\Export;

use Illuminate\Foundation\Http\FormRequest;

class PerkaraExportRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk melakukan ekspor excel.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('export-excel') ?? false;
    }

    /**
     * Aturan validasi parameter filter ekspor excel.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'mode' => ['nullable', 'string', 'in:semua,tahun,bulan,rentang'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'check' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan validasi kustom berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mode.in' => 'Pilihan periode ekspor tidak valid.',
            'tahun.integer' => 'Tahun harus berupa bilangan bulat.',
            'bulan.between' => 'Bulan harus di antara 1 dan 12.',
            'tanggal_selesai.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
        ];
    }
}
