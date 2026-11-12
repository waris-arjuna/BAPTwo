<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstansiHukum extends Model
{
    use SoftDeletes;

    protected $table = 'instansi_hukum';

    protected $fillable = [
        'jenis',
        'nama',
        'wilayah',
        'created_by',
        'updated_by',
    ];

    public function jenisLabel(): string
    {
        return match ($this->jenis) {
            'kejaksaan' => __('Kejaksaan'),
            'pengadilan' => __('Pengadilan'),
            default => '-',
        };
    }
}
