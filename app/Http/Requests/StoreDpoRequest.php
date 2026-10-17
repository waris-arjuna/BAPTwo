<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDpoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create-cases') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_surat' => ['required', 'string', 'max:150'],
            'tanggal_penetapan' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_surat.required' => 'Nomor surat penetapan DPO wajib diisi.',
            'nomor_surat.max' => 'Nomor surat penetapan DPO tidak boleh lebih dari :max karakter.',
            'tanggal_penetapan.required' => 'Tanggal penetapan DPO wajib diisi.',
            'tanggal_penetapan.date' => 'Format tanggal penetapan DPO tidak valid.',
            'dokumen.mimes' => 'File dokumen pendukung DPO wajib berformat PDF.',
            'dokumen.max' => 'Ukuran file dokumen pendukung DPO maksimal 5 MB.',
        ];
    }
}
