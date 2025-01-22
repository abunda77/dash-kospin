<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiReferral extends Model
{
    protected $table = 'transaksi_referral';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_referral',
        'id_nasabah',
        'kode_komisi',
        'nominal_transaksi',
        'nilai_komisi',
        'nilai_withdrawal',
        'tanggal_transaksi',
        'status_komisi',
        'keterangan',
        'jenis_transaksi'
    ];

    protected $casts = [
        'nominal_transaksi' => 'decimal:2',
        'nilai_komisi' => 'decimal:2',
        'nilai_withdrawal' => 'decimal:2',
        'tanggal_transaksi' => 'datetime'
    ];

    public function anggotaReferral(): BelongsTo
    {
        return $this->belongsTo(AnggotaReferral::class, 'id_referral');
    }

    public function settingKomisi(): BelongsTo
    {
        return $this->belongsTo(SettingKomisi::class, 'kode_komisi');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'id_nasabah', 'id_user');
    }
}
