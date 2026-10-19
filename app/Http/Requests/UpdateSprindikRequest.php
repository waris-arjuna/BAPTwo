<?php

namespace App\Http\Requests;

use App\Models\Perkara;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSprindikRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Perkara|null $perkara */
        $perkara = $this->route('perkara');
        $isLocked = $perkara instanceof Perkara ? $perkara->isSprindikLocked() : false;

        return $this->user()?->can('edit-draft', $isLocked) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Perkara|string|int|null $perkara */
        $perkara = $this->route('perkara');
        $perkaraId = $perkara instanceof Perkara ? $perkara->id : $perkara;
        $isDraft = $this->input('action_type') === 'draft';

        return [
            'action_type' => ['nullable', 'in:draft,lanjut'],
            'nomor_sprindik' => [
                'required',
                'string',
                'max:100',
                Rule::unique('perkara', 'nomor_surat_perintah')->ignore($perkaraId),
            ],
            'tanggal_sprindik' => [
                $isDraft ? 'nullable' : 'required',
                'date',
            ],
            'dokumen_sprindik' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_sprindik.required' => 'Nomor sprindik wajib diisi.',
            'nomor_sprindik.unique' => 'Nomor sprindik sudah digunakan.',
            'tanggal_sprindik.required' => 'Tanggal sprindik wajib diisi untuk melanjutkan tahap.',
            'tanggal_sprindik.date' => 'Format tanggal sprindik tidak valid.',
            'dokumen_sprindik.mimes' => 'Dokumen sprindik harus berformat PDF.',
            'dokumen_sprindik.max' => 'Ukuran dokumen sprindik maksimal 5 MB.',
        ];
    }
}
