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
         Schema::create('super_admin_invoice_products', function (Blueprint $table) {
            $table->id();
            $table->integer('faturaid')->nullable(); // super_admin_invoices tablosundaki id
            $table->text('aciklama')->nullable();
            $table->integer('miktar')->nullable();
            $table->decimal('fiyat', 10, 2)->nullable();
            $table->decimal('tutar', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_admin_invoice_products');
    }
};
