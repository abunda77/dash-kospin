<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisTabungan extends Model
{
    protected $table = 'jenis_tabungans';

    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'name' => 'string'
    ];

    public function produkTabungan()
    {
        return $this->hasMany(ProdukTabungan::class);
    }
}
