<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeayaTabungan extends Model
{
    protected $table = 'bunga_beaya_tabungans';

    protected $fillable = [
        'name',
        'persentase_bunga',
        'biaya_administrasi'
    ];

    public function produkTabungan()
    {
        return $this->hasMany(ProdukTabungan::class, 'bunga_beaya_tabungans', 'id');
    }
}
