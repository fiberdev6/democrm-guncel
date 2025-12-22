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
        Schema::create('personel_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('firma_id')->nullable();
            $table->integer('kid')->nullable();      // firma veya tenant ID
            $table->integer('pid');                  // personel id
            $table->integer('stokid');               // stok id
            $table->integer('adet');                 // adet bilgisi
            $table->timestamp('tarih')->useCurrent(); // işlem tarihi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personel_stocks');
    }
};
