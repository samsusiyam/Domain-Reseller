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
        Schema::create('registrar_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 50)->unique();
            $table->boolean('is_active')->default(false)->index();
            $table->boolean('is_default')->default(false);
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->text('config'); // Encrypted JSON
            $table->decimal('balance', 12, 4)->default(0.0000);
            $table->char('currency', 3)->default('USD');
            $table->timestamp('last_balance_sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrar_modules');
    }
};
