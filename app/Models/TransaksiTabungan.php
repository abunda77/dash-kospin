<?php

namespace App\Models;

use App\Events\TransaksiTabunganCreated;
use App\Events\TransaksiTabunganDeleted;
use App\Events\TransaksiTabunganUpdated;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TransaksiTabungan extends Model
{
    use LogsActivity;

    protected $table = 'transaksi_tabungans';

    protected $fillable = [
        'id_tabungan',
        'setoran_id',
        'penarikan_id',
        'jenis_transaksi',
        'jumlah',
        'tanggal_transaksi',
        'keterangan',
        'kode_transaksi',
        'kode_teller',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal_transaksi' => 'datetime',
    ];

    const JENIS_SETORAN = 'debit';

    const JENIS_PENARIKAN = 'kredit';

    protected $dispatchesEvents = [
        'created' => TransaksiTabunganCreated::class,
        'updated' => TransaksiTabunganUpdated::class,
        'deleted' => TransaksiTabunganDeleted::class,
    ];

    public function tabungan()
    {
        return $this->belongsTo(Tabungan::class, 'id_tabungan');
    }

    public function setoran()
    {
        return $this->belongsTo(SetoranTabungan::class, 'setoran_id');
    }

    public function penarikan()
    {
        return $this->belongsTo(PenarikanTabungan::class, 'penarikan_id');
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
                'setoran_id',
                'jenis_transaksi',
                'jumlah',
                'tanggal_transaksi',
                'keterangan',
                'kode_transaksi',
                'kode_teller',
            ]);
    }
}
