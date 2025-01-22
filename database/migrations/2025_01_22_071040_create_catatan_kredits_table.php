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
            Schema::create('catatan_kredits', function (Blueprint $table) {
                $table->id();
                $table->string('write_by');
                $table->string('nama_nasabah');
                $table->enum('status_notes', ['open', 'close'])->default('open');
                $table->text('notes');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('catatan_kredits');
        }
    };
