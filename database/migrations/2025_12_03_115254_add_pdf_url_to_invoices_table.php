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
            $table->text('formalization_pdf_url')->nullable()->after('formalization_error');
            $table->timestamp('formalization_pdf_expires_at')->nullable()->after('formalization_pdf_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['formalization_pdf_url', 'formalization_pdf_expires_at']);

        });
    }
};
