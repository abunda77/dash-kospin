<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MakanBergizisGratis extends Model
{
    use LogsActivity;

    protected $table = 'makan_bergizis_gratis';

    protected $fillable = [
        'tabungan_id',
        'profile_id',
        'no_tabungan',
        'tanggal_pemberian',
        'data_rekening',
        'data_nasabah',
        'data_produk',
        'data_transaksi_terakhir',
        'scanned_at',
    ];

    protected $casts = [
        'tanggal_pemberian' => 'date',
        'data_rekening' => 'array',
        'data_nasabah' => 'array',
        'data_produk' => 'array',
        'data_transaksi_terakhir' => 'array',
        'scanned_at' => 'datetime',
    ];

    public function tabungan()
    {
        return $this->belongsTo(Tabungan::class, 'tabungan_id');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id_user');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'tabungan_id',
                'profile_id',
                'no_tabungan',
                'tanggal_pemberian',
                'scanned_at'
            ]);
    }

    /**
     * Check if record exists for today for given no_tabungan
     */
    public static function existsForToday(string $noTabungan): bool
    {
        return self::where('no_tabungan', $noTabungan)
            ->whereDate('tanggal_pemberian', today())
            ->exists();
    }
}
