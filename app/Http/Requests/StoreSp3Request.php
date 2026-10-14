<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSp3Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-cases') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_surat' => ['required', 'string', 'max:150'],
            'tanggal_sp3' => ['required', 'date'],
            'alasan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'dokumen' => ['required', 'file', 'mimes:pdf', 'max:20480'], // PDF murni maks 20MB (D-007)
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
            'alasan.required' => 'Alasan penghentian penyidikan wajib dipilih/diisi.',
            'dokumen.required' => 'Dokumen fisik SP3 wajib diunggah.',
            'dokumen.mimes' => 'Dokumen SP3 wajib berupa file format PDF.',
            'dokumen.max' => 'Ukuran dokumen maksimal 20 MB.',
        ];
    }
}
