<?php

namespace App\Models;

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
     * @return HasMany<BerkasPerkara, $this>
     */
    public function berkasPerkara(): HasMany
    {
        return $this->berkasPerkaras();
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
}
