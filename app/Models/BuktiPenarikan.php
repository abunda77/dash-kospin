<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiPenarikan extends Model
{
    protected $table = 'bukti_penarikans';

    protected $fillable = [
        'penarikan_id',
        'file_path',
        'nama_asli',
        'mime_type',
        'ukuran_file',
        'diunggah_oleh_type',
        'diunggah_oleh_id',
        'is_terkini',
    ];

    protected $casts = [
        'is_terkini' => 'boolean',
    ];

    public function penarikan()
    {
        return $this->belongsTo(PenarikanTabungan::class, 'penarikan_id');
    }

    public function diunggahOleh()
    {
        return $this->morphTo('diunggah_oleh');
    }
}
