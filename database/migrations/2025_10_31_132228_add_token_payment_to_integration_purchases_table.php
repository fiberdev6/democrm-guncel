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
            $table->string('tokenPayment')->nullable()->after('integration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integration_purchases', function (Blueprint $table) {
            $table->dropColumn('tokenPayment');
        });
    }
};
