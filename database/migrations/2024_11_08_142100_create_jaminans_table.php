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
        Schema::create('jaminans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pinjaman')->constrained('pinjamans', 'id_pinjaman')->onDelete('cascade');
            $table->string('jenis_jaminan');
            $table->decimal('nilai_jaminan', 10, 2);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jaminans');
    }
};
