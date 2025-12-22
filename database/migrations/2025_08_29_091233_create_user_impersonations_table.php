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
        Schema::create('user_impersonations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('impersonator_id'); // Kimliğini kullanan admin
            $table->unsignedBigInteger('impersonated_id'); // Kimliği kullanılan user
            $table->unsignedBigInteger('tenant_id'); // Hangi firmada
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('reason')->nullable(); // Neden impersonate edildi
            $table->timestamps();

            
            $table->foreign('impersonator_id')->references('user_id')->on('tb_user');
            $table->foreign('impersonated_id')->references('user_id')->on('tb_user');
            $table->foreign('tenant_id')->references('id')->on('tenants');
            
            $table->index(['impersonator_id', 'impersonated_id']);
            $table->index(['tenant_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_impersonations');
    }
};
