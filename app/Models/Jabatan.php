<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jabatan extends Model
{
    use SoftDeletes;

    protected $table = 'jabatan';

    protected $fillable = [
        'nama_jabatan',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'jabatan_id');
    }
}
