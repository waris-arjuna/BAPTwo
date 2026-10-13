<?php

namespace App\Http\Requests;

use App\Models\TahapanPerkara;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTahapanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var TahapanPerkara|null $tahap */
        $tahap = $this->route('tahapan');

        if ($tahap && $tahap->isFinal()) {
            return false;
        }

        return $this->user()?->can('edit-draft') ?? false;
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
            'nomor_surat' => [
                'required',
                'string',
                'max:150',
            ],
            'tanggal_surat' => [
                $isDraft ? 'nullable' : 'required',
                'date',
            ],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_surat.required' => 'Nomor surat / dokumen wajib diisi.',
            'nomor_surat.max' => 'Nomor surat tidak boleh lebih dari 150 karakter.',
            'tanggal_surat.required' => 'Tanggal surat / dokumen wajib diisi untuk melanjutkan tahapan.',
            'tanggal_surat.date' => 'Format tanggal surat tidak valid.',
            'dokumen.mimes' => 'Dokumen tahapan harus berupa file PDF.',
            'dokumen.max' => 'Ukuran dokumen tahapan maksimal 20 MB.',
        ];
    }
}
