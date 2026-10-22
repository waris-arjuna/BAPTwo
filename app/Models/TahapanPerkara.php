<?php

namespace App\Models;

use App\Enums\JenisTahap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahapanPerkara extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tahapan_perkara';

    protected $fillable = [
        'berkas_perkara_id',
        'jenis_tahap',
        'nomor_surat',
        'tanggal_surat',
        'catatan',
        'pengadilan_instansi_id',
        'amar_putusan',
        'status',
        'finalized_at',
        'finalized_by',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_tahap' => JenisTahap::class,
            'tanggal_surat' => 'date',
            'finalized_at' => 'datetime',
        ];
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function isLocked(): bool
    {
        return $this->isFinal();
    }

    /**
     * @return BelongsTo<BerkasPerkara, $this>
     */
    public function berkasPerkara(): BelongsTo
    {
        return $this->belongsTo(BerkasPerkara::class, 'berkas_perkara_id');
    }

    /**
     * Instansi Pengadilan pada Keputusan Pengadilan (Poin 8, ref: D-053)
     *
     * @return BelongsTo<InstansiHukum, $this>
     */
    public function pengadilanInstansi(): BelongsTo
    {
        return $this->belongsTo(InstansiHukum::class, 'pengadilan_instansi_id');
    }

    /**
     * @return HasMany<Dokumen, $this>
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'tahapan_perkara_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
