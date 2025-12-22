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
        Schema::create('ariza_kodlari', function (Blueprint $table) {
           $table->integer('id', true); // true = AUTO_INCREMENT
            $table->integer('marka_id');
            $table->integer('model_id');
            $table->string('kodu', 500);
            $table->string('baslik', 500);
            $table->text('aciklama');
            $table->enum('durum', ['0', '1'])->default('1');
            
            // Index'ler - performans için (opsiyonel)
            $table->index('marka_id');
            $table->index('model_id');
            $table->index('kodu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ariza_kodlari');
    }
};
