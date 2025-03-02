<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CicilanEmas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cicilan_emas';

    protected $primaryKey = 'id_cicilan_emas';

    protected $fillable = [
        'user_id',
        'pinjaman_id',
        'no_transaksi',
        'berat_emas',
        'total_harga',
        'setoran_awal',
        'biaya_admin',
        'status',
        'keterangan'
    ];

    protected $casts = [
        'berat_emas' => 'decimal:3',
        'total_harga' => 'decimal:2',
        'setoran_awal' => 'decimal:2',
        'biaya_admin' => 'decimal:2'

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id', 'id_pinjaman');
    }

    public function hitungSetoranAwal()
    {
        return $this->total_harga * 0.05; // 5% dari harga emas
    }

    public function hitungBiayaAdmin()
    {
        return $this->total_harga * 0.005; // 0.5% dari total harga
    }

    public function hitungPenaltyPelunasan()
    {
        if ($this->is_pelunasan_dipercepat) {
            // 2x margin sesuai ketentuan
            return $this->angsuran_per_bulan * 2;
        }
        return 0;
    }

    public function cekTelatBayar()
    {
        if ($this->telat_bayar > 3) {
            $this->status = 'gagal_bayar';
            $this->save();
        }
    }
}
