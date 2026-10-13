<?php

namespace App\Http\Requests;

use App\Models\Perkara;
use Illuminate\Foundation\Http\FormRequest;

class SavePerkaraBerkasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Perkara|null $perkara */
        $perkara = $this->route('perkara');
        $isLocked = $perkara instanceof Perkara && $perkara->isPerkaraLocked();

        return $this->user()?->can('edit-draft', $isLocked) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isFinal = $this->input('action_type', 'lanjut') === 'lanjut';

        return [
            'action_type' => ['nullable', 'string', 'in:draft,lanjut'],

            // TKP (F-07, F-36, F-37)
            'tkp_klasifikasi' => [$isFinal ? 'required' : 'nullable', 'in:retailer,distributor'],
            'tkp_legalitas' => [$isFinal ? 'required' : 'nullable', 'in:legal,ilegal'],
            'tkp_modus' => [$isFinal ? 'required' : 'nullable', 'in:online,offline'],
            'tkp_provinsi_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'max:15'],
            'tkp_provinsi_nama' => ['nullable', 'string', 'max:100'],
            'tkp_kabupaten_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'max:15'],
            'tkp_kabupaten_nama' => ['nullable', 'string', 'max:100'],
            'tkp_kecamatan_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'max:15'],
            'tkp_kecamatan_nama' => ['nullable', 'string', 'max:100'],
            'tkp_desa_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'max:15'],
            'tkp_desa_nama' => ['nullable', 'string', 'max:100'],
            'tkp_rt' => ['nullable', 'string', 'max:5'],
            'tkp_rw' => ['nullable', 'string', 'max:5'],
            'tkp_alamat_detail' => [$isFinal ? 'required' : 'nullable', 'string', 'max:255'],

            // Tim PPNS (F-08)
            'ppns_utama_id' => [$isFinal ? 'required' : 'nullable', 'integer', 'exists:user,id'],
            'ppns_anggota_ids' => ['nullable', 'array'],
            'ppns_anggota_ids.*' => ['integer', 'exists:user,id'],

            // Tersangka, Berkas Perkara & Pasal Berlapis (F-02, F-03, F-04, F-33)
            'tersangka' => ['required', 'array', 'min:1'],
            'tersangka.*.id' => ['nullable', 'integer'],
            'tersangka.*.nama' => ['required', 'string', 'max:150'],
            'tersangka.*.alias' => ['nullable', 'string', 'max:150'],
            'tersangka.*.bin_binti' => ['nullable', 'string', 'max:150'],
            'tersangka.*.nik' => ['nullable', 'string', 'max:20'],
            'tersangka.*.tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tersangka.*.tanggal_lahir' => ['nullable', 'date'],
            'tersangka.*.jenis_kelamin' => [$isFinal ? 'required' : 'nullable', 'in:laki-laki,perempuan'],
            'tersangka.*.agama' => ['nullable', 'in:islam,kristen,katolik,hindu,buddha,konghucu,lainnya'],
            'tersangka.*.pekerjaan' => ['nullable', 'string', 'max:100'],
            'tersangka.*.kewarganegaraan' => ['nullable', 'string', 'max:50'],
            // Nomor Berkas Perkara diinput manual TANPA validasi pola regex (PRD F-33, T-025)
            'tersangka.*.nomor_berkas' => ['required', 'string', 'max:150'],
            // Pasal Berlapis per Berkas Perkara (PRD F-04, T-022)
            'tersangka.*.pasal_ids' => [$isFinal ? 'required' : 'nullable', 'array', $isFinal ? 'min:1' : 'min:0'],
            'tersangka.*.pasal_ids.*' => ['integer', 'exists:pasal,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tkp_klasifikasi.required' => 'Klasifikasi TKP wajib dipilih.',
            'tkp_legalitas.required' => 'Legalitas TKP wajib dipilih.',
            'tkp_modus.required' => 'Modus operandi TKP wajib dipilih.',
            'tkp_provinsi_kode.required' => 'Provinsi lokasi TKP wajib dipilih.',
            'tkp_kabupaten_kode.required' => 'Kabupaten/Kota lokasi TKP wajib dipilih.',
            'tkp_kecamatan_kode.required' => 'Kecamatan lokasi TKP wajib dipilih.',
            'tkp_desa_kode.required' => 'Desa/Kelurahan lokasi TKP wajib dipilih.',
            'tkp_alamat_detail.required' => 'Detail alamat TKP wajib diisi.',
            'ppns_utama_id.required' => 'PPNS Utama wajib dipilih.',
            'tersangka.required' => 'Minimal harus terdapat 1 (satu) Tersangka pada perkara ini.',
            'tersangka.min' => 'Minimal harus terdapat 1 (satu) Tersangka pada perkara ini.',
            'tersangka.*.nama.required' => 'Nama tersangka wajib diisi.',
            'tersangka.*.jenis_kelamin.required' => 'Jenis kelamin tersangka wajib dipilih.',
            'tersangka.*.nomor_berkas.required' => 'Nomor Berkas Perkara wajib diisi untuk setiap tersangka.',
            'tersangka.*.pasal_ids.required' => 'Minimal pilih 1 (satu) Pasal yang disangkakan pada berkas perkara.',
            'tersangka.*.pasal_ids.min' => 'Minimal pilih 1 (satu) Pasal yang disangkakan pada berkas perkara.',
        ];
    }
}
