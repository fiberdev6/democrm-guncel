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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->integer('firma_id');
            $table->integer('personel_id');
            $table->integer('musteri_id');
            $table->decimal('toplam')->nullable();
            $table->decimal('kdvTutar')->nullable();
            $table->integer('kdv')->nullable();
            $table->decimal('genelToplam')->nullable();
            $table->string('toplamYazi')->nullable();
            $table->decimal('dovizKuru')->nullable();
            $table->text('aciklamalar')->nullable();
            $table->string('baslik1')->nullable();
            $table->string('baslik2')->nullable();
            $table->enum('durum', ['0','1'])->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
