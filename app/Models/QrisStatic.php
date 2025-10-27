<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrisStatic extends Model
{
    protected $fillable = [
        'name',
        'qris_string',
        'qris_image',
        'merchant_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
