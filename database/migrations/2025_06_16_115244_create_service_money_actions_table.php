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
        Schema::create('service_money_actions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('kid')->nullable();
            $table->integer('pid')->nullable();
            $table->integer('servisid')->nullable();
            $table->integer('odemeSekli')->nullable();
            $table->integer('odemeDurum')->nullable();
            $table->decimal('fiyat')->nullable();
            $table->string('aciklama')->nullable();
            $table->tinyInteger('odemeYonu')->nullable();
            $table->integer('stokIslem')->nullable();
            $table->integer('planIslem')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_money_actions');
    }
};
