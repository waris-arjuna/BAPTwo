<?php

namespace App\Http\Requests;

use App\Enums\JenisTahap;
use App\Models\TahapanPerkara;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var TahapanPerkara|null $tahap */
        $tahap = $this->route('tahapan');
        if (is_numeric($tahap)) {
            $tahap = TahapanPerkara::find($tahap);
        }
        $isFinal = $tahap && $tahap->isFinal();
        $isDraft = $this->input('action_type') === 'draft';
        $isOptionalStage = $tahap && in_array($tahap->jenis_tahap?->value, ['p16', 'p17p', 'p18p', 'p19'], true);
        $hasExistingDocument = $tahap && $tahap->dokumen()->exists();
        $isDocumentRequired = ! $isDraft && ! $isFinal && ! $isOptionalStage && ! $hasExistingDocument;
        $isKeputusanPengadilan = $tahap && $tahap->jenis_tahap === JenisTahap::KEPUTUSAN_PENGADILAN;
        $dokumenInput = $this->file('dokumen');
        $isMultiDokumen = is_array($dokumenInput);

        $rules = [
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
            'deleted_dokumen_ids' => ['nullable', 'array'],
            'deleted_dokumen_ids.*' => ['integer'],

            // Sub-tahap opsional P-16 (Penunjukan JPU pada SPDP)
            'p16_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p16_tanggal_surat' => ['nullable', 'date'],
            'p16_catatan' => ['nullable', 'string', 'max:2000'],
            'p16_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Sub-tahap opsional P-17P (Permintaan Perkembangan pada SPDP)
            'p17p_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p17p_tanggal_surat' => ['nullable', 'date'],
            'p17p_catatan' => ['nullable', 'string', 'max:2000'],
            'p17p_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Sub-tahap opsional P-18P (Hasil Penyidikan Belum Lengkap pada Tahap 1)
            'p18p_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p18p_tanggal_surat' => ['nullable', 'date'],
            'p18p_catatan' => ['nullable', 'string', 'max:2000'],
            'p18p_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Sub-tahap opsional P-19 (Pengembalian Berkas pada Tahap 1)
            'p19_nomor_surat' => ['nullable', 'string', 'max:150'],
            'p19_tanggal_surat' => ['nullable', 'date'],
            'p19_catatan' => ['nullable', 'string', 'max:2000'],
            'p19_dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Keputusan Pengadilan (Poin 8, ref: D-053)
            'pengadilan_instansi_id' => ['nullable', 'integer', 'exists:instansi_hukum,id'],
            'amar_putusan' => ['nullable', 'string', 'max:5000'],
        ];

        if ($isKeputusanPengadilan) {
            if ($isMultiDokumen) {
                $rules['dokumen'] = ['nullable', 'array'];
                $rules['dokumen.*'] = ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'];
            } else {
                $rules['dokumen'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'];
            }
        } else {
            $rules['dokumen'] = [$isDocumentRequired ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:5120'];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var TahapanPerkara|null $tahap */
            $tahap = $this->route('tahapan');
            if (is_numeric($tahap)) {
                $tahap = TahapanPerkara::find($tahap);
            }
            $isKeputusanPengadilan = $tahap && $tahap->jenis_tahap === JenisTahap::KEPUTUSAN_PENGADILAN;

            if (! $isKeputusanPengadilan) {
                return;
            }

            $deletedIds = (array) $this->input('deleted_dokumen_ids', []);
            $existingDocs = $tahap ? $tahap->dokumen()->whereNotIn('id', $deletedIds)->get() : collect();
            $existingSize = $existingDocs->sum('file_size');
            $existingCount = $existingDocs->count();

            $dokumenInput = $this->file('dokumen');
            $newFiles = is_array($dokumenInput) ? $dokumenInput : ($dokumenInput ? [$dokumenInput] : []);

            $newFilesSize = 0;
            foreach ($newFiles as $file) {
                if ($file instanceof UploadedFile) {
                    $newFilesSize += $file->getSize() ?: 0;
                }
            }
            $newFilesCount = count($newFiles);

            if (($existingSize + $newFilesSize) > 5 * 1024 * 1024) {
                $validator->errors()->add('dokumen', 'Total ukuran gabungan seluruh dokumen dan foto keputusan pengadilan tidak boleh melebihi 5 MB.');
            }

            $isLanjut = ($this->input('action_type') ?? 'lanjut') === 'lanjut';
            if ($isLanjut && ($existingCount + $newFilesCount) < 1) {
                $validator->errors()->add('dokumen', 'Wajib melampirkan minimal 1 dokumen salinan putusan (PDF atau Foto) untuk melanjutkan tahapan.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        /** @var TahapanPerkara|null $tahap */
        $tahap = $this->route('tahapan');
        if (is_numeric($tahap)) {
            $tahap = TahapanPerkara::find($tahap);
        }
        $isKeputusanPengadilan = $tahap && $tahap->jenis_tahap === JenisTahap::KEPUTUSAN_PENGADILAN;

        return [
            'nomor_surat.required' => 'Nomor surat / dokumen wajib diisi.',
            'nomor_surat.max' => 'Nomor surat tidak boleh lebih dari 150 karakter.',
            'tanggal_surat.required' => 'Tanggal surat / dokumen wajib diisi untuk melanjutkan tahapan.',
            'tanggal_surat.date' => 'Format tanggal surat tidak valid.',
            'dokumen.required' => 'Dokumen wajib diunggah saat melanjutkan tahapan.',
            'dokumen.mimes' => $isKeputusanPengadilan
                ? 'Dokumen keputusan pengadilan harus berformat PDF atau Gambar (JPG, JPEG, PNG, WEBP).'
                : 'Dokumen tahapan harus berupa file PDF.',
            'dokumen.max' => $isKeputusanPengadilan
                ? 'Ukuran file dokumen keputusan pengadilan tidak boleh melebihi 5 MB.'
                : 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
            'dokumen.*.mimes' => 'Setiap berkas keputusan pengadilan harus berformat PDF atau Gambar (JPG, JPEG, PNG, WEBP).',
            'dokumen.*.max' => 'Ukuran setiap berkas dokumen keputusan pengadilan tidak boleh melebihi 5 MB.',
            'p16_dokumen.mimes' => 'Dokumen P-16 harus berupa file PDF.',
            'p16_dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
            'p17p_dokumen.mimes' => 'Dokumen P-17 harus berupa file PDF.',
            'p17p_dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
            'p18p_dokumen.mimes' => 'Dokumen P-18 harus berupa file PDF.',
            'p18p_dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
            'p19_dokumen.mimes' => 'Dokumen P-19 harus berupa file PDF.',
            'p19_dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
        ];
    }
}
