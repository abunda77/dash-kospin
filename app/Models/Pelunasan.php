<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Pelunasan extends Model
{
    use LogsActivity;

    protected $table = 'pelunasans';

    protected $primaryKey = 'id_pelunasan';

    protected $fillable = [
        'profile_id',
        'no_pinjaman',
        'tanggal_pelunasan',
        'jumlah_pelunasan',
        'pinjaman_id',
        'status_pelunasan'
    ];

    protected $casts = [
        'no_pinjaman' => 'string',
        'tanggal_pelunasan' => 'date',
        'jumlah_pelunasan' => 'decimal:2',
        'status_pelunasan' => 'string'
    ];

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id', 'id_pinjaman');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id_user');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'profile_id',
                'no_pinjaman',
                'tanggal_pelunasan',
                'jumlah_pelunasan',
                'pinjaman_id',
                'status_pelunasan'
            ]);
    }
}
