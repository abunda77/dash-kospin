<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Profile extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'id';  // Primary key adalah 'id', bukan 'id_user'

    protected $fillable = [
        'id_user',
        'first_name',
        'last_name',
        'address',
        'sign_identity',
        'no_identity',
        'image_identity',
        'phone',
        'email',
        'whatsapp',
        'gender',
        'birthday',
        'mariage',
        'job',
        'province_id',
        'district_id',
        'city_id',
        'village_id',
        'monthly_income',
        'is_active',
        'type_member',
        'avatar',
        'remote_url',
        'notes',
        'ibu_kandung',
    ];

    protected $casts = [
        'image_identity' => 'json',
        'monthly_income' => 'decimal:2',
        'birthday' => 'date',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    /**
     * Get the user that owns the profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function birthdayLogs()
    {
        return $this->hasMany(BirthdayLog::class, 'id_profile', 'id_user');
    }

    public function pinjamans()
    {
        return $this->hasMany(Pinjaman::class, 'profile_id');
    }

    public function tabungans()
    {
        return $this->hasMany(Tabungan::class, 'id_profile');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_user',
                'first_name',
                'last_name',
                'address',
                'sign_identity',
                'no_identity',
                'image_identity',
                'phone',
                'email',
                'whatsapp',
                'gender',
                'birthday',
                'mariage',
                'job',
                'province_id',
                'district_id',
                'city_id',
                'village_id',
                'monthly_income',
                'is_active',
                'type_member',
                'avatar',
                'remote_url',
                'notes',
                'ibu_kandung',
            ]);
    }
}
