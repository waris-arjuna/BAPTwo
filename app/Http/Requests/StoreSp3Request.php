<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSp3Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-cases') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_surat' => ['required', 'string', 'max:150'],
            'tanggal_sp3' => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:2000'],
            'dokumen' => ['required', 'file', 'mimes:pdf', 'max:5120'], // PDF murni maks 5MB (Poin 18)
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_surat.required' => 'Nomor surat SP3 wajib diisi.',
            'tanggal_sp3.required' => 'Tanggal surat SP3 wajib diisi.',
            'keterangan.required' => 'Alasan dan catatan penghentian penyidikan wajib diisi.',
            'dokumen.required' => 'Dokumen fisik SP3 wajib diunggah.',
            'dokumen.mimes' => 'Dokumen SP3 wajib berupa file format PDF.',
            'dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
        ];
    }
}
