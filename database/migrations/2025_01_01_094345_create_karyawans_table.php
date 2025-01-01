<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();

            // Data Pribadi
            $table->string('nik_karyawan')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('status_pernikahan');
            $table->string('agama');
            $table->string('golongan_darah')->nullable();
            $table->text('alamat');
            $table->string('no_ktp')->unique();
            $table->json('foto_ktp')->nullable();
            $table->string('no_npwp')->unique()->nullable();
            $table->json('foto_npwp')->nullable();
            $table->string('email')->unique();
            $table->string('no_telepon');
            $table->json('foto_profil')->nullable();

            // Kontak Darurat
            $table->string('kontak_darurat_nama');
            $table->string('kontak_darurat_hubungan');
            $table->string('kontak_darurat_telepon');

            // Data Kepegawaian
            $table->string('nomor_pegawai')->unique();
            $table->date('tanggal_bergabung');
            $table->string('status_kepegawaian');
            $table->string('departemen');
            $table->string('jabatan');
            $table->string('level_jabatan');
            $table->string('lokasi_kerja');
            $table->decimal('gaji_pokok', 15, 2);

            // Data Pendidikan
            $table->string('pendidikan_terakhir');
            $table->string('nama_institusi');
            $table->string('jurusan');
            $table->integer('tahun_lulus');
            $table->decimal('ipk', 3, 2)->nullable();

            // Pengalaman, Keahlian & Sertifikasi
            $table->json('pengalaman_kerja')->nullable();
            $table->json('keahlian')->nullable();
            $table->json('sertifikasi')->nullable();

            // Data Bank
            $table->string('nama_bank');
            $table->string('nomor_rekening');
            $table->string('nama_pemilik_rekening');

            // BPJS
            $table->string('no_bpjs_kesehatan')->nullable();
            $table->string('no_bpjs_ketenagakerjaan')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->date('tanggal_keluar')->nullable();
            $table->string('alasan_keluar')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
