<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanKredit extends Model
{
    protected $table = 'catatan_kredits';

    protected $fillable = [
        'write_by',
        'notes',
        'nama_nasabah',
        'status_notes'
    ];
}
