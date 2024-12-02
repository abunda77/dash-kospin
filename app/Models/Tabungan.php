<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tabungan extends Model
{
    protected $table = 'tabungans';

    protected $fillable = [
        'no_tabungan',
        'id_profile',
        'produk_tabungan',
        'saldo',
        'tanggal_buka_rekening',
        'status_rekening'
    ];

    protected $casts = [
        'tanggal_buka_rekening' => 'datetime',
        'saldo' => 'decimal:2'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'id_profile', 'id');
    }

    public function produkTabungan()
    {
        return $this->belongsTo(ProdukTabungan::class, 'produk_tabungan');
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiTabungan::class, 'id_tabungan');
    }
}
