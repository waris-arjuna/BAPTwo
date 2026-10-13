<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSprindikRequest extends FormRequest
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
        $isDraft = $this->input('action_type') === 'draft';

        return [
            'action_type' => ['nullable', 'in:draft,lanjut'],
            'perkara_id' => ['required', 'exists:perkara,id'],
            'nomor_sprindik' => [
                $isDraft ? 'nullable' : 'required',
                'string',
                'max:100',
                'unique:perkara,nomor_surat_perintah',
            ],
            'tanggal_sprindik' => [
                $isDraft ? 'nullable' : 'required',
                'date',
            ],
            'dokumen_sprindik' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'perkara_id.required' => 'Laporan kejadian wajib dipilih.',
            'perkara_id.exists' => 'Laporan kejadian yang dipilih tidak ditemukan.',
            'nomor_sprindik.required' => 'Nomor sprindik wajib diisi untuk melanjutkan tahap.',
            'nomor_sprindik.unique' => 'Nomor sprindik sudah digunakan.',
            'tanggal_sprindik.required' => 'Tanggal sprindik wajib diisi untuk melanjutkan tahap.',
            'tanggal_sprindik.date' => 'Format tanggal sprindik tidak valid.',
            'dokumen_sprindik.mimes' => 'Dokumen sprindik harus berformat PDF.',
            'dokumen_sprindik.max' => 'Ukuran dokumen sprindik maksimal 20 MB.',
        ];
    }
}
