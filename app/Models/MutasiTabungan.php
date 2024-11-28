<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiTabungan extends Model
{
    protected $table = 'mutasi_tabungans';

    protected $fillable = [
        'id_transaksi',
        'jenis_transaksi',
        'jumlah_saldo',
        'tanggal_transaksi',
        'keterangan_transaksi'
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'jumlah_saldo' => 'decimal:2'
    ];

    public function tabungan(): BelongsTo
    {
        return $this->belongsTo(Tabungan::class, 'id_transaksi');
    }
}
