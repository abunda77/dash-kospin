<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Events\TransaksiTabunganCreated;

class TransaksiTabungan extends Model
{
    use LogsActivity;

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

    const JENIS_SETORAN = 'debit';
    const JENIS_PENARIKAN = 'kredit';

    protected $dispatchesEvents = [
        'created' => TransaksiTabunganCreated::class,
    ];

    public function tabungan()
    {
        return $this->belongsTo(Tabungan::class, 'id_tabungan');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'kode_teller', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_tabungan',
                'jenis_transaksi',
                'jumlah',
                'tanggal_transaksi',
                'keterangan',
                'kode_transaksi',
                'kode_teller'
            ]);
    }
}
