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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('subtotal', 10, 4);
            $table->decimal('tax_amount', 10, 4)->default(0.0000);
            $table->decimal('discount_amount', 10, 4)->default(0.0000);
            $table->decimal('total_amount', 10, 4);
            $table->decimal('balance_due', 10, 4);
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['unpaid', 'paid', 'partially_paid', 'cancelled', 'refunded'])->default('unpaid');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
