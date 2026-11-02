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
            'penandatangan_id' => ['required', 'integer', 'exists:user,id'],
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
            'nomor_surat.max' => 'Nomor surat SP3 tidak boleh melebihi 150 karakter.',
            'tanggal_sp3.required' => 'Tanggal surat SP3 wajib diisi.',
            'keterangan.required' => 'Alasan dan catatan penghentian penyidikan wajib diisi.',
            'keterangan.max' => 'Alasan dan catatan penghentian penyidikan tidak boleh melebihi 2000 karakter.',
            'penandatangan_id.required' => 'Pejabat penandatangan SP3 wajib dipilih.',
            'penandatangan_id.integer' => 'Pejabat penandatangan yang dipilih tidak valid.',
            'penandatangan_id.exists' => 'Pejabat penandatangan yang dipilih tidak terdaftar di sistem.',
            'dokumen.required' => 'Dokumen fisik SP3 wajib diunggah.',
            'dokumen.mimes' => 'Dokumen SP3 wajib berupa file format PDF.',
            'dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
        ];
    }
}
