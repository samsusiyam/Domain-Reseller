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
        Schema::create('dns_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('type', ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SRV', 'CAA']);
            $table->string('host', 150);
            $table->text('value');
            $table->unsignedSmallInteger('priority')->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->unsignedSmallInteger('weight')->nullable();
            $table->unsignedInteger('ttl')->default(3600);
            $table->string('registrar_record_id', 100)->nullable();
            $table->timestamps();

            $table->index('domain_id');
            $table->index(['domain_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dns_records');
    }
};
