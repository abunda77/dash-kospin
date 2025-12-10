<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tabungan extends Model
{
    use LogsActivity;

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
        return $this->belongsTo(Profile::class, 'id_profile');
    }

    public function produkTabungan()
    {
        return $this->belongsTo(ProdukTabungan::class, 'produk_tabungan');
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiTabungan::class, 'id_tabungan');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'no_tabungan',
                'id_profile',
                'produk_tabungan',
                'saldo',
                'tanggal_buka_rekening',
                'status_rekening'
            ]);
    }
}
