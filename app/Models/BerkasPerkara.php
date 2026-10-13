<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BerkasPerkara extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'berkas_perkara';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'perkara_id',
        'tersangka_id',
        'nomor_berkas',
        'tanggal_spdp',
        'status_formal',
        'status_proses',
        'progress_persen',
        'is_dpo',
        'sla_deadline',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_spdp' => 'date',
            'sla_deadline' => 'date',
            'progress_persen' => 'integer',
            'is_dpo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Perkara, $this>
     */
    public function perkara(): BelongsTo
    {
        return $this->belongsTo(Perkara::class, 'perkara_id');
    }

    /**
     * @return BelongsTo<Tersangka, $this>
     */
    public function tersangka(): BelongsTo
    {
        return $this->belongsTo(Tersangka::class, 'tersangka_id');
    }

    /**
     * Pasal berlapis pada Berkas Perkara (PRD F-04)
     *
     * @return BelongsToMany<Pasal, $this>
     */
    public function pasals(): BelongsToMany
    {
        return $this->belongsToMany(Pasal::class, 'berkas_pasal', 'berkas_perkara_id', 'pasal_id');
    }

    /**
     * @return HasMany<Dokumen, $this>
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'berkas_perkara_id');
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'berkas_perkara_id');
    }

    /**
     * Label tampilan status formal (PRD F-20, F-21: null = "Dalam Proses")
     */
    public function getStatusFormalLabelAttribute(): string
    {
        return match ($this->status_formal) {
            'selesai' => 'Selesai',
            'tidak_selesai' => 'Tidak Selesai',
            default => 'Dalam Proses',
        };
    }
}
