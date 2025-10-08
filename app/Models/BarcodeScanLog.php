<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarcodeScanLog extends Model
{
    protected $fillable = [
        'tabungan_id',
        'hash',
        'ip_address',
        'user_agent',
        'referer',
        'country',
        'city',
        'is_mobile',
        'scanned_at',
    ];

    protected $casts = [
        'is_mobile' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    /**
     * Get the tabungan that was scanned
     */
    public function tabungan(): BelongsTo
    {
        return $this->belongsTo(Tabungan::class);
    }

    /**
     * Scope for recent scans
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('scanned_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific tabungan
     */
    public function scopeForTabungan($query, $tabunganId)
    {
        return $query->where('tabungan_id', $tabunganId);
    }
}
