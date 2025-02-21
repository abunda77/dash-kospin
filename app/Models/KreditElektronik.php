<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class KreditElektronik extends Model
{
    use LogsActivity;

    protected $table = 'kredit_elektroniks';

    protected $primaryKey = 'id_kredit_elektronik';

    protected $fillable = [
        'pinjaman_id',
        'kode_barang',
        'nama_barang',
        'jenis_barang',
        'merk',
        'tipe',
        'tahun_pembuatan',
        'kondisi',
        'kelengkapan',
        'harga_barang',
        'uang_muka',
        'nilai_hutang',
        'note',
        'status_kredit'
    ];

    protected $casts = [
        'harga_barang' => 'decimal:2',
        'uang_muka' => 'decimal:2',
        'nilai_hutang' => 'decimal:2',
        'tahun_pembuatan' => 'integer',
        'status_kredit' => 'string'
    ];

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id', 'id_pinjaman');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'pinjaman_id',
                'kode_barang',
                'nama_barang',
                'jenis_barang',
                'merk',
                'tipe',
                'tahun_pembuatan',
                'kondisi',
                'kelengkapan',
                'harga_barang',
                'uang_muka',
                'nilai_hutang',
                'note',
                'status_kredit'
            ]);
    }
}
