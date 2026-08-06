<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setoran_tabungans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_setoran')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_tabungan')->constrained('tabungans')->cascadeOnDelete();
            $table->string('jenis_simpanan');
            $table->integer('jumlah');
            $table->integer('kode_unik');
            $table->integer('jumlah_bayar');
            $table->text('qris_payload')->nullable();
            $table->string('qris_image_path')->nullable();
            $table->timestamp('qris_dibuat_at')->nullable();
            $table->timestamp('kedaluwarsa_at')->nullable();
            $table->string('status');
            $table->timestamp('waktu_klaim_bayar')->nullable();
            $table->string('nama_pembayar')->nullable();
            $table->string('referensi_pembayaran')->nullable();
            $table->string('bukti_pembayaran_path')->nullable();
            $table->text('catatan_pengguna')->nullable();
            $table->timestamp('dikirim_at')->nullable();
            $table->boolean('is_terlambat')->default(false);
            $table->timestamp('mulai_review_at')->nullable();
            $table->timestamp('direview_at')->nullable();
            $table->foreignId('diperiksa_oleh')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('referensi_transaksi_provider')->nullable()->unique();
            $table->timestamp('waktu_bayar_provider')->nullable();
            $table->string('nama_pembayar_provider')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('disetujui_at')->nullable();
            $table->timestamp('ditolak_at')->nullable();
            $table->foreignId('ditolak_oleh')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('diposting_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['id_tabungan', 'status']);
            $table->index(['jumlah_bayar', 'status']);
            $table->index(['kedaluwarsa_at', 'status']);
            $table->index('referensi_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran_tabungans');
    }
};
