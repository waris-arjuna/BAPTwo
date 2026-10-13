<?php

namespace App\Http\Requests;

use App\Enums\JenisTahap;
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
            // SPDP dan Tahap 1 diizinkan diakses untuk melengkapi sub-tahap (P-16/P-17 atau P-18/P-19)
            if (in_array($tahap->jenis_tahap, [JenisTahap::SPDP, JenisTahap::TAHAP_1], true)) {
                return $this->user()?->can('edit-draft') ?? false;
            }

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
        /** @var TahapanPerkara|null $tahap */
        $tahap = $this->route('tahapan');
        $isFinal = $tahap && $tahap->isFinal();
        $isDraft = $this->input('action_type') === 'draft';

        return [
            'action_type' => ['nullable', 'in:draft,lanjut'],
            'nomor_surat' => [
                $isFinal ? 'nullable' : 'required',
                'string',
                'max:150',
            ],
            'tanggal_surat' => [
                ($isDraft || $isFinal) ? 'nullable' : 'required',
                'date',
            ],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],

            // Sub-tahap opsional P-16 (Penunjukan JPU pada SPDP)
            'p16_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p16_tanggal_surat' => ['nullable', 'date'],
            'p16_catatan' => ['nullable', 'string', 'max:2000'],
            'p16_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],

            // Sub-tahap opsional P-17P (Permintaan Perkembangan pada SPDP)
            'p17p_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p17p_tanggal_surat' => ['nullable', 'date'],
            'p17p_catatan' => ['nullable', 'string', 'max:2000'],
            'p17p_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],

            // Sub-tahap opsional P-18P (Hasil Penyidikan Belum Lengkap pada Tahap 1)
            'p18p_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p18p_tanggal_surat' => ['nullable', 'date'],
            'p18p_catatan' => ['nullable', 'string', 'max:2000'],
            'p18p_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],

            // Sub-tahap opsional P-19 (Pengembalian Berkas pada Tahap 1)
            'p19_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p19_tanggal_surat' => ['nullable', 'date'],
            'p19_catatan' => ['nullable', 'string', 'max:2000'],
            'p19_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
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
            'p16_dokumen.mimes' => 'Dokumen P-16 harus berupa file PDF.',
            'p16_dokumen.max' => 'Ukuran dokumen P-16 maksimal 20 MB.',
            'p17p_dokumen.mimes' => 'Dokumen P-17 harus berupa file PDF.',
            'p17p_dokumen.max' => 'Ukuran dokumen P-17 maksimal 20 MB.',
            'p18p_dokumen.mimes' => 'Dokumen P-18 harus berupa file PDF.',
            'p18p_dokumen.max' => 'Ukuran dokumen P-18 maksimal 20 MB.',
            'p19_dokumen.mimes' => 'Dokumen P-19 harus berupa file PDF.',
            'p19_dokumen.max' => 'Ukuran dokumen P-19 maksimal 20 MB.',
        ];
    }
}
