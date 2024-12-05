<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BackupLog extends Model
{
    use HasFactory;

    // Tentukan nama tabel secara eksplisit karena berbeda dengan konvensi
    protected $table = 'backup_logs';

    protected $fillable = [
        'filename',
        'path',
        'size',
        'type',
        'status',
        'notes'
    ];

    // Konstanta untuk status
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    // Konstanta untuk tipe
    const TYPE_MANUAL = 'manual';
    const TYPE_SCHEDULED = 'scheduled';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!in_array($model->type, [self::TYPE_MANUAL, self::TYPE_SCHEDULED])) {
                throw new \InvalidArgumentException('Invalid backup type');
            }

            if (!in_array($model->status, [self::STATUS_SUCCESS, self::STATUS_FAILED])) {
                throw new \InvalidArgumentException('Invalid backup status');
            }
        });
    }

    /**
     * Cek apakah backup berhasil
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Cek apakah backup manual
     */
    public function isManual(): bool
    {
        return $this->type === self::TYPE_MANUAL;
    }

    /**
     * Format ukuran file ke format yang mudah dibaca
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size) return '-';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = floatval($this->size);
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
