<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanKejadianRequest extends FormRequest
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
            'nomor_laporan' => ['required', 'string', 'max:100', 'unique:perkara,nomor_laporan_kejadian'],
            'tanggal_laporan' => ['required', 'date'],
            'dokumen_laporan' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
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
            'tanggal_laporan.required' => 'Tanggal laporan kejadian wajib diisi.',
            'tanggal_laporan.date' => 'Format tanggal laporan kejadian tidak valid.',
            'dokumen_laporan.mimes' => 'Dokumen laporan harus berformat PDF.',
            'dokumen_laporan.max' => 'Ukuran dokumen laporan maksimal 20 MB.',
        ];
    }
}
