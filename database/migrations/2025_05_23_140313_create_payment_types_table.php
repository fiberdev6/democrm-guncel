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
        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('kid')->nullable();
            $table->string('odemeTuru')->nullable();
            $table->string('cevaplar')->nullable();
            $table->enum('stok', ['0','1'])->default('0')->nullable();
            $table->enum('servis', ['0','1'])->default('0')->nullable();
            $table->enum('parca', ['0','1'])->default('0')->nullable();
            $table->enum('personel', ['0','1'])->default('0')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
