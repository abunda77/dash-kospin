<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableInfo extends Model
{
    protected $fillable = [
        'table_name',
        'field_count',
        'record_count'
    ];
}
