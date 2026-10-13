<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150', 'regex:/^[\pL\s.,\'\-]+$/u'],
            'nip' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/', Rule::unique('user', 'nip')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('user', 'email')],
            'role' => ['required', Rule::enum(UserRole::class)],
            'status' => ['required', Rule::in(['pns', 'pppk', 'lainnya'])],
            'jabatan_id' => ['nullable', 'integer', 'exists:jabatan,id'],
            'bidang_id' => ['nullable', 'integer', 'exists:bidang,id'],
            'panggol_id' => ['nullable', 'integer', 'exists:panggol,id'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => __('Nama lengkap wajib diisi.'),
            'nama.regex' => __('Nama lengkap hanya boleh berisi huruf, spasi, dan tanda baca gelar (tanpa angka).'),
            'nip.regex' => __('NIP hanya boleh berisi angka.'),
            'nip.unique' => __('NIP sudah terdaftar pada pengguna lain.'),
            'email.required' => __('Alamat email wajib diisi.'),
            'email.email' => __('Format alamat email tidak valid.'),
            'email.unique' => __('Alamat email sudah digunakan oleh pengguna lain.'),
            'password.required' => __('Password wajib diisi.'),
            'password.min' => __('Password minimal harus 8 karakter.'),
        ];
    }
}
