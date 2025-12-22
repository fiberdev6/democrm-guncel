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
        Schema::create('modeller', function (Blueprint $table) {
            $table->integer('id', true); // true = AUTO_INCREMENT
            $table->integer('mid');
            $table->string('model', 500);
            $table->string('resimyol', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modeller');
    }
};
