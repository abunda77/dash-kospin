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
        Schema::create('transaksi_pinjamans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pembayaran');
            $table->foreignId('pinjaman_id')->constrained('pinjamans', 'id_pinjaman');
            $table->decimal('angsuran_pokok', 15, 2);
            $table->decimal('angsuran_bunga', 15, 2);
            $table->decimal('total_pembayaran', 10, 2);
            $table->decimal('sisa_pinjaman', 10, 2);
            $table->string('status_pembayaran');
            $table->decimal('denda', 15, 2)->default(0);
            $table->integer('hari_terlambat')->default(0);
            $table->integer('angsuran_ke');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_pinjamans');
    }
};
