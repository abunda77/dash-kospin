<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukti_penarikans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penarikan_id')->constrained('penarikan_tabungans')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('nama_asli');
            $table->string('mime_type');
            $table->integer('ukuran_file');
            $table->string('diunggah_oleh_type')->nullable();
            $table->unsignedBigInteger('diunggah_oleh_id')->nullable();
            $table->boolean('is_terkini')->default(true);
            $table->timestamps();

            $table->index(['diunggah_oleh_type', 'diunggah_oleh_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_penarikans');
    }
};
