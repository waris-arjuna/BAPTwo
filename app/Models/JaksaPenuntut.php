<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JaksaPenuntut extends Model
{
    use SoftDeletes;

    protected $table = 'jaksa_penuntut';

    protected $fillable = [
        'instansi_hukum_id',
        'nama_jaksa',
        'nip',
        'pangkat',
        'created_by',
        'updated_by',
    ];

    public function instansiHukum(): BelongsTo
    {
        return $this->belongsTo(InstansiHukum::class, 'instansi_hukum_id');
    }
}
