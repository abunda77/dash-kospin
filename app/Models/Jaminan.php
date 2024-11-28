<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jaminan extends Model
{
    protected $table = 'jaminans';

    protected $fillable = [
        'id_pinjaman',
        'jenis_jaminan',
        'nilai_jaminan',
        'keterangan'
    ];

    protected $casts = [
        'jenis_jaminan' => 'string',
        'nilai_jaminan' => 'decimal:2',
        'keterangan' => 'string'
    ];

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'id_pinjaman', 'id_pinjaman');
    }
}
