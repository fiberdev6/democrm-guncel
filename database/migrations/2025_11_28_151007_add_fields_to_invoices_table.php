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
            $table->integer('tevkifatOrani')->nullable()->after('faturaDurumu');
            $table->integer('tevkifatKodu')->nullable()->after('faturaDurumu');
            $table->integer('tevkifatTutari')->nullable()->after('faturaDurumu');
            $table->integer('kdvKodu')->nullable()->after('faturaDurumu');
            $table->string('kdvAciklama')->nullable()->after('faturaDurumu');
            $table->string('faturaAciklama')->nullable()->after('faturaDurumu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['tevkifatOrani', 'tevkifatKodu', 'tevkifatTutari', 'kdvKodu', 'kdvAciklama', 'faturaAciklama']);

        });
    }
};
