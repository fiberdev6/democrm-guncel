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
        Schema::create('tevkifat_kodlari', function (Blueprint $table) {
            $table->id();
            $table->integer('kodu')->nullable();
            $table->string('adi')->nullable();
            $table->integer('orani')->nullable();
            $table->enum('durum', ['0', '1'])->default('1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tevkifat_kodlari');
    }
};
