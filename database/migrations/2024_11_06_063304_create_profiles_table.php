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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->text('address')->nullable();
            $table->string('sign_identity')->nullable();
            $table->string('no_identity')->nullable();
            $table->json('image_identity')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('gender')->nullable();
            $table->date('birthday')->nullable();
            $table->string('mariage')->nullable();
            $table->string('job')->nullable();
            $table->string('province_id')->nullable();
            $table->string('district_id')->nullable();
            $table->string('city_id')->nullable();
            $table->string('village_id')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('type_member')->nullable();
            $table->string('avatar')->nullable();
            $table->string('remote_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
