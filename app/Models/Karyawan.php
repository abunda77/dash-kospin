<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Karyawan extends Model
{
    use LogsActivity, HasFactory;

    protected $fillable = [
        // Data Pribadi
        'nik_karyawan',
        'first_name',
        'last_name',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'status_pernikahan',
        'agama',
        'golongan_darah',
        'alamat',
        'no_ktp',
        'foto_ktp',
        'no_npwp',
        'foto_npwp',
        'email',
        'no_telepon',
        'foto_profil',

        // Kontak Darurat
        'kontak_darurat_nama',
        'kontak_darurat_hubungan',
        'kontak_darurat_telepon',

        // Data Kepegawaian
        'nomor_pegawai',
        'tanggal_bergabung',
        'status_kepegawaian', // tetap, kontrak, probation
        'departemen',
        'jabatan',
        'level_jabatan',
        'lokasi_kerja',
        'gaji_pokok',

        // Data Pendidikan
        'pendidikan_terakhir',
        'nama_institusi',
        'jurusan',
        'tahun_lulus',
        'ipk',

        // Pengalaman Kerja
        'pengalaman_kerja',  // JSON

        // Keahlian & Sertifikasi
        'keahlian',  // JSON
        'sertifikasi',  // JSON

        // Data Bank
        'nama_bank',
        'nomor_rekening',
        'nama_pemilik_rekening',

        // BPJS
        'no_bpjs_kesehatan',
        'no_bpjs_ketenagakerjaan',

        // Status
        'is_active',
        'tanggal_keluar',
        'alasan_keluar'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
        'tanggal_keluar' => 'date',
        'is_active' => 'boolean',
        'gaji_pokok' => 'decimal:2',
        'foto_ktp' => 'json',
        'foto_npwp' => 'json',
        'foto_profil' => 'json',
        'pengalaman_kerja' => 'json',
        'keahlian' => 'json',
        'sertifikasi' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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
