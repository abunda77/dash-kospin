<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukTabungan extends Model
{
    protected $table = 'produk_tabungans';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'jenis_tabungan_id',
        'bunga_beaya_id',
        'keterangan'
    ];

    public function jenisTabungan()
    {
        return $this->belongsTo(JenisTabungan::class, 'jenis_tabungan_id');
    }

    public function beayaTabungan()
    {
        return $this->belongsTo(BeayaTabungan::class, 'bunga_beaya_id');
    }

    public function tabungan()
    {
        return $this->hasMany(Tabungan::class, 'produk_tabungans');
    }
}
