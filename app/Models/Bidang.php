<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bidang extends Model
{
    use SoftDeletes;

    protected $table = 'bidang';

    protected $fillable = [
        'nama_bidang',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'bidang_id');
    }
}
