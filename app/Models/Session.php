<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'sessions';

    // protected $fillable = [
    //     'id',
    //     'user_id',
    //     'ip_address',
    //     'user_agent',
    //     'payload',
    //     'last_activity'
    // ];

    // protected $casts = [
    //     'last_activity' => 'datetime',
    //     'user_id' => 'integer'
    // ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
