<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiSetoran extends Model
{
    protected $table = 'bukti_setorans';

    protected $fillable = [
        'setoran_id',
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

    public function setoran()
    {
        return $this->belongsTo(SetoranTabungan::class, 'setoran_id');
    }

    public function diunggahOleh()
    {
        return $this->morphTo('diunggah_oleh');
    }
}
