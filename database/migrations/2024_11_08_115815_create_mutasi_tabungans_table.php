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
        Schema::create('mutasi_tabungans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_transaksi')->constrained('tabungans');
            $table->string('jenis_transaksi');
            $table->decimal('jumlah_saldo', 15, 2);
            $table->dateTime('tanggal_transaksi');
            $table->string('keterangan_transaksi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_tabungans');
    }
};
