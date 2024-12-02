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
        'produk_pinjaman_id',
        'jumlah_pinjaman',
        'beaya_bunga_pinjaman_id',
        'tanggal_pinjaman',
        'jangka_waktu',
        'tanggal_jatuh_tempo',
        'jangka_waktu_satuan',
        'status_pinjaman'
    ];

    protected $casts = [
        'jumlah_pinjaman' => 'decimal:2',
        'suku_bunga' => 'decimal:2',
        'tanggal_pinjaman' => 'date',
        'jangka_waktu' => 'integer',
        'tanggal_jatuh_tempo' => 'date',
        'jangka_waktu_satuan' => 'string',
        'status_pinjaman' => 'string'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function produkPinjaman()
    {
        return $this->belongsTo(ProdukPinjaman::class, 'produk_pinjaman_id');
    }

    public function biayaBungaPinjaman()
    {
        return $this->belongsTo(BiayaBungaPinjaman::class, 'beaya_bunga_pinjaman_id');
    }
}
