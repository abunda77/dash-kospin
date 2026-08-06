<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStatusSetoran extends Model
{
    protected $table = 'riwayat_status_setorans';

    protected $fillable = [
        'setoran_id',
        'status_sebelumnya',
        'status_baru',
        'diubah_oleh_type',
        'diubah_oleh_id',
        'catatan',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function setoran()
    {
        return $this->belongsTo(SetoranTabungan::class, 'setoran_id');
    }

    public function diubahOleh()
    {
        return $this->morphTo('diubah_oleh');
    }
}
