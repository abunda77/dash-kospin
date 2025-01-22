<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettingKomisi extends Model
{
    protected $table = 'setting_komisi';
    protected $primaryKey = 'kode_komisi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_komisi',
        'jenis_komisi',
        'persen_komisi',
        'nominal_komisi',
        'minimal_transaksi',
        'maksimal_komisi',
        'keterangan',
        'status_aktif'
    ];

    protected $casts = [
        'persen_komisi' => 'float',
        'nominal_komisi' => 'float',
        'minimal_transaksi' => 'float',
        'maksimal_komisi' => 'float',
        'status_aktif' => 'boolean'
    ];

    protected function setMinimalTransaksiAttribute($value)
    {
        $this->attributes['minimal_transaksi'] = str_replace(['Rp', '.', ','], '', $value);
    }

    protected function setNominalKomisiAttribute($value)
    {
        $this->attributes['nominal_komisi'] = str_replace(['Rp', '.', ','], '', $value);
    }

    protected function setMaksimalKomisiAttribute($value)
    {
        $this->attributes['maksimal_komisi'] = str_replace(['Rp', '.', ','], '', $value);
    }

    public function transaksiReferral(): HasMany
    {
        return $this->hasMany(TransaksiReferral::class, 'kode_komisi');
    }
}
