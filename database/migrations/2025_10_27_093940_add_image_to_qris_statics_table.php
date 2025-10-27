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
        Schema::table('qris_statics', function (Blueprint $table) {
            $table->string('qris_image')->nullable()->after('qris_string');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qris_statics', function (Blueprint $table) {
            $table->dropColumn('qris_image');
        });
    }
};
