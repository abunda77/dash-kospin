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
        Schema::create('cicilan_emas', function (Blueprint $table) {
            $table->id('id_cicilan_emas' );
            $table->foreignId('pinjaman_id')->constrained('pinjamans', 'id_pinjaman')->onDelete('cascade');
            $table->string('no_transaksi')->unique();
            $table->decimal('berat_emas', 10, 3);
            $table->decimal('total_harga', 15, 2);
            $table->decimal('setoran_awal', 15, 2)->comment('5% dari harga emas');
            $table->decimal('biaya_admin', 15, 2)->comment('0.5% dari total harga');
            $table->enum('status', ['aktif', 'lunas', 'gagal_bayar'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cicilan_emas');
    }
};
