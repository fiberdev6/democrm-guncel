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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('firma_adi')->nullable();
            $table->string('firma_slug')->nullable();
            $table->string('username')->nullable();
            $table->string('eposta')->unique();
            $table->string('tel1')->nullable();
            $table->string('tel2')->nullable();
            $table->string('il')->nullable();
            $table->string('ilce')->nullable();
            $table->string('adres')->nullable();
            $table->string('webSitesi')->nullable();
            $table->string('vergiNo')->nullable();
            $table->string('vergiDairesi')->nullable();
            $table->string('iban')->nullable();
            $table->string('logo')->nullable();
            $table->integer('personelSayisi')->nullable();
            $table->integer('bayiSayisi')->nullable();
            $table->integer('stokSayisi')->nullable();
            $table->integer('kdvOrani')->nullable();
            $table->string('smsKullanici')->nullable();
            $table->string('smsSifre')->nullable();
            $table->string('smsGonderici')->nullable();
            $table->string('smsKaraliste')->nullable();
            $table->date('kayitTarihi')->nullable();
            $table->date('bitisTarihi')->nullable();
            $table->tinyInteger('status')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
