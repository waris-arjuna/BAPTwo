<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class DokumenService
{
    /**
     * Simpan file dokumen PDF ke storage privat dan catat ke tabel dokumen.
     *
     * @param  array<string, mixed>  $metadata  ['perkara_id' => int, 'berkas_perkara_id' => ?int, 'tahapan_perkara_id' => ?int, 'dpo_id' => ?int, 'nama_dokumen' => ?string, 'folder' => ?string]
     */
    public function simpan(
        UploadedFile $file,
        string $kategori,
        array $metadata,
        User|int $user
    ): Dokumen {
        $userId = $user instanceof User ? $user->id : (int) $user;
        $folder = $metadata['folder'] ?? "dokumen/{$kategori}";
        $path = $file->store($folder, 'local');

        $namaDokumen = $metadata['nama_dokumen'] ?? $file->getClientOriginalName();

        return Dokumen::create([
            'perkara_id' => $metadata['perkara_id'] ?? null,
            'berkas_perkara_id' => $metadata['berkas_perkara_id'] ?? null,
            'tahapan_perkara_id' => $metadata['tahapan_perkara_id'] ?? null,
            'dpo_id' => $metadata['dpo_id'] ?? null,
            'kategori' => $kategori,
            'nama_dokumen' => $namaDokumen,
            'nama_file_asli' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize() ?: 0,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }
}
