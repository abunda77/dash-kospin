<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penarikan_tabungans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penarikan')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_tabungan')->constrained('tabungans')->cascadeOnDelete();
            $table->string('jenis_simpanan');
            $table->integer('jumlah');
            $table->string('bank');
            $table->string('nama_bank');
            $table->string('nama_nasabah');
            $table->string('referensi_penarikan')->nullable();
            $table->string('bukti_penarikan_path')->nullable();
            $table->text('catatan_pengguna')->nullable();
            $table->string('status');
            $table->timestamp('dikirim_at')->nullable();
            $table->timestamp('mulai_review_at')->nullable();
            $table->timestamp('direview_at')->nullable();
            $table->foreignId('diperiksa_oleh')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('referensi_transfer')->nullable();
            $table->timestamp('waktu_transfer')->nullable();
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
            $table->index('referensi_penarikan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penarikan_tabungans');
    }
};
