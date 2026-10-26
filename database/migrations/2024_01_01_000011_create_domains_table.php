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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('registrar_id')->constrained('registrar_modules')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('domain_name', 255)->unique();
            $table->string('tld', 30)->index();
            $table->unsignedTinyInteger('registration_years')->default(1);
            $table->enum('status', [
                'active', 'pending', 'expired', 'redemption',
                'transferring_in', 'transferring_out', 'cancelled', 'fraud'
            ])->default('pending');
            $table->string('registrar_order_id', 100)->nullable();
            $table->string('registrar_domain_id', 100)->nullable();
            $table->boolean('is_locked')->default(true);
            $table->boolean('is_private')->default(false);
            $table->boolean('is_auto_renew')->default(false);
            $table->json('nameservers')->nullable();
            $table->text('auth_code')->nullable(); // Encrypted EPP code
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->date('next_due_date')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->decimal('registrar_cost', 10, 4)->default(0.0000);
            $table->decimal('selling_price', 10, 4)->default(0.0000);
            $table->foreignId('registrant_contact_id')->nullable()->constrained('contacts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('admin_contact_id')->nullable()->constrained('contacts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('tech_contact_id')->nullable()->constrained('contacts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('billing_contact_id')->nullable()->constrained('contacts')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
