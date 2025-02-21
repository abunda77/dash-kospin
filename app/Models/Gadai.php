<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Gadai extends Model
{
    use LogsActivity;

    protected $table = 'gadais';

    protected $primaryKey = 'id_gadai';

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
        'nilai_taksasi',
        'nilai_hutang',
        'note',
        'status_gadai'
    ];

    protected $casts = [
        'harga_barang' => 'decimal:2',
        'nilai_taksasi' => 'decimal:2',
        'nilai_hutang' => 'decimal:2',
        'tahun_pembuatan' => 'integer',
        'status_gadai' => 'string'
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
                'nilai_taksasi',
                'nilai_hutang',
                'note',
                'status_gadai'
            ]);
    }
}
