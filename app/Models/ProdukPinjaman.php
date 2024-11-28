<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukPinjaman extends Model
{
    protected $table = 'produk_pinjamans';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'beaya_bunga_id',
        'denda',
        'keterangan'
    ];

    public function biayaBunga()
    {
        return $this->belongsTo(BiayaBungaPinjaman::class, 'beaya_bunga_id');
    }

    public function denda()
    {
        return $this->belongsTo(Denda::class, 'denda');
    }
}
