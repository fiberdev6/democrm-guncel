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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('api_token', 80)->unique()->nullable()->after('subscription_ends_at');
            $table->boolean('api_enabled')->default(false)->after('subscription_ends_at');
            $table->timestamp('api_token_created_at')->nullable()->after('subscription_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['api_token', 'api_enabled', 'api_token_created_at']);
        });
    }
};
