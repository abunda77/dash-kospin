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
            $table->decimal('total_pembayaran', 10, 2);
            $table->decimal('sisa_pinjaman', 10, 2);
            $table->string('status_pembayaran');
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
