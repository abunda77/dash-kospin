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
        Schema::create('pinjamans', function (Blueprint $table) {
            $table->id('id_pinjaman');
            $table->string('no_pinjaman');
            $table->foreignId('profile_id')->constrained('profiles');
            $table->foreignId('produk_pinjaman')->constrained('produk_pinjamans');
            $table->decimal('jumlah_pinjaman', 10, 2);
            $table->decimal('suku_bunga', 5, 2);
            $table->date('tanggal_pinjaman');
            $table->integer('jangka_waktu');
            $table->string('jangka_waktu_satuan');
            $table->string('status_pinjaman');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjamans');
    }
};
