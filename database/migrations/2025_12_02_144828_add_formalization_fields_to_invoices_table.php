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
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('formalized')->default(false)->after('faturaDurumu');
            $table->enum('formalization_status', ['pending', 'sent', 'error', null])->nullable()->after('formalized');
            $table->enum('formalization_type', ['e-invoice', 'e-archive', null])->nullable()->after('formalization_status');
            $table->text('formalization_error')->nullable()->after('formalization_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['formalized', 'formalization_status', 'formalization_type', 'formalization_error']);

        });
    }
};
