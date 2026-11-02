<?php

namespace App\Models;

use App\Enums\JenisTahap;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perkara extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'perkara';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nomor_laporan_kejadian',
        'tanggal_laporan_kejadian',
        'tahap_laporan_status',
        'laporan_locked_at',
        'laporan_finalized_by',
        'nomor_surat_perintah',
        'tanggal_surat_perintah',
        'tahap_sprindik_status',
        'sprindik_locked_at',
        'sprindik_finalized_by',
        'tahap_perkara_status',
        'perkara_locked_at',
        'perkara_finalized_by',
        'tkp_id',
        'penuntut_umum_instansi_id',
        'penuntut_umum_nama',
        'penuntut_umum_nip',
        'penuntut_umum_pangkat',
        'deskripsi',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_laporan_kejadian' => 'date',
            'laporan_locked_at' => 'datetime',
            'tanggal_surat_perintah' => 'date',
            'sprindik_locked_at' => 'datetime',
            'perkara_locked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tkp, $this>
     */
    public function tkp(): BelongsTo
    {
        return $this->belongsTo(Tkp::class, 'tkp_id');
    }

    /**
     * Instansi Kejaksaan Penuntut Umum (Poin 2 & Poin 8, ref: D-053)
     *
     * @return BelongsTo<InstansiHukum, $this>
     */
    public function penuntutUmumInstansi(): BelongsTo
    {
        return $this->belongsTo(InstansiHukum::class, 'penuntut_umum_instansi_id');
    }

    /**
     * Memeriksa apakah data Penuntut Umum telah diisi
     */
    public function hasPenuntutUmum(): bool
    {
        return $this->penuntut_umum_instansi_id !== null || ! empty($this->penuntut_umum_nama);
    }

    /**
     * Tim PPNS (Utama dan Anggota) pada Perkara (PRD F-08)
     *
     * @return BelongsToMany<User, $this>
     */
    public function ppnsUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'perkara_ppns', 'perkara_id', 'user_id')
            ->withPivot('peran');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function ppnsUtama(): BelongsToMany
    {
        return $this->ppnsUsers()->wherePivot('peran', 'utama');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function ppnsAnggota(): BelongsToMany
    {
        return $this->ppnsUsers()->wherePivot('peran', 'anggota');
    }

    /**
     * @return HasMany<Tersangka, $this>
     */
    public function tersangkas(): HasMany
    {
        return $this->hasMany(Tersangka::class, 'perkara_id');
    }

    /**
     * @return HasMany<BerkasPerkara, $this>
     */
    public function berkasPerkaras(): HasMany
    {
        return $this->hasMany(BerkasPerkara::class, 'perkara_id');
    }

    /**
     * @return HasMany<Dokumen, $this>
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'perkara_id');
    }

    /**
     * @return HasOne<Dokumen, $this>
     */
    public function dokumenLaporan(): HasOne
    {
        return $this->hasOne(Dokumen::class, 'perkara_id')
            ->where('kategori', 'lapkej')
            ->latestOfMany();
    }

    /**
     * @return HasOne<Dokumen, $this>
     */
    public function dokumenSprindik(): HasOne
    {
        return $this->hasOne(Dokumen::class, 'perkara_id')
            ->where('kategori', 'sprindik')
            ->latestOfMany();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function laporanFinalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'laporan_finalized_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sprindikFinalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sprindik_finalized_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function perkaraFinalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'perkara_finalized_by');
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'perkara_id');
    }

    /**
     * Apakah tahap Laporan Kejadian sudah difinalkan & terkunci permanen (F-17, F-18)
     */
    public function isLaporanLocked(): bool
    {
        return $this->tahap_laporan_status === 'final' || $this->laporan_locked_at !== null;
    }

    /**
     * Apakah tahap Sprindik sudah difinalkan & terkunci permanen (F-17, F-18)
     */
    public function isSprindikLocked(): bool
    {
        return $this->tahap_sprindik_status === 'final' || $this->sprindik_locked_at !== null;
    }

    /**
     * Apakah tahap Pembentukan Perkara (TKP, PPNS, Tersangka, Berkas, Pasal) sudah difinalkan & terkunci permanen (F-17, F-18)
     */
    public function isPerkaraLocked(): bool
    {
        return $this->tahap_perkara_status === 'final' || $this->perkara_locked_at !== null;
    }

    /**
     * Status proses Laporan Kejadian
     */
    public function getStatusLaporanAttribute(): string
    {
        if ($this->tahap_laporan_status === 'draft') {
            return 'Draft';
        }

        if ($this->isPerkaraLocked()) {
            return 'Perkara Terbentuk';
        }

        if (empty($this->nomor_surat_perintah)) {
            return 'Belum Dilanjutkan';
        }

        return 'Sprindik Tersedia';
    }

    /**
     * Status proses Sprindik
     */
    public function getStatusSprindikAttribute(): string
    {
        if ($this->tahap_sprindik_status === 'draft') {
            return 'Draft';
        }

        if ($this->isPerkaraLocked()) {
            return 'Perkara Terbentuk';
        }

        if ($this->tahap_perkara_status === 'draft') {
            return 'Draft Perkara';
        }

        return 'Belum Menjadi Perkara';
    }

    /**
     * Evaluasi status formal level perkara dengan urutan prioritas:
     * SP3 (tidak_selesai) > Selesai > DPO Aktif > Dalam Proses
     */
    public function getStatusFormalAttribute(): string
    {
        $berkasList = $this->relationLoaded('berkasPerkaras')
            ? $this->berkasPerkaras
            : $this->berkasPerkaras()->get();

        if ($berkasList->isEmpty()) {
            return 'dalam_proses';
        }

        // 1. Prioritas Tertinggi: Jika ada minimal 1 berkas yang tidak_selesai (SP3 / kedaluwarsa tahun)
        if ($berkasList->contains(fn ($b) => $b->status_formal === 'tidak_selesai')) {
            return 'tidak_selesai';
        }

        // 2. Jika seluruh berkas telah selesai
        $allSelesai = $berkasList->every(fn ($b) => $b->status_formal === 'selesai');
        if ($allSelesai) {
            return 'selesai';
        }

        // 3. Jika perkara belum selesai semua tapi memiliki berkas dengan status DPO aktif
        if ($berkasList->contains(fn ($b) => (bool) $b->is_dpo)) {
            return 'dpo_aktif';
        }

        // 4. Default: Dalam Proses
        return 'dalam_proses';
    }

    /**
     * Label tampilan status formal level perkara
     */
    public function getStatusFormalLabelAttribute(): string
    {
        return match ($this->status_formal) {
            'tidak_selesai' => 'Tidak Selesai / SP3',
            'selesai' => 'Selesai',
            'dpo_aktif' => 'DPO Aktif',
            default => 'Dalam Proses',
        };
    }

    /**
     * Kelas badge warna status formal level perkara
     */
    public function getStatusFormalBadgeClassAttribute(): string
    {
        return match ($this->status_formal) {
            'tidak_selesai' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
            'selesai' => 'bg-success-50 text-success-700 border-success-200 dark:bg-success-900/20 dark:text-success-300 dark:border-success-800',
            'dpo_aktif' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
            default => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
        };
    }

    /**
     * Evaluasi tahap efektif perkara (6 nilai tahapan) beserta persentasenya.
     * Khusus status SP3 / tidak_selesai, mengambil tahap final terakhir yang pernah dicapai.
     *
     * @return array{nama: string, persen: int, key: string}
     */
    public function getTahapTerakhirInfo(): array
    {
        $berkasList = $this->relationLoaded('berkasPerkaras')
            ? $this->berkasPerkaras
            : $this->berkasPerkaras()->with('tahapan')->get();

        // Kumpulkan semua tahapan final dari berkas
        $finalTahapTypes = collect();
        foreach ($berkasList as $berkas) {
            $tahapanList = $berkas->relationLoaded('tahapan') ? $berkas->tahapan : $berkas->tahapan()->get();
            foreach ($tahapanList as $t) {
                if ($t->status === 'final') {
                    $val = $t->jenis_tahap instanceof JenisTahap ? $t->jenis_tahap->value : $t->jenis_tahap;
                    $finalTahapTypes->push($val);
                }
            }
        }

        if ($finalTahapTypes->contains(JenisTahap::KEPUTUSAN_PENGADILAN->value)) {
            return ['nama' => 'Keputusan Pengadilan', 'persen' => 100, 'key' => 'keputusan_pengadilan'];
        }
        if ($finalTahapTypes->contains(JenisTahap::TAHAP_2->value)) {
            return ['nama' => 'Tahap II', 'persen' => 100, 'key' => 'tahap_2'];
        }
        if ($finalTahapTypes->contains(JenisTahap::P21->value)) {
            return ['nama' => 'P-21', 'persen' => 85, 'key' => 'p21'];
        }
        if ($finalTahapTypes->contains(JenisTahap::TAHAP_1->value)) {
            return ['nama' => 'Tahap I', 'persen' => 55, 'key' => 'tahap_1'];
        }
        if ($finalTahapTypes->contains(JenisTahap::SPDP->value)) {
            return ['nama' => 'SPDP', 'persen' => 15, 'key' => 'spdp'];
        }

        return ['nama' => 'Pra-SPDP', 'persen' => 0, 'key' => 'pra_spdp'];
    }

    /**
     * Metadata visual badge SLA untuk kolom Status & SLA
     *
     * @return array{label: string, badge_class: string}|null
     */
    public function getSlaBadgeInfo(): ?array
    {
        $statusFormal = $this->status_formal;

        // SP3 dan DPO Aktif tidak menampilkan badge SLA
        if ($statusFormal === 'tidak_selesai' || $statusFormal === 'dpo_aktif') {
            return null;
        }

        $berkasList = $this->relationLoaded('berkasPerkaras')
            ? $this->berkasPerkaras
            : $this->berkasPerkaras()->with('tahapan')->get();

        if ($statusFormal === 'selesai') {
            $hasLewatSla = $berkasList->contains(fn ($b) => $b->sla_status === 'selesai_lewat_sla');
            if ($hasLewatSla) {
                return [
                    'label' => 'Selesai Terlambat',
                    'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:border-gray-800',
                ];
            }

            return [
                'label' => 'Selesai Tepat Waktu',
                'badge_class' => 'bg-success-50 text-success-700 border-success-200 dark:bg-success-900/20 dark:text-success-300 dark:border-success-800',
            ];
        }

        // Status Dalam Proses
        $berkasDenganSpdp = $berkasList->filter(fn ($b) => $b->spdpTahap() !== null || $b->tanggal_spdp !== null);
        if ($berkasDenganSpdp->isEmpty()) {
            return [
                'label' => 'Tenggang Waktu belum berjalan',
                'badge_class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:border-gray-800',
            ];
        }

        // Hitung minimum sisa hari SLA
        $sisaList = $berkasDenganSpdp->map(fn ($b) => $b->sisa_hari_sla)->filter(fn ($v) => $v !== null);
        if ($sisaList->isEmpty()) {
            return [
                'label' => 'Tenggang Waktu belum berjalan',
                'badge_class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:border-gray-800',
            ];
        }

        $minSisa = $sisaList->min();

        if ($minSisa < 0) {
            return [
                'label' => 'Terlambat '.abs($minSisa).' hari',
                'badge_class' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
            ];
        }

        return [
            'label' => 'Sisa '.$minSisa.' hari',
            'badge_class' => $minSisa <= 7
                ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800'
                : 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
        ];
    }

    /**
     * Rekomendasi Tindakan Administrasi / Pelimpahan Perkara (Poin 10)
     *
     * @return array{label: string, deskripsi: string, badge_class: string, icon: string}
     */
    public function getRekomendasiTindakanInfo(): array
    {
        $statusFormal = $this->status_formal;

        // 1. Perkara Selesai
        if ($statusFormal === 'selesai') {
            return [
                'label' => 'Perkara Selesai',
                'deskripsi' => 'Seluruh berkas perkara telah diselesaikan.',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                'icon' => 'fi fi-rr-check-circle',
            ];
        }

        // 2. Perkara SP3 / Tidak Selesai
        if ($statusFormal === 'tidak_selesai') {
            return [
                'label' => 'Perkara Dihentikan (SP3)',
                'deskripsi' => 'Penyidikan telah dihentikan secara resmi.',
                'badge_class' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                'icon' => 'fi fi-rr-ban',
            ];
        }

        $berkasList = $this->relationLoaded('berkasPerkaras')
            ? $this->berkasPerkaras
            : $this->berkasPerkaras()->with('tahapan')->get();

        // 3. Terkendala DPO
        if ($berkasList->contains(fn ($b) => $b->is_dpo)) {
            return [
                'label' => 'Terkendala DPO',
                'deskripsi' => 'Pelimpahan ditunda karena Tersangka berstatus DPO Aktif.',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                'icon' => 'fi fi-rr-user-slash',
            ];
        }

        // 4. Melebihi SLA (SLA habis dan belum selesai)
        $today = Carbon::today(DashboardService::ZONA_WAKTU)->toDateString();
        $isLewatSla = $berkasList->contains(function ($b) use ($today) {
            $isAktif = empty($b->status_formal) || $b->status_formal === 'dalam_proses';

            return $isAktif && ! empty($b->sla_deadline) && $b->sla_deadline->toDateString() < $today;
        });

        if ($isLewatSla) {
            return [
                'label' => 'Prioritas: Melebihi SLA',
                'deskripsi' => 'Batas waktu tahapan terlampaui, prioritaskan tindak lanjut.',
                'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20',
                'icon' => 'fi fi-rr-alarm-exclamation',
            ];
        }

        // 5. Siap Pelimpahan Tahap II (Progress >= 55% / Tahap 1 / P-21 dan tidak DPO)
        $isSiapTahap2 = $berkasList->contains(function ($b) {
            $isAktif = empty($b->status_formal) || $b->status_formal === 'dalam_proses';

            return $isAktif && ! $b->is_dpo && in_array((int) $b->progress_persen, [55, 85], true);
        });

        if ($isSiapTahap2) {
            return [
                'label' => 'Siap Pelimpahan Tahap II',
                'deskripsi' => 'Berkas lengkap, siap penyerahan Tersangka & Barang Bukti.',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
                'icon' => 'fi fi-rr-paper-plane',
            ];
        }

        // 6. Siap Pelimpahan Tahap I (Progress == 15% / SPDP final dan tidak DPO)
        $isSiapTahap1 = $berkasList->contains(function ($b) {
            $isAktif = empty($b->status_formal) || $b->status_formal === 'dalam_proses';

            return $isAktif && ! $b->is_dpo && (int) $b->progress_persen === 15;
        });

        if ($isSiapTahap1) {
            return [
                'label' => 'Siap Pelimpahan Tahap I',
                'deskripsi' => 'SPDP final, berkas perkara siap dilimpahkan ke Kejaksaan.',
                'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20',
                'icon' => 'fi fi-rr-document-signed',
            ];
        }

        // 7. Default: Dalam Proses Normal
        return [
            'label' => 'Dalam Proses Normal',
            'deskripsi' => 'Penyidikan berjalan sesuai tahapan.',
            'badge_class' => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
            'icon' => 'fi fi-rr-time-forward',
        ];
    }

    /**
     * Query scope filter status formal perkara
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFilterStatusFormal(Builder $query, string $status): Builder
    {
        $statusNorm = strtolower(trim($status));

        if (in_array($statusNorm, ['tidak_selesai', 'tidak selesai', 'tidak selesai (sp3)', 'sp3'], true)) {
            return $query->whereHas('berkasPerkaras', fn ($q) => $q->where('status_formal', 'tidak_selesai'));
        }

        if (in_array($statusNorm, ['selesai'], true)) {
            return $query->whereDoesntHave('berkasPerkaras', fn ($q) => $q->where('status_formal', 'tidak_selesai'))
                ->whereHas('berkasPerkaras', fn ($q) => $q->where('status_formal', 'selesai'))
                ->whereDoesntHave('berkasPerkaras', fn ($b) => $b->whereNull('status_formal')->orWhere('status_formal', '!=', 'selesai'));
        }

        if (in_array($statusNorm, ['dpo_aktif', 'dpo aktif', 'dpo'], true)) {
            return $query->whereDoesntHave('berkasPerkaras', fn ($q) => $q->where('status_formal', 'tidak_selesai'))
                ->whereHas('berkasPerkaras', fn ($q) => $q->where('is_dpo', true))
                ->where(function (Builder $q) {
                    $q->whereHas('berkasPerkaras', fn ($b) => $b->whereNull('status_formal')->orWhere('status_formal', '!=', 'selesai'));
                });
        }

        if (in_array($statusNorm, ['dalam_proses', 'dalam proses'], true)) {
            return $query->whereDoesntHave('berkasPerkaras', fn ($q) => $q->where('status_formal', 'tidak_selesai'))
                ->whereDoesntHave('berkasPerkaras', fn ($q) => $q->where('is_dpo', true))
                ->where(function (Builder $q) {
                    $q->whereDoesntHave('berkasPerkaras')
                        ->orWhereHas('berkasPerkaras', fn ($b) => $b->whereNull('status_formal')->orWhere('status_formal', '!=', 'selesai'));
                });
        }

        return $query;
    }

    /**
     * Query scope filter tahap terakhir perkara
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFilterTahapTerakhir(Builder $query, string $tahap): Builder
    {
        $tahapNorm = strtolower(trim($tahap));

        if (in_array($tahapNorm, ['keputusan_pengadilan', 'keputusan pengadilan', 'putusan'], true)) {
            return $query->whereHas('berkasPerkaras.tahapan', fn ($t) => $t->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN->value)->where('status', 'final'));
        }

        if (in_array($tahapNorm, ['tahap_2', 'tahap 2', 'tahap ii'], true)) {
            return $query->whereHas('berkasPerkaras.tahapan', fn ($t) => $t->where('jenis_tahap', JenisTahap::TAHAP_2->value)->where('status', 'final'))
                ->whereDoesntHave('berkasPerkaras.tahapan', fn ($t) => $t->where('jenis_tahap', JenisTahap::KEPUTUSAN_PENGADILAN->value)->where('status', 'final'));
        }

        if (in_array($tahapNorm, ['p21', 'p-21'], true)) {
            return $query->whereHas('berkasPerkaras.tahapan', fn ($t) => $t->where('jenis_tahap', JenisTahap::P21->value)->where('status', 'final'))
                ->whereDoesntHave('berkasPerkaras.tahapan', fn ($t) => $t->whereIn('jenis_tahap', [JenisTahap::TAHAP_2->value, JenisTahap::KEPUTUSAN_PENGADILAN->value])->where('status', 'final'));
        }

        if (in_array($tahapNorm, ['tahap_1', 'tahap 1', 'tahap i'], true)) {
            return $query->whereHas('berkasPerkaras.tahapan', fn ($t) => $t->where('jenis_tahap', JenisTahap::TAHAP_1->value)->where('status', 'final'))
                ->whereDoesntHave('berkasPerkaras.tahapan', fn ($t) => $t->whereIn('jenis_tahap', [JenisTahap::P21->value, JenisTahap::TAHAP_2->value, JenisTahap::KEPUTUSAN_PENGADILAN->value])->where('status', 'final'));
        }

        if (in_array($tahapNorm, ['spdp'], true)) {
            return $query->whereHas('berkasPerkaras.tahapan', fn ($t) => $t->where('jenis_tahap', JenisTahap::SPDP->value)->where('status', 'final'))
                ->whereDoesntHave('berkasPerkaras.tahapan', fn ($t) => $t->whereIn('jenis_tahap', [JenisTahap::TAHAP_1->value, JenisTahap::P21->value, JenisTahap::TAHAP_2->value, JenisTahap::KEPUTUSAN_PENGADILAN->value])->where('status', 'final'));
        }

        if (in_array($tahapNorm, ['pra_spdp', 'pra-spdp'], true)) {
            return $query->whereDoesntHave('berkasPerkaras.tahapan', fn ($t) => $t->where('jenis_tahap', JenisTahap::SPDP->value)->where('status', 'final'));
        }

        return $query;
    }
}
