<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPinjaman extends Model
{
    protected $table = 'transaksi_pinjamans';

    protected $fillable = [
        'tanggal_pembayaran',
        'pinjaman_id',
        'total_pembayaran',
        'sisa_pinjaman',
        'status_pembayaran'
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'total_pembayaran' => 'decimal:2',
        'sisa_pinjaman' => 'decimal:2',
        'status_pembayaran' => 'string'
    ];

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id', 'id_pinjaman');
    }
}
