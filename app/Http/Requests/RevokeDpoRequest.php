<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevokeDpoRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tanggal_pencabutan' => ['required', 'date'],
            'alasan_pencabutan' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal_pencabutan.required' => 'Tanggal pencabutan status DPO wajib diisi.',
            'tanggal_pencabutan.date' => 'Format tanggal pencabutan status DPO tidak valid.',
            'alasan_pencabutan.required' => 'Alasan pencabutan status DPO wajib diisi.',
            'alasan_pencabutan.max' => 'Alasan pencabutan tidak boleh lebih dari :max karakter.',
        ];
    }
}
