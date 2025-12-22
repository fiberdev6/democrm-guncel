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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->integer('firma_id');
            $table->integer('personel_id');
            $table->enum('musteriTipi',['1', '2'])->default('1');
            $table->string('adSoyad')->nullable();
            $table->string('tel1')->nullable();
            $table->string('tel2')->nullable();
            $table->string('il')->nullable();
            $table->string('ilce')->nullable();
            $table->string('adres')->nullable();
            $table->string('tcNo')->nullable();
            $table->string('vergiNo')->nullable();
            $table->string('vergiDairesi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
