<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Panggol extends Model
{
    use SoftDeletes;

    protected $table = 'panggol';

    protected $fillable = [
        'pangkat',
        'golongan',
    ];

    public function label(): string
    {
        return "{$this->pangkat} ({$this->golongan})";
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'panggol_id');
    }
}
