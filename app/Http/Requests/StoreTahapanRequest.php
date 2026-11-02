<?php

namespace App\Http\Requests;

use App\Enums\JenisTahap;
use App\Models\BerkasPerkara;
use App\Services\TahapanService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isDraft = $this->input('action_type') === 'draft';
        $jenisInput = (string) $this->input('jenis_tahap');
        $isOptionalStage = in_array($jenisInput, ['p16', 'p17p', 'p18p', 'p19'], true);
        $isKeputusanPengadilan = $jenisInput === JenisTahap::KEPUTUSAN_PENGADILAN->value || $jenisInput === JenisTahap::KEPUTUSAN_PENGADILAN->name;
        $dokumenInput = $this->file('dokumen');
        $isMultiDokumen = is_array($dokumenInput);

        $rules = [
            'action_type' => ['nullable', 'in:draft,lanjut'],
            'jenis_tahap' => [
                'required',
                Rule::enum(JenisTahap::class),
                function ($attribute, $value, $fail) {
                    $berkas = $this->route('berkas');
                    if (is_numeric($berkas)) {
                        $berkas = BerkasPerkara::find($berkas);
                    }
                    if ($berkas instanceof BerkasPerkara) {
                        $enum = $value instanceof JenisTahap ? $value : JenisTahap::tryFrom((string) $value);
                        if ($enum && $berkas->hasTahap($enum)) {
                            $fail("Tahapan {$enum->label()} sudah pernah dicatat pada Berkas Perkara ini. Data dari tahap sebelumnya yang sudah dipakai tidak dapat dipilih lagi.");

                            return;
                        }
                        $available = app(TahapanService::class)->getNextAvailableStages($berkas);
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
                $rules['dokumen.*'] = ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'];
            } else {
                $rules['dokumen'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'];
            }
        } else {
            $rules['dokumen'] = [$isDraft || $isOptionalStage ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:5120'];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $jenisInput = (string) $this->input('jenis_tahap');
            $isKeputusanPengadilan = $jenisInput === JenisTahap::KEPUTUSAN_PENGADILAN->value || $jenisInput === JenisTahap::KEPUTUSAN_PENGADILAN->name;

            if (! $isKeputusanPengadilan) {
                return;
            }

            $dokumenInput = $this->file('dokumen');
            $newFiles = is_array($dokumenInput) ? $dokumenInput : ($dokumenInput ? [$dokumenInput] : []);

            $newFilesSize = 0;
            foreach ($newFiles as $file) {
                if ($file instanceof UploadedFile) {
                    $newFilesSize += $file->getSize() ?: 0;
                }
            }

            if ($newFilesSize > 10 * 1024 * 1024) {
                $validator->errors()->add('dokumen', 'Total ukuran gabungan seluruh dokumen dan foto keputusan pengadilan tidak boleh melebihi 10 MB.');
            }

            $isLanjut = ($this->input('action_type') ?? 'lanjut') === 'lanjut';
            if ($isLanjut && count($newFiles) < 1) {
                $validator->errors()->add('dokumen', 'Wajib melampirkan minimal 1 dokumen salinan putusan (PDF atau Foto) untuk melanjutkan tahapan.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $jenisInput = (string) $this->input('jenis_tahap');
        $isKeputusanPengadilan = $jenisInput === JenisTahap::KEPUTUSAN_PENGADILAN->value || $jenisInput === JenisTahap::KEPUTUSAN_PENGADILAN->name;

        return [
            'jenis_tahap.required' => 'Jenis tahapan wajib ditentukan.',
            'nomor_surat.required' => 'Nomor surat / dokumen wajib diisi.',
            'nomor_surat.max' => 'Nomor surat tidak boleh lebih dari 150 karakter.',
            'tanggal_surat.required' => 'Tanggal surat / dokumen wajib diisi untuk melanjutkan tahapan.',
            'tanggal_surat.date' => 'Format tanggal surat tidak valid.',
            'dokumen.required' => 'Dokumen fisik wajib diunggah untuk melanjutkan tahapan.',
            'dokumen.mimes' => $isKeputusanPengadilan
                ? 'Dokumen keputusan pengadilan harus berformat PDF atau Gambar (JPG, JPEG, PNG, WEBP).'
                : 'Dokumen tahapan harus berupa file format PDF.',
            'dokumen.max' => $isKeputusanPengadilan
                ? 'Ukuran file dokumen keputusan pengadilan tidak boleh melebihi 10 MB.'
                : 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
            'dokumen.*.mimes' => 'Setiap berkas keputusan pengadilan harus berformat PDF atau Gambar (JPG, JPEG, PNG, WEBP).',
            'dokumen.*.max' => 'Ukuran setiap berkas dokumen keputusan pengadilan tidak boleh melebihi 10 MB.',
            'p16_dokumen.mimes' => 'Dokumen P-16 harus berupa file format PDF.',
            'p16_dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
            'p17p_dokumen.mimes' => 'Dokumen P-17 harus berupa file format PDF.',
            'p17p_dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
            'p18p_dokumen.mimes' => 'Dokumen P-18 harus berupa file format PDF.',
            'p18p_dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
            'p19_dokumen.mimes' => 'Dokumen P-19 harus berupa file format PDF.',
            'p19_dokumen.max' => 'Ukuran file dokumen tidak boleh melebihi 5 MB.',
        ];
    }
}
