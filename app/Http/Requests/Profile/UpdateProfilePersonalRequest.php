<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'no_telp' => ['required', 'string', 'min:8', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'provinsi_kode' => ['required', 'string', 'max:15'],
            'provinsi_nama' => ['required', 'string', 'max:100'],
            'kabupaten_kode' => ['required', 'string', 'max:15'],
            'kabupaten_nama' => ['required', 'string', 'max:100'],
            'kecamatan_kode' => ['required', 'string', 'max:15'],
            'kecamatan_nama' => ['required', 'string', 'max:100'],
            'desa_kode' => ['required', 'string', 'max:15'],
            'desa_nama' => ['required', 'string', 'max:100'],
            'rt' => ['nullable', 'string', 'max:5', 'regex:/^[0-9]+$/'],
            'rw' => ['nullable', 'string', 'max:5', 'regex:/^[0-9]+$/'],
            'alamat_detail' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'no_telp.required' => __('Nomor Telepon / WhatsApp wajib diisi.'),
            'no_telp.min' => __('Nomor Telepon / WhatsApp minimal 8 karakter.'),
            'no_telp.max' => __('Nomor Telepon / WhatsApp maksimal 25 karakter.'),
            'no_telp.regex' => __('Format Nomor Telepon / WhatsApp hanya boleh berisi angka, spasi, atau tanda + - ( ).'),
            'provinsi_kode.required' => __('Provinsi wajib dipilih.'),
            'provinsi_nama.required' => __('Nama provinsi wajib diisi.'),
            'kabupaten_kode.required' => __('Kabupaten / Kota wajib dipilih.'),
            'kabupaten_nama.required' => __('Nama kabupaten / kota wajib diisi.'),
            'kecamatan_kode.required' => __('Kecamatan wajib dipilih.'),
            'kecamatan_nama.required' => __('Nama kecamatan wajib diisi.'),
            'desa_kode.required' => __('Desa / Kelurahan wajib dipilih.'),
            'desa_nama.required' => __('Nama desa / kelurahan wajib diisi.'),
            'rt.regex' => __('RT hanya boleh berisi angka.'),
            'rw.regex' => __('RW hanya boleh berisi angka.'),
            'alamat_detail.required' => __('Detail alamat (nama jalan / nomor rumah) wajib diisi.'),
            'alamat_detail.max' => __('Detail alamat maksimal 255 karakter.'),
        ];
    }
}
