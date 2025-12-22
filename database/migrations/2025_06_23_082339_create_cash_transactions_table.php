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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('kid')->nullable();
            $table->integer('pid')->nullable();
            $table->integer('odemeYonu')->nullable();
            $table->integer('odemeSekli')->nullable();
            $table->integer('odemeTuru')->nullable();
            $table->integer('odemeDurum')->nullable();
            $table->decimal('fiyat')->nullable();
            $table->enum('fiyatBirim', ['1','2','3'])->nullable();
            $table->integer('taksit')->nullable();
            $table->enum('tarihSure', ['1','2','3','4'])->nullable();
            $table->string('aciklama')->nullable();
            $table->integer('personel')->nullable();
            $table->integer('servis')->nullable();
            $table->integer('tedarikci')->nullable();
            $table->integer('marka')->nullable();
            $table->integer('cihaz')->nullable();
            $table->integer('stokIslem')->nullable();
            $table->integer('servisIslem')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
