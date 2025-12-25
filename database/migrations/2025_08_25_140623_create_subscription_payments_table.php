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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('tenant_subscriptions');
            $table->string('payment_id')->unique(); // Benzersiz ödeme ID'si
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'canceled']);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('gateway')->nullable(); // stripe, iyzico, paypal vb.
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
