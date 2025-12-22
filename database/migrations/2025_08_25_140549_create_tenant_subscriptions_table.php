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
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->enum('status', [
                'trial', 'active', 'canceled', 'expired', 'past_due', 'suspended'
            ]);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('external_subscription_id')->nullable(); // Stripe, İyzico vb.
            $table->json('subscription_data')->nullable(); // Gateway'den gelen ek bilgiler
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_subscriptions');
    }
};
