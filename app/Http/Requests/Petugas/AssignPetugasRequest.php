<?php

declare(strict_types=1);

namespace App\Http\Requests\Petugas;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignPetugasRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan penugasan peran petugas.
     */
    public function authorize(): bool
    {
        $currentUser = $this->user();

        return $currentUser !== null && ($currentUser->isAdmin() || $currentUser->isPenyidik());
    }

    /**
     * Aturan validasi pembaruan peran dan status petugas.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $currentUser = $this->user();

        return [
            'user_id' => [
                'required',
                Rule::exists('user', 'id')->where(function ($q) {
                    $q->where('role', '!=', UserRole::ADMINISTRATOR->value);
                }),
                function ($attribute, $value, $fail) use ($currentUser) {
                    if ($currentUser?->isPenyidik() && (int) $value === (int) $currentUser->id) {
                        $fail('Penyidik tidak diizinkan mengubah akun dirinya sendiri.');
                    }
                },
            ],
            'role' => [
                'required',
                Rule::in([UserRole::PENYIDIK->value, UserRole::UMUM->value]),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan validasi kustom berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Pilih petugas yang akan ditugaskan.',
            'user_id.exists' => 'Petugas yang dipilih tidak valid atau merupakan Administrator.',
            'role.required' => 'Pilih peran penugasan yang diberikan.',
            'role.in' => 'Peran yang dipilih tidak valid.',
        ];
    }
}
