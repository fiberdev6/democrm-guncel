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
        Schema::create('hipcall_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('uuid')->unique();
            $table->string('event_type')->default('call');
            $table->string('caller_number')->nullable();
            $table->string('callee_number')->nullable();
            $table->integer('call_duration')->nullable(); // saniye cinsinden
            $table->text('record_url')->nullable();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('customer_id')->nullable(); // müşteri ile ilişkilendirmek için
            $table->json('raw_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hipcall_call_logs');
    }
};
