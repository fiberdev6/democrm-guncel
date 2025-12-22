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
        Schema::create('tenant_prims', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->decimal('operatorPrim')->nullable();
            $table->decimal('operatorPrimTutari')->nullable();
            $table->decimal('teknisyenPrim')->nullable();
            $table->decimal('teknisyenPrimTutari')->nullable();
            $table->decimal('atolyePrim')->nullable();
            $table->decimal('atolyePrimTutari')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_prims');
    }
};
