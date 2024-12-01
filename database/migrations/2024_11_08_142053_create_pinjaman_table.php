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
            $table->foreignId('produk_pinjaman_id')->constrained('produk_pinjamans');
            $table->decimal('jumlah_pinjaman', 10, 2);
            $table->foreignId('beaya_bunga_pinjaman_id')->constrained('beaya_bunga_pinjamans');
            $table->date('tanggal_pinjaman');
            $table->integer('jangka_waktu');
            $table->date('tanggal_jatuh_tempo');
            $table->enum('jangka_waktu_satuan', ['bulan', 'tahun']);
            $table->enum('status_pinjaman', ['pending', 'approved', 'rejected', 'completed']);
            $table->text('keterangan')->nullable();
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
