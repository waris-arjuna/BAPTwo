<?php

namespace App\Http\Requests;

use App\Models\Perkara;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLaporanKejadianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Perkara|null $perkara */
        $perkara = $this->route('perkara');
        $isLocked = $perkara instanceof Perkara ? $perkara->isLaporanLocked() : false;

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
            'nomor_laporan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('perkara', 'nomor_laporan_kejadian')->ignore($perkaraId),
            ],
            'tanggal_laporan' => [
                $isDraft ? 'nullable' : 'required',
                'date',
            ],
            'dokumen_laporan' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_laporan.required' => 'Nomor laporan kejadian wajib diisi.',
            'nomor_laporan.unique' => 'Nomor laporan kejadian sudah digunakan.',
            'tanggal_laporan.required' => 'Tanggal laporan kejadian wajib diisi untuk melanjutkan tahap.',
            'tanggal_laporan.date' => 'Format tanggal laporan kejadian tidak valid.',
            'dokumen_laporan.mimes' => 'Dokumen laporan harus berformat PDF.',
            'dokumen_laporan.max' => 'Ukuran dokumen laporan maksimal 5 MB.',
        ];
    }
}
