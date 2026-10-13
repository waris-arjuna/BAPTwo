<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tersangka extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'tersangka';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'perkara_id',
        'nama',
        'alias',
        'bin_binti',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'pekerjaan',
        'kewarganegaraan',
        'alamat_id',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
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
     * 1 Tersangka = 1 Berkas Perkara (PRD F-02)
     *
     * @return HasOne<BerkasPerkara, $this>
     */
    public function berkasPerkara(): HasOne
    {
        return $this->hasOne(BerkasPerkara::class, 'tersangka_id');
    }

    /**
     * @return BelongsTo<Alamat, $this>
     */
    public function alamat(): BelongsTo
    {
        return $this->belongsTo(Alamat::class, 'alamat_id');
    }
}
