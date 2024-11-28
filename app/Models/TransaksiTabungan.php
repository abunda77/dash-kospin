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
        'keterangan',
        'kode_transaksi',
        'kode_teller'
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal_transaksi' => 'datetime'
    ];

    const JENIS_SETORAN = 'kredit';
    const JENIS_PENARIKAN = 'debit';

    public function tabungan()
    {
        return $this->belongsTo(Tabungan::class, 'id_tabungan');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'kode_teller', 'id');
    }
}
