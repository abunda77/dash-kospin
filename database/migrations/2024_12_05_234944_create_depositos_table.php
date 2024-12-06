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
        Schema::create('depositos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('profiles', 'id_user')
                  ->onDelete('cascade');
            $table->string('nomor_rekening')->unique();
            $table->decimal('nominal_penempatan', 20, 2);
            $table->integer('jangka_waktu'); // dalam bulan
            $table->date('tanggal_pembukaan');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('rate_bunga', 5, 2); // dalam persen
            $table->decimal('nominal_bunga', 20, 2);
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->boolean('perpanjangan_otomatis')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('tanggal_jatuh_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depositos');
    }
};
