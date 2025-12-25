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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ekleyen'); // ekleyen (kayıt eden kullanıcı)
            $table->unsignedBigInteger('personel'); // anket yapılan personel
            $table->unsignedBigInteger('servisid'); // servis ID
            $table->tinyInteger('soru1');
            $table->string('soru1Text', 500)->nullable();
            $table->tinyInteger('soru2');
            $table->string('soru2Text', 500)->nullable();
            $table->tinyInteger('soru3');
            $table->string('soru3Text', 500)->nullable();
            $table->tinyInteger('soru4');
            $table->string('soru4Text', 500)->nullable();
            $table->tinyInteger('soru5');
            $table->string('soru5Text', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
