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
        Schema::create('stock_actions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('kid')->nullable();
            $table->integer('pid')->nullable();
            $table->integer('stokId')->nullable();
            $table->tinyInteger('islem')->nullable();
            $table->integer('tedarikci')->nullable();
            $table->integer('servisid')->nullable();
            $table->integer('depo')->nullable();
            $table->integer('adet')->nullable();
            $table->decimal('fiyat')->nullable();
            $table->enum('fiyatBirim', ['0','1','2'])->nullable();
            $table->integer('planId')->nullable();
            $table->integer('perStokId')->nullable();
            $table->integer('personel')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_actions');
    }
};
