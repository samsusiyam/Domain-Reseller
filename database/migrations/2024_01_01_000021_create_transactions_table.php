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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('transaction_number', 60)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete()->cascadeOnUpdate();
            $table->enum('gateway', ['manual', 'bkash', 'stripe', 'binance', 'balance']);
            $table->string('gateway_transaction_id', 150)->nullable()->index();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 4);
            $table->char('currency', 3);
            $table->decimal('exchange_rate', 14, 6)->default(1.000000);
            $table->decimal('amount_in_usd', 15, 4);
            $table->decimal('fee', 10, 4)->default(0.0000);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->json('payment_details')->nullable();
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['gateway', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
