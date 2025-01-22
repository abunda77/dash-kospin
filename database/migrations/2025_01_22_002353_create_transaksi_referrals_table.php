<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_referral', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->foreignId('id_referral')->constrained('anggota_referral', 'id_referral');
            $table->unsignedBigInteger('id_nasabah')->nullable()->change();
            $table->string('kode_komisi');
            $table->decimal('nominal_transaksi', 15, 2);
            $table->decimal('nilai_komisi', 15, 2)->default(0);
            $table->decimal('nilai_withdrawal', 15, 2)->default(0);
            $table->datetime('tanggal_transaksi');
            $table->enum('status_komisi', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('keterangan')->nullable();
            $table->enum('jenis_transaksi', ['deposit', 'withdrawal'])->default('deposit');
            $table->timestamps();

            $table->foreign('kode_komisi')->references('kode_komisi')->on('setting_komisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_referral');
    }
};
