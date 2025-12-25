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
        Schema::create('incoming_calls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('servisKaynak')->nullable();
            $table->integer('marka')->nullable();
            $table->integer('kayitAlan')->nullable();
            $table->string('ariza')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_calls');
    }
};
