<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Panggol extends Model
{
    protected $table = 'panggol';

    protected $fillable = [
        'pangkat',
        'golongan',
    ];

    public function label(): string
    {
        return "{$this->pangkat} ({$this->golongan})";
    }
}
