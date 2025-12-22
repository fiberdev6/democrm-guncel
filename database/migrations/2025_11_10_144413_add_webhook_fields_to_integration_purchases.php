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
        Schema::table('integration_purchases', function (Blueprint $table) {
            $table->string('webhook_token', 64)->nullable()->unique()->after('credentials');
            $table->text('webhook_url')->nullable()->after('webhook_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integration_purchases', function (Blueprint $table) {
            $table->dropColumn(['webhook_token', 'webhook_url']);
        });
    }
};
