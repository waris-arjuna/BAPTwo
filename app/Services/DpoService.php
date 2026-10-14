<?php

namespace App\Services;

use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Dpo;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DpoService
{
    public function __construct(
        protected AuditTrailService $auditTrail
    ) {}

    /**
     * Mengaktifkan status DPO pada Berkas Perkara
     *
     * @param  array<string, mixed>  $data
     *
     * @throws DomainException
     */
    public function activateDpo(
        BerkasPerkara $berkas,
        array $data,
        ?UploadedFile $file,
        User $user
    ): Dpo {
        return DB::transaction(function () use ($berkas, $data, $file, $user): Dpo {
            if ($berkas->is_dpo) {
                throw new DomainException('Tersangka pada Berkas Perkara ini sudah berstatus DPO.');
            }

            if ($berkas->status_formal !== null) {
                throw new DomainException("Berkas Perkara sudah berstatus formal {$berkas->status_formal_label} dan tidak dapat diubah.");
            }

            $dokumenId = null;
            if ($file !== null) {
                $path = $file->store('dokumen/dpo', 'local');
                $dokumen = Dokumen::create([
                    'perkara_id' => $berkas->perkara_id,
                    'berkas_perkara_id' => $berkas->id,
                    'kategori' => 'dpo',
                    'nama_dokumen' => "Surat Penetapan DPO - {$data['nomor_surat']}",
                    'nama_file_asli' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize() ?: 0,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
                $dokumenId = $dokumen->id;
            }

            $dpo = Dpo::create([
                'berkas_perkara_id' => $berkas->id,
                'nomor_surat' => $data['nomor_surat'],
                'tanggal_penetapan' => $data['tanggal_penetapan'],
                'status' => 'aktif',
                'keterangan' => $data['keterangan'] ?? null,
                'dokumen_id' => $dokumenId,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $berkas->update([
                'is_dpo' => true,
                'updated_by' => $user->id,
            ]);

            $this->auditTrail->log(
                aksi: 'aktifkan_dpo',
                tabel: 'dpo',
                recordId: $dpo->id,
                dataLama: null,
                dataBaru: [
                    'nomor_surat' => $dpo->nomor_surat,
                    'tanggal_penetapan' => $dpo->tanggal_penetapan?->toDateString(),
                    'keterangan' => $dpo->keterangan,
                    'is_dpo' => true,
                ],
                perkaraId: $berkas->perkara_id,
                berkasPerkaraId: $berkas->id,
                userId: $user->id
            );

            return $dpo;
        });
    }

    /**
     * Mencabut status DPO pada Berkas Perkara (PRD F-26, F-27)
     *
     * @param  array<string, mixed>  $data
     *
     * @throws DomainException
     */
    public function revokeDpo(
        BerkasPerkara $berkas,
        array $data,
        User $user
    ): Dpo {
        return DB::transaction(function () use ($berkas, $data, $user): Dpo {
            if (! $berkas->is_dpo) {
                throw new DomainException('Berkas Perkara ini tidak sedang berstatus DPO.');
            }

            $activeDpo = $berkas->activeDpo();
            if ($activeDpo === null) {
                throw new DomainException('Data penetapan DPO aktif tidak ditemukan.');
            }

            $activeDpo->update([
                'status' => 'nonaktif',
                'tanggal_pencabutan' => $data['tanggal_pencabutan'],
                'alasan_pencabutan' => $data['alasan_pencabutan'],
                'updated_by' => $user->id,
            ]);

            $berkas->update([
                'is_dpo' => false,
                'updated_by' => $user->id,
            ]);

            $this->auditTrail->log(
                aksi: 'nonaktifkan_dpo',
                tabel: 'dpo',
                recordId: $activeDpo->id,
                dataLama: [
                    'status' => 'aktif',
                    'is_dpo' => true,
                ],
                dataBaru: [
                    'status' => 'nonaktif',
                    'tanggal_pencabutan' => $activeDpo->tanggal_pencabutan?->toDateString(),
                    'alasan_pencabutan' => $activeDpo->alasan_pencabutan,
                    'is_dpo' => false,
                ],
                perkaraId: $berkas->perkara_id,
                berkasPerkaraId: $berkas->id,
                userId: $user->id
            );

            return $activeDpo;
        });
    }
}
