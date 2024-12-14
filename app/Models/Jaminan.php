<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Jaminan extends Model
{
    use LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_pinjaman',
                'jenis_jaminan',
                'nilai_jaminan',
                'keterangan'
            ]);
    }
}
