<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_komisi', function (Blueprint $table) {
            $table->string('kode_komisi', 20)->primary();
            $table->enum('jenis_komisi', ['tabungan', 'pinjaman', 'deposito']);
            $table->decimal('persen_komisi', 5, 2);
            $table->decimal('nominal_komisi', 15, 2);
            $table->decimal('minimal_transaksi', 15, 2);
            $table->decimal('maksimal_komisi', 15, 2);
            $table->text('keterangan')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_komisi');
    }
};
