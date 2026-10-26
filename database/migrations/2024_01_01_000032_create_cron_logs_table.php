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
        Schema::create('cron_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command_name', 150);
            $table->enum('status', ['running', 'completed', 'failed'])->default('running')->index();
            $table->longText('output')->nullable();
            $table->decimal('duration_seconds', 8, 2)->default(0.00);
            $table->timestamp('executed_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['command_name', 'executed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_logs');
    }
};
