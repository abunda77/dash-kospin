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
        Schema::create('pelunasans', function (Blueprint $table) {
            $table->id('id_pelunasan');
            $table->foreignId('profile_id')->constrained('profiles', 'id_user');
            $table->string('no_pinjaman');
            $table->date('tanggal_pelunasan');
            $table->decimal('jumlah_pelunasan', 20, 2);
            $table->foreignId('pinjaman_id')->constrained('pinjamans', 'id_pinjaman');
            $table->enum('status_pelunasan', ['normal', 'dipercepat', 'tunggakan']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelunasans');
    }
};