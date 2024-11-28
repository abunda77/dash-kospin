<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiTabungan extends Model
{
    protected $table = 'transaksi_tabungans';

    protected $fillable = [
        'id_tabungan',
        'jenis_transaksi',
        'jumlah',
        'tanggal_transaksi',
        'keterangan'
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal_transaksi' => 'datetime'
    ];

    const JENIS_SETORAN = 'setoran';
    const JENIS_PENARIKAN = 'penarikan';

    public function tabungan()
    {
        return $this->belongsTo(Tabungan::class, 'id_tabungan');
    }
}
