<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sp3 extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'sp3';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'berkas_perkara_id',
        'nomor_surat',
        'tanggal_sp3',
        'alasan',
        'keterangan',
        'dokumen_id',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_sp3' => 'date',
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
}
