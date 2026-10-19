<?php

namespace App\Http\Requests;

use App\Models\Perkara;
use Illuminate\Contracts\Validation\ValidationRule;
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
     * Normalisasi input sebelum validasi (dukung single input form dan format array)
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('tersangka_nama') && ! $this->has('tersangka')) {
            $this->merge([
                'tersangka' => [
                    [
                        'id' => $this->input('tersangka_id'),
                        'nama' => $this->input('tersangka_nama'),
                        'alias' => $this->input('tersangka_alias'),
                        'bin_binti' => $this->input('tersangka_bin_binti'),
                        'nik' => $this->input('tersangka_nik'),
                        'tempat_lahir' => $this->input('tersangka_tempat_lahir'),
                        'tanggal_lahir' => $this->input('tersangka_tanggal_lahir'),
                        'jenis_kelamin' => $this->input('tersangka_jenis_kelamin'),
                        'agama' => $this->input('tersangka_agama'),
                        'pekerjaan' => $this->input('tersangka_pekerjaan'),
                        'kewarganegaraan' => $this->input('tersangka_kewarganegaraan', 'WNI'),
                        'provinsi_kode' => $this->input('tersangka_provinsi_kode'),
                        'provinsi_nama' => $this->input('tersangka_provinsi_nama'),
                        'kabupaten_kode' => $this->input('tersangka_kabupaten_kode'),
                        'kabupaten_nama' => $this->input('tersangka_kabupaten_nama'),
                        'kecamatan_kode' => $this->input('tersangka_kecamatan_kode'),
                        'kecamatan_nama' => $this->input('tersangka_kecamatan_nama'),
                        'desa_kode' => $this->input('tersangka_desa_kode'),
                        'desa_nama' => $this->input('tersangka_desa_nama'),
                        'rt' => $this->input('tersangka_rt'),
                        'rw' => $this->input('tersangka_rw'),
                        'alamat_detail' => $this->input('tersangka_alamat_detail'),
                        'nomor_berkas' => $this->input('nomor_berkas'),
                        'pasal_ids' => $this->input('pasal_ids', []),
                    ],
                ],
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isFinal = $this->input('action_type', 'lanjut') === 'lanjut';

        return [
            'action_type' => ['nullable', 'string', 'in:draft,lanjut'],

            // TKP (F-07, F-36, F-37, Poin 3 & Poin 4)
            'tkp_nama' => ['nullable', 'string', 'max:150'],
            'tkp_klasifikasi' => [$isFinal ? 'required' : 'nullable', 'in:retailer,distributor'],
            'tkp_legalitas' => [$isFinal ? 'required' : 'nullable', 'in:legal,ilegal'],
            'tkp_modus' => [$isFinal ? 'required' : 'nullable', 'in:online,offline'],
            'tkp_provinsi_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'in:63'],
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

            // Single Tersangka, Berkas Perkara & Pasal Berlapis (Poin 2, 8, 11, 13, 17)
            'tersangka' => ['required', 'array', 'min:1'],
            'tersangka.*.id' => ['nullable', 'integer'],
            'tersangka.*.nama' => ['required', 'string', 'max:150'],
            'tersangka.*.alias' => ['nullable', 'string', 'max:150'],
            'tersangka.*.bin_binti' => ['nullable', 'string', 'max:150'],
            'tersangka.*.nik' => [$isFinal ? 'required' : 'nullable', 'string', 'digits:16'],
            'tersangka.*.tempat_lahir' => [$isFinal ? 'required' : 'nullable', 'string', 'max:100'],
            'tersangka.*.tanggal_lahir' => [$isFinal ? 'required' : 'nullable', 'date', 'before_or_equal:today'],
            'tersangka.*.jenis_kelamin' => [$isFinal ? 'required' : 'nullable', 'in:laki-laki,perempuan'],
            'tersangka.*.agama' => [$isFinal ? 'required' : 'nullable', 'in:islam,kristen,katolik,hindu,buddha,konghucu,lainnya'],
            'tersangka.*.pekerjaan' => [$isFinal ? 'required' : 'nullable', 'string', 'max:100'],
            'tersangka.*.kewarganegaraan' => [$isFinal ? 'required' : 'nullable', 'string', 'max:50'],

            // Alamat Tersangka (PRD F-03, 38 Provinsi)
            'tersangka.*.provinsi_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'max:15'],
            'tersangka.*.provinsi_nama' => ['nullable', 'string', 'max:100'],
            'tersangka.*.kabupaten_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'max:15'],
            'tersangka.*.kabupaten_nama' => ['nullable', 'string', 'max:100'],
            'tersangka.*.kecamatan_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'max:15'],
            'tersangka.*.kecamatan_nama' => ['nullable', 'string', 'max:100'],
            'tersangka.*.desa_kode' => [$isFinal ? 'required' : 'nullable', 'string', 'max:15'],
            'tersangka.*.desa_nama' => ['nullable', 'string', 'max:100'],
            'tersangka.*.rt' => ['nullable', 'string', 'max:5'],
            'tersangka.*.rw' => ['nullable', 'string', 'max:5'],
            'tersangka.*.alamat_detail' => [$isFinal ? 'required' : 'nullable', 'string', 'max:255'],

            // Nomor Berkas Perkara (otomatis mengikuti Nomor LK jika kosong)
            'tersangka.*.nomor_berkas' => ['nullable', 'string', 'max:150'],
            // Pasal Berlapis (Poin 8)
            'tersangka.*.pasal_ids' => [$isFinal ? 'required' : 'nullable', 'array', $isFinal ? 'min:1' : 'min:0'],
            'tersangka.*.pasal_ids.*' => ['integer', 'exists:pasal,id'],

            // Validasi jika field top-level dikirim langsung
            'tersangka_nik' => [$isFinal ? 'sometimes' : 'nullable', 'string', 'digits:16'],
            'tersangka_provinsi_kode' => [$isFinal ? 'sometimes' : 'nullable', 'string', 'max:15'],
            'tersangka_kabupaten_kode' => [$isFinal ? 'sometimes' : 'nullable', 'string', 'max:15'],
            'tersangka_kecamatan_kode' => [$isFinal ? 'sometimes' : 'nullable', 'string', 'max:15'],
            'tersangka_desa_kode' => [$isFinal ? 'sometimes' : 'nullable', 'string', 'max:15'],
            'tersangka_alamat_detail' => [$isFinal ? 'sometimes' : 'nullable', 'string', 'max:255'],
            'pasal_ids' => [$isFinal ? 'sometimes' : 'nullable', 'array'],
            'pasal_ids.*' => ['integer', 'exists:pasal,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tkp_klasifikasi.required' => 'Klasifikasi TKP wajib dipilih.',
            'tkp_legalitas.required' => 'Legalitas TKP wajib dipilih (Legal / Ilegal).',
            'tkp_modus.required' => 'Modus operandi TKP wajib dipilih (Online / Offline).',
            'tkp_provinsi_kode.required' => 'Provinsi lokasi TKP wajib dipilih.',
            'tkp_provinsi_kode.in' => 'Provinsi lokasi TKP harus Kalimantan Selatan (kode 63).',
            'tkp_kabupaten_kode.required' => 'Kabupaten/Kota lokasi TKP wajib dipilih.',
            'tkp_kecamatan_kode.required' => 'Kecamatan lokasi TKP wajib dipilih.',
            'tkp_desa_kode.required' => 'Desa/Kelurahan lokasi TKP wajib dipilih.',
            'tkp_alamat_detail.required' => 'Detail alamat TKP wajib diisi.',
            'ppns_utama_id.required' => 'PPNS Utama wajib dipilih.',
            'tersangka.required' => 'Data Tersangka pada perkara ini wajib diisi.',
            'tersangka.min' => 'Data Tersangka pada perkara ini wajib diisi.',
            'tersangka.*.nama.required' => 'Nama lengkap tersangka wajib diisi.',
            'tersangka.*.nik.required' => 'NIK tersangka wajib diisi (tepat 16 digit angka).',
            'tersangka.*.nik.digits' => 'NIK tersangka harus tepat 16 digit angka.',
            'tersangka_nik.digits' => 'NIK tersangka harus tepat 16 digit angka.',
            'tersangka.*.jenis_kelamin.required' => 'Jenis kelamin tersangka wajib dipilih.',
            'tersangka.*.tempat_lahir.required' => 'Tempat lahir tersangka wajib diisi.',
            'tersangka.*.tanggal_lahir.required' => 'Tanggal lahir tersangka wajib diisi.',
            'tersangka.*.tanggal_lahir.before_or_equal' => 'Tanggal lahir tersangka tidak boleh di masa depan.',
            'tersangka.*.agama.required' => 'Agama tersangka wajib dipilih.',
            'tersangka.*.pekerjaan.required' => 'Pekerjaan tersangka wajib diisi.',
            'tersangka.*.kewarganegaraan.required' => 'Kewarganegaraan tersangka wajib diisi.',
            'tersangka.*.provinsi_kode.required' => 'Provinsi alamat tersangka wajib dipilih.',
            'tersangka.*.kabupaten_kode.required' => 'Kabupaten/Kota alamat tersangka wajib dipilih.',
            'tersangka.*.kecamatan_kode.required' => 'Kecamatan alamat tersangka wajib dipilih.',
            'tersangka.*.desa_kode.required' => 'Desa/Kelurahan alamat tersangka wajib dipilih.',
            'tersangka.*.alamat_detail.required' => 'Detail jalan/alamat tersangka wajib diisi.',
            'tersangka.*.pasal_ids.required' => 'Minimal pilih 1 (satu) Pasal yang disangkakan pada berkas perkara.',
            'tersangka.*.pasal_ids.min' => 'Minimal pilih 1 (satu) Pasal yang disangkakan pada berkas perkara.',
            'pasal_ids.required' => 'Minimal pilih 1 (satu) Pasal yang disangkakan pada berkas perkara.',
            'pasal_ids.min' => 'Minimal pilih 1 (satu) Pasal yang disangkakan pada berkas perkara.',
        ];
    }
}
