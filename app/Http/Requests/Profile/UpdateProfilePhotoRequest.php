<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePhotoRequest extends FormRequest
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
            'foto_profil' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'foto_profil.required' => __('Silakan pilih file foto profil terlebih dahulu.'),
            'foto_profil.file' => __('File foto profil tidak valid.'),
            'foto_profil.image' => __('File yang diunggah harus berupa gambar.'),
            'foto_profil.mimes' => __('Format foto profil harus JPG, JPEG, PNG, atau WEBP.'),
            'foto_profil.max' => __('Ukuran foto profil maksimal 2 MB.'),
        ];
    }
}
