<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnggotaReferral extends Model
{
    protected $table = 'anggota_referral';
    protected $primaryKey = 'id_referral';

    protected $fillable = [
        'kode_referral',
        'nama',
        'status_referral',
        'no_rekening',
        'bank',
        'atas_nama_bank',
        'email',
        'no_hp',
        'tanggal_bergabung',
        'status_aktif'
    ];

    protected $casts = [
        'tanggal_bergabung' => 'datetime',
        'status_aktif' => 'boolean'
    ];

    public function transaksiReferral(): HasMany
    {
        return $this->hasMany(TransaksiReferral::class, 'id_referral');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'id_referral');
    }
}
