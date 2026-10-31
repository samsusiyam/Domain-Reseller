<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tld_pricing', function (Blueprint $table) {
            $table->decimal('dns_management_cost', 10, 4)->default(0.0000)->after('dns_management_available');
            $table->decimal('email_forwarding_cost', 10, 4)->default(0.0000)->after('email_forwarding_available');
        });
    }

    public function down(): void
    {
        Schema::table('tld_pricing', function (Blueprint $table) {
            $table->dropColumn(['dns_management_cost', 'email_forwarding_cost']);
        });
    }
};
