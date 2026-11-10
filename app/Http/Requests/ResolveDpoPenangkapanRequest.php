<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class ResolveDpoPenangkapanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('toggle-dpo') ?? false;
    }

    /**
     * Normalisasi input file jika dikirim sebagai single file
     */
    protected function prepareForValidation(): void
    {
        $file = $this->file('dokumen_penangkapan');
        if ($file instanceof UploadedFile) {
            $this->files->set('dokumen_penangkapan', [$file]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_surat_penangkapan' => ['required', 'string', 'max:150'],
            'tanggal_penangkapan' => ['required', 'date'],
            'tanggal_surat_penangkapan' => ['nullable', 'date'],
            'nomor_surat_penahanan' => ['required', 'string', 'max:150'],
            'tanggal_penahanan' => ['required', 'date'],
            'dokumen_penangkapan' => ['required', 'array', 'min:1'],
            'dokumen_penangkapan.*' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'keterangan_penangkapan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Validasi batas total akumulasi ukuran berkas tidak melebihi 5 MB (5.242.880 bytes)
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $files = $this->file('dokumen_penangkapan');
            if (is_array($files)) {
                $totalSize = 0;
                foreach ($files as $file) {
                    if ($file instanceof UploadedFile) {
                        $totalSize += $file->getSize();
                    }
                }

                $maxTotalBytes = 5 * 1024 * 1024; // 5 MB = 5.242.880 bytes
                if ($totalSize > $maxTotalBytes) {
                    $validator->errors()->add(
                        'dokumen_penangkapan',
                        'Total ukuran gabungan seluruh file berkas tidak boleh melebihi 5 MB.'
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_surat_penangkapan.required' => 'Nomor surat perintah penangkapan wajib diisi.',
            'nomor_surat_penangkapan.max' => 'Nomor surat penangkapan tidak boleh lebih dari :max karakter.',
            'tanggal_penangkapan.required' => 'Tanggal pelaksanaan penangkapan wajib diisi.',
            'tanggal_penangkapan.date' => 'Format tanggal pelaksanaan penangkapan tidak valid.',
            'tanggal_surat_penangkapan.date' => 'Format tanggal surat perintah penangkapan tidak valid.',
            'nomor_surat_penahanan.required' => 'Nomor surat perintah penahanan wajib diisi.',
            'nomor_surat_penahanan.max' => 'Nomor surat penahanan tidak boleh lebih dari :max karakter.',
            'tanggal_penahanan.required' => 'Tanggal pelaksanaan penahanan wajib diisi.',
            'tanggal_penahanan.date' => 'Format tanggal pelaksanaan penahanan tidak valid.',
            'dokumen_penangkapan.required' => 'Minimal 1 berkas dokumen PDF penangkapan/penahanan wajib diunggah.',
            'dokumen_penangkapan.array' => 'Berkas dokumen penangkapan harus berupa daftar file.',
            'dokumen_penangkapan.min' => 'Minimal 1 berkas dokumen PDF penangkapan/penahanan wajib diunggah.',
            'dokumen_penangkapan.*.required' => 'File berkas dokumen wajib disertakan.',
            'dokumen_penangkapan.*.file' => 'File berkas dokumen tidak valid.',
            'dokumen_penangkapan.*.mimes' => 'Setiap file berkas dokumen wajib berformat PDF.',
            'dokumen_penangkapan.*.max' => 'Ukuran setiap file berkas dokumen tidak boleh melebihi 5 MB.',
            'keterangan_penangkapan.max' => 'Keterangan penangkapan tidak boleh lebih dari :max karakter.',
        ];
    }
}
