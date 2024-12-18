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
        Schema::create('birthday_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_profile')->constrained('profiles', 'id_user');
            $table->boolean('status_sent')->default(false);
            $table->timestamp('date_sent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthday_logs');
    }
};
