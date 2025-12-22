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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('kid')->nullable();
            $table->integer('bid')->nullable();
            $table->integer('pid')->nullable();
            $table->integer('musteri_id')->nullable();
            $table->date('kayitTarihi')->nullable();
            $table->integer('servisKaynak')->nullable();
            $table->date('musaitTarih')->nullable();
            $table->string('musaitSaat1')->nullable();
            $table->string('musaitSaat2')->nullable();
            $table->integer('cihazMarka')->nullable();
            $table->integer('cihazTur')->nullable();
            $table->string('cihazModel')->nullable();
            $table->string('cihazSeriNo')->nullable();
            $table->string('cihazAriza')->nullable();
            $table->string('operatorNotu')->nullable();
            $table->integer('garantiSuresi')->nullable();
            $table->integer('servisDurum')->nullable();
            $table->integer('planDurum')->nullable();
            $table->integer('pbDurum')->nullable();
            $table->integer('kayitAlan')->nullable();
            $table->integer('acil')->nullable();
            $table->tinyInteger('durum')->nullable();
            $table->date('silinmeTarihi')->nullable();
            $table->integer('silenKisi')->nullable();
            $table->string('faturaNumarasi')->nullable();
            $table->integer('konsinye')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
