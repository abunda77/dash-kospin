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
        Schema::create('transaksi_tabungans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tabungan')->constrained('tabungans')->onDelete('cascade');
            $table->enum('jenis_transaksi', ['kredit', 'debit']);
            $table->decimal('jumlah', 15, 2); // Disesuaikan dengan cast di model
            $table->dateTime('tanggal_transaksi');
            $table->string('keterangan')->nullable();
            $table->string('kode_transaksi');
            $table->unsignedBigInteger('kode_teller')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_tabungans');
    }
};
