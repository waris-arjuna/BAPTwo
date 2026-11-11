<?php

declare(strict_types=1);

namespace App\Http\Requests\Tahapan;

use Illuminate\Foundation\Http\FormRequest;

class SaveSubTahapRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan menyimpan sub-tahap.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create-cases') ?? false;
    }

    /**
     * Aturan validasi penyimpanan sub-tahap perkara.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'sub_tahap_group' => ['required', 'in:spdp,tahap_1'],

            'p16_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p16_tanggal_surat' => ['nullable', 'date'],
            'p16_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'p16_catatan' => ['nullable', 'string', 'max:1000'],

            'p17p_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p17p_tanggal_surat' => ['nullable', 'date'],
            'p17p_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'p17p_catatan' => ['nullable', 'string', 'max:1000'],

            'p18p_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p18p_tanggal_surat' => ['nullable', 'date'],
            'p18p_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'p18p_catatan' => ['nullable', 'string', 'max:1000'],

            'p19_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p19_tanggal_surat' => ['nullable', 'date'],
            'p19_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'p19_catatan' => ['nullable', 'string', 'max:1000'],
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
            'sub_tahap_group.required' => 'Grup sub-tahap wajib ditentukan.',
            'sub_tahap_group.in' => 'Grup sub-tahap tidak valid.',
            'p16_dokumen.mimes' => 'Format file dokumen P-16 harus berupa PDF.',
            'p16_dokumen.max' => 'Ukuran file dokumen P-16 tidak boleh melebihi 5 MB.',
            'p17p_dokumen.mimes' => 'Format file dokumen P-17P harus berupa PDF.',
            'p17p_dokumen.max' => 'Ukuran file dokumen P-17P tidak boleh melebihi 5 MB.',
            'p18p_dokumen.mimes' => 'Format file dokumen P-18P harus berupa PDF.',
            'p18p_dokumen.max' => 'Ukuran file dokumen P-18P tidak boleh melebihi 5 MB.',
            'p19_dokumen.mimes' => 'Format file dokumen P-19 harus berupa PDF.',
            'p19_dokumen.max' => 'Ukuran file dokumen P-19 tidak boleh melebihi 5 MB.',
        ];
    }
}
