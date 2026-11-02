<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dpo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'dpo';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'berkas_perkara_id',
        'nomor_surat',
        'tanggal_penetapan',
        'tanggal_pencabutan',
        'status',
        'keterangan',
        'alasan_pencabutan',
        'dokumen_id',
        'nomor_surat_penangkapan',
        'tanggal_surat_penangkapan',
        'tanggal_penangkapan',
        'nomor_surat_penahanan',
        'tanggal_penahanan',
        'dokumen_penangkapan_id',
        'keterangan_penangkapan',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_penetapan' => 'date',
            'tanggal_pencabutan' => 'date',
            'tanggal_surat_penangkapan' => 'date',
            'tanggal_penangkapan' => 'date',
            'tanggal_penahanan' => 'date',
        ];
    }

    /**
     * @return BelongsTo<BerkasPerkara, $this>
     */
    public function berkasPerkara(): BelongsTo
    {
        return $this->belongsTo(BerkasPerkara::class, 'berkas_perkara_id');
    }

    /**
     * @return BelongsTo<Dokumen, $this>
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    /**
     * @return BelongsTo<Dokumen, $this>
     */
    public function dokumenPenangkapan(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_penangkapan_id');
    }

    /**
     * Daftar seluruh berkas dokumen pendukung penangkapan & penahanan DPO (multi-file)
     *
     * @return HasMany<Dokumen, $this>
     */
    public function dokumenPenangkapanList(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'dpo_id');
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
     * Memeriksa apakah status DPO masih aktif
     */
    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}
