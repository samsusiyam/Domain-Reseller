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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete()->cascadeOnUpdate();
            $table->enum('item_type', ['domain_register', 'domain_renew', 'domain_transfer', 'id_protection', 'redemption_fee']);
            $table->string('domain_name', 255);
            $table->unsignedTinyInteger('years')->default(1);
            $table->decimal('unit_price', 10, 4);
            $table->decimal('total_price', 10, 4);
            $table->foreignId('registrar_id')->constrained('registrar_modules')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('registrar_cost', 10, 4)->default(0.0000);
            $table->json('registrar_payload')->nullable();
            $table->json('registrar_response')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
