<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SaveMasterDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-master-data');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = (string) $this->route('type');
        $id = $this->route('id');

        return match ($type) {
            'jabatan' => [
                'nama_jabatan' => [
                    'required',
                    'string',
                    'max:150',
                    'regex:/^[\pL\s.,\-\/()]+$/u',
                    Rule::unique('jabatan', 'nama_jabatan')->ignore($id),
                ],
            ],
            'bidang' => [
                'nama_bidang' => [
                    'required',
                    'string',
                    'max:150',
                    'regex:/^(?=.*[\pL])[\pL0-9\s.,\-\/()&]+$/u',
                    Rule::unique('bidang', 'nama_bidang')->ignore($id),
                ],
            ],
            'panggol' => [
                'pangkat' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
                'golongan' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\/\-]+$/'],
            ],
            'pasal' => [
                'nama_pasal' => ['required', 'string', 'max:150', 'regex:/^(?=.*[\pL])[\pL0-9\s.,\-\/()&]+$/u'],
                'pasal' => ['required', 'string', 'max:150', 'regex:/^(?=.*[\pL0-9])[\pL0-9\s.,\-\/()]+$/u'],
                'nomor' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
                'tahun' => ['nullable', 'string', 'digits:4', 'regex:/^[0-9]{4}$/'],
                'isi' => ['nullable', 'string', 'max:2000'],
                'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            ],
            'instansi-hukum' => [
                'jenis' => ['required', Rule::in(['kejaksaan', 'pengadilan'])],
                'nama' => ['required', 'string', 'max:150', 'regex:/^(?=.*[\pL])[\pL0-9\s.,\-\/()]+$/u'],
                'wilayah' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s.,\-\/()]+$/u'],
            ],
            'jaksa-penuntut' => [
                'instansi_hukum_id' => ['required', 'integer', 'exists:instansi_hukum,id'],
                'nama_jaksa' => ['required', 'string', 'max:225', 'regex:/^[\pL\s.,\'\-]+$/u'],
                'nip' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
                'pangkat' => ['nullable', 'string', 'max:100', 'regex:/^(?=.*[\pL])[\pL0-9\s.,\-\/()]+$/u'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_jabatan.required' => __('Nama Jabatan wajib diisi.'),
            'nama_jabatan.regex' => __('Nama Jabatan hanya boleh berisi huruf, spasi, dan tanda baca standar (tanpa angka).'),
            'nama_jabatan.unique' => __('Nama Jabatan sudah terdaftar.'),
            'nama_bidang.required' => __('Nama Bidang / Unit wajib diisi.'),
            'nama_bidang.regex' => __('Nama Bidang hanya boleh berisi huruf, angka, dan tanda baca standar (wajib mengandung huruf).'),
            'nama_bidang.unique' => __('Nama Bidang / Unit sudah terdaftar.'),
            'pangkat.required' => __('Nama Pangkat wajib diisi.'),
            'pangkat.regex' => __('Nama Pangkat hanya boleh berisi huruf dan spasi (tanpa angka).'),
            'golongan.required' => __('Golongan Ruang wajib diisi.'),
            'golongan.regex' => __('Golongan Ruang hanya boleh berisi huruf, angka, dan tanda garis miring (contoh: IV/a).'),
            'nama_pasal.required' => __('Nama Undang-Undang / Regulasi wajib diisi.'),
            'nama_pasal.regex' => __('Nama Undang-Undang hanya boleh berisi huruf, angka, dan tanda baca standar (wajib mengandung huruf).'),
            'pasal.required' => __('Pasal & Ayat wajib diisi.'),
            'pasal.regex' => __('Pasal & Ayat hanya boleh berisi huruf, angka, dan tanda baca pasal.'),
            'nomor.regex' => __('Nomor UU hanya boleh berisi angka.'),
            'tahun.digits' => __('Tahun UU harus terdiri dari 4 digit angka (contoh: 2023).'),
            'tahun.regex' => __('Tahun UU hanya boleh berisi 4 digit angka.'),
            'nama.required' => __('Nama Instansi wajib diisi.'),
            'nama.regex' => __('Nama Instansi hanya boleh berisi huruf, angka, dan tanda baca standar (wajib mengandung huruf).'),
            'wilayah.regex' => __('Wilayah hanya boleh berisi huruf dan spasi (tanpa angka).'),
            'instansi_hukum_id.required' => __('Asal Kejaksaan wajib dipilih.'),
            'instansi_hukum_id.exists' => __('Asal Kejaksaan yang dipilih tidak valid.'),
            'nama_jaksa.required' => __('Nama Lengkap Jaksa wajib diisi.'),
            'nama_jaksa.regex' => __('Nama Lengkap Jaksa hanya boleh berisi huruf, spasi, dan tanda baca gelar (tanpa angka).'),
            'nip.regex' => __('NIP hanya boleh berisi angka.'),
            'pangkat.regex' => __('Pangkat Jaksa hanya boleh berisi huruf, angka, dan tanda baca standar (wajib mengandung huruf).'),
        ];
    }
}
