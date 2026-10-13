<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'nomor_surat_perintah',
        'tanggal_surat_perintah',
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
            'tanggal_surat_perintah' => 'date',
        ];
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
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'perkara_id');
    }

    /**
     * Status proses Laporan Kejadian
     */
    public function getStatusLaporanAttribute(): string
    {
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
        return 'Belum Menjadi Perkara';
    }
}
