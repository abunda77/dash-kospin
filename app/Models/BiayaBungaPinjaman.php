<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiayaBungaPinjaman extends Model
{
    protected $table = 'beaya_bunga_pinjamans';

    protected $fillable = [
        'name',
        'persentase_bunga',
        'biaya_administrasi'

    ];

    protected $casts = [
        'persentase_bunga' => 'decimal:2',
        'biaya_administrasi' => 'decimal:2'

    ];
}
