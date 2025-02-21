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
        Schema::create('gadais', function (Blueprint $table) {
            $table->id('id_gadai');
            $table->foreignId('pinjaman_id')->constrained('pinjamans', 'id_pinjaman')->onDelete('cascade');
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->string('jenis_barang');
            $table->string('merk');
            $table->string('tipe');
            $table->integer('tahun_pembuatan');
            $table->string('kondisi');
            $table->text('kelengkapan');
            $table->decimal('harga_barang', 15, 2);
            $table->decimal('nilai_taksasi', 15, 2);
            $table->decimal('nilai_hutang', 15, 2);
            $table->text('note')->nullable();
            $table->string('status_gadai')->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gadais');
    }
};
