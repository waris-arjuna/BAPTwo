<?php

namespace App\Services;

use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Sp3;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class Sp3Service
{
    public function __construct(
        protected AuditTrailService $auditTrail
    ) {}

    /**
     * Menerbitkan SP3 untuk berkas perkara dan menetapkan status formal menjadi Tidak Selesai.
     *
     * @param  array{nomor_surat: string, tanggal_sp3: string, keterangan: string}  $data
     *
     * @throws DomainException
     */
    public function issueSp3(BerkasPerkara $berkas, array $data, UploadedFile $file, ?int $actorId = null): Sp3
    {
        if ($berkas->status_formal === 'selesai') {
            throw new DomainException('Berkas perkara yang sudah Selesai tidak dapat diterbitkan SP3.');
        }

        if ($berkas->hasSp3()) {
            throw new DomainException('Berkas perkara ini sudah memiliki dokumen SP3.');
        }

        return DB::transaction(function () use ($berkas, $data, $file, $actorId): Sp3 {
            // 1. Simpan dokumen PDF privat
            $path = $file->store('dokumen', 'local');

            $dokumen = Dokumen::create([
                'perkara_id' => $berkas->perkara_id,
                'berkas_perkara_id' => $berkas->id,
                'kategori' => 'sp3',
                'nama_dokumen' => "Surat Perintah Penghentian Penyidikan - {$data['nomor_surat']}",
                'nama_file_asli' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize() ?: 0,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            // 2. Buat record SP3
            $sp3 = Sp3::create([
                'berkas_perkara_id' => $berkas->id,
                'nomor_surat' => $data['nomor_surat'],
                'tanggal_sp3' => $data['tanggal_sp3'],
                'keterangan' => $data['keterangan'],
                'dokumen_id' => $dokumen->id,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            // 3. Update status formal berkas perkara menjadi tidak_selesai dan status proses menjadi SP3
            $oldStatusFormal = $berkas->status_formal;
            $oldStatusProses = $berkas->status_proses;
            $berkas->update([
                'status_formal' => 'tidak_selesai',
                'status_proses' => 'SP3',
                'updated_by' => $actorId,
            ]);

            // 4. Catat Audit Log
            $this->auditTrail->log(
                aksi: 'terbitkan_sp3',
                tabel: 'sp3',
                recordId: $sp3->id,
                dataLama: [
                    'status_formal' => $oldStatusFormal,
                    'status_proses' => $oldStatusProses,
                ],
                dataBaru: [
                    'status_formal' => 'tidak_selesai',
                    'status_proses' => 'SP3',
                    'nomor_sp3' => $sp3->nomor_surat,
                    'tanggal_sp3' => $sp3->tanggal_sp3->format('Y-m-d'),
                    'keterangan' => $sp3->keterangan,
                ],
                perkaraId: $berkas->perkara_id,
                berkasPerkaraId: $berkas->id,
                userId: $actorId
            );

            return $sp3;
        });
    }
}
