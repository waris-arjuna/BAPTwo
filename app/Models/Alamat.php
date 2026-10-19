<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    protected $table = 'alamat';

    protected $fillable = [
        'nama',
        'provinsi_kode',
        'provinsi_nama',
        'kabupaten_kode',
        'kabupaten_nama',
        'kecamatan_kode',
        'kecamatan_nama',
        'desa_kode',
        'desa_nama',
        'rt',
        'rw',
        'alamat_detail',
    ];

    /**
     * Accessor untuk alamat lengkap format utuh (detail jalan, RT/RW, Desa, Kec, Kab, Prov)
     */
    public function getAlamatLengkapAttribute(): string
    {
        return $this->formatted();
    }

    public function formatted(): string
    {
        $parts = array_filter([
            $this->alamat_detail,
            ($this->rt || $this->rw) ? 'RT '.($this->rt ?: '-').'/RW '.($this->rw ?: '-') : null,
            $this->desa_nama ? 'Desa/Kel. '.$this->desa_nama : null,
            $this->kecamatan_nama ? 'Kec. '.$this->kecamatan_nama : null,
            $this->kabupaten_nama,
            $this->provinsi_nama,
        ]);

        return $parts !== [] ? implode(', ', $parts) : '-';
    }
}
