<?php

namespace App\Models;

use App\Enums\StatusSetoran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SetoranTabungan extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'setoran_tabungans';

    protected $fillable = [
        'nomor_setoran',
        'user_id',
        'id_tabungan',
        'jenis_simpanan',
        'jumlah',
        'kode_unik',
        'jumlah_bayar',
        'qris_payload',
        'qris_image_path',
        'qris_dibuat_at',
        'kedaluwarsa_at',
        'status',
        'waktu_klaim_bayar',
        'nama_pembayar',
        'referensi_pembayaran',
        'bukti_pembayaran_path',
        'catatan_pengguna',
        'dikirim_at',
        'is_terlambat',
        'mulai_review_at',
        'direview_at',
        'diperiksa_oleh',
        'referensi_transaksi_provider',
        'waktu_bayar_provider',
        'nama_pembayar_provider',
        'catatan_verifikasi',
        'disetujui_at',
        'ditolak_at',
        'ditolak_oleh',
        'alasan_penolakan',
        'diposting_at',
        'selesai_at',
    ];

    protected $casts = [
        'status' => StatusSetoran::class,
        'jumlah' => 'integer',
        'kode_unik' => 'integer',
        'jumlah_bayar' => 'integer',
        'qris_dibuat_at' => 'datetime',
        'kedaluwarsa_at' => 'datetime',
        'waktu_klaim_bayar' => 'datetime',
        'dikirim_at' => 'datetime',
        'mulai_review_at' => 'datetime',
        'direview_at' => 'datetime',
        'waktu_bayar_provider' => 'datetime',
        'disetujui_at' => 'datetime',
        'ditolak_at' => 'datetime',
        'diposting_at' => 'datetime',
        'selesai_at' => 'datetime',
        'is_terlambat' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tabungan()
    {
        return $this->belongsTo(Tabungan::class, 'id_tabungan');
    }

    public function pemeriksa()
    {
        return $this->belongsTo(Admin::class, 'diperiksa_oleh');
    }

    public function penolak()
    {
        return $this->belongsTo(Admin::class, 'ditolak_oleh');
    }

    public function riwayatStatus()
    {
        return $this->hasMany(RiwayatStatusSetoran::class, 'setoran_id');
    }

    public function buktiSetoran()
    {
        return $this->hasMany(BuktiSetoran::class, 'setoran_id');
    }

    public function buktiSetoranTerkini()
    {
        return $this->hasOne(BuktiSetoran::class, 'setoran_id')->where('is_terkini', true);
    }

    public function transaksiTabungan()
    {
        return $this->hasOne(TransaksiTabungan::class, 'setoran_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nomor_setoran',
                'status',
                'jumlah',
                'kode_unik',
                'jumlah_bayar',
            ]);
    }
}
