<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasal extends Model
{
    use SoftDeletes;

    protected $table = 'pasal';

    protected $fillable = [
        'nama_pasal',
        'pasal',
        'nomor',
        'tahun',
        'isi',
        'status',
        'created_by',
        'updated_by',
    ];

    public function label(): string
    {
        $uu = trim("{$this->nama_pasal} No. {$this->nomor} Tahun {$this->tahun}");

        return "{$this->pasal} ({$uu})";
    }
}
