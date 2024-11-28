<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pinjaman extends Model
{
    protected $table = 'pinjamans';

    protected $primaryKey = 'id_pinjaman';

    protected $fillable = [
        'no_pinjaman',
        'profile_id',
        'produk_pinjaman',
        'jumlah_pinjaman',
        'suku_bunga',
        'tanggal_pinjaman',
        'jangka_waktu',
        'jangka_waktu_satuan',
        'status_pinjaman'
    ];

    protected $casts = [
        'jumlah_pinjaman' => 'decimal:2',
        'suku_bunga' => 'decimal:2',
        'tanggal_pinjaman' => 'date',
        'jangka_waktu_satuan' => 'string',
        'status_pinjaman' => 'string'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function produkPinjaman()
    {
        return $this->belongsTo(ProdukPinjaman::class, 'produk_pinjaman');
    }
}
