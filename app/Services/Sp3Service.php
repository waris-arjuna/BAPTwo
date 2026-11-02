<?php

namespace App\Services;

use App\Models\BerkasPerkara;
use App\Models\Dokumen;
use App\Models\Sp3;
use App\Models\User;
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
     * @param  array{nomor_surat: string, tanggal_sp3: string, keterangan: string, penandatangan_id?: int|null, penyidik_utama_id?: int|null, penyidik_anggota_ids?: array<int>, penandatangan_nama?: string|null, penandatangan_jabatan?: string|null}  $data
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
            // Periksa dan cabut status DPO jika aktif saat SP3 diterbitkan (PRD F-26, F-28, D-053)
            $wasDpo = (bool) $berkas->is_dpo;
            $activeDpo = $wasDpo ? $berkas->activeDpo() : null;

            if ($activeDpo) {
                $activeDpo->update([
                    'status' => 'nonaktif',
                    'tanggal_pencabutan' => $data['tanggal_sp3'],
                    'alasan_pencabutan' => 'Ditutup karena SP3',
                    'updated_by' => $actorId,
                ]);
            }

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

            // Ambil data pejabat penandatangan dari tabel pegawai (user)
            if (! empty($data['penandatangan_id'])) {
                $penandatangan = User::with('jabatan')->findOrFail($data['penandatangan_id']);
                $penandatanganNama = $penandatangan->nama;
                $penandatanganJabatan = $penandatangan->jabatan?->nama_jabatan ?? '-';
            } else {
                $penandatanganNama = $data['penandatangan_nama'] ?? null;
                $penandatanganJabatan = $data['penandatangan_jabatan'] ?? null;
            }

            // Ambil Penyidik Utama & Anggota otomatis dari Perkara terkait jika tidak ditentukan eksplisit
            $penyidikUtamaId = $data['penyidik_utama_id'] ?? $berkas->perkara?->ppnsUtama()->first()?->id ?? null;
            $penyidikAnggotaIds = $data['penyidik_anggota_ids'] ?? ($berkas->perkara?->ppnsAnggota()->pluck('user.id')->toArray() ?? []);

            // 2. Buat record SP3 (Poin 6)
            $sp3 = Sp3::create([
                'berkas_perkara_id' => $berkas->id,
                'nomor_surat' => $data['nomor_surat'],
                'tanggal_sp3' => $data['tanggal_sp3'],
                'keterangan' => $data['keterangan'],
                'dokumen_id' => $dokumen->id,
                'penyidik_utama_id' => $penyidikUtamaId,
                'penandatangan_nama' => $penandatanganNama,
                'penandatangan_jabatan' => $penandatanganJabatan,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            if (! empty($penyidikAnggotaIds)) {
                $sp3->penyidikAnggota()->sync($penyidikAnggotaIds);
            }

            // 3. Update status formal berkas perkara menjadi tidak_selesai, status proses menjadi SP3, dan cabut DPO jika aktif
            $oldStatusFormal = $berkas->status_formal;
            $oldStatusProses = $berkas->status_proses;
            $berkas->update([
                'status_formal' => 'tidak_selesai',
                'status_proses' => 'SP3',
                'is_dpo' => false,
                'updated_by' => $actorId,
            ]);

            // 4. Catat Audit Log SP3
            $this->auditTrail->log(
                aksi: 'terbitkan_sp3',
                tabel: 'sp3',
                recordId: $sp3->id,
                dataLama: [
                    'status_formal' => $oldStatusFormal,
                    'status_proses' => $oldStatusProses,
                    'is_dpo' => $wasDpo,
                ],
                dataBaru: [
                    'status_formal' => 'tidak_selesai',
                    'status_proses' => 'SP3',
                    'is_dpo' => false,
                    'dpo_dicabut_otomatis' => $wasDpo,
                    'nomor_sp3' => $sp3->nomor_surat,
                    'tanggal_sp3' => $sp3->tanggal_sp3->format('Y-m-d'),
                    'penyidik_utama_id' => $sp3->penyidik_utama_id,
                    'penyidik_anggota_ids' => $penyidikAnggotaIds,
                    'penandatangan_nama' => $sp3->penandatangan_nama,
                    'penandatangan_jabatan' => $sp3->penandatangan_jabatan,
                    'keterangan' => $sp3->keterangan,
                ],
                perkaraId: $berkas->perkara_id,
                berkasPerkaraId: $berkas->id,
                userId: $actorId
            );

            // Jika sebelumnya berstatus DPO aktif, catat jejak audit pencabutan DPO otomatis
            if ($activeDpo) {
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
                        'keterangan' => 'Pencabutan otomatis akibat penerbitan SP3',
                    ],
                    perkaraId: $berkas->perkara_id,
                    berkasPerkaraId: $berkas->id,
                    userId: $actorId
                );
            }

            return $sp3;
        });
    }
}
