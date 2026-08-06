<?php

namespace App\Models;

use App\Enums\StatusPenarikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PenarikanTabungan extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'penarikan_tabungans';

    protected $fillable = [
        'nomor_penarikan',
        'user_id',
        'id_tabungan',
        'jenis_simpanan',
        'jumlah',
        'bank',
        'nama_bank',
        'nama_nasabah',
        'referensi_penarikan',
        'bukti_penarikan_path',
        'catatan_pengguna',
        'status',
        'dikirim_at',
        'mulai_review_at',
        'direview_at',
        'diperiksa_oleh',
        'referensi_transfer',
        'waktu_transfer',
        'catatan_verifikasi',
        'disetujui_at',
        'ditolak_at',
        'ditolak_oleh',
        'alasan_penolakan',
        'diposting_at',
        'selesai_at',
    ];

    protected $casts = [
        'status' => StatusPenarikan::class,
        'jumlah' => 'integer',
        'dikirim_at' => 'datetime',
        'mulai_review_at' => 'datetime',
        'direview_at' => 'datetime',
        'waktu_transfer' => 'datetime',
        'disetujui_at' => 'datetime',
        'ditolak_at' => 'datetime',
        'diposting_at' => 'datetime',
        'selesai_at' => 'datetime',
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
        return $this->hasMany(RiwayatStatusPenarikan::class, 'penarikan_id');
    }

    public function buktiPenarikan()
    {
        return $this->hasMany(BuktiPenarikan::class, 'penarikan_id');
    }

    public function buktiPenarikanTerkini()
    {
        return $this->hasOne(BuktiPenarikan::class, 'penarikan_id')->where('is_terkini', true);
    }

    public function transaksiTabungan()
    {
        return $this->hasOne(TransaksiTabungan::class, 'penarikan_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nomor_penarikan',
                'status',
                'jumlah',
                'bank',
                'nama_bank',
                'nama_nasabah',
            ]);
    }
}
