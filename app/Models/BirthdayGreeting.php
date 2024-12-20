<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BirthdayGreeting extends Model
{
    use HasFactory;

    protected $table = 'birthday_greetings';

    protected $fillable = [
        'code',
        'message'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
