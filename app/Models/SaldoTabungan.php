<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoTabungan extends Model
{
    protected $table = 'saldo_tabungans';

    protected $fillable = [
        'id_tabungan',
        'saldo_akhir'
    ];

    protected $casts = [
        'saldo_akhir' => 'decimal:2'
    ];

    public function tabungan()
    {
        return $this->belongsTo(Tabungan::class, 'id_tabungan');
    }
}
