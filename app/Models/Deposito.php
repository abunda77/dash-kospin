<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Events\DepositoCreated;
use App\Events\DepositoUpdated;
use App\Events\DepositoDeleted;

class Deposito extends Model
{
    use LogsActivity;

    protected $dispatchesEvents = [
        'created' => DepositoCreated::class,
        'updated' => DepositoUpdated::class,
        'deleted' => DepositoDeleted::class,
    ];

    protected $fillable = [
        'id_user',
        'nomor_rekening',
        'nominal_penempatan',
        'jangka_waktu',
        'tanggal_pembukaan',
        'tanggal_jatuh_tempo',
        'rate_bunga',
        'nominal_bunga',
        'status',
        'perpanjangan_otomatis',
        'notes',
        'nama_bank',
        'nomor_rekening_bank',
        'nama_pemilik_rekening_bank'
    ];

    protected $casts = [
        'nominal_penempatan' => 'decimal:2',
        'nominal_bunga' => 'decimal:2',
        'rate_bunga' => 'decimal:2',
        'tanggal_pembukaan' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'perpanjangan_otomatis' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the profile that owns the deposito
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'id_user', 'id_user');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->tanggal_jatuh_tempo) {
                $tanggalPembukaan = \Carbon\Carbon::parse($model->tanggal_pembukaan);
                $jangkaWaktu = (int) $model->jangka_waktu;
                $model->tanggal_jatuh_tempo = $tanggalPembukaan->copy()->addMonths($jangkaWaktu);
            }

            if (!$model->nominal_bunga) {
                $nominal = (float) $model->nominal_penempatan;
                $rate = (float) $model->rate_bunga;
                $jangkaWaktu = (int) $model->jangka_waktu;

                $model->nominal_bunga = ($nominal * $rate * $jangkaWaktu) / (100 * 12);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_user',
                'nomor_rekening',
                'nominal_penempatan',
                'jangka_waktu',
                'tanggal_pembukaan',
                'tanggal_jatuh_tempo',
                'rate_bunga',
                'nominal_bunga',
                'status',
                'perpanjangan_otomatis',
                'notes'
            ]);
    }
}
