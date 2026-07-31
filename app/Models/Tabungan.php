<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tabungan extends Model
{
    use LogsActivity;

    protected $table = 'tabungans';

    protected $appends = ['saldo_akhir'];

    protected $fillable = [
        'no_tabungan',
        'id_profile',
        'produk_tabungan',
        'saldo',
        'tanggal_buka_rekening',
        'status_rekening',
        'notes',
    ];

    protected $casts = [
        'tanggal_buka_rekening' => 'datetime',
        'saldo' => 'decimal:2',
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

    public function getSaldoAkhirAttribute(): float
    {
        $saldoAwal = (float) $this->saldo;
        $totalDebit = (float) $this->transaksi()->where('jenis_transaksi', 'debit')->sum('jumlah');
        $totalKredit = (float) $this->transaksi()->where('jenis_transaksi', 'kredit')->sum('jumlah');

        return $saldoAwal + ($totalDebit - $totalKredit);
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
                'status_rekening',
                'notes',
            ]);
    }
}
