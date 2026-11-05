<?php

namespace App\Services;

use App\Models\Alamat;
use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\Tersangka;
use App\Models\Tkp;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BerkasPerkaraService
{
    public function __construct(
        protected AlamatService $alamatService,
        protected AuditTrailService $auditTrail
    ) {}

    /**
     * Menyimpan atau mengupdate entitas TKP dan Alamat terkait untuk Perkara.
     */
    public function syncTkp(Perkara $perkara, array $data, User $user): ?int
    {
        $tkpId = $perkara->tkp_id;
        $hasTkpInput = ! empty($data['tkp_nama'])
            || ! empty($data['tkp_provinsi_kode'])
            || ! empty($data['tkp_alamat_detail'])
            || ! empty($data['tkp_klasifikasi'])
            || ! empty($data['tkp_legalitas'])
            || ! empty($data['tkp_modus']);

        if (! $hasTkpInput && $tkpId === null) {
            return null;
        }

        $alamatPayload = $this->alamatService->resolveAlamatPayload($data, $perkara->tkp?->alamat, 'tkp_');

        if ($perkara->tkp && $perkara->tkp->alamat) {
            $perkara->tkp->alamat->update($alamatPayload);
            $alamatId = $perkara->tkp->alamat_id;
        } else {
            $alamat = Alamat::create($alamatPayload);
            $alamatId = $alamat->id;
        }

        $tkpPayload = [
            'alamat_id' => $alamatId,
            'klasifikasi' => $data['tkp_klasifikasi'] ?? $perkara->tkp?->klasifikasi,
            'legalitas' => $data['tkp_legalitas'] ?? $perkara->tkp?->legalitas,
            'modus' => $data['tkp_modus'] ?? $perkara->tkp?->modus,
            'updated_by' => $user->id,
        ];

        if ($perkara->tkp) {
            $perkara->tkp->update($tkpPayload);

            return $perkara->tkp->id;
        }

        $tkpPayload['created_by'] = $user->id;
        $tkp = Tkp::create($tkpPayload);

        return $tkp->id;
    }

    /**
     * Menyinkronkan tim PPNS Utama dan Anggota untuk Perkara.
     */
    public function syncPpns(Perkara $perkara, array $data): void
    {
        $ppnsSync = [];
        $utamaId = ! empty($data['ppns_utama_id']) ? (int) $data['ppns_utama_id'] : null;
        if ($utamaId !== null) {
            $ppnsSync[$utamaId] = ['peran' => 'utama'];
        }

        $anggotaIds = is_array($data['ppns_anggota_ids'] ?? null) ? $data['ppns_anggota_ids'] : [];
        foreach ($anggotaIds as $anggotaId) {
            $id = (int) $anggotaId;
            if ($id > 0 && $id !== $utamaId) {
                $ppnsSync[$id] = ['peran' => 'anggota'];
            }
        }

        if ($ppnsSync !== [] || array_key_exists('ppns_utama_id', $data)) {
            $perkara->ppnsUsers()->sync($ppnsSync);
        }
    }

    /**
     * Menyimpan Tersangka, Alamat, 1 Berkas Perkara per Tersangka, dan Relasi Pasal Berlapis.
     *
     * @return array<int, int> Daftar ID tersangka yang disimpan
     */
    public function syncTersangkaAndBerkas(Perkara $perkara, array $data, User $user): array
    {
        if (! is_array($data['tersangka'] ?? null)) {
            return [];
        }

        $keptTersangkaIds = [];

        foreach ($data['tersangka'] as $tskData) {
            $existingId = ! empty($tskData['id']) ? (int) $tskData['id'] : null;
            $tersangka = null;

            if ($existingId !== null) {
                $tersangka = Tersangka::where('perkara_id', $perkara->id)->find($existingId);
            }

            // Simpan / Update Alamat Tersangka (PRD F-03)
            $hasTskAlamat = ! empty($tskData['provinsi_kode'])
                || ! empty($tskData['alamat_detail'])
                || ! empty($tskData['kabupaten_kode'])
                || ! empty($tskData['kecamatan_kode'])
                || ! empty($tskData['desa_kode']);

            $tskAlamatId = $tersangka?->alamat_id;

            if ($hasTskAlamat || $tskAlamatId !== null) {
                $existingTskAlamat = $tskAlamatId ? Alamat::find($tskAlamatId) : null;
                $alamatPayload = $this->alamatService->resolveAlamatPayload($tskData, $existingTskAlamat);

                if ($existingTskAlamat instanceof Alamat) {
                    $existingTskAlamat->update($alamatPayload);
                } else {
                    $newAlamat = Alamat::create($alamatPayload);
                    $tskAlamatId = $newAlamat->id;
                }
            }

            $tskPayload = [
                'perkara_id' => $perkara->id,
                'nama' => $tskData['nama'] ?? null,
                'alias' => $tskData['alias'] ?? null,
                'bin_binti' => $tskData['bin_binti'] ?? null,
                'nik' => $tskData['nik'] ?? null,
                'tempat_lahir' => $tskData['tempat_lahir'] ?? null,
                'tanggal_lahir' => ($tskData['tanggal_lahir'] ?? null) ?: null,
                'jenis_kelamin' => ($tskData['jenis_kelamin'] ?? null) ?: null,
                'agama' => ($tskData['agama'] ?? null) ?: null,
                'pekerjaan' => $tskData['pekerjaan'] ?? null,
                'kewarganegaraan' => $tskData['kewarganegaraan'] ?? 'WNI',
                'alamat_id' => $tskAlamatId,
                'updated_by' => $user->id,
            ];

            if ($tersangka instanceof Tersangka) {
                $tersangka->update($tskPayload);
            } else {
                $tskPayload['created_by'] = $user->id;
                $tersangka = Tersangka::create($tskPayload);
            }

            $keptTersangkaIds[] = $tersangka->id;

            // 1 Tersangka = 1 Berkas Perkara (UNIQUE tersangka_id, Nomor Berkas manual tanpa pola - F-02, F-33)
            $berkas = BerkasPerkara::withTrashed()
                ->where('tersangka_id', $tersangka->id)
                ->first();

            $nomorBerkas = ! empty($tskData['nomor_berkas'])
                ? $tskData['nomor_berkas']
                : ($perkara->nomor_laporan_kejadian ?: 'BP-'.$perkara->id);

            if ($berkas instanceof BerkasPerkara) {
                if ($berkas->trashed()) {
                    $berkas->restore();
                }
                $berkas->update([
                    'perkara_id' => $perkara->id,
                    'nomor_berkas' => $nomorBerkas,
                    'updated_by' => $user->id,
                ]);
            } else {
                $berkas = BerkasPerkara::create([
                    'perkara_id' => $perkara->id,
                    'tersangka_id' => $tersangka->id,
                    'nomor_berkas' => $nomorBerkas,
                    'status_formal' => null,
                    'status_proses' => 'Baru',
                    'progress_persen' => 0,
                    'is_dpo' => false,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }

            // Sinkronisasi Pasal Berlapis per Berkas Perkara (F-04)
            $pasalIds = array_values(array_filter(
                array_map('intval', is_array($tskData['pasal_ids'] ?? null) ? $tskData['pasal_ids'] : []),
                fn (int $id): bool => $id > 0
            ));
            $berkas->pasals()->sync($pasalIds);
        }

        // Soft delete tersangka & berkas yang dihapus dari daftar saat masih draft
        $removedTersangkas = Tersangka::where('perkara_id', $perkara->id)
            ->whereNotIn('id', $keptTersangkaIds)
            ->get();

        foreach ($removedTersangkas as $removed) {
            $removed->berkasPerkara?->delete();
            $removed->delete();
        }

        return $keptTersangkaIds;
    }

    /**
     * Orkestrasi pembentukan berkas perkara pra-SPDP (TKP, PPNS, Tersangka, Berkas Perkara, dan Pasal).
     */
    public function savePerkaraBerkas(Perkara $perkara, array $data, User $user): Perkara
    {
        if ($perkara->isPerkaraLocked()) {
            abort(403, 'Data pembentukan perkara dan berkas pra-SPDP sudah difinalkan dan terkunci secara permanen.');
        }

        return DB::transaction(function () use ($perkara, $data, $user) {
            $oldData = $perkara->load(['tkp.alamat', 'ppnsUsers', 'tersangkas.alamat', 'tersangkas.berkasPerkara.pasals'])->toArray();
            $isFinal = ($data['action_type'] ?? 'lanjut') === 'lanjut';

            // 1. Simpan / Update Alamat & TKP (F-07, F-36, F-37)
            $tkpId = $this->syncTkp($perkara, $data, $user);

            // 2. Sinkronisasi Tim PPNS Utama & Anggota (F-08)
            $this->syncPpns($perkara, $data);

            // 3. Simpan Tersangka -> 1 Berkas Perkara per Tersangka -> Pasal Berlapis (F-02, F-03, F-04, F-33)
            $this->syncTersangkaAndBerkas($perkara, $data, $user);

            // 4. Update status tahapan perkara
            $perkara->update([
                'tkp_id' => $tkpId,
                'penuntut_umum_instansi_id' => ! empty($data['penuntut_umum_instansi_id']) ? (int) $data['penuntut_umum_instansi_id'] : null,
                'penuntut_umum_nama' => ! empty($data['penuntut_umum_nama']) ? trim($data['penuntut_umum_nama']) : null,
                'penuntut_umum_nip' => ! empty($data['penuntut_umum_nip']) ? trim($data['penuntut_umum_nip']) : null,
                'tahap_laporan_status' => 'final',
                'laporan_locked_at' => $perkara->laporan_locked_at ?? now(),
                'laporan_finalized_by' => $perkara->laporan_finalized_by ?? $user->id,
                'tahap_sprindik_status' => 'final',
                'sprindik_locked_at' => $perkara->sprindik_locked_at ?? now(),
                'sprindik_finalized_by' => $perkara->sprindik_finalized_by ?? $user->id,
                'tahap_perkara_status' => $isFinal ? 'final' : 'draft',
                'perkara_locked_at' => $isFinal ? now() : null,
                'perkara_finalized_by' => $isFinal ? $user->id : null,
                'updated_by' => $user->id,
            ]);

            $freshPerkara = $perkara->fresh(['tkp.alamat', 'ppnsUsers', 'tersangkas.berkasPerkara.pasals']);

            $this->auditTrail->log(
                aksi: $isFinal ? 'finalize_perkara_berkas' : 'save_draft_perkara_berkas',
                tabel: 'perkara',
                recordId: $perkara->id,
                dataLama: $oldData,
                dataBaru: $freshPerkara?->toArray(),
                perkaraId: $perkara->id,
                userId: $user->id
            );

            return $freshPerkara ?? $perkara;
        });
    }
}
