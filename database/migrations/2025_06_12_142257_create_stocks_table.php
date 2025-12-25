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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('kid')->nullable();
            $table->integer('pid')->nullable();
            $table->string('urunKodu')->nullable();
            $table->string('urunAdi')->nullable();
            $table->integer('urunKategori')->nullable();
            $table->integer('urunDepo')->nullable();
            $table->decimal('fiyat')->nullable();
            $table->enum('fiyatBirim', ['1','2','3'])->default('1')->nullable();
            $table->enum('durum', ['0','1'])->default('1');
            $table->string('aciklama')->nullable();
            $table->integer('stok_marka')->nullable();
            $table->integer('stok_cihaz')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
