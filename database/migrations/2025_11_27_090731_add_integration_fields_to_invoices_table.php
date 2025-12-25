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
            // Entegrasyon invoice ID'si (Paraşüt'teki ID)
            $table->string('integration_invoice_id')->nullable()->after('id');
            
            // Entegrasyon hata mesajı
            $table->text('integration_error')->nullable()->after('faturaDurumu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
             $table->dropColumn(['integration_invoice_id', 'integration_error']);

        });
    }
};
