<?php

namespace App\Http\Requests;

use App\Enums\JenisTahap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTahapanRequest extends FormRequest
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
            'jenis_tahap' => [
                'required',
                Rule::enum(JenisTahap::class),
                function ($attribute, $value, $fail) {
                    $berkas = $this->route('berkas');
                    if (is_numeric($berkas)) {
                        $berkas = \App\Models\BerkasPerkara::find($berkas);
                    }
                    if ($berkas instanceof \App\Models\BerkasPerkara) {
                        $enum = $value instanceof JenisTahap ? $value : JenisTahap::tryFrom((string) $value);
                        if ($enum && $berkas->hasTahap($enum)) {
                            $fail("Tahapan {$enum->label()} sudah pernah dicatat pada Berkas Perkara ini. Data dari tahap sebelumnya yang sudah dipakai tidak dapat dipilih lagi.");

                            return;
                        }
                        $available = app(\App\Services\TahapanService::class)->getNextAvailableStages($berkas);
                        if ($enum && ! in_array($enum, $available, true)) {
                            $fail("Tahapan {$enum->label()} tidak valid untuk urutan Berkas Perkara saat ini.");
                        }
                    }
                },
            ],
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
            'jenis_tahap.required' => 'Jenis tahapan wajib ditentukan.',
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
