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
            'nama' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('user', 'nip')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('user', 'email')],
            'role' => ['required', Rule::enum(UserRole::class)],
            'status' => ['required', Rule::in(['pns', 'pppk', 'lainnya'])],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
