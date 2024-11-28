<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tabungans', function (Blueprint $table) {
            $table->id();
            $table->string('no_tabungan');
            $table->foreignId('id_profile')->constrained('profiles');
            $table->foreignId('produk_tabungan')->constrained('produk_tabungans');
            $table->decimal('saldo', 15, 2);
            $table->dateTime('tanggal_buka_rekening');
            $table->enum('status_rekening', ['aktif', 'ditutup']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tabungans');
    }
};
