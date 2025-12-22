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
        Schema::table('storage_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('storage_purchases', 'invoice_path')) {
                $table->string('invoice_path')->nullable()->after('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storage_purchases', function (Blueprint $table) {
            //
        });
    }
};
