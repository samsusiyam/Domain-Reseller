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
        Schema::create('reseller_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('tld_pricing_id')->constrained('tld_pricing')->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('register_price', 10, 4);
            $table->decimal('renew_price', 10, 4);
            $table->decimal('transfer_price', 10, 4);
            $table->timestamps();

            $table->unique(['user_id', 'tld_pricing_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_pricing');
    }
};
