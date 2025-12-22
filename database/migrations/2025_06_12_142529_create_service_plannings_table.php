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
        Schema::create('service_plannings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('kid')->nullable();
            $table->integer('pid')->nullable();
            $table->integer('servisid')->nullable();
            $table->integer('gelenIslem')->nullable();
            $table->integer('gidenIslem')->nullable();
            $table->integer('tarihDurum')->nullable();
            $table->integer('tarihKontrol')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plannings');
    }
};
