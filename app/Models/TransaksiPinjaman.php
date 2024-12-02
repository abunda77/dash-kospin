<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPinjaman extends Model
{
    protected $table = 'transaksi_pinjamans';

    protected $fillable = [
        'tanggal_pembayaran',
        'pinjaman_id',
        'angsuran_pokok',
        'angsuran_bunga',
        'denda',
        'total_pembayaran',
        'sisa_pinjaman',
        'status_pembayaran',
        'angsuran_ke',
        'hari_terlambat'
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'angsuran_pokok' => 'decimal:2',
        'angsuran_bunga' => 'decimal:2',
        'denda' => 'decimal:2',
        'total_pembayaran' => 'decimal:2',
        'sisa_pinjaman' => 'decimal:2',
        'status_pembayaran' => 'string',
        'angsuran_ke' => 'integer',
        'hari_terlambat' => 'integer'
    ];

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id', 'id_pinjaman');
    }
}
