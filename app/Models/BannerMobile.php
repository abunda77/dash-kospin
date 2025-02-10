<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerMobile extends Model
{
    protected $fillable = [
        'title',
        'url',
        'type',
        'note',
        'image'
    ];

    protected $casts = [
        'type' => 'string'
    ];

    // Tambahkan mutator untuk memastikan URL selalu terisi
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->url) && !empty($model->image)) {
                $model->url = asset('storage/' . $model->image);
            }
        });

        static::updating(function ($model) {
            if (empty($model->url) && !empty($model->image)) {
                $model->url = asset('storage/banner-mobile/' . $model->image);
            }
        });
    }
}
