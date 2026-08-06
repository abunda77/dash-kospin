<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStatusPenarikan extends Model
{
    protected $table = 'riwayat_status_penarikans';

    protected $fillable = [
        'penarikan_id',
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

    public function penarikan()
    {
        return $this->belongsTo(PenarikanTabungan::class, 'penarikan_id');
    }

    public function diubahOleh()
    {
        return $this->morphTo('diubah_oleh');
    }
}
