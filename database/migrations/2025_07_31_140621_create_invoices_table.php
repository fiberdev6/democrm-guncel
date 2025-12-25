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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->bigInteger('servisid')->nullable();
            $table->bigInteger('musteriid')->nullable();
            $table->string('faturaNumarasi')->nullable();
            $table->date('faturaTarihi')->nullable();
            $table->integer('odemeSekli')->nullable();
            $table->decimal('toplam')->nullable();
            $table->decimal('indirim')->nullable();
            $table->decimal('kdv')->nullable();
            $table->integer('kdvTutar')->nullable();
            $table->decimal('genelToplam')->nullable();
            $table->string('toplamYazi')->nullable();
            $table->bigInteger('kayitAlan')->nullable();
            $table->enum('faturaTipi', ['manual', 'e_fatura'])->default('manual');
            $table->enum('faturaDurumu', ['draft', 'sent', 'error'])->default('draft');
            $table->text('efaturaYaniti')->nullable();
            $table->enum('durum', ['0', '1'])->default('1');
            $table->string('faturaPdf')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
