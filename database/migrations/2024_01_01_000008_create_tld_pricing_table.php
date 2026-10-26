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
        Schema::create('tld_pricing', function (Blueprint $table) {
            $table->id();
            $table->string('tld', 30)->unique();
            $table->foreignId('registrar_id')->constrained('registrar_modules')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('cost_register', 10, 4);
            $table->decimal('cost_renew', 10, 4);
            $table->decimal('cost_transfer', 10, 4);
            $table->decimal('client_register', 10, 4);
            $table->decimal('client_renew', 10, 4);
            $table->decimal('client_transfer', 10, 4);
            $table->decimal('default_reseller_register', 10, 4);
            $table->decimal('default_reseller_renew', 10, 4);
            $table->decimal('default_reseller_transfer', 10, 4);
            $table->unsignedTinyInteger('min_years')->default(1);
            $table->unsignedTinyInteger('max_years')->default(10);
            $table->boolean('is_active')->default(true);
            $table->boolean('dns_management_available')->default(true);
            $table->boolean('email_forwarding_available')->default(true);
            $table->boolean('id_protection_available')->default(true);
            $table->decimal('id_protection_cost', 10, 4)->default(0.0000);
            $table->boolean('epp_code_required')->default(true);
            $table->unsignedSmallInteger('grace_period_days')->default(30);
            $table->unsignedSmallInteger('redemption_period_days')->default(30);
            $table->decimal('redemption_fee', 10, 4)->default(80.0000);
            $table->timestamps();

            $table->index(['is_active', 'tld']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tld_pricing');
    }
};
