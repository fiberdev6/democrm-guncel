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
        Schema::create('verimor_webphone_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('extension'); // Dahili numarası (1000, 1001 vs.)
            $table->string('token'); // API'den dönen webphone token
            $table->timestamp('expires_at'); // Token 1 gün geçerli
            $table->timestamps();
            
            $table->index('tenant_id');
            $table->index(['tenant_id', 'extension']);
            $table->unique(['tenant_id', 'extension']); // Her tenant için bir dahili
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verimor_webphone_tokens');
    }
};
