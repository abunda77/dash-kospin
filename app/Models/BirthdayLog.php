<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthdayLog extends Model
{
    protected $fillable = [
        'id_profile',
        'status_sent',
        'date_sent'
    ];

    protected $casts = [
        'date_sent' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the profile that owns the birthday log
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'id_profile', 'id_user');
    }
}
