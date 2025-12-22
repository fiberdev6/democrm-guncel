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
        Schema::create('integration_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('integration_id')->constrained('integrations')->onDelete('cascade');
            
            // Ödeme Bilgileri
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->string('status')->default('pending'); // pending, completed, failed, cancelled
            
            // Ödeme Gateway Bilgileri
            $table->string('payment_method')->nullable(); // credit_card, bank_transfer, etc.
            $table->string('transaction_id')->nullable();
            $table->string('gateway')->nullable(); // iyzico, stripe, etc.
            $table->json('payment_response')->nullable();
            
            // Fatura
            $table->string('invoice_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // API Bilgileri (Şifrelenmiş)
            $table->json('credentials')->nullable();
            $table->json('settings')->nullable();
            
            // Entegrasyon Durumu
            $table->boolean('is_active')->default(false);
            $table->timestamp('activated_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_purchases');
    }
};
