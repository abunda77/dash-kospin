<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    protected $table = 'dendas';

    protected $fillable = [
        'penalty_code',
        'rate_denda'
    ];

    protected $casts = [
        'rate_denda' => 'decimal:2'
    ];
}
