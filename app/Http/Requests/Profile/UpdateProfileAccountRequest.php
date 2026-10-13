<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileAccountRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('user', 'email')->ignore($this->user()?->id),
            ],
            'current_password' => ['required', 'string', 'current_password'],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'confirmed',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('Alamat email wajib diisi.'),
            'email.email' => __('Format alamat email tidak valid.'),
            'email.unique' => __('Alamat email sudah digunakan oleh pengguna lain.'),
            'current_password.required' => __('Password lama wajib diisi untuk konfirmasi perubahan akun.'),
            'current_password.current_password' => __('Password lama yang Anda masukkan tidak sesuai.'),
            'password.min' => __('Password baru minimal harus 8 karakter.'),
            'password.regex' => __('Password baru wajib mengandung kombinasi huruf besar, huruf kecil, angka, dan simbol.'),
            'password.confirmed' => __('Konfirmasi password baru tidak cocok.'),
        ];
    }
}
