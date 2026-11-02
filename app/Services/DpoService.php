<?php

namespace App\Services;

use App\Enums\JenisTahap;
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

            if ($berkas->hasSp3() || $berkas->status_formal === 'tidak_selesai') {
                throw new DomainException('Berkas perkara yang sudah dihentikan (SP3 / Tidak Selesai) tidak dapat ditetapkan DPO.');
            }

            if ($berkas->hasTahap(JenisTahap::KEPUTUSAN_PENGADILAN)
                && $berkas->tahapan()->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN->value)->where('status', 'final')->exists()) {
                throw new DomainException('Perkara telah selesai hingga Keputusan Pengadilan dan terkunci permanen.');
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

    /**
     * Mencatat penangkapan dan penahanan tersangka DPO (Poin 4, ref: D-053).
     *
     * @param  array<string, mixed>  $data
     * @param  UploadedFile|array<UploadedFile>|null  $files
     *
     * @throws DomainException
     */
    public function recordPenangkapan(
        BerkasPerkara $berkas,
        array $data,
        UploadedFile|array|null $files,
        User $user
    ): Dpo {
        return DB::transaction(function () use ($berkas, $data, $files, $user): Dpo {
            if (! $berkas->is_dpo) {
                throw new DomainException('Berkas Perkara ini tidak sedang berstatus DPO.');
            }

            $activeDpo = $berkas->activeDpo();
            if ($activeDpo === null) {
                throw new DomainException('Data penetapan DPO aktif tidak ditemukan.');
            }

            if ($files instanceof UploadedFile) {
                $files = [$files];
            } elseif (! is_array($files)) {
                $files = [];
            }

            $dokumenPenangkapanId = null;
            $createdDokumens = [];

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $path = $file->store('dokumen/dpo', 'local');
                    $dokumen = Dokumen::create([
                        'perkara_id' => $berkas->perkara_id,
                        'berkas_perkara_id' => $berkas->id,
                        'dpo_id' => $activeDpo->id,
                        'kategori' => 'dpo',
                        'nama_dokumen' => "Surat Penangkapan/Penahanan DPO - {$data['nomor_surat_penangkapan']} ({$file->getClientOriginalName()})",
                        'nama_file_asli' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize() ?: 0,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    if ($dokumenPenangkapanId === null) {
                        $dokumenPenangkapanId = $dokumen->id;
                    }
                    $createdDokumens[] = $dokumen;
                }
            }

            $alasan = 'Tertangkap / Ditahan (Surat: '.$data['nomor_surat_penangkapan'].')';

            $activeDpo->update([
                'status' => 'nonaktif',
                'tanggal_pencabutan' => $data['tanggal_penangkapan'],
                'alasan_pencabutan' => $alasan,
                'nomor_surat_penangkapan' => $data['nomor_surat_penangkapan'],
                'tanggal_surat_penangkapan' => $data['tanggal_surat_penangkapan'] ?? $data['tanggal_penangkapan'],
                'tanggal_penangkapan' => $data['tanggal_penangkapan'],
                'nomor_surat_penahanan' => $data['nomor_surat_penahanan'] ?? null,
                'tanggal_penahanan' => $data['tanggal_penahanan'] ?? null,
                'dokumen_penangkapan_id' => $dokumenPenangkapanId,
                'keterangan_penangkapan' => $data['keterangan_penangkapan'] ?? null,
                'updated_by' => $user->id,
            ]);

            $berkas->update([
                'is_dpo' => false,
                'updated_by' => $user->id,
            ]);

            $this->auditTrail->log(
                aksi: 'penangkapan_dpo',
                tabel: 'dpo',
                recordId: $activeDpo->id,
                dataLama: [
                    'status' => 'aktif',
                    'is_dpo' => true,
                ],
                dataBaru: [
                    'status' => 'nonaktif',
                    'nomor_surat_penangkapan' => $activeDpo->nomor_surat_penangkapan,
                    'tanggal_penangkapan' => $activeDpo->tanggal_penangkapan?->toDateString(),
                    'nomor_surat_penahanan' => $activeDpo->nomor_surat_penahanan,
                    'tanggal_penahanan' => $activeDpo->tanggal_penahanan?->toDateString(),
                    'jumlah_dokumen' => count($createdDokumens),
                    'keterangan_penangkapan' => $activeDpo->keterangan_penangkapan,
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
