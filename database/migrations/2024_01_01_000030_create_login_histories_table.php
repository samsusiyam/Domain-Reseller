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
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('email', 191);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->char('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->string('failure_reason', 255)->nullable();
            $table->timestamp('login_at');

            $table->index(['user_id', 'login_at']);
            $table->index(['ip_address', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
