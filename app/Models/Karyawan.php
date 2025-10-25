<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Karyawan extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nik_karyawan',
        'nama',
        'alamat',
        'tempat_lahir',
        'tanggal_lahir',
        'no_telepon',
        'no_telepon_keluarga',
        'foto_profil',
        'is_active',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_active' => 'boolean',
        'foto_profil' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    // Relationships
    // public function departemen()
    // {
    //     return $this->belongsTo(Departemen::class);
    // }

    // public function jabatan()
    // {
    //     return $this->belongsTo(Jabatan::class);
    // }

    // public function cuti()
    // {
    //     return $this->hasMany(Cuti::class);
    // }

    // public function absensi()
    // {
    //     return $this->hasMany(Absensi::class);
    // }

    // public function pelatihan()
    // {
    //     return $this->hasMany(Pelatihan::class);
    // }
}
